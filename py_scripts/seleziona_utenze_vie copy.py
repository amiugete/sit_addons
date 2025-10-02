#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2021
# Roberto Marzocchi

'''
Script sulla falsariga di quello per gli ecopunti 
non parte da alberghi.base_ecopunti, ma direttamente dai codici via che vanno variati di volta in volta

Esegue le seguenti operazioni:

1) con l'elenco dei codici civici cerca le utenze domestiche e non domestiche su Oracle e produce due file excel

'''


#codici_via= '40500, 19420, 61980, 49860'

#file_csv ='elenco_vie_test2.txt'
#prefisso1 ='zona valpolcevera'
#mail = 'roberto.marzocchi@gmail.com'






import os,sys, getopt
import inspect, os.path
# da sistemare per Linux
import cx_Oracle


import xlsxwriter


import psycopg2

import datetime
import csv
import zipfile

currentdir = os.path.dirname(os.path.realpath(__file__))
parentdir = os.path.dirname(currentdir)
sys.path.append(parentdir)





from credenziali import *
#from credenziali import db, port, user, pwd, host, user_mail, pwd_mail, port_mail, smtp_mail



#libreria per gestione log
import logging

giorno_file=datetime.datetime.today().strftime('%Y%m%d%H%M%S')
path=os.path.dirname(sys.argv[0]) 
nome=os.path.basename(__file__).replace('.py','')
#tmpfolder=tempfile.gettempdir() # get the current temporary directory
logfile='{}/log/{}_{}.log'.format(path, giorno_file, nome)
#logfile='{0}/log/{1}.log'.format(path,nome)
errorfile='{0}/log/error_{1}.log'.format(path,nome)







# Create a custom logger
logging.basicConfig(
    level=logging.DEBUG,
    handlers=[
    ]
)

logger = logging.getLogger()

# Create handlers
c_handler = logging.FileHandler(filename=errorfile, encoding='utf-8', mode='w')
#f_handler = logging.StreamHandler()
f_handler = logging.FileHandler(filename=logfile, encoding='utf-8', mode='w')


c_handler.setLevel(logging.ERROR)
f_handler.setLevel(logging.DEBUG)


# Add handlers to the logger
logger.addHandler(c_handler)
logger.addHandler(f_handler)


cc_format = logging.Formatter('%(asctime)s\t%(levelname)s\t%(message)s')

c_handler.setFormatter(cc_format)
f_handler.setFormatter(cc_format)





# Libreria per invio mail
import email, smtplib, ssl
import mimetypes
from email.mime.multipart import MIMEMultipart
from email import encoders
from email.message import Message
from email.mime.audio import MIMEAudio
from email.mime.base import MIMEBase
from email.mime.image import MIMEImage
from email.mime.text import MIMEText
from invio_messaggio import *

# funzionde per restituire un dizionario
def makeDictFactory(cursor):
    columnNames = [d[0] for d in cursor.description]
    def createRow(*args):
        return dict(zip(columnNames, args))
    return createRow


def main(argv):
    #num_giorno=datetime.datetime.today().weekday()
    #giorno=datetime.datetime.today().strftime('%A')

    filename = inspect.getframeinfo(inspect.currentframe()).filename
    path     = os.path.dirname(os.path.abspath(filename))


    

    #giorno_file='{}_{}'.format(giorno_file, prefisso1.replace(' ', '_'))
    giorno_file=datetime.datetime.today().strftime('%Y%m%d%H%M%S')

    logger.info('Leggo gli input')
    try:
        #opts, args = getopt.getopt(argv,"hi:p:m:c:",["ifile=","prefix=", "mail="])
        opts, args = getopt.getopt(argv,"hi:p:c:",["ifile=","prefix="])
    except getopt.GetoptError:
        logger.error('seleziona_utenze_vie.py -i <inputfile> -p <prefisso>')
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print('seleziona_utenze_vie.py -i <inputfile> -o <outputfile>')
            sys.exit()
        elif opt in ("-i", "--ifile"):
            file_csv = arg
            logger.info('Input file = {}'.format(file_csv))
        elif opt in ("-p", "--prefix"):
            prefisso1 = arg
            logger.info('Prefisso file = {}'.format(prefisso1))
        elif opt in ("-ut", "--utenze"):
            utenze = arg
            logger.info('Utenze selezionate = {}'.format(utenze))
        #elif opt in ("-m", "--mail"):
        #    mail = arg
        #    logger.info('Mail cui inviare i dati = {}'.format(mail))
        elif opt in ("-c", "--consegne"):
            consegne = arg
            logger.info('Da inserire sul portale consegne (1) o no (0) (default 0) = {}'.format(consegne))


    consegne=int(consegne)
    logger.debug(consegne)

    #aggiorno il prefisso del file
    giorno_file='{}_{}'.format(giorno_file, prefisso1.replace(' ', '_'))
    
    # Leggo il file
    logger.info('Leggo il file CSV {}' . format(file_csv))

    with open(file_csv) as csv_file:
        csv_reader = csv.reader(csv_file, delimiter=',')
        line_count = 0
        for row in csv_reader:
            if line_count == 0:
                logger.debug(f'Column names are {", ".join(row)}')
                line_count += 1
            elif line_count==1:
                codici_via = '{}'.format(row[0])
                line_count += 1
            else: 
                codici_via = '{}, {}'.format(codici_via, row[0])
                line_count += 1
        logger.debug(f'Processed {line_count-1} lines.')
        logger.debug(codici_via)

    #exit()
    

    logger.info('Connessione al db')
    conn = psycopg2.connect(dbname=db,
                        port=port,
                        user=user,
                        password=pwd,
                        host=host)

    curr = conn.cursor()
    conn.autocommit = True

    if utenze == 'uted':
        query='''select n.cod_civico from geo.civici_neri n 
where cod_strada::integer in ({0})'''.format(codici_via)
        query2 = ''' SELECT v.nome, be.testo FROM
(select n.testo, n.cod_strada from geo.civici_neri n 
where cod_strada::integer in ({0})'''.format(codici_via)
    elif utenze == 'utend':
        query='''select n.cod_civico from geo.civici_rossi n 
where cod_strada::integer in ({0})'''.format(codici_via)
        query2 = '''select n.testo, n.cod_strada from geo.civici_rossi n 
where cod_strada::integer in ({0})) as be
join  topo.vie v 
ON v.id_via::integer = be.cod_strada::integer'''.format(codici_via)
    else:
        query='''select n.cod_civico from geo.civici_neri n 
where cod_strada::integer in ({0}) 
union 
select n.cod_civico from geo.civici_rossi n 
where cod_strada::integer in ({0})'''.format(codici_via)
        query2 = ''' SELECT v.nome, be.testo FROM
(select n.testo, n.cod_strada from geo.civici_neri n 
where cod_strada::integer in ({0}) 
union 
select n.testo, n.cod_strada from geo.civici_rossi n 
where cod_strada::integer in ({0})) as be
join  topo.vie v 
ON v.id_via::integer = be.cod_strada::integer'''.format(codici_via)
    
    try:
        curr.execute(query)
        lista_civici=curr.fetchall()
    except Exception as e:
        logger.error(e)


    #inizializzo gli array
    cod_civico=[]

           
    for vv in lista_civici:
        #logger.debug(vv[0])
        cod_civico.append(vv[0])

    curr.close()


    logger.info('Lista civici')
    curr2 = conn.cursor()
    
    try:
        curr2.execute(query2)
        lista_civici2=curr2.fetchall()
    except Exception as e:
        logger.error(e)


    # array che uso dopo quando devo inviare le mail
    nomi_files=[]
    files=[]

    nomi_files.append('elenco_vie.txt')
    #files.append('/var/www/html/utenze/file/elenco_vie.txt')
    files.append(file_csv)

    nome_file0="{0}_elenco_civici_completo.xlsx".format(giorno_file)
    file_civici="{0}/utenze/{1}".format(path,nome_file0)
    
    nomi_files.append(nome_file0)
    files.append(file_civici)

    
    workbook0 = xlsxwriter.Workbook(file_civici)
    w0 = workbook0.add_worksheet()

    w0.write(0, 0, 'id') 
    w0.write(0, 1, 'Nome_via')
    w0.write(0, 2, 'Civico')
    i=1
    for vv in lista_civici2:
        w0.write(i, 0, i) 
        w0.write(i, 1, vv[0])
        w0.write(i, 2, vv[1])
        i+=1
        

    workbook0.close()


    logger.info("Tentativo connessione ORACLE")
    # connessione Oracle
    #cx_Oracle.init_oracle_client(lib_dir=r"C:\oracle\instantclient_19_10")
    cx_Oracle.init_oracle_client()
    parametri_con='{}/{}@//{}:{}/{}'.format(user_strade,pwd_strade, host_uo,port_uo,service_uo)
    logger.debug(parametri_con)
    con = cx_Oracle.connect(parametri_con)
    logger.info("Versione ORACLE: {}".format(con.version))

    nome_file="{0}_utenze_domestiche.xlsx".format(giorno_file)
    nome_file2="{0}_utenze_nondomestiche.xlsx".format(giorno_file)
    nome_file3="{0}_civici_utenze_domestiche.xlsx".format(giorno_file)
    nome_file4="{0}_civici_utenze_nondomestiche.xlsx".format(giorno_file)
    
    if consegne == 1:
        nome_file5="{0}_file_IDEA.xlsx".format(giorno_file)
        nome_file6="{0}_file_portale_utenze.xlsx".format(giorno_file)
       
    
    file_domestiche="{0}/utenze/{1}".format(path,nome_file)
    file_nondomestiche="{0}/utenze/{1}".format(path,nome_file2)
    file_civdomestiche="{0}/utenze/{1}".format(path,nome_file3)
    file_civnondomestiche="{0}/utenze/{1}".format(path,nome_file4)
    if consegne == 1:
        file_idea="{0}/utenze/{1}".format(path,nome_file5)
        file_portale_utenze="{0}/utenze/{1}".format(path,nome_file6)

    if utenze == 'uted':
        nomi_files.append(nome_file)
        files.append(file_domestiche)
        nomi_files.append(nome_file3)
        files.append(file_civdomestiche)

        workbook = xlsxwriter.Workbook(file_domestiche)
        w = workbook.add_worksheet()

        w.write(0, 0, 'ID_UTENTE') 
        w.write(0, 1, 'PROGR_UTENZA') 
        w.write(0, 2, 'COGNOME') 
        w.write(0, 3, 'NOME')
        w.write(0, 4, 'COD_VIA') 
        w.write(0, 5, 'DESCR_VIA') 
        w.write(0, 6, 'CIVICO') 
        w.write(0, 7, 'LETTERA_CIVICO')
        w.write(0, 8, 'COLORE') 
        w.write(0, 9, 'SCALA') 
        w.write(0, 10, 'INTERNO') 
        w.write(0, 11, 'LETTERA_INTERNO')
        w.write(0, 12, 'CAP') 
        w.write(0, 13, 'UNITA_URBANISTICA') 
        w.write(0, 14, 'QUARTIERE') 
        w.write(0, 15, 'CIRCOSCRIZIONE')
        w.write(0, 16, 'ZONA') 
        w.write(0, 17, 'ABITAZIONE_DI_RESIDENZA') 
        w.write(0, 18, 'NUM_OCCUPANTI') 
        w.write(0, 19, 'DESCR_CATEGORIA')
        w.write(0, 20, 'DESCR_UTILIZZO') 



        logger.info('*****************************************************')
        logger.info('Utenze domestiche su strade')

        cur = con.cursor()
        query=''' SELECT ID_UTENTE, PROGR_UTENZA, COGNOME, NOME, COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
            UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, ABITAZIONE_DI_RESIDENZA, NUM_OCCUPANTI,
            DESCR_CATEGORIA, DESCR_UTILIZZO
            FROM STRADE.UTENZE_TIA_DOMESTICHE
            WHERE COD_VIA in ({})
            '''.format(codici_via)

        try: 
            lista_domestiche = cur.execute(query)
        except Exception as e:
            logger.error(query)
            logger.error(e)
            exit()
        

        i=1
        for rr in lista_domestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w.write(i, j, rr[j])
                j+=1
            i+=1

        cur.close()
        workbook.close()


        logger.info('*****************************************************')
        logger.info('Civici Utenze domestiche su strade')
        # civici domestiche
        workbook3 = xlsxwriter.Workbook(file_civdomestiche)
        w3 = workbook3.add_worksheet()

        w3.write(0, 0, 'COD_VIA') 
        w3.write(0, 1, 'DESCR_VIA') 
        w3.write(0, 2, 'CIVICO') 
        w3.write(0, 3, 'LETTERA_CIVICO')
        w3.write(0, 4, 'COLORE')



        cur3 = con.cursor()
        query='''SELECT DISTINCT COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE 
            FROM STRADE.UTENZE_TIA_DOMESTICHE
            WHERE COD_VIA in ({}) ORDER BY DESCR_VIA
            '''.format(codici_via) 
            

        #logger.debug(query)
        lista_civdomestiche = cur3.execute(query)

        i=1
        for rr in lista_civdomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w3.write(i, j, rr[j])
                j+=1
            i+=1

        cur3.close()
        workbook3.close()

    elif utenze == 'utend':
        nomi_files.append(nome_file2)
        files.append(file_nondomestiche)
        nomi_files.append(nome_file4)
        files.append(file_civnondomestiche)

        logger.info('*****************************************************')
        logger.info('Uenze non domestiche su strade')
        # non domestiche
        cur2 = con.cursor()

        workbook2 = xlsxwriter.Workbook(file_nondomestiche)
        w2 = workbook2.add_worksheet()


        w2.write(0, 0, 'ID_UTENTE') 
        w2.write(0, 1, 'PROGR_UTENZA') 
        w2.write(0, 2, 'NOMINATIVO') 
        w2.write(0, 3, 'CFISC_PARIVA')
        w2.write(0, 4, 'COD_VIA') 
        w2.write(0, 5, 'DESCR_VIA') 
        w2.write(0, 6, 'CIVICO') 
        #w2.write(0, 7, 'SUB_CIVICO')
        w2.write(0, 7, 'COLORE') 
        w2.write(0, 8, 'SCALA') 
        #w2.write(0, 10, 'PIANO') 
        w2.write(0, 9, 'INTERNO')
        w2.write(0, 10, 'LETTERA_INTERNO')
        w2.write(0, 11, 'CAP') 
        w2.write(0, 12, 'UNITA_URBANISTICA') 
        w2.write(0, 13, 'QUARTIERE') 
        w2.write(0, 14, 'CIRCOSCRIZIONE')
        w2.write(0, 15, 'ZONA')
        w2.write(0, 16, 'SUPERFICIE')
        w2.write(0, 17, 'NUM_OCCUPANTI')
        w2.write(0, 18, 'ABITAZIONE_DI_RESIDENZA') 
        w2.write(0, 19, 'DESCR_CATEGORIA')
        w2.write(0, 20, 'DESCR_UTILIZZO')



        query='''SELECT ID_UTENTE, PROGR_UTENZA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA,
    CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
    UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, SUPERFICIE, NUM_OCCUPANTI, ABITAZIONE_DI_RESIDENZA,  DESCR_CATEGORIA, DESCR_UTILIZZO
    FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
    WHERE COD_VIA in ({})
            '''.format(codici_via)

        lista_nondomestiche = cur2.execute(query)

        i=1
        for rr in lista_nondomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w2.write(i, j, rr[j])
                j+=1
            i+=1

        cur2.close()
        workbook2.close()


        logger.info('*****************************************************')
        logger.info('Civici Utenze Non domestiche su strade')
        # civici  non domestiche
        workbook4 = xlsxwriter.Workbook(file_civnondomestiche)
        w4 = workbook4.add_worksheet()

        w4.write(0, 0, 'COD_VIA') 
        w4.write(0, 1, 'DESCR_VIA') 
        w4.write(0, 2, 'CIVICO') 
        w4.write(0, 3, 'LETTERA_CIVICO')
        w4.write(0, 4, 'COLORE')



        cur4 = con.cursor()
        query=''' SELECT DISTINCT COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE 
            FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
            WHERE COD_VIA in ({}) ORDER BY DESCR_VIA
            '''.format(codici_via)

        #logger.debug(query)
        lista_civnondomestiche = cur4.execute(query)

        i=1
        for rr in lista_civnondomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w4.write(i, j, rr[j])
                j+=1
            i+=1

        cur4.close()
        workbook4.close()


    else:
        nomi_files.append(nome_file)
        files.append(file_domestiche)
        nomi_files.append(nome_file2)
        files.append(file_nondomestiche)
        nomi_files.append(nome_file3)
        files.append(file_civdomestiche)
        nomi_files.append(nome_file4)
        files.append(file_civnondomestiche)

        workbook = xlsxwriter.Workbook(file_domestiche)
        w = workbook.add_worksheet()

        w.write(0, 0, 'ID_UTENTE') 
        w.write(0, 1, 'PROGR_UTENZA') 
        w.write(0, 2, 'COGNOME') 
        w.write(0, 3, 'NOME')
        w.write(0, 4, 'COD_VIA') 
        w.write(0, 5, 'DESCR_VIA') 
        w.write(0, 6, 'CIVICO') 
        w.write(0, 7, 'LETTERA_CIVICO')
        w.write(0, 8, 'COLORE') 
        w.write(0, 9, 'SCALA') 
        w.write(0, 10, 'INTERNO') 
        w.write(0, 11, 'LETTERA_INTERNO')
        w.write(0, 12, 'CAP') 
        w.write(0, 13, 'UNITA_URBANISTICA') 
        w.write(0, 14, 'QUARTIERE') 
        w.write(0, 15, 'CIRCOSCRIZIONE')
        w.write(0, 16, 'ZONA') 
        w.write(0, 17, 'ABITAZIONE_DI_RESIDENZA') 
        w.write(0, 18, 'NUM_OCCUPANTI') 
        w.write(0, 19, 'DESCR_CATEGORIA')
        w.write(0, 20, 'DESCR_UTILIZZO') 



        logger.info('*****************************************************')
        logger.info('Utenze domestiche su strade')

        cur = con.cursor()
        query=''' SELECT ID_UTENTE, PROGR_UTENZA, COGNOME, NOME, COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
            UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, ABITAZIONE_DI_RESIDENZA, NUM_OCCUPANTI,
            DESCR_CATEGORIA, DESCR_UTILIZZO
            FROM STRADE.UTENZE_TIA_DOMESTICHE
            WHERE COD_VIA in ({})
            '''.format(codici_via)

        try: 
            lista_domestiche = cur.execute(query)
        except Exception as e:
            logger.error(query)
            logger.error(e)
            exit()
        

        i=1
        for rr in lista_domestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w.write(i, j, rr[j])
                j+=1
            i+=1

        cur.close()
        workbook.close()


        logger.info('*****************************************************')
        logger.info('Civici Utenze domestiche su strade')
        # civici domestiche
        workbook3 = xlsxwriter.Workbook(file_civdomestiche)
        w3 = workbook3.add_worksheet()

        w3.write(0, 0, 'COD_VIA') 
        w3.write(0, 1, 'DESCR_VIA') 
        w3.write(0, 2, 'CIVICO') 
        w3.write(0, 3, 'LETTERA_CIVICO')
        w3.write(0, 4, 'COLORE')



        cur3 = con.cursor()
        query='''SELECT DISTINCT COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE 
            FROM STRADE.UTENZE_TIA_DOMESTICHE
            WHERE COD_VIA in ({}) ORDER BY DESCR_VIA
            '''.format(codici_via) 
            

        #logger.debug(query)
        lista_civdomestiche = cur3.execute(query)

        i=1
        for rr in lista_civdomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w3.write(i, j, rr[j])
                j+=1
            i+=1

        cur3.close()
        workbook3.close()
        
        logger.info('*****************************************************')
        logger.info('Uenze non domestiche su strade')
        # non domestiche
        cur2 = con.cursor()

        workbook2 = xlsxwriter.Workbook(file_nondomestiche)
        w2 = workbook2.add_worksheet()


        w2.write(0, 0, 'ID_UTENTE') 
        w2.write(0, 1, 'PROGR_UTENZA') 
        w2.write(0, 2, 'NOMINATIVO') 
        w2.write(0, 3, 'CFISC_PARIVA')
        w2.write(0, 4, 'COD_VIA') 
        w2.write(0, 5, 'DESCR_VIA') 
        w2.write(0, 6, 'CIVICO') 
        #w2.write(0, 7, 'SUB_CIVICO')
        w2.write(0, 7, 'COLORE') 
        w2.write(0, 8, 'SCALA') 
        #w2.write(0, 10, 'PIANO') 
        w2.write(0, 9, 'INTERNO')
        w2.write(0, 10, 'LETTERA_INTERNO')
        w2.write(0, 11, 'CAP') 
        w2.write(0, 12, 'UNITA_URBANISTICA') 
        w2.write(0, 13, 'QUARTIERE') 
        w2.write(0, 14, 'CIRCOSCRIZIONE')
        w2.write(0, 15, 'ZONA')
        w2.write(0, 16, 'SUPERFICIE')
        w2.write(0, 17, 'NUM_OCCUPANTI')
        w2.write(0, 18, 'ABITAZIONE_DI_RESIDENZA') 
        w2.write(0, 19, 'DESCR_CATEGORIA')
        w2.write(0, 20, 'DESCR_UTILIZZO')



        query='''SELECT ID_UTENTE, PROGR_UTENZA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA,
    CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
    UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, SUPERFICIE, NUM_OCCUPANTI, ABITAZIONE_DI_RESIDENZA,  DESCR_CATEGORIA, DESCR_UTILIZZO
    FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
    WHERE COD_VIA in ({})
            '''.format(codici_via)

        lista_nondomestiche = cur2.execute(query)

        i=1
        for rr in lista_nondomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w2.write(i, j, rr[j])
                j+=1
            i+=1

        cur2.close()
        workbook2.close()


        logger.info('*****************************************************')
        logger.info('Civici Utenze Non domestiche su strade')
        # civici  non domestiche
        workbook4 = xlsxwriter.Workbook(file_civnondomestiche)
        w4 = workbook4.add_worksheet()

        w4.write(0, 0, 'COD_VIA') 
        w4.write(0, 1, 'DESCR_VIA') 
        w4.write(0, 2, 'CIVICO') 
        w4.write(0, 3, 'LETTERA_CIVICO')
        w4.write(0, 4, 'COLORE')



        cur4 = con.cursor()
        query=''' SELECT DISTINCT COD_VIA, DESCR_VIA,
            CIVICO, LETTERA_CIVICO, COLORE 
            FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
            WHERE COD_VIA in ({}) ORDER BY DESCR_VIA
            '''.format(codici_via)

        #logger.debug(query)
        lista_civnondomestiche = cur4.execute(query)

        i=1
        for rr in lista_civnondomestiche:
            j=0
            #logger.debug(len(rr))
            while j<len(rr):
                w4.write(i, j, rr[j])
                j+=1
            i+=1

        cur4.close()
        workbook4.close()


        if consegne ==1:
            nomi_files.append(nome_file5)
            files.append(file_idea)   
            nomi_files.append(nome_file6)
            files.append(file_portale_utenze)

            logger.info('*****************************************************')
            logger.info('FILE X IDEA')
            # civici  non domestiche
            workbook5 = xlsxwriter.Workbook(file_idea)
            date_format = workbook5.add_format({'font_size': 9, 'border':   1,
            'num_format': 'dd/mm/yyyy', 'valign': 'vcenter', 'center_across': True})
            title = workbook5.add_format({'bold': True,  'font_size': 9, 'border':   1, 'bg_color': '#F9FF33', 'valign': 'vcenter', 'center_across': True,'text_wrap': True})
            tc =  workbook5.add_format({'border':   1, 'font_size': 9, 'valign': 'vcenter', 'center_across': True, 'text_wrap': True})
            w5 = workbook5.add_worksheet()


            cur5 = con.cursor()
            query='''SELECT ID_UTENZA, CODICE_IMMOBILE, COD_INTERNO, COD_CIVICO, TIPO_UTENZA, 
                CATEGORIA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA, CIVICO,
                LETTERA_CIVICO, COLORE_CIVICO, SCALA, INTERNO,
                LETTERA_INTERNO, ZONA_MUNICIPIO, SUBZONA_QUARTIERE, DATA_CESSAZIONE
                FROM STRADE.UTENZE_ND_X_APP_IDEA 
                WHERE COD_VIA in ({}) '''.format(codici_via)



            try:
                cur5.execute(query)
                cur5.rowfactory = makeDictFactory(cur5)
                lista_idea=cur5.fetchall()
            except Exception as e:
                logger.error(query)
                logger.error(e)
            #logger.debug(query)
            #lista_civnondomestiche = cur4.execute(query)


            rr=1
            for pp in lista_idea:
                cc=0
                for key, value in pp.items():
                    #print(key)
                    #print(value)
                    w5.write(0, cc, key.replace('_', ' '), title)
                    if type(value) is str:
                        w5.write(rr, cc, value, tc)
                    elif type(value) is datetime :
                        w5.write(rr, cc, value, date_format)
                        #logger.debug(type(value))
                    elif type(value) is int :
                        w5.write(rr, cc, value, tc)
                    cc+=1
                rr+=1

            '''i=1
            for rr in lista_idea:
                j=0
                #logger.debug(len(rr))
                while j<len(rr):
                    w4.write(i, j, rr[j])
                    j+=1
                i+=1'''

            cur5.close()
            workbook5.close()


            
            logger.info('*****************************************************')
            logger.info('FILE X PORTALE UTENZE')
            # civici  non domestiche
            workbook6 = xlsxwriter.Workbook(file_portale_utenze)
            date_format = workbook6.add_format({'font_size': 9, 'border':   1,
            'num_format': 'dd/mm/yyyy', 'valign': 'vcenter', 'center_across': True})
            title = workbook6.add_format({'bold': True,  'font_size': 9, 'border':   1, 'bg_color': '#F9FF33', 'valign': 'vcenter', 'center_across': True,'text_wrap': True})
            #text_common 
            tc =  workbook6.add_format({'border':   1, 'font_size': 9, 'valign': 'vcenter', 'center_across': True, 'text_wrap': True})
            w6 = workbook6.add_worksheet()


            w6.set_column(0, 10, 15)




            cur6 = con.cursor()
            query=''' SELECT ID_UTENZA, RIFERIMENTO_FISCALE, RAGIONE_SOCIALE, COGNOME, NOME, STRADA, CIVICO, CIVICO_LETTERA,
                CIVICO_COLORE, SCALA, INTERNO, INTERNO_LETTERA, LOCALITA, CAP, MUNICIPIO, QUARTIERE, UNITA_URBANISTICA,
                TIPOLOGIA_UTENZA, SOTTOTIPOLOGIA_UTENZA, NOTES, FLAG_ANOMALIA, CAMPAGNE
                FROM STRADE.UTENZE_PORTALE_CONSEGNE_GE 
                WHERE STRADA in ({}) '''.format(codici_via)



            try:
                cur6.execute(query)
                # cur.rowfactory = makeNamedTupleFactory(cur)
                cur6.rowfactory = makeDictFactory(cur6)
                lista_idea=cur6.fetchall()
            except Exception as e:
                logger.error(query)
                logger.error(e)
            #logger.debug(query)
            #lista_civnondomestiche = cur4.execute(query)


            rr=1
            for pp in lista_idea:
                cc=0
                for key, value in pp.items():
                    #print(key)
                    #print(value)
                    w6.write(0, cc, key.replace('_', ' '), title)
                    
                    if type(value) is str:
                        w6.write(rr, cc, value, tc)
                    elif type(value) is datetime :
                        w6.write(rr, cc, value, date_format)
                        #logger.debug(type(value))
                    elif type(value) is int :
                        w6.write(rr, cc, value, tc)
                    cc+=1
                rr+=1

            '''i=1
            for rr in lista_idea:
                j=0
                #logger.debug(len(rr))
                while j<len(rr):
                    w4.write(i, j, rr[j])
                    j+=1
                i+=1'''

            cur6.close()
            workbook6.close()
        
    
    ###########################
    # Download file 
    ###########################

    logger.info("download file")


    # Creazione archivio ZIP con tutti i file generati
    zip_filename = "/tmp/utenze_via/{}_utenze.zip".format(giorno_file)
    with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for file_path, nome_file in zip(files, nomi_files):
            zipf.write(file_path, arcname=nome_file)
            logger.info("Aggiunto al ZIP: {}".format(nome_file))

    logger.info("Archivio creato: {}".format(zip_filename))

    with open("/tmp/utenze_via/last_zip.txt", "w") as f:
        f.write(zip_filename)


    # check se c_handller contiene almeno una riga 
    error_log_mail(errorfile, 'assterritorio@amiu.genova.it', os.path.basename(__file__), logger)

if __name__ == "__main__":
    main(sys.argv[1:])
#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2021
# Roberto Marzocchi

'''
Script per le attività da fare una volta prodotto etl.base_ecopunti  e verificato con l'ausilio del progetto QGIS apposito

Esegue le seguenti operazioni:

1) con l'elenco dei codici civici cerca le utenze domestiche e non domestiche su Oracle e produce due file excel

2) inserisce i dati nella cartella che serve a Laura Calvello per Saltax (etl.ecopunti)
'''


import os,sys, getopt
import inspect, os.path
# da sistemare per Linux
import cx_Oracle


import xlsxwriter


import psycopg2

import datetime
import zipfile

currentdir = os.path.dirname(os.path.realpath(__file__))
parentdir = os.path.dirname(currentdir)
sys.path.append(parentdir)

from credenziali import *
#from credenziali import db, port, user, pwd, host, user_mail, pwd_mail, port_mail, smtp_mail



#libreria per gestione log
import logging


#num_giorno=datetime.datetime.today().weekday()
#giorno=datetime.datetime.today().strftime('%A')
giorno_file=datetime.datetime.today().strftime('%Y%m%d%H%M%S')
path=os.path.dirname(sys.argv[0]) 
nome=os.path.basename(__file__).replace('.py','')

if (os.getuid()==33): # wwww-data
        if not os.path.exists('/tmp/utenze_area'):
            os.makedirs("/tmp/utenze_area")
        if not os.path.exists('/tmp/utenze_area/log'):
            os.makedirs("/tmp/utenze_area/log")
        logfile='/tmp/utenze_area/log/{2}_{1}.log'.format(path,nome, giorno_file)
        errorfile='/tmp/utenze_area/log/{2}_error_{1}.log'.format(path,nome, giorno_file)
else:
    # inizializzo i nomi dei file di log (per capire cosa stia succedendo)
    logfile='{0}/log/{2}_{1}.log'.format(path,nome, giorno_file)
    errorfile='{0}/log/{2}_error_{1}.log'.format(path,nome, giorno_file)


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


# ============================
# DIZIONARIO DELLE INTESTAZIONI  (da cabiare all'occorrenza)
# ============================

HEADERS = {
    "civici": {
        "1": [
            "id", "Nome_via", "Civico", "Note"
        ]
    },
    "domestiche": {
        "1": [
            "ID_UTENTE", "PROGR_UTENZA", "COGNOME", 
            "NOME", "COD_VIA", "DESCR_VIA", "CIVICO", 
            "LETTERA_CIVICO", "COLORE", "SCALA", 
            "INTERNO", "LETTER", "QUARTIERE", 
            "CIRCOSCRIZIONE", "ZONA", 
            "ABITAZIONE_DI_RESIDENZA", "NUM_OCCUPANTI", 
            "DESCR_CATEGORIA", "DESCR_UTILIZZO", "COD_INTERNO",
            "Presenza dato su Saltax?", "Chiave consegnata?"
        ],
        "2": [
            "COD_VIA", "DESCR_VIA", "CIVICO", 
            "LETTERA_CIVICO", "COLORE"
        ]
    },
    "nondomestiche": {
        "1": [
            "ID servizio COGE", "Desc servizio COGE", "Giorno",
            "ID Comune", "Comune", "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "Tipo mezzo", "Sportello", "Ore"
        ],
        "2": [
            "ID_UTENTE", "PROGR_UTENZA", "NOMINATIVO", 
            "CFISC_PARIVA", "COD_VIA", "DESCR_VIA", 
            "CIVICO", "COLORE", "SCALA", "INTERNO", 
            "LETTERA_INTERNO", "CAP", "UNITA_URBANISTICA", 
            "QUARTIERE", "CIRCOSCRIZIONE", "SUPERFICIE", 
            "DESCR_CATEGORIA", "DESCR_UTILIZZO", "COD_INTERNO", 
            "Presenza dato su Saltax?", "Chiave consegnata?"
        ]
    }
}

# ============================
# FUNZIONE PER SCRIVERE INTESTAZIONI
# ============================

def write_headers(ws, tipo, arg3, cell_format_title):
    """
    Scrive le intestazioni sul worksheet.
    
    :param ws: oggetto worksheet
    :param tipo: "personale" o "mezzi"
    :param arg3: stringa "1", "2" o "3"
    :param cell_format_title: formato celle intestazione
    """
    
    headers = HEADERS.get(tipo, {}).get(arg3, [])
    for col, title in enumerate(headers):
        ws.write(0, col, title, cell_format_title)   



def main(argv):

    logging.info('Il PID corrente è {0}'.format(os.getpid()))
    logging.info('Leggo gli input')
    try:
        #opts, args = getopt.getopt(argv,"hu:a:e:",["utenze=", "area=", "ecopunti="])
        opts, args = getopt.getopt(argv,"hu:a:e:",["utenze=", "area="])
    except getopt.GetoptError:
        logging.error('ecopunti_parte2.py  -u <utenze> -a <area>')
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print('ecopunti_parte2.py -u <utenze> -a <area>')
            sys.exit()
        elif opt in ("-u", "--utenze"):
            utenze = arg
            logging.info('Utenze selezionate = {}'.format(utenze))
        elif opt in ("-a", "--area"):
            area = arg
            logging.info('id area = {}'.format(area))
        '''
        elif opt in ("-e", "--ecopunti"):
            ecopunto = arg
            if ecopunto=='true':
                check_eco=1
            elif ecopunto=='false':
                check_eco=2
            else:
                print('ecopunti_parte2.py - m <mail> -a <area> -e <true/false>')
                sys.exit()
                
            logging.info('ecopunto = {}'.format(ecopunto))
        '''

    # carico i mezzi sul DB PostgreSQL
    logging.info('Connessione al db SIT')
    try:
        conn = psycopg2.connect(dbname=db,
                        port=port,
                        user=user,
                        password=pwd,
                        host=host)
        logging.info('Connessione riuscita')
    except Exception as e:
        logging.error(e)


    curr = conn.cursor()
    conn.autocommit = True



    logging.info('Connessione al db Saltax')
    try:
        conn_saltax = psycopg2.connect(dbname=db_saltax,
                        port=port,
                        user=user,
                        password=pwd,
                        host=host_saltax)
        logging.info('Connessione riuscita')
    except Exception as e:
        logging.error(e)

    query='''select cod_civico from etl.base_ecopunti'''
    
    try:
        curr.execute(query)
        lista_civici=curr.fetchall()
    except Exception as e:
        logging.error(e)


    #inizializzo gli array
    cod_civico=[]

           
    for vv in lista_civici:
        #logging.debug(vv[0])
        cod_civico.append(vv[0])

    curr.close()
    curr = conn.cursor()

    #if check_eco==1:
    #    query_area='''select replace(nome,' ', '_') as nome_area from etl.aree_ecopunti_4326 where id = %s'''
    #elif check_eco==2:
    query_area='''select replace(nome,' ', '_') as nome_area from etl.aree_4326 where id = %s'''
        
    
    try:
        curr.execute(query_area, (area,))
        n_area=curr.fetchall()
    except Exception as e:
        logging.error(e)
        logging.error(query_area)
        #logging.error(area)

    for aa in n_area:
        nome_area=aa[0]

    curr.close()

    '''
    if check_eco==1:
        oggetto_mail='Invio utenze ecopunti ({0})'.format(nome_area)
    elif check_eco==2:
        oggetto_mail='Invio utenze area {0}'.format(nome_area)
    '''

    logging.info('Lista civici')
    curr2 = conn.cursor()
    query2 = ''' SELECT v.nome, be.testo, be.note 
	FROM etl.base_ecopunti be 
    JOIN topo.vie v 
    ON v.id_via::integer = be.cod_strada::integer
    order by 1,2
    '''

    try:
        curr2.execute(query2)
        lista_civici2=curr2.fetchall()
    except Exception as e:
        logging.error(e)


    try: 
        nome_file0="{0}_{1}_elenco_civici_completo.xlsx".format(giorno_file, nome_area)
        file_civici="/tmp/utenze_area/{0}".format(nome_file0)
        
        
        workbook0 = xlsxwriter.Workbook(file_civici)
        w0 = workbook0.add_worksheet()

        #w0.write(0, 0, 'id') 
        #w0.write(0, 1, 'Nome_via')
        #w0.write(0, 2, 'Civico')
        #w0.write(0, 3, 'Note')
        write_headers(w0, "civici", "1", "")
        i=1
        for vv in lista_civici2:
            w0.write(i, 0, i) 
            w0.write(i, 1, vv[0])
            w0.write(i, 2, vv[1])
            w0.write(i, 3, vv[2])
            i+=1
            

        workbook0.close()
    except Exception as e:
        logging.error(e)


    # connessione Oracle
    #cx_Oracle.init_oracle_client(lib_dir=r"C:\oracle\instantclient_19_10")
    logging.info('Connessione a DB Oracle')
    cx_Oracle.init_oracle_client()
    parametri_con='{}/{}@//{}:{}/{}'.format(user_strade,pwd_strade, host_uo,port_uo,service_uo)
    logging.debug(parametri_con)
    con = cx_Oracle.connect(parametri_con)
    logging.info("Versione ORACLE: {}".format(con.version))

    logging.debug(len(cod_civico))
    #exit()
    # Array con i civici neri e rossi
    
    
    
    # creo una tabelle temporanea
    
    check_if_exist='''select count(*)
from all_objects
where object_type in ('TABLE','VIEW')
and object_name = 'CIV_TMP' '''
    cur = con.cursor()
    cc=cur.execute(check_if_exist)
    
    for c in cc:
        check=c[0]
    
    print(check)
    cur.close()

    if check==1:
        truncate='''TRUNCATE TABLE STRADE.CIV_TMP'''
        cur = con.cursor()
        cur.execute(truncate)
        con.commit()
        cur.close()
    else:
        create_table='''CREATE TABLE STRADE.CIV_TMP (
        COD_CIVICO VARCHAR2(11) NULL
        )'''
        cur = con.cursor()
        cur.execute(create_table)
        con.commit()
        cur.close()
    
    
    cur = con.cursor()
    i=0
    while i< len(cod_civico):
        insert_query='''insert into STRADE.CIV_TMP cod_civico values(:cc)'''
        cur.execute(insert_query, (cod_civico[i],) )
        if i == 0:
            civ= ''' ('{}' '''.format(cod_civico[i])
        else:
             civ= '''{} , '{}' '''.format(civ, cod_civico[i])
        i+=1
    civ= '''{})'''.format(civ)
    con.commit()
    cur.close()



    nome_file="{0}_{1}_utenze_domestiche.xlsx".format(giorno_file, nome_area)
    nome_file2="{0}_{1}_utenze_nondomestiche.xlsx".format(giorno_file,nome_area)
    nome_file3="{0}_{1}_civici_utenze_domestiche.xlsx".format(giorno_file, nome_area)
    nome_file4="{0}_{1}_civici_utenze_nondomestiche.xlsx".format(giorno_file, nome_area)
    file_domestiche="/tmp/utenze_area/{0}".format(nome_file)
    file_nondomestiche="/tmp/utenze_area/{0}".format(nome_file2)
    file_civdomestiche="/tmp/utenze_area/{0}".format(nome_file3)
    file_civnondomestiche="/tmp/utenze_area/{0}".format(nome_file4)
    


    # array che uso dopo quando devo inviare le mail
    nomi_files=[]
    files=[]

    
    if utenze == 'uted':
        nomi_files.append(nome_file0)
        files.append(file_civici)

        nomi_files.append(nome_file)
        files.append(file_domestiche)

        nomi_files.append(nome_file3)
        files.append(file_civdomestiche)
    elif utenze == 'utend':
        nomi_files.append(nome_file0)
        files.append(file_civici)

        nomi_files.append(nome_file2)
        files.append(file_nondomestiche)

        nomi_files.append(nome_file4)
        files.append(file_civnondomestiche)
    else:
        nomi_files.append(nome_file0)
        files.append(file_civici)

        nomi_files.append(nome_file)
        files.append(file_domestiche)

        nomi_files.append(nome_file3)
        files.append(file_civdomestiche)

        nomi_files.append(nome_file2)
        files.append(file_nondomestiche)

        nomi_files.append(nome_file4)
        files.append(file_civnondomestiche)


    workbook = xlsxwriter.Workbook(file_domestiche)
    w = workbook.add_worksheet()



    """     
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
    w.write(0, 21, 'COD_INTERNO')
    w.write(0, 22, 'Presenza dato su Saltax?')
    w.write(0, 23, 'Chiave consegnata?') 
    """


    logging.info('*****************************************************')
    logging.info('Utenze domestiche su strade')

    cur = con.cursor()
   
    

    #exit()
    query=''' SELECT ID_UTENTE, PROGR_UTENZA, COGNOME, NOME, COD_VIA, DESCR_VIA,
        CIVICO, LETTERA_CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
        UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, ABITAZIONE_DI_RESIDENZA, NUM_OCCUPANTI, DESCR_CATEGORIA, DESCR_UTILIZZO, COD_INTERNO
        FROM STRADE.UTENZE_TIA_DOMESTICHE
        WHERE COD_CIVICO in (SELECT COD_CIVICO FROM STRADE.CIV_TMP )
        '''
    try:
        lista_domestiche = cur.execute(query)
    except Exception as e:
        logging.error(e)
    
    try: 
        write_headers(w, "domestiche", "1", "")
        i=1
        for rr in lista_domestiche:
            j=0
            #logging.debug(len(rr))
            while j<len(rr):
                w.write(i, j, rr[j])
                j+=1
            query_saltax='''select * from ecopunti_xatlas_key 
                where pper ={0} and cod_interno = '{1}'
                and data_attivazione_utenza is not null 
                and data_cessazione_utenza is null '''.format(rr[0],rr[19])
            #print(query_saltax)
            cur_saltax= conn_saltax.cursor()
            cur_saltax.execute(query_saltax)
            presente_saltax=cur_saltax.fetchall()
            if len(presente_saltax)>0:
                w.write(i, j, 'S')
            else:
                w.write(i, j, 'N')
            j+=1
            query_saltax1='''select * from ecopunti_xatlas_key 
                where pper ={0} and cod_interno = '{1}'
                and data_attivazione_utenza is not null 
                and data_cessazione_utenza is null 
                and data_consegna is not null'''.format(rr[0],rr[19])
            #print(query_saltax1)
            cur_saltax.execute(query_saltax1)
            consegnato_saltax=cur_saltax.fetchall()
            if len(consegnato_saltax)>0:
                w.write(i, j, 'Chiave già consegnata')
            cur_saltax.close()
            j+=1
            i+=1

        cur.close()
        workbook.close()
    except Exception as e:
        logging.error(e)


    # civici domestiche
    workbook3 = xlsxwriter.Workbook(file_civdomestiche)
    w3 = workbook3.add_worksheet()
    """
    w3.write(0, 0, 'COD_VIA') 
    w3.write(0, 1, 'DESCR_VIA') 
    w3.write(0, 2, 'CIVICO') 
    w3.write(0, 3, 'LETTERA_CIVICO')
    w3.write(0, 4, 'COLORE')
    """
    cur3 = con.cursor()
    query=''' SELECT DISTINCT COD_VIA, DESCR_VIA,
        CIVICO, LETTERA_CIVICO, COLORE 
        FROM STRADE.UTENZE_TIA_DOMESTICHE
        WHERE COD_CIVICO in (SELECT COD_CIVICO FROM STRADE.CIV_TMP) ORDER BY DESCR_VIA
        '''
    #logging.debug(query)
    lista_civdomestiche = cur3.execute(query)

    write_headers(w3, "domestiche", "2", "")
    i=1
    for rr in lista_civdomestiche:
        j=0
        #logging.debug(len(rr))
        while j<len(rr):
            w3.write(i, j, rr[j])
            j+=1
        i+=1

    cur3.close()
    workbook3.close()

    logging.info('*****************************************************')
    logging.info('Utenze non domestiche su strade')
    # non domestiche
    cur2 = con.cursor()

    workbook2 = xlsxwriter.Workbook(file_nondomestiche)
    w2 = workbook2.add_worksheet()

    """
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
    w2.write(0, 15, 'SUPERFICIE') 
    w2.write(0, 16, 'DESCR_CATEGORIA')
    w2.write(0, 17, 'DESCR_UTILIZZO')
    w2.write(0, 18, 'COD_INTERNO')
    w2.write(0, 19, 'Presenza dato su Saltax?')
    w2.write(0, 20, 'Chiave consegnata?')
    """
  
    
    query='''SELECT ID_UTENTE, PROGR_UTENZA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA,
CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE,  SUPERFICIE, DESCR_CATEGORIA, DESCR_UTILIZZO, COD_INTERNO
FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
WHERE COD_CIVICO in (SELECT COD_CIVICO FROM STRADE.CIV_TMP )'''
    lista_nondomestiche = cur2.execute(query)

    write_headers(w2, "nondomestiche", "1", "")

    i=1
    for rr in lista_nondomestiche:
        j=0
        #logging.debug(len(rr))
        while j<len(rr):
            w2.write(i, j, rr[j])
            j+=1
        query_saltax='''select * from ecopunti_xatlas_key 
            where pper ={0} and cod_interno = '{1}'
            and data_attivazione_utenza is not null 
            and data_cessazione_utenza is null '''.format(rr[0], rr[18])
        #print(query_saltax)
        cur_saltax= conn_saltax.cursor()
        cur_saltax.execute(query_saltax)
        presente_saltax=cur_saltax.fetchall()
        if len(presente_saltax)>0:
            w2.write(i, j, 'S')
        else:
            w2.write(i, j, 'N')
        j+=1
        query_saltax1='''select * from ecopunti_xatlas_key 
            where pper ={0} and cod_interno = '{1}'
            and data_attivazione_utenza is not null 
            and data_cessazione_utenza is null 
            and data_consegna is not null'''.format(rr[0],rr[18])
        #print(query_saltax1)
        cur_saltax.execute(query_saltax1)
        consegnato_saltax=cur_saltax.fetchall()
        if len(consegnato_saltax)>0:
            w2.write(i, j, 'Chiave già consegnata')
        cur_saltax.close()
        i+=1

    cur2.close()
    workbook2.close()



    # civici  non domestiche
    workbook4 = xlsxwriter.Workbook(file_civnondomestiche)
    w4 = workbook4.add_worksheet()
    """
    w4.write(0, 0, 'COD_VIA') 
    w4.write(0, 1, 'DESCR_VIA') 
    w4.write(0, 2, 'CIVICO') 
    w4.write(0, 3, 'LETTERA_CIVICO')
    w4.write(0, 4, 'COLORE')
    """


    cur4 = con.cursor()
    
    
    query=''' SELECT DISTINCT COD_VIA, DESCR_VIA,
        CIVICO, LETTERA_CIVICO, COLORE 
        FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
        WHERE COD_CIVICO in (SELECT COD_CIVICO FROM STRADE.CIV_TMP) ORDER BY DESCR_VIA''' 

    #logging.debug(query)
    lista_civnondomestiche = cur4.execute(query)

    write_headers(w4, "nondomestiche", "2", "")
    i=1
    for rr in lista_civnondomestiche:
        j=0
        #logging.debug(len(rr))
        while j<len(rr):
            w4.write(i, j, rr[j])
            j+=1
        i+=1

    cur4.close()
    workbook4.close()





    ###########################
    # Download file 
    ###########################

    logger.info("download file")

    logger.info(len(nomi_files))

    # Creazione archivio ZIP con tutti i file generati
    zip_filename = "/tmp/utenze_area/{}_utenze.zip".format(giorno_file)
    with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for file_path, nome_file in zip(files, nomi_files):
            zipf.write(file_path, arcname=nome_file)
            logger.info("Aggiunto al ZIP: {}".format(nome_file))

    logger.info("Archivio creato: {}".format(zip_filename))

    #if check_eco == 1:
    #    with open("/tmp/utenze_area/last_zip_eco.txt", "w") as f:
    #        f.write(zip_filename)
    #else:
    with open("/tmp/utenze_area/last_zip.txt", "w") as f:
        f.write(zip_filename)
    


    logging.info("CHIUSURA CONNESSIONI DB APERTE")
    conn.close()
    con.close()
    conn_saltax.close()
    


if __name__ == "__main__":
    main(sys.argv[1:])
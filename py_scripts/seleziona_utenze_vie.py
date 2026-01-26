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



if (os.getuid()==33): # wwww-data
        if not os.path.exists('/tmp/utenze_via'):
            os.makedirs("/tmp/utenze_via")
        if not os.path.exists('/tmp/utenze_via/log'):
            os.makedirs("/tmp/utenze_via/log")
        logfile='/tmp/utenze_via/log/{2}_{1}.log'.format(path,nome, giorno_file)
        errorfile='/tmp/utenze_via/log/{2}_error_{1}.log'.format(path,nome, giorno_file)
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

# funzionde per restituire un dizionario
def makeDictFactory(cursor):
    columnNames = [d[0] for d in cursor.description]
    def createRow(*args):
        return dict(zip(columnNames, args))
    return createRow

def write_excel_from_query(con, file_path, headers, query, logger):
    """Esegue una query e scrive i risultati in un file Excel."""
    workbook = xlsxwriter.Workbook(file_path)
    sheet = workbook.add_worksheet()

    # Scrivo intestazioni
    for col, header in enumerate(headers):
        sheet.write(0, col, header)

    cur = con.cursor()
    try:
        rows = cur.execute(query)
    except Exception as e:
        logger.error(query)
        logger.error(e)
        cur.close()
        workbook.close()
        return

    # Scrivo righe
    for i, row in enumerate(rows, start=1):
        for j, value in enumerate(row):
            sheet.write(i, j, value)

    cur.close()
    workbook.close()
    logger.info(f"Creato file: {file_path}")

def write_excel_from_dict(con, file_path, query, logger):
    """Esegue una query con rowfactory=dict e scrive i risultati in un file Excel con formattazione."""
    workbook = xlsxwriter.Workbook(file_path)
    sheet = workbook.add_worksheet()

    date_format = workbook.add_format({
        'font_size': 9, 'border': 1,
        'num_format': 'dd/mm/yyyy',
        'valign': 'vcenter', 'center_across': True
    })
    title = workbook.add_format({
        'bold': True, 'font_size': 9, 'border': 1,
        'bg_color': '#F9FF33', 'valign': 'vcenter',
        'center_across': True, 'text_wrap': True
    })
    cell_fmt = workbook.add_format({
        'border': 1, 'font_size': 9,
        'valign': 'vcenter', 'center_across': True,
        'text_wrap': True
    })

    cur = con.cursor()
    try:
        cur.execute(query)
        cur.rowfactory = makeDictFactory(cur)
        rows = cur.fetchall()
    except Exception as e:
        logger.error(query)
        logger.error(e)
        cur.close()
        workbook.close()
        return

    # Scrivo intestazioni dinamiche
    if rows:
        for col, key in enumerate(rows[0].keys()):
            sheet.write(0, col, key.replace('_', ' '), title)

    # Scrivo dati
    for row_idx, row in enumerate(rows, start=1):
        for col, (key, value) in enumerate(row.items()):
            if isinstance(value, str):
                sheet.write(row_idx, col, value, cell_fmt)
            elif isinstance(value, datetime):
                sheet.write(row_idx, col, value, date_format)
            else:
                sheet.write(row_idx, col, value, cell_fmt)

    cur.close()
    workbook.close()
    logger.info(f"Creato file: {file_path}")

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
        opts, args = getopt.getopt(argv,"hi:p:u:c:",["ifile=","prefix=","utenze="])
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
        elif opt in ("-u", "--utenze"):
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
    logger.info('Prefisso {}' . format(giorno_file))

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
        query2 = '''SELECT v.nome, be.testo FROM
(select n.testo, n.cod_strada from geo.civici_neri n 
where cod_strada::integer in ({0})) as be
join  topo.vie v ON v.id_via::integer = be.cod_strada::integer'''.format(codici_via)
    elif utenze == 'utend':
        query='''select n.cod_civico from geo.civici_rossi n 
where cod_strada::integer in ({0})'''.format(codici_via)
        query2 = '''SELECT v.nome, be.testo FROM
(select n.testo, n.cod_strada from geo.civici_rossi n 
where cod_strada::integer in (6300)) as be
join  topo.vie v ON v.id_via::integer = be.cod_strada::integer'''.format(codici_via)
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

    logger.info(query)
    logger.info(query2)
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
    file_civici="/tmp/utenze_via/{0}".format(nome_file0)
    
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

    nome_file = f"{giorno_file}_utenze_domestiche.xlsx"
    nome_file1="{0}_abitanti_x_via.xlsx".format(giorno_file)

    nome_file2 = f"{giorno_file}_utenze_nondomestiche.xlsx"
    nome_file3 = f"{giorno_file}_civici_utenze_domestiche.xlsx"
    nome_file4 = f"{giorno_file}_civici_utenze_nondomestiche.xlsx"

    if consegne == 1:
        nome_file5 = f"{giorno_file}_file_IDEA.xlsx"
        nome_file6 = f"{giorno_file}_file_portale_utenze.xlsx"

    file_domestiche = f"/tmp/utenze_via/{nome_file}"
    file_abitanti = f"/tmp/utenze_via/{nome_file1}"
    file_nondomestiche = f"/tmp/utenze_via/{nome_file2}"
    file_civdomestiche = f"/tmp/utenze_via/{nome_file3}"
    file_civnondomestiche = f"/tmp/utenze_via/{nome_file4}"

    if consegne == 1:
        file_idea = f"/tmp/utenze_via/{nome_file5}"
        file_portale_utenze = f"/tmp/utenze_via/{nome_file6}"

    # --- CASE: utenze domestiche ---
    if utenze == 'uted':
        nomi_files += [nome_file, nome_file3, nome_file1]
        files += [file_domestiche, file_civdomestiche, file_abitanti]

        logger.info("Utenze domestiche su strade")
        write_excel_from_query(con, file_domestiche,
            ['ID_UTENTE','PROGR_UTENZA','COGNOME','NOME','COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO',
            'COLORE','SCALA','INTERNO','LETTERA_INTERNO','CAP','UNITA_URBANISTICA','QUARTIERE','CIRCOSCRIZIONE',
            'ZONA','ABITAZIONE_DI_RESIDENZA','NUM_OCCUPANTI','DESCR_CATEGORIA','DESCR_UTILIZZO'],
            f'''SELECT ID_UTENTE, PROGR_UTENZA, COGNOME, NOME, COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
                UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, ABITAZIONE_DI_RESIDENZA, NUM_OCCUPANTI,
                DESCR_CATEGORIA, DESCR_UTILIZZO
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via})''', logger)



        logger.info("Civici Utenze domestiche")
        write_excel_from_query(con, file_civdomestiche,
            ['COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO','COLORE'],
            f'''SELECT DISTINCT COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via}) ORDER BY DESCR_VIA''', logger)



        logger.info("Abitanti strade")
        write_excel_from_query(con, file_abitanti,
            ['COD_VIA','DESCR_VIA','UTENZE_DOM','OCCUPANTI'],
            f'''SELECT COD_VIA, DESCR_VIA,
                count(DISTINCT id_utente) as  UTENZE_DOM , sum(num_occupanti) AS OCCUPANTI
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via})
                GROUP BY COD_VIA, DESCR_VIA
                ORDER BY DESCR_VIA
                ''', logger)
        
        
    # --- CASE: utenze non domestiche ---
    elif utenze == 'utend':
        nomi_files += [nome_file2, nome_file4]
        files += [file_nondomestiche, file_civnondomestiche]

        logger.info("Utenze non domestiche su strade")
        write_excel_from_query(con, file_nondomestiche,
            ['ID_UTENTE','PROGR_UTENZA','NOMINATIVO','CFISC_PARIVA','COD_VIA','DESCR_VIA','CIVICO',
            'COLORE','SCALA','INTERNO','LETTERA_INTERNO','CAP','UNITA_URBANISTICA','QUARTIERE','CIRCOSCRIZIONE',
            'ZONA','SUPERFICIE','NUM_OCCUPANTI','ABITAZIONE_DI_RESIDENZA','DESCR_CATEGORIA','DESCR_UTILIZZO'],
            f'''SELECT ID_UTENTE, PROGR_UTENZA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA,
                CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
                UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, SUPERFICIE, NUM_OCCUPANTI,
                ABITAZIONE_DI_RESIDENZA, DESCR_CATEGORIA, DESCR_UTILIZZO
                FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
                WHERE COD_VIA in ({codici_via})''', logger)

        logger.info("Civici Utenze non domestiche")
        write_excel_from_query(con, file_civnondomestiche,
            ['COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO','COLORE'],
            f'''SELECT DISTINCT COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE
                FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
                WHERE COD_VIA in ({codici_via}) ORDER BY DESCR_VIA''', logger)

    # --- CASE: entrambi ---
    else:
        nomi_files += [nome_file1, nome_file, nome_file2, nome_file3, nome_file4]
        files += [file_abitanti, file_domestiche, file_nondomestiche, file_civdomestiche, file_civnondomestiche]


        logger.info("Abitanti strade")
        write_excel_from_query(con, file_abitanti,
            ['COD_VIA','DESCR_VIA','UTENZE_DOM','OCCUPANTI'],
            f'''SELECT COD_VIA, DESCR_VIA,
                count(DISTINCT id_utente) UTENZE_DOM , sum(num_occupanti) AS OCCUPANTI
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via})
                GROUP BY COD_VIA, DESCR_VIA
                ORDER BY DESCR_VIA
                ''', logger)

        # Domestiche
        logger.info("Utenze domestiche")
        write_excel_from_query(con, file_domestiche,
            ['ID_UTENTE','PROGR_UTENZA','COGNOME','NOME','COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO',
            'COLORE','SCALA','INTERNO','LETTERA_INTERNO','CAP','UNITA_URBANISTICA','QUARTIERE','CIRCOSCRIZIONE',
            'ZONA','ABITAZIONE_DI_RESIDENZA','NUM_OCCUPANTI','DESCR_CATEGORIA','DESCR_UTILIZZO'],
            f'''SELECT ID_UTENTE, PROGR_UTENZA, COGNOME, NOME, COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
                UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, ABITAZIONE_DI_RESIDENZA, NUM_OCCUPANTI,
                DESCR_CATEGORIA, DESCR_UTILIZZO
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via})''', logger)

        logger.info("Civici Utenze domestiche")
        write_excel_from_query(con, file_civdomestiche,
            ['COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO','COLORE'],
            f'''SELECT DISTINCT COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE
                FROM STRADE.UTENZE_TIA_DOMESTICHE
                WHERE COD_VIA in ({codici_via}) ORDER BY DESCR_VIA''', logger)

        # Non domestiche
        logger.info("Utenze non domestiche")
        write_excel_from_query(con, file_nondomestiche,
            ['ID_UTENTE','PROGR_UTENZA','NOMINATIVO','CFISC_PARIVA','COD_VIA','DESCR_VIA','CIVICO',
            'COLORE','SCALA','INTERNO','LETTERA_INTERNO','CAP','UNITA_URBANISTICA','QUARTIERE','CIRCOSCRIZIONE',
            'ZONA','SUPERFICIE','NUM_OCCUPANTI','ABITAZIONE_DI_RESIDENZA','DESCR_CATEGORIA','DESCR_UTILIZZO'],
            f'''SELECT ID_UTENTE, PROGR_UTENZA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA,
                CIVICO, COLORE, SCALA, INTERNO, LETTERA_INTERNO, CAP, 
                UNITA_URBANISTICA, QUARTIERE, CIRCOSCRIZIONE, ZONA, SUPERFICIE, NUM_OCCUPANTI,
                ABITAZIONE_DI_RESIDENZA, DESCR_CATEGORIA, DESCR_UTILIZZO
                FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
                WHERE COD_VIA in ({codici_via})''', logger)

        logger.info("Civici Utenze non domestiche")
        write_excel_from_query(con, file_civnondomestiche,
            ['COD_VIA','DESCR_VIA','CIVICO','LETTERA_CIVICO','COLORE'],
            f'''SELECT DISTINCT COD_VIA, DESCR_VIA,
                CIVICO, LETTERA_CIVICO, COLORE
                FROM STRADE.UTENZE_TIA_NON_DOMESTICHE
                WHERE COD_VIA in ({codici_via}) ORDER BY DESCR_VIA''', logger)

        # --- File aggiuntivi (IDEA e PORTALE) ---
        if consegne == 1:
            nomi_files += [nome_file5, nome_file6]
            files += [file_idea, file_portale_utenze]

            logger.info("FILE X IDEA")
            write_excel_from_dict(con, file_idea,
                f'''SELECT ID_UTENZA, CODICE_IMMOBILE, COD_INTERNO, COD_CIVICO, TIPO_UTENZA, 
                    CATEGORIA, NOMINATIVO, CFISC_PARIVA, COD_VIA, DESCR_VIA, CIVICO,
                    LETTERA_CIVICO, COLORE_CIVICO, SCALA, INTERNO,
                    LETTERA_INTERNO, ZONA_MUNICIPIO, SUBZONA_QUARTIERE, DATA_CESSAZIONE
                    FROM STRADE.UTENZE_ND_X_APP_IDEA 
                    WHERE COD_VIA in ({codici_via})''', logger)

            logger.info("FILE X PORTALE UTENZE")
            write_excel_from_dict(con, file_portale_utenze,
                f'''SELECT ID_UTENZA, RIFERIMENTO_FISCALE, RAGIONE_SOCIALE, COGNOME, NOME, STRADA, CIVICO, CIVICO_LETTERA,
                    CIVICO_COLORE, SCALA, INTERNO, INTERNO_LETTERA, LOCALITA, CAP, MUNICIPIO, QUARTIERE, UNITA_URBANISTICA,
                    TIPOLOGIA_UTENZA, SOTTOTIPOLOGIA_UTENZA, NOTES, FLAG_ANOMALIA, CAMPAGNE
                    FROM STRADE.UTENZE_PORTALE_CONSEGNE_GE 
                    WHERE STRADA in ({codici_via})''', logger)
        
    
    ###########################
    # Download file 
    ###########################

    logger.info("download file")

    logger.info(len(nomi_files))


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
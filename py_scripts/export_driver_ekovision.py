#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2025
# Roberto Marzocchi

'''
Lo script esporta un file excel con i driver ekovision
'''

import os, sys, re  # ,shutil,glob
import inspect, os.path

import psycopg2
import cx_Oracle


import xlsxwriter

import datetime

# il file con le credenziali per ovvie ragioni non è su github
from credenziali import *


#import requests

import logging


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



#cerco la directory corrente
currentdir = os.path.dirname(os.path.realpath(__file__))
parentdir = os.path.dirname(currentdir)
sys.path.append(parentdir)

filename = inspect.getframeinfo(inspect.currentframe()).filename

#inizializzo la variabile path
path=currentdir

# nome dello script python
nome=os.path.basename(__file__).replace('.py','')



giorno_file=datetime.datetime.today().strftime('%Y%m%d_%H%M%S')


if (os.getuid()==33): # wwww-data
        if not os.path.exists('/tmp/driver_eko'):
            os.makedirs("/tmp/driver_eko")
        if not os.path.exists('/tmp/driver_eko/log'):
            os.makedirs("/tmp/driver_eko/log")
        logfile='/tmp/driver_eko/log/{2}_{1}.log'.format(path,nome, giorno_file)
        errorfile='/tmp/driver_eko/log/{2}_error_{1}.log'.format(path,nome, giorno_file)
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


####################################################################################################
# FUNZIONI INVIO MAIL (prese da invio_messaggio.py su altro repository)

mail_footer='''<hr>Questa mail è stata creata in automatico. 
            In caso di dubbi non rispondere alla presente mail ma scrivere a
            <a href="mailto:AssTerritorio@amiu.genova.it">AssTerritorio@amiu.genova.it</a>.'''



    

# ============================
# DIZIONARIO DELLE INTESTAZIONI  (da cabiare all'occorrenza)
# ============================

HEADERS = {
    "personale": {
        "1": [
            "ID servizio COGE", "Desc servizio COGE", "Mese",
            "ID Comune", "Comune", "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "ID UO Lavoro", "Desc UO Lavoro",
            "Mansione", "Ore"
        ],
        "2": [
            "ID servizio", "Desc servizio",
            "ID servizio COGE", "Desc servizio COGE",
            "Mese", "ID Comune", "Comune",
            "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "ID UO Lavoro", "Desc UO Lavoro",
            "Mansione", "Ore"
        ],
        "3": [
            "ID percorso", "Desc percorso",
            "ID servizio", "Desc servizio",
            "ID servizio COGE", "Desc servizio COGE",
            "Mese", "ID Comune", "Comune",
            "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "ID UO Lavoro", "Desc UO Lavoro",
            "Mansione", "Ore"
        ]
    },
    "mezzi": {
        "1": [
            "ID servizio COGE", "Desc servizio COGE", "Giorno",
            "ID Comune", "Comune", "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "Tipo mezzo", "Sportello", "Ore"
        ],
        "2": [
            "ID servizio", "Desc servizio",
            "ID servizio COGE", "Desc servizio COGE",
            "Giorno", "ID Comune", "Comune",
            "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "Tipo mezzo", "Sportello", "Ore"
        ],
        "3": [
            "ID percorso", "Desc percorso",
            "ID servizio", "Desc servizio",
            "ID servizio COGE", "Desc servizio COGE",
            "Giorno", "ID Comune", "Comune",
            "ID Municipio", "Municipio",
            "ID UO", "Desc UO", "Tipo mezzo", "Sportello", "Ore"
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


############################################################################################
# funzione per copiare formato


def copy_format(book, fmt):
    '''
    new_format = copy_format(workbook, initial_format)
    '''
    properties = [f[4:] for f in dir(fmt) if f[0:4] == 'set_']
    dft_fmt = book.add_format()
    return book.add_format({k : v for k, v in fmt.__dict__.items() if k in properties and dft_fmt.__dict__[k] != v})







def main(arg1, arg2, arg3, arg4): 
    
    
    logger.info('Il PID corrente è {0}'.format(os.getpid()))
    
    
    
    
    
    '''
    Gli input sono: 
    - data start 
    - data end
    - tipo report
    
    '''
    
    
    # leggo l'input
    try: 
        #codice='0203009803'
        data_start=arg1 #sys.argv[1]
        logger.info('Data inizio report {}'.format(data_start))
    except Exception as e:
        logger.error(e)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )
        exit()
    
    
    try: 
        #codice='0203009803'
        data_end=arg2 #sys.argv[1]
        logger.info('Data inizio report {}'.format(data_end))
    except Exception as e:
        logger.error(e)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )
        exit()
    
    
    '''
    Tipo report 1/2 che richiama le query diverse


    NOTE: per poi suddividere i servizi che non sono su SIT fra più comuni 
    dobbiamo creare una tabella accessoria da popolare manualmente o automaticamente 

    RM ha  iniziato con la vista UNIOPE.V_PERCORSI_X_COMUNE_UO_GIORNO ma probabilmente non è strada percorribile
    
    1) vanno creati dei servizi per i singoli comuni (es. fiere e manifestazioni) da trattare in maniera statica
    
    2) gli altri vanno divisi non in maniera flat ma usando dei criteri da condividere con Arboco/Sobrino

    '''

    
    
    query_personale0=''' SELECT 
                DISTINCT 
                coalesce(hs.COD_DIPENDENTE, CAST(hs.ID_PERSONA AS varchar(20))) AS pers,
                per.cod_postoorg AS MANSIONE, 
                COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
                COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
                as2.id_SERVIZIO,
                as2.DESC_SERVIZIO,
                hs.DURATA, 
                aspu.ID_PERCORSO,
                aspu.descrizione, 
                hs.ID_SER_PER_UO,
                hs.DTA_SERVIZIO,
                to_char(hs.DTA_SERVIZIO, 'YYYY/MM') AS mese,
                CASE 
                    WHEN id_comune IS NULL AND au1.ID_UO IN (SELECT cu.id_uo FROM comuni_ut cu WHERE cu.id_comune =1) 
                    THEN 1
                    ELSE id_comune
                END id_comune, 
                CASE 
                    WHEN id_comune IS NULL AND au1.ID_UO IN (SELECT cu.id_uo FROM comuni_ut cu WHERE cu.id_comune =1) 
                    THEN 'GENOVA'
                    ELSE comune
                END comune ,
                id_municipio,
                municipio,
                au1.ID_UO, 
                au1.DESC_UO, -- da cahiamare DESC_UO_SERVIZIO
                au2.id_uo AS id_uo_lavoro, 
                au2.DESC_UO AS desc_uo_lavoro,-- da cahiamare DESC_UO_UOMO
                perc
            FROM HIST_SERVIZI hs
                JOIN ANAGR_SER_PER_UO aspu 
                    ON aspu.ID_SER_PER_UO = hs.ID_SER_PER_UO
                JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
                LEFT JOIN ANAGR_SERVIZI_COGE asc2 
                    ON asc2.id_servizio_COGE = as2.id_servizio_coge		
                LEFT JOIN UNIOPE.PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
                    ON pxcuo.id_percorso = aspu.ID_PERCORSO 
                    AND pxcuo.giorno = hs.dta_servizio 
                    AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE
                JOIN anagr_uo au1 ON aspu.ID_UO = au1.ID_UO
                LEFT JOIN T_ANAGR_PERS_EKOVISION per
                    ON (/*per.id_persona = hs.id_persona OR*/ hs.COD_DIPENDENTE = concat(concat(lpad(per.COD_MATLIBROMAT, 5,'0'), '_'),per.id_azienda))
                    AND hs.dta_servizio BETWEEN per.dta_inizio
                                            AND per.dta_fine
                    AND per.dta_fine > TO_DATE('01/01/2024', 'DD/MM/YYYY')                        
                /*JOIN HCMDB9.hrhistory@cezanne8 h 
                    ON h.ID_PERSONA = hs.ID_PERSONA 
                    AND hs.dta_servizio BETWEEN h.DTA_INIZIO AND h.DTA_FINE*/
                LEFT JOIN UNIOPE.V_AFFERENZE_PERSONALE vap 
                    ON per.COD_SEDE=vap.ID_SEDE_TRASPORTO AND per.COD_CDC = vap.CODICE_CDC
                    AND per.COD_UNITAORG = vap.COD_UNITAORG
                LEFT JOIN ANAGR_UO au2 ON vap.ID_UO_GEST = au2.ID_UO
                --JOIN anagr_uo au2 ON hs.ID_UO_LAVORO = au2.ID_UO
            WHERE  
            hs.DTA_SERVIZIO BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
            AND hs.durata > 0 AND coalesce(perc,1) > 0 '''
            
            
            
    query_mezzi0='''SELECT 
                DISTINCT hsm.sportello,
                COALESCE (asc2.ID_SERVIZIO_COGE,'SRV0000') AS ID_SERVIZIO_COGE,
                COALESCE (asc2.DESCR_SERVIZIO_COGE,'Ore non assegnate') AS DESCR_SERVIZIO_COGE,
                as2.id_SERVIZIO,
                as2.DESC_SERVIZIO,
                hsm.DURATA, 
                aspu.ID_PERCORSO,
                aspu.descrizione,
                TO_DATE(see.DATA_ESECUZIONE_PREVISTA,'YYYYMMDD') AS giorno,
                CASE 
                    WHEN id_comune IS NULL AND au.ID_UO IN (SELECT cu.id_uo FROM comuni_ut cu WHERE cu.id_comune =1) 
                    THEN 1
                    ELSE id_comune
                END id_comune, 
                CASE 
                    WHEN id_comune IS NULL AND au.ID_UO IN (SELECT cu.id_uo FROM comuni_ut cu WHERE cu.id_comune =1) 
                    THEN 'GENOVA'
                    ELSE comune
                END comune ,
                id_municipio,
                municipio,
                /*au.id_uo,
                au.desc_uo,*/
                /*Correggo i mezzi grandi*/
                CASE 
                    WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1')*/
                    COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
                        (SELECT DISTINCT au1.id_uo FROM ANAGR_SER_PER_UO aspu1 
                            JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
                            WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
                    ELSE /*au.ID_UO*/
                    (SELECT DISTINCT aspu1.ID_UO FROM ANAGR_SER_PER_UO aspu1 
                    JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO
                    WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO
                    and au1.ID_ZONATERRITORIALE <> 5)
                END AS id_uo, 
                CASE 
                    WHEN /*trim(a.CDAOG3) NOT IN ('08','09', 'I1') */
                    COALESCE(a.SEDE_PRESA_SERV, 'ND') LIKE 'RIMESSA%' AND au.ID_ZONATERRITORIALE IN (1,2,3) THEN 
                        (SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1 
                            JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO 
                            WHERE au1.ID_ZONATERRITORIALE = 5 AND aspu1.ID_PERCORSO = aspu.ID_PERCORSO)
                    ELSE 
                    (SELECT DISTINCT au1.DESC_UO FROM ANAGR_SER_PER_UO aspu1
                    JOIN anagr_uo au1 ON au1.ID_UO = aspu1.ID_UO
                    WHERE id_squadra <> 15 AND aspu1.ID_SER_PER_UO = aspu.ID_SER_PER_UO
                    and au1.ID_ZONATERRITORIALE <> 5)
                END AS desc_uo,
                codice_tipologia_mezzo,
                descrizione_tipologia_mezzo AS tipo_mezzo,
                perc     
                FROM HIST_SERVIZI_MEZZI_OK hsm
                JOIN SCHEDE_ESEGUITE_EKOVISION see 
                    ON see.ID_SCHEDA = hsm.ID_SCHEDA_EKOVISION AND see.RECORD_VALIDO='S'
                    AND see.COD_CAUS_SRV_NON_ESEG_EXT IS null
                JOIN ANAGR_SER_PER_UO aspu 
                    ON aspu.ID_PERCORSO = see.CODICE_SERV_PRED 
                    AND to_date(see.DATA_PIANIF_INIZIALE, 'YYYYMMDD') BETWEEN 
                    aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
                JOIN anagr_servizi as2 ON aspu.id_servizio = as2. id_servizio
                LEFT JOIN ANAGR_SERVIZI_COGE asc2 
                    ON asc2.id_servizio_COGE = as2.id_servizio_coge	
                LEFT JOIN UNIOPE.PERCORSI_X_COMUNE_UO_GIORNO pxcuo 
                    ON pxcuo.id_percorso = aspu.ID_PERCORSO 
                    AND pxcuo.giorno = to_date(see.DATA_PIANIF_INIZIALE, 'YYYYMMDD')   
                    AND pxcuo.giorno BETWEEN aspu.DTA_ATTIVAZIONE AND aspu.DTA_DISATTIVAZIONE 
                JOIN anagr_uo au
                    ON au.ID_UO= aspu.ID_UO
                /*LEFT JOIN (SELECT ma.numatr AS sportello, ma.CDAOG3, oa.DSAOG3 FROM MAC_AMIUAUTO@info ma
            JOIN OG3_AMIUAUTO@info oa ON oa.CDAOG3 =ma.CDAOG3) a ON trim(a.sportello) = lpad(hsm.sportello, 5,'0') */
                JOIN v_AUTO_EKOVISION@info a ON  trim(a.sportello) = lpad(hsm.sportello, 5,'0')
                WHERE  /*id_comune = 2 AND au.id_uo = 10 AND*/
                /*trim(a.sportello)= '03754' and*/
                trim(a.sportello) is not null AND
                TO_DATE(see.DATA_PIANIF_INIZIALE,'YYYYMMDD') 
                BETWEEN TO_DATE(:d1, 'DD/MM/YYYY') AND to_date(:d2, 'DD/MM/YYYY')
                /*ORDER BY ID_PERCORSO, giorno*/'''
    
    try: 
        #codice='0203009803'
        if arg3=='1': #sys.argv[3]=='no':
            logger.info('Tipo report 1 definisco le query per personale e mezzi')
            query_personale='''
            SELECT 
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo,
            id_uo_lavoro,
            desc_uo_lavoro,
            MANSIONE,
            round(sum(COALESCE(perc,1)*durata)/60,2) AS ore
            FROM (
            {0}
            ) pp
            WHERE durata > 0
            GROUP BY 
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE, 
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo, id_uo_lavoro,
            desc_uo_lavoro, mansione
            ORDER BY 1, 5, 3 
            '''.format(query_personale0)








            query_mezzi='''SELECT 
            ID_SERVIZIO_COGE,
            DESCR_SERVIZIO_COGE,
            giorno,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo,
            TIPO_MEZZO,
            sportello,
            sum(COALESCE(perc,1)*durata/60) AS ore
            FROM (
                {0}
            ) pp
            WHERE sportello IS NOT NULL 
            and id_uo is not null
            GROUP BY
            ID_SERVIZIO_COGE,
            DESCR_SERVIZIO_COGE,
            giorno,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo, tipo_mezzo, sportello
            ORDER BY 1, 3, 4, 6, 8, 9'''.format(query_mezzi0)



        elif arg3 == '2':
            logger.info('Tipo report 2 definisco le query per personale e mezzi')

            query_personale='''
            SELECT 
            ID_SERVIZIO, DESC_SERVIZIO,
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo,
            id_uo_lavoro,
            desc_uo_lavoro,
            MANSIONE,
            round(sum(COALESCE(perc,1)*durata)/60,2) AS ore
            FROM (
            {0}
            ) pp
            WHERE durata > 0
            GROUP BY 
            ID_SERVIZIO, DESC_SERVIZIO,
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo, id_uo_lavoro,
            desc_uo_lavoro, mansione
            ORDER BY 1, 5, 3 '''.format(query_personale0)


            query_mezzi='''SELECT 
                ID_SERVIZIO,
                DESC_SERVIZIO,
                ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
                giorno,
                id_comune,  
                comune,
                id_municipio,
                municipio,
                id_uo,
                desc_uo,
                TIPO_MEZZO,
                sportello,
                sum(COALESCE(perc,1)*durata/60) AS ore
                FROM (
                    {0}
                ) pp
                WHERE sportello IS NOT NULL 
                and id_uo is not null
                GROUP BY
                ID_SERVIZIO,
                DESC_SERVIZIO,
                ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
                giorno,
                id_comune,  
                comune,
                id_municipio,
                municipio,
                id_uo,
                desc_uo, tipo_mezzo, sportello
                ORDER BY 1, 3, 4, 6, 8, 9'''.format(query_mezzi0)
                
                
                
        elif arg3 == '3':
            logger.info(f'Tipo report {arg3} definisco le query per personale e mezzi')

            query_personale='''
            SELECT 
            ID_PERCORSO, DESCRIZIONE AS DESC_PERCORSO,
            ID_SERVIZIO, DESC_SERVIZIO,
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo,
            id_uo_lavoro,
            desc_uo_lavoro,
            MANSIONE,
            round(sum(COALESCE(perc,1)*durata)/60,2) AS ore
            FROM (
            {0}
            ) pp
            WHERE durata > 0
            GROUP BY 
            ID_PERCORSO, DESCRIZIONE,
            ID_SERVIZIO, DESC_SERVIZIO,
            ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
            mese,
            id_comune,  
            comune,
            id_municipio,
            municipio,
            id_uo,
            desc_uo, id_uo_lavoro,
            desc_uo_lavoro, mansione
            ORDER BY 1, 5, 3 '''.format(query_personale0)


            query_mezzi='''SELECT 
                ID_PERCORSO, 
                DESCRIZIONE AS DESC_PERCORSO, 
                ID_SERVIZIO,
                DESC_SERVIZIO,
                ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
                giorno,
                id_comune,  
                comune,
                id_municipio,
                municipio,
                id_uo,
                desc_uo,
                TIPO_MEZZO,
                sportello,
                sum(COALESCE(perc,1)*durata/60) AS ore
                FROM (
                    {0}
                ) pp
                WHERE sportello IS NOT NULL 
                and id_uo is not null
                GROUP BY
                ID_PERCORSO, DESCRIZIONE,
                ID_SERVIZIO,
                DESC_SERVIZIO,
                ID_SERVIZIO_COGE, DESCR_SERVIZIO_COGE,
                giorno,
                id_comune,  
                comune,
                id_municipio,
                municipio,
                id_uo,
                desc_uo, tipo_mezzo, sportello
                ORDER BY 1, 3, 4, 6, 8, 9'''.format(query_mezzi0)
            
    except Exception as e:
        logger.error(e)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )
        exit()
      
    
    try: 
        dest_mail=arg4
        logger.info('Dovrò inviare il report via mail a {}'.format(dest_mail))
    except Exception as e:
        logger.error(e)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )        
        exit()
        
        
    # Mi connetto al DB oracle UO
    cx_Oracle.init_oracle_client(percorso_oracle) # necessario configurare il client oracle correttamente
    #cx_Oracle.init_oracle_client() # necessario configurare il client oracle correttamente
    parametri_con='{}/{}@//{}:{}/{}'.format(user_uo,pwd_uo, host_uo,port_uo,service_uo)
    logger.debug(parametri_con)
    con = cx_Oracle.connect(parametri_con)
    logger.info("Versione ORACLE: {}".format(con.version))    
        
    

    logger.info('Lancio la query del personale')
    cur = con.cursor()
    
    cur.prefetchrows = 10000
    cur.arraysize = 10000
    try:
        bind_dict = {'d1':data_start,  'd2': data_end}
        cur.prepare(query_personale)
        cur.execute(None, bind_dict)
        #cur.execute(query_personale, (data_start, data_end,))
        dettagli_personale=cur.fetchall()
    except Exception as e:
        logger.error(e)
        logger.error(query_personale)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )
        exit()
   
    logger.info('Fine query personale')
    cur.close()
    


    
    logger.info('Lancio la query dei mezzi')
    cur = con.cursor()   
    cur.prefetchrows = 10000
    cur.arraysize = 10000 
    try:
        bind_dict = {'d1':data_start,  'd2': data_end}
        cur.prepare(query_mezzi)
        cur.execute(None, bind_dict)
        dettagli_mezzi=cur.fetchall()
    except Exception as e:
        logger.error(e)
        logger.error(query_mezzi)
        error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger )
        exit()
    logger.info('Fine query mezzi')
    cur.close()

    
    #nome_file="driver_ekovision_{0}".format(giorno_file)
    
    if arg3=='1':
        desc_file='ID_COGE'
    elif arg3=='2':
        desc_file='ID_SERVIZIO'    
    elif arg3=='3':
        desc_file='ID_PERCORSO'  
    
    nome_file=f"driver_ekovision_{desc_file}"



    '''
    if (os.getuid()==33): # wwww-data
        if not os.path.exists('/tmp/driver_eko'):
            os.makedirs("/tmp/driver_eko")
        file_report="/tmp/driver_eko/{1}.xlsx".format(path,nome_file)
    else:
        file_report="{0}/driver_eko/{1}.xlsx".format(path,nome_file)
    '''
    
    if not os.path.exists('/tmp/driver_eko'):
        os.makedirs("/tmp/driver_eko")
    file_report="/tmp/driver_eko/{0}.xlsx".format(nome_file)
    
    workbook = xlsxwriter.Workbook(file_report)
    
    
    ''' FORMATI '''
    cell_format = workbook.add_format()
    cell_format.set_bold(False)
    cell_format.set_border(1)
    
    

    cell_format_title = workbook.add_format()
    cell_format_title.set_border(1)
    cell_format_title.set_bold(True)
    cell_format_title.set_font_color('#144798')


    cell_format_colorato = copy_format(workbook, cell_format)
    cell_format_colorato.set_bg_color('#3CFF1A')
    cell_format_colorato.set_align('center_across')


    cell_format_grande = copy_format(workbook, cell_format)
    cell_format_grande.set_font_size(13)
    cell_format_grande.set_bg_color('yellow')
    
    cell_format_title_grande = copy_format(workbook, cell_format_title)
    cell_format_title_grande.set_font_size(13)
    
       
    merge_format = workbook.add_format({
    'border': 1,
    'align': 'center',
    'valign': 'vcenter'})
    
    merge_format_grande=copy_format(workbook, merge_format)
    merge_format_grande.set_font_size(13)    
    merge_format_grande.set_bg_color('yellow')

    data_format = workbook.add_format({
        'num_format': 'dd/mm/yyyy', 
        'border': 1,
        'align': 'center'
    })







    w = workbook.add_worksheet('Personale')


    # PAGE SETUP
    w.set_landscape()
    w.set_paper(9) #A4
    w.center_vertically()
    #w.set_margins(left, right, top=0.5, bottom=1.5)
    w.repeat_rows(6)
    w.set_header('&CPage &P of &N')
    w.set_footer('&RReport prodotto grazie a SW del gruppo APTE (assterritorio@amiu.genova.it) in data &D alle ore &T')
    w.fit_to_pages(1, 0) # 1 page wide and as long as necessary.


    #w.insert_image('I2', '{}/img/logo_amiu.jpg'.format(path), {'x_scale': 0.8, 'y_scale': 0.8, 'x_offset': 10, 'y_offset': 10})
    '''
    if tipologia=='racc' or tipologia=='altro':
        w.set_column(0,0, 6)
        w.set_column(1,1, 34)
        w.set_column(2,2, 40)
        w.set_column(3,3, 30)
        w.set_column(4,4, 30)
        w.set_column(5,11, 6.5)
    elif tipologia=='spazz':
        if check_s==0:
            w.set_column(0,0, 30)
            w.set_column(1,1, 60)
            w.set_column(2,2, 15)
            w.set_column(3,3, 15)
        else:
            w.set_column(0,0, 40)
            w.set_column(1,1, 80)
            w.set_column(2,2, 0)
            w.set_column(3,3, 0)
        w.set_column(4,4, 20)
        w.set_column(5,11, 6.5)

    '''
    
    '''
    
    if arg3 == '1':
        w.write('A1', 'ID servizio COGE', cell_format_title) 
        w.write('B1', 'Desc servizio COGE', cell_format_title)
        w.write('C1', 'Mese', cell_format_title)
        w.write('D1', 'ID Comune', cell_format_title)
        w.write('E1', 'Comune', cell_format_title)
        w.write('F1', 'ID Municipio', cell_format_title)
        w.write('G1', 'Municipio', cell_format_title)
        w.write('H1', 'ID UO ', cell_format_title)    
        w.write('I1', 'Desc UO', cell_format_title)
        w.write('J1', 'ID UO Lavoro', cell_format_title)
        w.write('K1', 'Desc UO Lavoro', cell_format_title)   
        w.write('L1', 'Mansione', cell_format_title)   
        w.write('M1', 'Ore', cell_format_title)
    elif arg3 == '2':
        w.write('A1', 'ID servizio', cell_format_title) 
        w.write('B1', 'Desc servizio', cell_format_title)
        w.write('C1', 'ID servizio COGE', cell_format_title) 
        w.write('D1', 'Desc servizio COGE', cell_format_title)
        w.write('E1', 'Mese', cell_format_title)
        w.write('F1', 'ID Comune', cell_format_title)
        w.write('G1', 'Comune', cell_format_title)
        w.write('H1', 'ID Municipio', cell_format_title)
        w.write('I1', 'Municipio', cell_format_title)
        w.write('J1', 'ID UO ', cell_format_title)    
        w.write('K1', 'Desc UO', cell_format_title)
        w.write('L1', 'ID UO Lavoro', cell_format_title)
        w.write('M1', 'Desc UO Lavoro', cell_format_title)   
        w.write('N1', 'Mansione', cell_format_title)   
        w.write('O1', 'Ore', cell_format_title)   
    
    '''
    # Per personale
    write_headers(w, "personale", arg3, cell_format_title)


    r=1       
    for pp in dettagli_personale:
        c=0
        while c<len(pp):
            w.write(r, c, pp[c], cell_format)
            c+=1
        r+=1
        if r==500:
            w.autofit()
    
    
        
    w.autofilter('A1:Q{}'.format(r))
    
    if len(dettagli_personale)< 500 :    
        w.autofit()    
            
    
    
    w = workbook.add_worksheet('Mezzi')


    # PAGE SETUP
    w.set_landscape()
    w.set_paper(9) #A4
    w.center_vertically()
    #w.set_margins(left, right, top=0.5, bottom=1.5)
    w.repeat_rows(6)
    w.set_header('&CPage &P of &N')
    w.set_footer('&RReport prodotto grazie a SW del gruppo APTE (assterritorio@amiu.genova.it) in data &D alle ore &T')
    w.fit_to_pages(1, 0) # 1 page wide and as long as necessary.


    #w.insert_image('I2', '{}/img/logo_amiu.jpg'.format(path), {'x_scale': 0.8, 'y_scale': 0.8, 'x_offset': 10, 'y_offset': 10})
    '''
    if tipologia=='racc' or tipologia=='altro':
        w.set_column(0,0, 6)
        w.set_column(1,1, 34)
        w.set_column(2,2, 40)
        w.set_column(3,3, 30)
        w.set_column(4,4, 30)
        w.set_column(5,11, 6.5)
    elif tipologia=='spazz':
        if check_s==0:
            w.set_column(0,0, 30)
            w.set_column(1,1, 60)
            w.set_column(2,2, 15)
            w.set_column(3,3, 15)
        else:
            w.set_column(0,0, 40)
            w.set_column(1,1, 80)
            w.set_column(2,2, 0)
            w.set_column(3,3, 0)
        w.set_column(4,4, 20)
        w.set_column(5,11, 6.5)

    '''
    
    
    '''
    if arg3 == '1':
        w.write('A1', 'ID servizio COGE', cell_format_title) 
        w.write('B1', 'Desc servizio COGE', cell_format_title)
        w.write('C1', 'Giorno', cell_format_title)
        w.write('D1', 'ID Comune', cell_format_title)
        w.write('E1', 'Comune', cell_format_title)
        w.write('F1', 'ID Municipio', cell_format_title)
        w.write('G1', 'Municipio', cell_format_title)
        w.write('H1', 'ID UO ', cell_format_title)    
        w.write('I1', 'Desc UO', cell_format_title)
        w.write('J1', 'Tipo mezzo', cell_format_title)
        w.write('K1', 'Sportello', cell_format_title)   
        w.write('L1', 'Ore', cell_format_title)
    elif arg3 =='2':
        w.write('A1', 'ID servizio', cell_format_title) 
        w.write('B1', 'Desc servizio', cell_format_title)
        w.write('C1', 'ID servizio COGE', cell_format_title) 
        w.write('D1', 'Desc servizio COGE', cell_format_title)
        w.write('E1', 'Giorno', cell_format_title)
        w.write('F1', 'ID Comune', cell_format_title)
        w.write('G1', 'Comune', cell_format_title)
        w.write('H1', 'ID Municipio', cell_format_title)
        w.write('I1', 'Municipio', cell_format_title)
        w.write('J1', 'ID UO ', cell_format_title)    
        w.write('K1', 'Desc UO', cell_format_title)
        w.write('L1', 'Tipo mezzo', cell_format_title)
        w.write('M1', 'Sportello', cell_format_title)   
        w.write('N1', 'Ore', cell_format_title)   

    '''
    # Per mezzi
    write_headers(w, "mezzi", arg3, cell_format_title)
    
    r=1       
    for mm in dettagli_mezzi:
        #logger.debug(r)
        c=0
        while c<len(mm):
            if arg3=='1' and c==2:
                w.write(r, c, mm[c], data_format)
            elif arg3=='2' and c==4:
                w.write(r, c, mm[c], data_format)
            elif arg3=='3' and c==6:
                w.write(r, c, mm[c], data_format)
            else:
                w.write(r, c, mm[c], cell_format)
            c+=1
        if r==1000:
            w.autofit()
        r+=1
        
        
    w.autofilter('A1:P{}'.format(r))
    
    if len(dettagli_mezzi)< 1000:    
        w.autofit()    
    
    
    
    
    
    
    

    workbook.close()
    #sent_log_by_mail(filename,logfile)

    
    
    
    #############################################################################
    # INVIO FILE EXCEL
    # Create a secure SSL context
    context = ssl.create_default_context()



    # messaggio='Test invio messaggio'


    subject = "Invio driver ekovision da {} a {} ".format(data_start, data_end)
        
        
    ##sender_email = user_mail
    receiver_email='assterritorio@amiu.genova.it'
    debug_email='roberto.marzocchi@amiu.genova.it'
    
    
    body = """
    <br>In allegato il file excel con i dettagli su personale e mezzi da {0} a {1} .
    <br><br>
    AMIU Assistenza Territorio<br>
    <img src="cid:image1" alt="Logo" width=197>
    <br>{2}
    """.format(data_start, data_end, mail_footer)


    # Create a multipart message and set headers
    message = MIMEMultipart()
    message["From"] = sender_email
    message["To"] =  dest_mail
    message["Cc"] = receiver_email
    message["Subject"] = subject
    #message["Bcc"] = debug_email  # Recommended for mass emails
    message.preamble = "Driver ekovision"


        
                        
    # Add body to email
    message.attach(MIMEText(body, "html"))


    #aggiungo logo 
    logoname='{}/img/logo_amiu.jpg'.format(parentdir)
    immagine(message,logoname)
    
    
    # aggiunto allegato (usando la funzione importata)
    allegato(message, file_report, '{}.xlsx'.format(nome_file))
    # Add body to email
    #message.attach(MIMEText(body, "plain"))
    
    
    #text = message.as_string()

    logger.info("Richiamo la funzione per inviare mail")
    invio=invio_messaggio(message)
    logger.info(invio)
    

    ##################################################################################################
    #                               CHIUDO LE CONNESSIONI
    ################################################################################################## 
    logger.info("Chiudo definitivamente le connesioni al DB")
    con.close()

    # check se c_handller contiene almeno una riga 
    error_log_mail(errorfile, 'assterritorio@amiu.genova.it', os.path.basename(__file__), logger)
    


if __name__ == "__main__":
    # passo le variabili come input della funzione main 
    main(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4])
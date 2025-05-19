#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2023
# Roberto Marzocchi

'''
Dato codice percorso e data disattivazione invia mail con elenco id schede ekovision da rimuovere manualmente

arg1 = cod_percorso
arg2 = data in formato YYYYMMDD (tipico di Ekovision)

'''

#from msilib import type_short
import os, sys, re  # ,shutil,glob

import inspect, os.path
#import getopt  # per gestire gli input

#import pymssql

from datetime import date, datetime, timedelta


import xlsxwriter

import psycopg2

import cx_Oracle

currentdir = os.path.dirname(os.path.realpath(__file__))
parentdir = os.path.dirname(currentdir)
sys.path.append(parentdir)
from credenziali import *


import requests
from requests.exceptions import HTTPError

import logging

#path=os.path.dirname(sys.argv[0]) 



filename = inspect.getframeinfo(inspect.currentframe()).filename
#path = os.path.dirname(os.path.abspath(filename))
path1 = os.path.dirname(os.path.dirname(os.path.abspath(filename)))
path=os.path.dirname(sys.argv[0]) 
path1 = os.path.dirname(os.path.dirname(os.path.abspath(filename)))
nome=os.path.basename(__file__).replace('.py','')
#tmpfolder=tempfile.gettempdir() # get the current temporary directory

giorno_file=datetime.today().strftime('%Y%m%d_%H%M%S')

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



#if os.path.exists(logfile):
#    os.remove(logfile)






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


# libreria per invio mail
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





def main(arg1, arg2):
    
    logger.info('Il PID corrente è {0}'.format(os.getpid()))

    # Get today's date
    #presentday = datetime.now() # or presentday = datetime.today()
    oggi=datetime.today()
    oggi=oggi.replace(hour=0, minute=0, second=0, microsecond=0)
    #oggi=date(oggi.year, oggi.month, oggi.day)
    logging.debug('Oggi {}'.format(oggi))
    
    num_giorno=datetime.today().weekday()
    giorno=datetime.today().strftime('%A')
    logging.info('Il giorno della settimana è {} o meglio {}'.format(num_giorno, giorno))

    start_week = date.today() - timedelta(days=datetime.today().weekday())
    logging.info('Il primo giorno della settimana è {} '.format(start_week))
    
    #exit()
    # Mi connetto a SIT (PostgreSQL) per poi recuperare le mail
    nome_db=db
    logger.info('Connessione al db {}'.format(nome_db))
    conn = psycopg2.connect(dbname=nome_db,
                        port=port,
                        user=user,
                        password=pwd,
                        host=host)


    curr = conn.cursor()
    
    
    


     # cerco le schede su ekovision
        # PARAMETRI GENERALI WS
    
    
    headers = {'Content-Type': 'application/x-www-form-urlencoded'}

    data_json={'user': eko_user, 
        'password': eko_pass,
        'o2asp' :  eko_o2asp
        }
    
    schede_cancellare=''
    

    
    
    #input
    
    cod_percorso=arg1
    data_dis=arg2

    
    logger.debug(oggi)
    #logger.debug(vv[1])
    
    gg_indietro=oggi-datetime.strptime(data_dis, '%d/%m/%Y')
    
    logger.debug(gg_indietro.days)
    #exit()
    gg=-gg_indietro.days
    
    while gg <= 14-datetime.today().weekday():
        day_check=oggi + timedelta(gg)
        day= day_check.strftime('%Y%m%d')
        #logger.debug(day)
        # se il percorso è previsto in quel giorno controllo che ci sia la scheda di lavoro corrispondente
        
        params={'obj':'schede_lavoro',
            'act' : 'r',
            'sch_lav_data': day,
            'cod_modello_srv': cod_percorso, 
            'flg_includi_eseguite': 1,
            'flg_includi_chiuse': 0
            }
        response = requests.post(eko_url, params=params, data=data_json, headers=headers)
        #response.json()
        #logger.debug(response.status_code)
        try:      
            response.raise_for_status()
            check=0
            # access JSOn content
            #jsonResponse = response.json()
            #print("Entire JSON response")
            #print(jsonResponse)
        except HTTPError as http_err:
            logger.error(f'HTTP error occurred: {http_err}')
            check=1
        except Exception as err:
            logger.error(f'Other error occurred: {err}')
            logger.error(response.json())
            check=1
        if check<1:
            letture = response.json()
            #logger.info(letture)
            if len(letture['schede_lavoro']) > 0 : 
                id_scheda=letture['schede_lavoro'][0]['id_scheda_lav']
                logger.info('Id_scheda:{}'.format(id_scheda))
                if schede_cancellare=='':
                    schede_cancellare='{}'.format(id_scheda)
                else:
                    schede_cancellare='{},{}'.format(schede_cancellare,id_scheda)
        gg+=1 
     

    


    
    
    # provo a mandare la mail
    try:
        if schede_cancellare!='':
            # Create a secure SSL context
            context = ssl.create_default_context()



        # messaggio='Test invio messaggio'


            subject = "ELIMINAZIONE SCHEDE LAVORO - Nuova versione"
            
            ##sender_email = user_mail
            receiver_email='assterritorio@amiu.genova.it'
            debug_email='roberto.marzocchi@amiu.genova.it'

            # Create a multipart message and set headers
            message = MIMEMultipart()
            message["From"] = sender_email
            message["To"] = receiver_email
            message["Subject"] = subject
            #message["Bcc"] = debug_email  # Recommended for mass emails
            message.preamble = "Cambio frequenze"


            body='''Per il percorso con codice {0} è stata creta una nuova versione. 
            <br><br>
            Bisogna eliminare <b>manualmente</b> le schede di lavoro su Ekovision. Usare la voce <i>Eliminazione massiva schede lavoro</i> 
            del menù scorciatoia di Ekovision e caricare la lista di ID schede da cancellare riportata nel seguito.
            Verificare il log e controllare a mano eventuali anomalie.
            <br><br>
            Elenco schede da cancellare: <br>
            {1}
            <br><br>
            AMIU Assistenza Territorio<br>
            <img src="cid:image1" alt="Logo" width=197>
            <br>'''.format(cod_percorso, schede_cancellare)
                                
            # Add body to email
            message.attach(MIMEText(body, "html"))


            #aggiungo logo 
            logoname='{}/img/logo_amiu.jpg'.format(path1)
            immagine(message,logoname)
            
            

            
            
            text = message.as_string()

            logger.info("Richiamo la funzione per inviare mail")
            invio=invio_messaggio(message)
            logger.info(invio)
    except Exception as e:
        logger.error(e) # se non fossi riuscito a mandare la mail
    
    
    
    
    
    
    
    
    
    # check se c_handller contiene almeno una riga 
    error_log_mail(errorfile, 'assterritorio@amiu.genova.it', os.path.basename(__file__), logger)
    logger.info("chiudo le connessioni in maniera definitiva")
    curr.close()
    conn.close()




if __name__ == "__main__":
    # passo le variabili come input della funzione main 
    main(sys.argv[1], sys.argv[2])  
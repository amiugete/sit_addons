#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2021
# Roberto Marzocchi

'''
Funzioni per inviare mail e aggiungere allegati usate dentro altri script
'''

import os, sys

import email, smtplib, ssl
import mimetypes
from email.mime.multipart import MIMEMultipart
from email import encoders
from email.message import Message
from email.mime.audio import MIMEAudio
from email.mime.base import MIMEBase
from email.mime.image import MIMEImage
from email.mime.text import MIMEText




currentdir = os.path.dirname(os.path.realpath(__file__))
#parentdir = os.path.dirname(currentdir)

sys.path.append(currentdir)


from credenziali import *


mail_footer='''<hr>Questa mail è stata creata in automatico. 
            In caso di dubbi non rispondere alla presente mail ma scrivere a
            <a href="mailto:AssTerritorio@amiu.genova.it">AssTerritorio@amiu.genova.it</a>.'''



def invio_messaggio(messaggio):

    # Create a secure SSL context
    context = ssl.create_default_context()

    check=0
    # questo funzionava con GMAIL ma non va bene con SERVER AMIU
    try:
        with smtplib.SMTP_SSL(smtp_mail, port_mail, context=context) as server:
            server.login(user_mail, pwd_mail)
            server.send_message(messaggio)
            #server.sendmail(user_mail, receiver_email, text)
        check=200
    except Exception as e:
        e1=e 
    
    if check==0:
        # questo dovrebbe  funzionare con SERVER AMIU INTERNO
        try:
            server = smtplib.SMTP(smtp_mail,port_mail)
            server.ehlo() # Can be omitted
            #server.starttls(context=context) # Secure the connection
            #server.ehlo() # Can be omitted
            #server.login(user_mail, pwd_mail)
            #server.sendmail(user_mail, receiver_email, text)
            server.send_message(messaggio)
            check=200
        except Exception as e:
            # Print any error messages to stdout
            e2=e
    
    if check==200: 
        return check
    else:
        check='500 - ERRORI: {} e {}'.format(e1,e2)

    return check



def allegato(messaggio, file, file_name):
    '''
    Funzione presente nello script invio_messaggio.py per aggiungere un allegato a un messaggio mail
    Input:
        - messaggio: 
        - file: path al file da aggiungere
        - file_name: nome del file da allegare
    '''
    ctype, encoding = mimetypes.guess_type(file)
    if ctype is None or encoding is not None:
        ctype = "application/octet-stream"

    maintype, subtype = ctype.split("/", 1)

    if maintype == "text":
        fp = open(file)
        # Note: we should handle calculating the charset
        attachment = MIMEText(fp.read(), _subtype=subtype)
        fp.close()
    elif maintype == "image":
        fp = open(file, "rb")
        attachment = MIMEImage(fp.read(), _subtype=subtype)
        fp.close()
    elif maintype == "audio":
        fp = open(file, "rb")
        attachment = MIMEAudio(fp.read(), _subtype=subtype)
        fp.close()
    else:
        fp = open(file, "rb")
        attachment = MIMEBase(maintype, subtype)
        attachment.set_payload(fp.read())
        fp.close()
        encoders.encode_base64(attachment)
    attachment.add_header("Content-Disposition", "attachment", filename=file_name)
    messaggio.attach(attachment)


def immagine(messaggio, file):
    '''
    Funzione presente nello script invio_messaggio.py per aggiungere un immagine a un messaggio
    '''
    # This example assumes the image is in the current directory
    fp = open(file, 'rb')
    msgImage = MIMEImage(fp.read())
    fp.close()

    # Define the image's ID as referenced above
    msgImage.add_header('Content-ID', '<image1>')
    messaggio.attach(msgImage)



def error_log_mail(error_log_file, receiver_email, script_name, logger_name):
    '''
    Funzione presente nello script invio_messaggio.py per inviare l'eventuale LOG con errori via mail
    Input:
        - error_log_file
        - receiver_email
        - script_name
        - logger_name
    '''

    logfile = open(error_log_file, 'r')
    loglist = logfile.readlines()
    logfile.close()
    found = False
    for line in loglist:
        if line!='':
            found = True
    if found == True: 
        subject = 'LOG - {}'.format(script_name)
        body = '''In allegato il file con errori e/o warning originato con lo script {0}<br><br>
        Il file completo di log è disponibile all'indirizzo {1} del server <b>amiugis</b><br><br>
Per info su accesso al server <b>amiugis</b> visualizza la WIKI: 
http://amiuintranet.amiu.genova.it/content/accesso-server-amiugis'''.format(script_name, error_log_file.replace('error_',''))
        #sender_email = user_mail


        # Create a multipart message and set headers
        message = MIMEMultipart()
        message["From"] = sender_email
        message["To"] = receiver_email
        message["CC"] = 'roberto.marzocchi@amiu.genova.it'
        message["Subject"] = subject
        #message["Bcc"] = debug_email  # Recommended for mass emails
        message.preamble = subject

                        
        # Add body to email
        message.attach(MIMEText(body, "html"))

        # aggiungi allegato
        allegato(message, error_log_file,'error.log')


        invio_messaggio(message)
        logger_name.info('Messaggio inviato')
        return 200
    else: 
        logger_name.info('Nessun errore, quindi nessun messaggio inviato')
        return 0
    
   
   
   
def warning_message_mail(message, receiver_email, script_name, logger_name):
    '''
    Funzione presente nello script invio_messaggio.py per inviare l'eventuale LOG con WARNING via mail
    Input:
        - messaggio
        - receiver_email
        - script_name
        - logger_name
    '''


   
    subject = 'WARNING MESSAGE - {}'.format(script_name)
    body = '''
    {0}<br><br>
    Automatically sent from AMIU Genova by {1} script<br><br>
    '''.format(message,script_name)
    #sender_email = user_mail


    # Create a multipart message and set headers
    message = MIMEMultipart()
    message["From"] = sender_email
    message["To"] = receiver_email
    #message["CC"] = 'roberto.marzocchi@amiu.genova.it'
    message["Subject"] = subject
    #message["Bcc"] = debug_email  # Recommended for mass emails
    message.preamble = subject

                    
    # Add body to email
    message.attach(MIMEText(body, "html"))

    # aggiungi allegato
    #allegato(message, warning_log_file,'warning.log')


    invio_messaggio(message)
    logger_name.info('Messaggio inviato')
    return 200
    
   
    
def warning_log_mail(warning_log_file, receiver_email, script_name, logger_name):
    '''
    Funzione presente nello script invio_messaggio.py per inviare l'eventuale LOG con WARNING via mail
    Input:
        - error_log_file
        - receiver_email
        - script_name
        - logger_name
    '''

    logfile = open(warning_log_file, 'r')
    loglist = logfile.readlines()
    logfile.close()
    found = False
    for line in loglist:
        if line!='':
            found = True
    if found == True: 
        subject = 'LOG - {}'.format(script_name)
        body = '''In allegato il file di LOG completo originato con lo script {0}<br><br>
Per info su accesso al server <b>amiugis</b> visualizza la WIKI: 
http://amiuintranet.amiu.genova.it/content/accesso-server-amiugis'''.format(script_name, warning_log_file.replace('error_',''))
        #sender_email = user_mail


        # Create a multipart message and set headers
        message = MIMEMultipart()
        message["From"] = sender_email
        message["To"] = receiver_email
        message["CC"] = 'roberto.marzocchi@amiu.genova.it'
        message["Subject"] = subject
        #message["Bcc"] = debug_email  # Recommended for mass emails
        message.preamble = subject

                        
        # Add body to email
        message.attach(MIMEText(body, "html"))

        # aggiungi allegato
        allegato(message, warning_log_file,'warning.log')


        invio_messaggio(message)
        logger_name.info('Messaggio inviato')
        return 200
    else: 
        logger_name.info('Nessun warning, quindi nessun messaggio inviato')
        return 0
    


    
def creazione_scheda_mail(body_text_html, receiver_email, script_name, logger_name):
    '''
    Funzione presente nello script invio_messaggio.py per inviare un messaggio che informi l'utente della creazione di una scheda di lavoro ekoision
    Input:
        - testo meail
        - receiver_email
        - script_name
        - logger_name
    '''


    subject = '{} - Creazione automatica scheda Ekovision per consuntivazione su totem'.format(script_name.replace('.py', ''))
    body = '''{}<br><br>
    AMIU Assistenza Territorio<br>
     <img src="cid:image1" alt="Logo" width=197>
    <br>{}'''.format(body_text_html, mail_footer)
    #sender_email = user_mail


    # Create a multipart message and set headers
    message = MIMEMultipart()
    message["From"] = sender_email
    message["To"] = receiver_email
    #message["CC"] = 'assterritorio@amiu.genova.it'
    message["Subject"] = subject
    message["Bcc"] = 'assterritorio@amiu.genova.it'  # Recommended for mass emails
    message.preamble = subject

                    
    # Add body to email
    message.attach(MIMEText(body, "html"))

    # aggiungi allegato
    #allegato(message, warning_log_file,'warning.log')


    #aggiungo logo 
    logoname='{}/img/logo_amiu.jpg'.format(currentdir)
    immagine(message,logoname)
    
    invio_messaggio(message)
    logger_name.info('Messaggio inviato')
    return 200




def ripasso_mail(body_text_html, receiver_email, script_name, logger_name):
    '''
    Funzione presente nello script invio_messaggio.py per inviare un messaggio che informi l'utente dell'inserimento di un ripasso 
    Input:
        - testo meail
        - receiver_email
        - script_name
        - logger_name
    '''


    subject = '{} - Inserito un ripasso su SIT'.format(script_name.replace('.py', ''))
    body = '''{}<br><br>
    AMIU Assistenza Territorio<br>
     <img src="cid:image1" alt="Logo" width=197>
    <br><hr>Questa mail è stata creata in automatico. 
    In caso di dubbi non rispondere alla presente mail ma scrivere a <a href="mailto:AssTerritorio@amiu.genova.it">AssTerritorio@amiu.genova.it</a>.'''.format(body_text_html)
    #sender_email = user_mail


    # Create a multipart message and set headers
    message = MIMEMultipart()
    message["From"] = sender_email
    message["To"] = receiver_email
    #message["CC"] = 'assterritorio@amiu.genova.it'
    message["Subject"] = subject
    message["Bcc"] = 'assterritorio@amiu.genova.it'  # Recommended for mass emails
    message.preamble = subject

                    
    # Add body to email
    message.attach(MIMEText(body, "html"))

    # aggiungi allegato
    #allegato(message, warning_log_file,'warning.log')


    #aggiungo logo 
    logoname='{}/img/logo_amiu.jpg'.format(currentdir)
    immagine(message,logoname)
    
    invio_messaggio(message)
    logger_name.info('Messaggio inviato')
    return 200
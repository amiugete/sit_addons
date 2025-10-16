#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2021
# Roberto Marzocchi

'''
Funzione per invio mail di log a
'''

import os

from credenziali import *

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


def sent_log_by_mail(script_name, log_file):
    # Create a secure SSL context
    context = ssl.create_default_context()
    filename= os.path.basename(__file__)
    path     = os.path.dirname(os.path.abspath(filename))

   # messaggio='Test invio messaggio'


    subject = "PROBLEMA SCRIPT PYTHON"
    body = '''Si è verificato  un problema con il seguente script {}\n
    Consulta il file di log {}\n\n'''.format(script_name ,log_file)
    ##sender_email = user_mail
    receiver_email='assterritorio@amiu.genova.it'
    debug_email='roberto.marzocchi@amiu.genova.it'

    # Create a multipart message and set headers
    message = MIMEMultipart()
    message["From"] = sender_email
    message["To"] = debug_email
    message["Subject"] = subject
    #message["Bcc"] = debug_email  # Recommended for mass emails
    message.preamble = "File giornaliero con le variazioni"

    # Add body to email
    message.attach(MIMEText(body, "plain"))


    ctype, encoding = mimetypes.guess_type(log_file)
    if ctype is None or encoding is not None:
        ctype = "application/octet-stream"

    maintype, subtype = ctype.split("/", 1)

    if maintype == "text":
        fp = open(log_file)
        # Note: we should handle calculating the charset
        attachment = MIMEText(fp.read(), _subtype=subtype)
        fp.close()
    elif maintype == "image":
        fp = open(log_file, "rb")
        attachment = MIMEImage(fp.read(), _subtype=subtype)
        fp.close()
    elif maintype == "audio":
        fp = open(log_file, "rb")
        attachment = MIMEAudio(fp.read(), _subtype=subtype)
        fp.close()
    else:
        fp = open(log_file, "rb")
        attachment = MIMEBase(maintype, subtype)
        attachment.set_payload(fp.read())
        fp.close()
        encoders.encode_base64(attachment)
    
    attachment.add_header("Content-Disposition", "attachment", filename=log_file.replace('{}/log'.format(path), ''))
    message.attach(attachment)
    
    
    text = message.as_string()




    # Now send or store the message
    #logging.info("Richiamo la funzione per inviare mail")
    invio=invio_messaggio(message)
    #logging.info(invio)




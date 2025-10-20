#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2021
# Roberto Marzocchi

'''
Lo script crea un report settimanale per i percorsi presenti in SIT (funziona per percorsi settimanali che mensili)
Lo scopo è supportare le UT fornendo un cartaceo per quella settimana del percorso in oggetto

Spazzamento / lavaggio 
'''

import os, sys, re  # ,shutil,glob
import inspect, os.path

import psycopg2

import xlsxwriter

import datetime

from credenziali import *

from mail_log import *

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
        if not os.path.exists('/tmp/report_settimanali_ok'):
            os.makedirs("/tmp/report_settimanali_ok")
        if not os.path.exists('/tmp/report_settimanali_ok/log'):
            os.makedirs("/tmp/report_settimanali_ok/log")
        logfile='/tmp/report_settimanali_ok/log/{2}_{1}.log'.format(path,nome, giorno_file)
        errorfile='/tmp/report_settimanali_ok/log/{2}_error_{1}.log'.format(path,nome, giorno_file)
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
f_handler.setLevel(logging.INFO)


# Add handlers to the logger
logger.addHandler(c_handler)
logger.addHandler(f_handler)


cc_format = logging.Formatter('%(asctime)s\t%(levelname)s\t%(message)s')

c_handler.setFormatter(cc_format)
f_handler.setFormatter(cc_format)


def coalesce_py(s, d):
    if s is None:
        return d
    else:
        return s
    
    
def sett(giorno):
    if giorno%7==0:
        set=int(giorno/7)
    else:
        set=int(giorno/7)+1
    return set



def ctrl_freq(freq_oggi, freq_prev ):
    sett=''
    if freq_prev[0]=='S':
        '''Frequenza settimanale'''
        check=int(freq_prev[int(freq_oggi[1])])
    elif freq_prev[0]=='M':
        '''Frequenza mensile'''
        # stringa contiene 
        # la settimana del mese e 
        # poi il giorno
        if freq_prev.find(freq_oggi)>0:
            check=1
        else:
            check=0
            giorni_mese=freq_prev[1:].split('_')
            # faccio ciclo sui giorni_mese
            # vedo se c'è quel giorno un'altra settimana 
            ss=0
            while ss < len(giorni_mese):
                if freq_oggi[1]==giorni_mese[ss][1]:
                    if sett=='':
                        sett='{}s'.format(giorni_mese[ss][0])
                    else:
                        sett='{},{}s'.format(sett,giorni_mese[ss][0])
                ss+=1
          
    return check, sett


def dayNameFromWeekday(weekday):
    if weekday == 0:
        return "LU"
    if weekday == 1:
        return "MA"
    if weekday == 2:
        return "ME"
    if weekday == 3:
        return "GI"
    if weekday == 4:
        return "VE"
    if weekday == 5:
        return "SA"
    if weekday == 6:
        return "DO"


def copy_format(book, fmt):
    '''
    new_format = copy_format(workbook, initial_format)
    '''
    properties = [f[4:] for f in dir(fmt) if f[0:4] == 'set_']
    dft_fmt = book.add_format()
    return book.add_format({k : v for k, v in fmt.__dict__.items() if k in properties and dft_fmt.__dict__[k] != v})



def main(arg1, arg2, arg3, arg4): 
    
    # leggo l'input
    try: 
        #codice='0203009803'
        codice=arg1 #sys.argv[1]
        logger.info('Inizio creazione report per percorso {}'.format(codice))
    except Exception as e:
        logger.error(e)
        sent_log_by_mail(filename,errorfile)
        exit()
    
    try: 
        #codice='0203009803'
        if arg2=='sempl': #sys.argv[2]=='sempl':
            check_s=1 # semplificato
            check_b=0 # non bilaterale
            logger.info('Richiesta report semplificato')
        elif arg2=='compl': #sys.argv[2]=='compl':
            check_s=0 # non semplificato 
            check_b=0 # non bilaterale
            logger.info('Report standard')
        elif arg2=='all_bilaterale':
            check_s=1 # semplificato
            check_b=1 #bilaterale
            logger.info('Report tutti i percorsi bilaterali')
    except Exception as e:
        logger.error(e)
        sent_log_by_mail(filename,errorfile)
        exit()
    
    try: 
        #codice='0203009803'
        if arg3=='no': #sys.argv[3]=='no':
            check_m=0
            logger.info('Non si richiede invio mail')
        else:
            indirizzo_mail_per_invio=arg3 #sys.argv[3]
            check_m=1
            logger.info('Si richiede invio mail a {}'.format(indirizzo_mail_per_invio))
    except Exception as e:
        logger.error(e)
        sent_log_by_mail(filename,errorfile)
        exit()
    
    
    try: 
        if arg4 is None or arg4==0:
            logger.info ('Non è stato specificato il numero di giorni')
            arg4=0
        else :
            logger.info('Si richiede di cercare le variazioni per {} giorni'.format(arg4))
    except Exception as e:
        logger.error(e)
        sent_log_by_mail(filename,errorfile)
        #exit()    
    
        
        
    if check_b == 0:
        codici_percorsi = [codice]
        nome_file="report_{0}".format(codice,giorno_file)
        # non uso la data perchè sarebbe difficilmente recuperabile da php
        
        if check_s == 1:
            nome_file='{}_operatore'.format(nome_file)


    if check_b == 1:
        nome_file='report_bilaterali'


    if (os.getuid()==33): # wwww-data
        if not os.path.exists('/tmp/report'):
            os.makedirs("/tmp/report")
        file_report="/tmp/report/{1}.xlsx".format(path,nome_file)
    else:
        file_report="{0}/report/{1}.xlsx".format(path,nome_file)
    
    
    if os.path.exists(file_report):
        logger.info('Rimuovo il file creato in precedenza')
        os.remove(file_report)
        
              
    workbook = xlsxwriter.Workbook(file_report)    
        
    
    # verifico se percorso spazzamento o lavaggio
    
    logger.info('Connessione al db')
    conn = psycopg2.connect(dbname=db,
                        port=port,
                        user=user,
                        password=pwd,
                        host=host)

    curr = conn.cursor()
    #conn.autocommit = True
        
    
    
    
    
    
    query_bilaterali='''select cod_percorso, descrizione, id_servizio, fo.num_giorni  
from elem.percorsi p 
join etl.frequenze_ok fo on fo.cod_frequenza = p.frequenza  
where id_categoria_uso = 3 and 
	id_servizio in (
		select id_servizio from elem.servizi s where s.descrizione ilike '%bilaterale%'
	)
order by 4 desc,1'''
    
    
    
    
    query='''select p.cod_percorso, p.versione, p.descrizione, 
case 
	when s.riempimento = 1 then 'racc'
	when s.riempimento = 0 then 'spazz'
	else 'altro'
end tipologia,
s.descrizione as servizio,
string_agg(u.descrizione, ',') as ut,
t.descrizione as turno, 
a.nome as mezzo, p.stagionalita,
fo.descrizione_long,
data_attivazione 
from elem.percorsi p 
join elem.servizi s 
on p.id_servizio =s.id_servizio 
join elem.percorsi_ut pu 
on pu.cod_percorso =p.cod_percorso 
join topo.ut u 
on u.id_ut = pu.id_ut 
join elem.turni t 
on t.id_turno = p.id_turno 
join elem.automezzi a 
on a.cdaog3 = p.famiglia_mezzo 
left join etl.frequenze_ok fo 
on fo.cod_frequenza = p.frequenza 
where p.cod_percorso= %s and p.id_categoria_uso in (3,6)
group by 
p.cod_percorso, p.versione, p.descrizione, 
case 
	when s.riempimento = 1 then 'racc'
	when s.riempimento = 0 then 'spazz'
	else 'altro'
end,
s.descrizione,
t.descrizione , 
a.nome,
p.stagionalita,
fo.descrizione_long,
data_attivazione
    '''
    
    query_elementi= ''' with percorso_sel as (
    select id_percorso 
    from elem.percorsi 
    where cod_percorso = %s 
      and id_categoria_uso in (3,6)
    )
    select coalesce(e.id_piazzola, e.id_elemento) as id, 
        concat(v.nome, ', ',
        coalesce(e.numero_civico, p.numero_civico)) as indirizzo, 
        coalesce(e.riferimento, p.riferimento) as riferimento, 
        te.descrizione, 
        coalesce(p.note,e.note) as note,
        eap.frequenza, fo.descrizione_long, fo.freq_binaria,
        count(e.id_elemento) 
        from elem.elementi_aste_percorso eap
        join elem.aste_percorso ap
        on eap.id_asta_percorso = ap.id_asta_percorso 
        join percorso_sel ps on ps.id_percorso = ap.id_percorso
        join elem.elementi e 
        on eap.id_elemento = e.id_elemento 
        left join elem.piazzole p 
        on p.id_piazzola = e.id_piazzola
        join elem.aste a
        on e.id_asta = a.id_asta 
        join topo.vie v 
        on v.id_via = a.id_via 
        join etl.frequenze_ok fo 
        on eap.frequenza::int = fo.cod_frequenza  
        join elem.tipi_elemento te 
        on te.tipo_elemento = e.tipo_elemento  
        group by coalesce(e.id_piazzola, e.id_elemento) , 
        concat(v.nome, ', ',
        coalesce(e.numero_civico, p.numero_civico)) , 
        coalesce(e.riferimento, p.riferimento), 
        te.descrizione, 
        coalesce(p.note,e.note),
        eap.frequenza, fo.descrizione_long, fo.freq_binaria, ap.num_seq
        order by ap.num_seq asc'''
    
    
    query_elementi_bilaterale='''with valori_bilaterali as (
 select s.id_piazzola, 
 coalesce(cc.codice_cer_corretto::text, ci.cod_cer_mat::text) as cod_cer,
 max(s.riempimento) as riemp_max_svuot,
 max(s.data_ora_svuotamento) as data_ora_svuot,
 min(ci.data_ultimo_agg) as  data_ultimo_agg, 
 round(avg(case when ci.val_riemp > 100 then 100 else ci.val_riemp end)) as riemp_max, 
 avg(mc.media_conf_giorno) as media_conf_giorno
 from idea.svuotamenti s 
 /*join idea.v_ultimi_svuotamenti vus 
on s.targa_contenitore = vus.targa_contenitore 
and s.data_ora_svuotamento = vus.last_data_ora_svuotamento*/ 
left join idea.censimento_idea ci on ci.targa_contenitore = s.targa_contenitore
left join idea.codici_cer cc on cc.codice_cer =ci.cod_cer_mat 
left join idea.mv_conferimenti_per_giorno_ultimo_mese mc on mc.targa_contenitore = s.targa_contenitore
group by s.id_piazzola, 
 coalesce(cc.codice_cer_corretto::text, ci.cod_cer_mat::text)
), percorso_sel as (
    select id_percorso 
    from elem.percorsi 
    where cod_percorso = %s 
      and id_categoria_uso in (3,6)
)
select coalesce(e.id_piazzola, e.id_elemento) as id, 
        concat(v.nome, ', ',
        coalesce(e.numero_civico, p.numero_civico)) as indirizzo, 
        coalesce(e.riferimento, p.riferimento) as riferimento, 
        te.descrizione, 
        coalesce(p.note,e.note) as note,
        eap.frequenza, fo.descrizione_long, fo.freq_binaria,
        count(e.id_elemento), 
        max(vb.riemp_max_svuot) as riemp_max_svuot,
		max(vb.data_ora_svuot) as data_ora_svuot,
		max(vb.data_ultimo_agg) as data_ultimo_agg, 
		max(vb.riemp_max) as riemp_max, 
		round(max(vb.media_conf_giorno)) as media_conf_giorno
        from elem.elementi_aste_percorso eap
        join elem.aste_percorso ap
        on eap.id_asta_percorso = ap.id_asta_percorso 
        join percorso_sel ps on ap.id_percorso = ps.id_percorso
        join elem.elementi e 
        on eap.id_elemento = e.id_elemento 
        left join elem.piazzole p 
        on p.id_piazzola = e.id_piazzola
        join elem.aste a
        on e.id_asta = a.id_asta 
        join topo.vie v 
        on v.id_via = a.id_via 
        join etl.frequenze_ok fo 
        on eap.frequenza::int = fo.cod_frequenza  
        join elem.tipi_elemento te 
        on te.tipo_elemento = e.tipo_elemento
        join elem.tipi_rifiuto tr on tr.tipo_rifiuto = te.tipo_rifiuto
       	left join valori_bilaterali vb on 
       	vb.id_piazzola = e.id_piazzola::text and vb.cod_cer = tr.codice_cer 
		/*where id_percorso = (select id_percorso from elem.percorsi p where p.cod_percorso= '0507124601' and id_categoria_uso in (3,6))  
        */
        group by coalesce(e.id_piazzola, e.id_elemento) , 
        /*concat(v.nome, ', ',
        coalesce(e.numero_civico, p.numero_civico)) ,*/ 
        v.nome, 
        coalesce(e.numero_civico, p.numero_civico),
        coalesce(e.riferimento, p.riferimento), 
        te.descrizione, 
        coalesce(p.note,e.note),
        eap.frequenza, fo.descrizione_long, fo.freq_binaria, ap.num_seq 
        order by ap.num_seq asc'''
    
    
    query_aste= ''' select   v.nome, ap.nota, ap.frequenza, fo.descrizione_long, fo.freq_binaria, 
    sum(a.lung_asta) as ml, 
    sum(a.lung_asta*a.larg_asta) as mq,
    sum(
    case 
        WHEN ap.lato_servizio = 'destro'::text THEN mq.area_d
        WHEN ap.lato_servizio = 'sinistro'::text THEN mq.area_p
            ELSE mq.area
        END * (ap.lung_trattamento / 100::numeric)
    ) as mq_ok
    from elem.aste_percorso ap
    --on eap.id_asta_percorso = ap.id_asta_percorso 
    join elem.aste a
    on ap.id_asta = a.id_asta
    left JOIN elem.v_aste_mq mq ON mq.id_asta = ap.id_asta
    join topo.vie v 
    on v.id_via = a.id_via 
    join etl.frequenze_ok fo 
    on ap.frequenza::int = fo.cod_frequenza  
    where ap.id_percorso = (select id_percorso from elem.percorsi p where p.cod_percorso= %s and p.id_categoria_uso in (3,6))  
    group  by v.nome, ap.nota, ap.frequenza, fo.descrizione_long, fo.freq_binaria
    order by min(ap.num_seq) asc'''
    
    
    
    
    
    
    # se check_b == 1 devo recuperare i codici percorso
    
    if check_b==1:
        codici_percorsi=[]
    
        try:
            curr.execute(query_bilaterali)
            elenco_percorsi_bilaterali=curr.fetchall()
        except Exception as e:
            logger.error(e)
            sent_log_by_mail(filename,logfile)
            #print('''Manca l'input''')
            exit()
    
        for epb in elenco_percorsi_bilaterali:
            codici_percorsi.append(epb[0])
    
    # faccio ciclo su array codici_percorsi dove: 
    # nel caso di check_b = 0 c'è solo il codice percorso singolo
    # nel caso di check_b = 1 ci sono tanti codici
    for cc in codici_percorsi:
    
    
        try:
            curr.execute(query, (cc,))
            dettagli_percorso=curr.fetchall()
        except Exception as e:
            logger.error(e)
            sent_log_by_mail(filename,logfile)
            #print('''Manca l'input''')
            exit()


        k=0       
        #logger.debug('la lunghezza della lista dettagli_percorso è: {}'.format(len(dettagli_percorso)))
        for dd in dettagli_percorso:
            #logger.debug('il codice_percorso in dettagli_percorso è: {}'.format(dd[0]))
            tipologia=dd[3]
            if check_b==1:
                descrizione_p=dd[2][::-1][0:3][::-1].strip() # prendo solo il codice finale (ultime 3 lettere es. A12 )
            else:
                descrizione_p=dd[2]
            
        #logger.info(tipologia)
        #logger.info(descrizione_p)
        # tronco a 32 caratteri che è il massimo consentito per il titolo
        title_sheet=f'{cc} - {descrizione_p}'[0:31]
        #logger.debug(title_sheet)
        curr.close()
        curr = conn.cursor()
    
        
        
        
        

        
        w = workbook.add_worksheet(title_sheet)
        cell_format = workbook.add_format()
        cell_format.set_bold(True)
        cell_format.set_border(1)
        
        

        cell_format_title = workbook.add_format({
            'border': 1,
            'text_wrap': True,
            'bold': True,
            'align': 'center',
            'valign': 'vcenter',
            'font_color': '#144798'
        })
        #cell_format_title.set_border(1)
        #cell_format_title.set_bold(True)
        #cell_format_title.set_font_color('#144798')


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
            'num_format': 'dd/mm/yyyy hh:mm', 
            'border': 1,
            'align': 'center'
        })

        cell_format_ev = workbook.add_format({
            'border': 1,
            'font_color': '#ff0000',
            'bg_color': '#fce805'
        })
        
        data_format_ev = workbook.add_format({
            'border': 1,
            'num_format': 'dd/mm/yyyy hh:mm',
            'font_color': '#ff0000',
            'bg_color': '#fce805'
        })

        # PAGE SETUP
        w.set_landscape()
        w.set_paper(9) #A4
        w.center_vertically()
        #w.set_margins(left, right, top=0.5, bottom=1.5)
        w.repeat_rows(6)
        w.set_header('&CPage &P of &N')
        w.set_footer('&RReport prodotto grazie a SW del gruppo APTE (assterritorio@amiu.genova.it) in data &D alle ore &T')
        w.fit_to_pages(1, 0) # 1 page wide and as long as necessary.


        w.insert_image('I2', '{}/img/logo_amiu.jpg'.format(path), {'x_scale': 0.8, 'y_scale': 0.8, 'x_offset': 10, 'y_offset': 10})

        if tipologia=='racc' or tipologia=='altro':
            w.set_column(0,0, 6)
            w.set_column(1,1, 34)
            w.set_column(2,2, 40)
            w.set_column(3,3, 30)
            w.set_column(4,4, 30)
            w.set_column(5,11, 6.5)
            if check_b==1:
                w.set_column(12,12,16)
                w.set_column(14,14,16)
                w.set_row(5,60)
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


        w.write('A1', 'codice', cell_format_title) 
        if check_s==0:
            w.write('C1', 'Vers', cell_format_title) 
            w.write('C2', 'Turno', cell_format_title) 
            w.write('C3', 'Stagion.', cell_format_title) 
            w.write('C4', 'Data ultima mod', cell_format_title) 
        
        w.write('A2', 'Serv', cell_format_title)     
        w.write('A3', 'Mezzo', cell_format_title) 
        w.write('A4', 'UT', cell_format_title) 
        w.write('C4', 'Frequenza', cell_format_title) 



        w.write(0, 4, 'Descrizione', cell_format_title) 
        



        k=0       
        for dd in dettagli_percorso:
            logger.info('''Inizio a scrivere l'intestazione''')
            w.write('B1', dd[0], cell_format_grande) # codice percorso
            if check_s==0: 
                w.write('D1', dd[1], cell_format) # versione
                w.write('D2', dd[6], cell_format) # turno
                w.write('D3', dd[8], cell_format) # stagionalita
                w.merge_range('F2:H2', dd[10], data_format) # data modifica

            w.write('B2', dd[4], cell_format) # tipo servizio
            w.write('B3', dd[7], cell_format) # mezzo
            w.write('B4', dd[5], cell_format) # ut 
            
            #w.write(3, 3, dd[9], cell_format) # frequenza (spostata sotto)

            w.merge_range('D4:H4', dd[9], merge_format) # frequenza
            w.merge_range('F1:L1', dd[2], merge_format_grande) # descrizione percorso
            
            descr_percorso=dd[2]
            
            
            
            
            
        
        if tipologia=='racc' or tipologia=='altro' :
            w.write(5,0, 'PdR', cell_format_title)
            w.write(5,1, 'Indirizzo', cell_format_title)
            w.write(5,2, 'Riferimento', cell_format_title)
            w.write(5,3, 'Tipologia',cell_format_title)
            w.write(5,4, 'Note', cell_format_title)
            if check_b==1:
                w.write(5,12, 'Ultimo agg', cell_format_title)
                w.write(5,13, 'Riemp', cell_format_title)
                w.write(5,14, 'Ultimo svuot', cell_format_title)
                w.write(5,15, 'Riemp svuot', cell_format_title)
                w.write(5,16, 'Media conf ultimo mese', cell_format_title)



            

            if check_b==1:
                try:
                    curr.execute(query_elementi_bilaterale, (cc,))
                    lista_elementi=curr.fetchall()
                except Exception as e:
                    logger.error(e)
                    sent_log_by_mail(filename,logfile)
            else:    
                try:
                    curr.execute(query_elementi, (cc,))
                    lista_elementi=curr.fetchall()
                except Exception as e:
                    logger.error(e)
                    sent_log_by_mail(filename,logfile)


            k=0       
            for vv in lista_elementi:
                if check_b==1 and coalesce_py(vv[12],0) > 80:
                    cf=cell_format_ev
                    df=data_format_ev
                else: 
                    cf=cell_format
                    df=data_format
                    
                    
                w.write(6+k,0, vv[0], cf) # id
                w.write(6+k,1, vv[1], cf) # indirizzo
                w.write(6+k,2, vv[2], cf) # riferimento
                w.write(6+k,3, '{} x {}'.format(vv[8],vv[3]), cf) # tipo
                w.write(6+k,4, vv[4], cf) # note
                i=0
                while i<7:
                    c=i-datetime.datetime.today().weekday()
                    giorno = datetime.datetime.today()+datetime.timedelta(days=c)
                    g2=giorno.weekday()+1
                    g1=sett(giorno.day)
                    w.write(5,5+i, '{}({}s)'.format(dayNameFromWeekday(giorno.weekday()), g1), cell_format_title)
                    stringa='{}{}'.format(g1, g2)
                    logger.debug('g1g2={}'.format(stringa))
                    logger.debug('freqbinaria={}'.format(vv[7]))
                    logger.debug(ctrl_freq(stringa,vv[7]))
                    if ctrl_freq(stringa,vv[7])[0]==1:
                        if check_s==1:
                            w.write(6+k,5+i, 'x', cell_format_colorato)
                        else:
                            w.write(6+k,5+i, 'x', cell_format)
                    else: 
                        if ctrl_freq(stringa,vv[7])[1]=='':
                            w.write(6+k,5+i, None, cell_format)
                        else:
                            w.write(6+k,5+i, ctrl_freq(stringa,vv[7])[1], cell_format)
                    i+=1
                if check_b ==1:
                    # 9 riemp_max_svuot
                    # 10 last svuot
                    # 11 last update
                    # 12 riemp_last_update
                    # 13 media conf giorni
                    w.write(6+k, 12, vv[11], df)
                    w.write(6+k, 13, vv[12], cf) 
                    w.write(6+k, 14, vv[10], df)
                    w.write(6+k, 15, vv[9], cf)
                    w.write(6+k, 16, vv[13], cf)
                k+=1
            
            if k==0:
                logger.error("Percorso non presente su SIT")
                sys.exit("Percorso non presente su SIT")
        elif tipologia=='spazz':
            
            w.write(5,0, 'Via', cell_format_title)
            if check_s==0:
                w.write(5,1, 'Nota via', cell_format_title)
                w.write(5,2, 'Metri lineari', cell_format_title)
                w.write(5,3, 'Metri quadrati',cell_format_title)
                w.write(5,4, 'Frequenza', cell_format_title)
            else: 
                w.merge_range('B6:E6', 'Nota via', cell_format_title)


            


            


            try:
                curr.execute(query_aste, (cc,))
                lista_aste=curr.fetchall()
            except Exception as e:
                logger.error(e)
                sent_log_by_mail(filename,logfile)


            #inizializzo le somme
            mq_lun=0
            mq_mar=0
            mq_mer=0
            mq_gio=0
            mq_ven=0
            mq_sab=0
            mq_dom=0
            
            k=0       
            for vv in lista_aste:
                w.write(6+k,0, vv[0], cell_format)
                if check_s==0:
                    w.write(6+k,1, vv[1], cell_format)
                    w.write(6+k,2, vv[5], cell_format)
                    w.write(6+k,3, round(vv[7]), cell_format)
                    w.write(6+k,4, vv[3], cell_format)
                else:
                    w.merge_range(6+k, 1, 6+k, 4, vv[1], cell_format)
                i=0
                while i<7:
                    c=i-datetime.datetime.today().weekday()
                    giorno = datetime.datetime.today()+datetime.timedelta(days=c)
                    g2=giorno.weekday()+1
                    g1=sett(giorno.day)
                    w.write(5,5+i, '{} ({}s)'.format(dayNameFromWeekday(giorno.weekday()), g1), cell_format_title)
                    stringa='{}{}'.format(g1, g2)
                    #logger.debug(stringa)
                    #logger.debug(vv[4])
                    #logger.debug(ctrl_freq(stringa,vv[4]))
                    if ctrl_freq(stringa,vv[4])[0]==1:
                        if check_s == 0:
                            w.write(6+k,5+i, 'x', cell_format)
                        else: 
                            w.write(6+k,5+i, 'x', cell_format_colorato)
                        #w.write(6+k,5+i, '{} mq'.format(vv[6]), cell_format)
                        # faccio sommatoria per giorno
                        if i==0:
                            mq_lun+=vv[7]
                        elif i==1:
                            mq_mar+=vv[7]
                        elif i==2:
                            mq_mer+=vv[7]
                        elif i==3:
                            mq_gio+=vv[7]
                        elif i==4:
                            mq_ven+=vv[7]
                        elif i==5:
                            mq_sab+=vv[7]
                        elif i==6:
                            mq_dom+=vv[7]
                    else:
                        # se non è nel giorno controllo se lo stesso giorno c'è in altre settimane
                        if ctrl_freq(stringa,vv[4])[1]=='':
                            w.write(6+k,5+i, None, cell_format)
                        else:
                            w.write(6+k,5+i, ctrl_freq(stringa,vv[4])[1], cell_format) 
                    i+=1
                k+=1
            if check_s == 0:    
                if mq_lun>0:
                    w.write(6+k,5, '{} mq'.format(round(mq_lun)), cell_format_title)
                if mq_mar>0:
                    w.write(6+k,6, '{} mq'.format(round(mq_mar)), cell_format_title)
                if mq_mer>0:
                    w.write(6+k,7, '{} mq'.format(round(mq_mer)), cell_format_title)
                if mq_gio>0:
                    w.write(6+k,8, '{} mq'.format(round(mq_gio)), cell_format_title)
                if mq_ven>0:
                    w.write(6+k,9, '{} mq'.format(round(mq_ven)), cell_format_title)
                if mq_sab>0:
                    w.write(6+k,10, '{} mq'.format(round(mq_sab)), cell_format_title)
                if mq_dom>0:
                    w.write(6+k,11, '{} mq'.format(round(mq_dom)), cell_format_title)
            
            
            
            if k==0:
                logger.error("Percorso non presente su SIT")
                sys.exit("Percorso non presente su SIT")







    # chiuso il ciclo sui percorsi

    workbook.close()
    #sent_log_by_mail(filename,logfile)

    

    '''
    if (weekday == 0 and day in (1, 2, 3)) or (weekday in (1, 2, 3, 4) and day == 1):
        print("Today {0} is the first weekday of the month.".format(date_str))
    else:
        print("Today {0} is not the first weekday of the month.".format(date_str))
    '''


    '''
    import win32com.client as win32
    excel = win32.gencache.EnsureDispatch('Excel.Application')
    wb = excel.Workbooks.Open(file_report)
    ws = wb.Worksheets("Sheet1")
    ws.Columns.AutoFit()
    wb.Save()
    excel.Application.Quit()
    '''
    
    
    
    
    # questa parte  non fa parte del ciclo perchè è sicuramente associata al caso di check_b == 0
    
    # cerco se le variazioni sono di qualche utente o se sono di procedure id_user=0
    
    if  check_m==1 and arg4>0:
        query_variazioni='''select su."name", 
            to_char(sh.datetime, 'DD/MM/YYYY HH24:MI:SS') as data_ora, 
            sh.description, 
            sh.id_piazzola, sh.id_elemento 
            from util.sys_history sh 
            join util.sys_users su on sh.id_user=su.id_user
            where sh.id_percorso
            in (select max(id_percorso) from elem.percorsi p 
            where cod_percorso = %s )
            and datetime > (now()::date - interval '%s' day)
            and sh.id_user != 0
            order by datetime desc'''
        
        
        try:
            curr.execute(query_variazioni, (cc,arg4))
            lista_variazioni=curr.fetchall()
        except Exception as e:
            logger.error(e)
            sent_log_by_mail(filename,logfile)    
        
        
        
        variazioni=''
        if len(lista_variazioni)> 0:
            variazioni='<ul>'
        for vv in lista_variazioni: 
            variazioni ='{0}<li> Data e ora: {1} User SIT: {2} - Descrizione: {3}'.format(variazioni, vv[1], vv[0], vv[2])
            if vv[3] is not None:
                variazioni='{0} - Piazz {1}'.format(variazioni, vv[3])
            if vv[4] is not None:
                variazioni='{0} - Elem {1}'.format(variazioni, vv[4])
            variazioni='{0}</li>'.format(variazioni)
        if len(lista_variazioni)> 0:
            variazioni='{0}</ul>'.format(variazioni)
    
    
    if check_m==1 and variazioni !='':
        # Create a secure SSL context
        context = ssl.create_default_context()



        # messaggio='Test invio messaggio'


        subject = "Percorso {} variato".format(codice)
            
            
        ##sender_email = user_mail
        receiver_email='assterritorio@amiu.genova.it'
        debug_email='roberto.marzocchi@amiu.genova.it'
        
        
        body = """Il percorso {0} - {1} è variato. 
        Di seguito le variazioni avvenute nei {3} giorni precedenti:
        {4}       
        <br>In allegato il nuovo report.
        <br>In caso di incongruenze aggiornare il SIT o contattare i RUTT competenti.
        <br><br>
        AMIU Assistenza Territorio<br>
        <img src="cid:image1" alt="Logo" width=197>
        <br>{2}
        """.format(codice, descr_percorso, mail_footer, arg4, variazioni)


        # Create a multipart message and set headers
        message = MIMEMultipart()
        message["From"] = sender_email
        message["To"] =  indirizzo_mail_per_invio
        message["Cc"] = receiver_email
        message["Subject"] = subject
        #message["Bcc"] = debug_email  # Recommended for mass emails
        message.preamble = "File con variazioni a percorso"


            
                            
        # Add body to email
        message.attach(MIMEText(body, "html"))


        #aggiungo logo 
        logoname='{}/img/logo_amiu.jpg'.format(path)
        immagine(message,logoname)
        
        
        # aggiunto allegato (usando la funzione importata)
        allegato(message, file_report, '{}.xlsx'.format(nome_file))
        # Add body to email
        #message.attach(MIMEText(body, "plain"))
        
        
        #text = message.as_string()

        logger.info("Richiamo la funzione per inviare mail")
        invio=invio_messaggio(message)
        logger.info(invio)
    
    
    if check_m==1 and variazioni =='':
        logger.warning('Non mando mail perchè non ci sono variazioni sostanziali nei {0} giorni precedenti'.format(arg4))
    
    ##################################################################################################
    #                               CHIUDO LE CONNESSIONI
    ################################################################################################## 
    logger.info("Chiudo definitivamente le connesioni al DB")
    conn.close()

    # check se c_handller contiene almeno una riga 
    error_log_mail(errorfile, 'roberto.marzocchi@amiu.genova.it', os.path.basename(__file__), logger)

if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4])
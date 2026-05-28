#!/usr/bin/env python
# -*- coding: utf-8 -*-

# AMIU copyleft 2026
# Roberta Fagandini, Roberto Marzocchi


'''
script per richiamare gli altri script con un unico comando
'''

import sys

from app.export_driver_ekovision import main as export_driver_ekovision
from app.ecopunti_parte2 import main as ecopunti_parte2
from app.report_settimanali_percorsi_ok import main as report_settimanali_percorsi_ok
from app.rimozione_schede_eko import main as rimozione_schede_eko
from app.seleziona_utenze_vie import main as seleziona_utenze_vie





if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Missing command")
        sys.exit(1)

    cmd = sys.argv[1]
    args = sys.argv[2:]

    if cmd == "export_driver_ekovision":
        export_driver_ekovision(args)
    elif cmd == "ecopunti_parte2":
        ecopunti_parte2(args)
    elif cmd == "report_settimanali_percorsi_ok":
        report_settimanali_percorsi_ok(args)
    elif cmd == "rimozione_schede_eko":
        rimozione_schede_eko(args)
    elif cmd == "seleziona_utenze_vie":
        seleziona_utenze_vie(args)
    else:
        print("Unknown command")

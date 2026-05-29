Cartella con script python (es. esportazione tabelle)

NOTA BENE 

E' richiesto che sul server sia installato python3 e alcune librerie base 


# rivisto il 27/05/2026

Su nuovo server installare solo python 

```
sudo apt install -y python3 python3-venv python3-pip
```

Vado dentro la directory py_scripts
```
cd /var/www/sit_addons/py_scripts
```

Lancio i seguenti comandi: 

```
python3 -m venv venv
source venv/bin/activate
pip3 install -r requirements.txt
```


# Per vedere variabili non usate si può usare l'estensione vulture di python 

1. Entro nel venv

```
source venv/bin/activate
```

2. Lancio _vulture_ escludendo le cartelle del venv

```
vulture . --exclude venv,__pycache__,build,dist > /home/procedure/anomalie_python.txt
```


NB per uscire da venv basta digitare `deactivate`
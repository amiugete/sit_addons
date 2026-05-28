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
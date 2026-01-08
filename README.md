# sit_addons

Componenti aggiuntivi (add-ons) del SIT aziendale

## Installazione

Vedi dipendenze sotto e crea i file nascosti:

- *conn_test.php*  per creare le connessioni al DB usato dall'ambiente di test
- *conn.php* per creare le connessioni al DB usato dall'ambiente di produzione

```
<?php 
$conn = pg_connect("host=XXX.XXX:X.XXX" port=5432 dbname=XXXX user=XXXX password=XXXXXX");
if (!$conn) {
        die('<br>Could not connect to DB PostgreSQL, please contact the administrator.');
}



include 'ldap.php';
include 'jwt.php';
?>
```

- *ldap.php*: parte segreta per connettersi al dominio di AMIU e verificare gli utenti (vedi autenticazione Gruppo Sigla)

```
<?php
$ldapDomain = "@domain.com"; 			// set here your ldap domain
$ldapHost = "ldap://XXX.XXX:X.XXX"; 	// set here your ldap host
$ldapPort = "389"; 						// ldap Port (default 389)
$ldapUser  = "USER"; 						// ldap User (rdn or dn)
$ldapPassword = "PWD";
?>
```

- *jwt.php*: parte segreta per creare jwt al SIT di AMIU (vedi autenticazione Gruppo Sigla)

```
<?php
// provenienza
$iss= 'XXXX';
// PWD
$secret_pwd = 'XXXXXXXXXXXX';
?>
```

## Dipendenze

### Usando composer

Tutte le librerie sono state installate usando composer che garantisce una più semplice mantenibilità futura

```

composer require eternicode/bootstrap-datepicker
composer require components/jquery
composer require twbs/bootstrap:5.3.1
composer require twbs/bootstrap-icons
composer require FortAwesome/Font-Awesome
composer require snapappointments/bootstrap-select
composer require wenzhixin/bootstrap-table
composer require picqer/php-barcode-generator
composer require firebase/php-jwt
composer require moment/moment
composer require hhurz/tableexport.jquery.plugin
```

Per installare l'applicazione è sufficiente lanciare un `composer install` nella cartella principale dlel'applicazione dove è contenuto il file `composer.json`.

Per fare update di versione è sufficiente un `composer update`



## Permessi

Ci sono alcune pagine che sono visibili solo a determinati utenti del SIT. 

Il tutto è controllato dalla tabella util_ns.sys_users_addons dove per ora (gennaio 2026) ci sono 5 gruppi:

- esternalizzati
- sovrariempimenti
- sovrariempimenti_admin
- coge: controllo gestione
- utenze: estrazione utenze per via ed area



# Per mettere in manutenzione il sito 

Agire sulla pagina `navbar_up.php` modificando il parametro `$in_manutenzione = 1;`

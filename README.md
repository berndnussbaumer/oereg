# oereg
Gästeregistrierung mit QR Code

Installation:

Es wird ein Apache Server mit PHP und myslq benötigt.
Entweder selbst installieren oder ein Paket bei einem Webhoster nehmen.

Datenbank:
Es liegt die Datei oereg.sql bei. Diese am mysql Server ausführen, damit werden die benötigten Tabellen angelegt.

PHP Dateien:
Alle anderen Dateien ins Root Verzeichnis des Servers kopieren.

Konfiguration:
In der Datei "config.php" werden Parameter gesetzt:
- $codeProtocoll: entweder "http://" oder "https://"
- $codeUrl: die IP-Adresse oder der Hostname des Webservers. z.B. "restaurant_xy.at". Falls die Dateien in einem Unterverzeichnis von root des Webservers liegen, muss der Name des Unterverzeichnisses angehängt werden. z.B. "restaurant_xy.at/oereg".
- $codeUrlCheck:  analog zu $codeUrl, gefolgt von "/check_code.php". Diese Adresse wird im QR Code kodiert, den ein Kunde erhält. Damit kann (soweit erwünscht) kontrolliert werden, ob eine Registrierung gültig ist.
- $show_reg_name: Standardmäßig false - Damit werden auf der Seite der erfolgreichen Registrierung die Daten angezeigt. Aus Datenschutzgründen sollte darauf verzichtet werden.
- $show_dsvgo: DSVGO Text wird Gästen angezeigt. Ebenfalls muss eine Gaststätte einen Datenschutzbeauftragten angeben. Dessen Kontaktdaten werden bei erfolgreicher Registrierung eines Gastes angezeigt.
- $staette_mail_activation: Falls das Service für mehrere Gaststätten angeboten wird, kann damit aktiviert werden, dass Gaststätten bei der Registrierung eine Email Aktivierung vornehmen müssen.
- $show_agb:  Falls das Service für mehrere Gaststätten angeboten wird, können AGBs des Anbieters hinterlegt werden und diese muss eine Gaststätte beim Registrieren bestätigen.
- $show_uid: Gaststätten müssen ihre UID bekannt geben.
- $guest_delete: Auf der Gästeliste können Einträge markiert und gelöscht werden.
- $dsvgo_text: Der DSVGO Text, der Kunden angezeigt wird.

In der Datei "set_db_vars.php" muss der Datenbankzugriff hinterlegt werden.
- $oereg_sec_check: Der Wert dieser Variablen muss dem in den aufrufenden Skripten entsprechen.
- $myServer = "localhost": Serveradresse
- $myUser = "root": User für mysql DB Zugriff
- $myPasswd = "": Passwort des Users
- $myDB = "oereg": Datenbankname - Dieser muss geändert werden, falls der Standard-Datenbankname "oereg" geändert wird.

Verwendung:
Es liegt eine Datei "Anleitung.pdf" bei.

Sicherheit:
Wenn eine neue Gaststätte angelegt wird, muss dabei ein Passwort gewählt werden.
Von diesem Passwort wird ein Hashwert in der Datenbank gespeichert. Beim Aurufen von Funktionen wird das Passwort gegen diesen Hashwert geprüft.
Ebenfalls wird ein Public/Private Schlüsselpaar erzeugt. Der Public Key wird in die Datenbank geschrieben. Der Private Key wird AES verschlüsselt abgespeichert.
Gästedaten werden mit dem Public Key verschlüsselt.
Beim Abrufen dieser Daten ist das Passwort notwendig. Damit wird der Private-Key entschlüsselt und damit die Gästedaten entschlüsselt.
Somit kann ausschließlich mit dem Passwort auf die Daten zugegriffen werden. 
Bei Verlust des Passworts ist kein Zugriff auf die verschlüsselten Gästedaten möglich.



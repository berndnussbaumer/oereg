<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) { // kein direkte Aufruf erlaubt
	die ("HTTP Error 404");
}

function encrypt_aes($plaintext, $password) {
/*	Entschlüsselt eine Text mit einem Passwort

	@param string $plaintext Text der vershlüsselt werden soll
	@param string $password Hash Wert des Passworts aus der Datenbank
	
	@return string Der verschlüsselte Text
*/
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return $iv . $hash . $ciphertext;
}

function decrypt_aes($ivHashCiphertext, $password) {
/*	Entschlüsselt eine Text mit einem Passwort

	@param string $ivHashCiphertext Verschlüsselter Text aus der Datenbank
	@param string $password Hash Wert des Passworts aus der Datenbank
	
	@return string Der entschlüsselte Text
*/
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}


function generate_key_pair() {
/*	Erzeugt ein RSA Schlüssel Paar

	@return Array mit public key und private key (jeweils Strings)
*/
	include('phpseclib/RSA.php');
	$rsa = new Crypt_RSA();
	extract($rsa->createKey(1024));
	return [$publickey, $privatekey];
}

function checks_gast($staette, $tischnr, $name, $telnr, $email) {
/*	Prüft die eingegebenen Werte

	@param string $staette Gaststättennummer
	@param string $tischnr Tischnnummer
	@param string $name Gast Name
	@param string $telnr Gast Telefonnnummer
	@param string $email Gast Mail Adresse
	
	@return bool true wenn alle Checks Ok sind, sonst false
	
	ToDo auf SQL Injection prüfen
*/
	$error = false;
	if ($staette == "") {
		echo '<font color=red>Die Gaststättennummer muss angegeben werden.</font><br />';
		$error = true;
	}
	if ($tischnr == "") {
		echo '<font color=red>Die Tischnummer muss angegeben werden.</font><br />';
		$error = true;
	}
	if ($name == "") {
		echo '<font color=red>Der Name muss angegeben werden.</font><br />';
		$error = true;
	}
	if ($telnr == "") {
		echo '<font color=red>Die Telefonnummer muss angegeben werden.</font><br />';
		$error = true;
	}
	if ($email == "") {
		echo '<font color=red>Die Emailadresse muss angegeben werden.</font><br />';
		$error = true;
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo '<font color=red>Eine gültige Emailadresse muss angegeben werden.</font><br />';
		$error = true;
	}
    return !$error; // return das Gegenteil von $error
}

function checks_gaststaette($name, $adresse, $plz, $ort, $dsvgo, $password, $password_confirm) {
/*	Prüft die eingegebenen Werte

	@param string $name Gaststättenname
	@param string $adresse Adresse
	@param string $plz PLZ
	@param string $ort Ort
	@param string $dsvgo Datenschutzbeauftragter
	@param string $password Passwort
	@param string $password_confirm Wiederholung des Passworts
	
	@return bool true wenn alle Checks Ok sind, sonst false
	
	ToDo auf SQL Injection prüfen
*/
	$error = false;
	if ($name == "") {
		echo '<font color=red>Name darf nicht leer sein!</font><br />';
		$error = true;
	}
	if ($adresse == "") {
		echo '<font color=red>Adresse darf nicht leer sein!</font><br />';
		$error = true;
	}
	if ($plz == "") {
		echo '<font color=red>Plz darf nicht leer sein!</font><br />';
		$error = true;
	}
	if ($ort == "") {
		echo '<font color=red>Ort darf nicht leer sein!</font><br />';
		$error = true;
	}
	if ($dsvgo == "") {
		echo '<font color=red>Datenschutzbeauftragter darf nicht leer sein!</font><br />';
		$error = true;
	}
    if (strlen($password) < 8) {
		echo '<font color=red>Passwort muss mindestens 8 Zeichen lang sein!</font><br />';
		$error = true;
    }

    if (!preg_match("#[0-9]+#", $password)) {
 		echo '<font color=red>Passwort muss mindest eine Zahl beinhalten!</font><br />';
		$error = true;
    }

    if (!preg_match("#[a-zA-Z]+#", $password)) {
		echo '<font color=red>Passwort muss mindestens einen Buchstaben beinhalten!</font><br />';
		$error = true;
    }     
	if ($password !== $password_confirm) {
		echo '<font color=red>Passwörter müssen gleich sein!</font><br />';
		$error = true;
	}
	return !$error;
}		



?>
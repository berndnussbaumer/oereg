<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">
<script> <!-- JS Funktion, die den Absendebutton erst aktiviert, wenn die AGBs angeklickt wurden -->
function agb_click() {
  var agb = document.getElementById("agb");
  var submitButton = document.getElementById("oereg_submit");
  if (agb.checked == true){
	submitButton.disabled = false;
  } else {
    submitButton.disabled = true;
  }
}
</script>
</head><body>
<?php

require_once('utils.php');
require_once('config.php');

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	$show_form = true; // Initialisierung der Variablen
	$name1 = "";
	$email = "";
	$adresse = "";
	$plz = "";
	$ort = "";
	$dsvgo = "";
	$uid = "";
	if ($_POST) { // Das Formular wurde abgeschickt
		$save_data = true;
		$name1 = $_POST["name1"];
		$email = $_POST["email"];
		$adresse = $_POST["adresse"];
		$plz = $_POST["plz"];
		$ort = $_POST["ort"];
		if ($show_dsvgo) {
			$dsvgo = $_POST["dsvgo"];
		} else {
			$dsvgo = 'N.N.';
		}
		$uid = '';
		if ($show_uid) {
			$uid = $_POST["uid"];
		}
		$password = $_POST["passwd"];
		$password_confirm = $_POST["passwd1"];
		if (!checks_gaststaette($name1, $adresse, $plz, $ort, $dsvgo, $password, $password_confirm)) { // Prüfe auf valide Eingaben
			$save_data = false;
		}
		if ($save_data) {
			require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
			$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
			$pwd_hash = password_hash($password, PASSWORD_DEFAULT); // Vom Passwort wird ein Hash erzeugt. Gegen den wird beim späteren Login gecheckt
			$rsa_keys = generate_key_pair(); // erzeuge ein RSA Schlüsselpaar
			$rsa_public_key = $rsa_keys[0];
			$rsa_private_key = base64_encode(encrypt_aes($rsa_keys[1], $password)); // der private Schlüssel wird mit AES verschlüsselt, Base64 kodiert und so in die DB geschrieben. Wird zum Auslesen der Gästeliste benötigt. Der unverschlüsselte private key wird nirgends gespeichert und nach Beendigung des Skripts nicht mehr verfügbar.
			$sql = "INSERT INTO staette (name, email, adresse, plz, ort, uid, dsvgo, crypt_key, public_key, private_key, aktiv_code, aktiviert) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $link->prepare($sql); // Speichere die Daten der Gaststätte
			if ($staette_mail_activation) { // wenn die Mail Aktivierung eingeschaltet ist, wird ein Aktivierungscode erstellt, ansonsten gleich aktiviert.
				$aktiv_code = base64_encode(password_hash(uniqid(), PASSWORD_DEFAULT));
				$aktiviert = 0;
			} else {
				$aktiv_code = "";
				$aktiviert = 1;
			}
			$stmt->bind_param('sssssssssssi', $name1, $email, $adresse, $plz, $ort, $uid, $dsvgo, $pwd_hash, $rsa_public_key, $rsa_private_key, $aktiv_code, $aktiviert);
			if ($stmt->execute()) {
				$show_form = false;
				$last_id = $stmt->insert_id;
				echo '<div class="w3-container w3-green" style="text-align: center">';
				echo '<h1>Registrierung erfolgreich!</h1>';
				echo '<h1>Ihre Gaststättennummer lautet:<br />' . $last_id . '</h1>';
				echo '<br />Diese Nummer und Ihr Passwort bitte gut merken!<br />Sie brauchen Sie, um die Gästeliste und QR Codes drucken zu können.<br /><br />';
				if ($staette_mail_activation) {
					require_once('phpmailer/send_activation_mail.php');
					if (send_activation_mail($email, $name1, $aktiv_code, $codeProtocoll, $codeUrl, $staette_mail_server, $staette_mail_user, $staette_mail_passwd, $staette_mail_from, $staette_mail_from_name, $staette_mail_reply)) {
						echo '<br /><h2>Zur Aktivierung folgen Sie bitte dem Link im Mail, das sie soeben erhalten haben.</h2>';
						echo '<br />Prüfen Sie bitte Ihren Spam Ordner, falls Sie das Mail nicht sehen können.<br /><br />';
					} else {
						echo '<br /><h2>Email zur Aktivierung konnte nicht verschickt werden.</h2>';
					}
				}
				echo '</div>';
			} else {
				echo '<div class="w3-container w3-red">';
				echo '<h1>Registrierung nicht erfolgreich!</h1>';
				echo '<br />Bitte versuchen Sie es etwas später noch einmal oder wenden Sie sich an den Support, um den Fehler zu beheben.';
				echo '</div>';
			}
			$stmt->close();
			mysqli_close($link);
		}
	} 
	if ($show_form) { // Zeige das Formular zur Registrierung einer Gaststätte an
		echo '<div class="w3-container w3-blue"><h3>COVID19 Erhebung von Kontaktdaten von Gästen in der Gastronomie - Gaststätte registrieren</h3></div>';
		echo '<form method="POST" class="w3-container w3-blue">';
		echo '<p><label>Name (Bezeichnung der Betriebsstätte):</label><input type="text" name="name1" value="' . $name1 . '" class="w3-input"></p>';
		echo '<p><label>E-Mail Adresse:</label><input type="text" name="email" value="' . $email . '" class="w3-input"></p>';
		echo '<p><label>Adresse:</label><input type="text" name="adresse" value="' . $adresse . '" class="w3-input"></p>';
		echo '<p><label>PLZ:</label><input type="text" name="plz" value="' . $plz . '" class="w3-input"></p>';
		echo '<p><label>Ort:</label><input type="text" name="ort" value="' . $ort . '" class="w3-input"></p>';
		if ($show_dsvgo) {
			echo '<p><label>Datenschutzbeauftragter:</label><input type="text" name="dsvgo" value="' . $dsvgo . '" class="w3-input"></p>';
		}
		if ($show_uid) {
			echo '<p><label>UID:</label><input type="text" name="uid" value="' . $uid . '" class="w3-input"></p>';
		}
		echo '<p><label>Passwort: (mindestens 8 Zeichen lang, mindestens 1 Buchstabe und 1 Zahl)</label><input type="password" name="passwd" value="" class="w3-input"></p>';
		echo '<p><label>Passwort wiederholen:</label><input type="password" name="passwd1" value="" class="w3-input"></p>';
		if ($show_agb) {
			echo '<p><label><input type="checkbox" name="agb" id="agb" class="w3-check" onClick="agb_click();"> <a href="agb.pdf" target="_blank">ABGs</a> akzeptieren</label></p>';
		}
		echo '<p><label><button type="submit" name="oereg_list" id="oereg_submit" class="w3-btn w3-green"';
		if ($show_agb) { echo ' disabled'; }
		echo '>Weiter</button></p>';
		echo '</form>';
		echo '<hr><b>Wichtige Info:</b> Das Passwort, das Sie hier eingegeben, unbedingt merken!<br />Nur mit diesem Passwort kann die Gästeliste entschlüsselt werden.<br />Bei Verlust des Passworts besteht keine Möglichkeit, dieses wiederherzustellen!<br /><br />Auf der nächsten Seite erhalten Sie Ihre Gaststättennummer.<br />Diese Nummer und das Passwort verwenden Sie zum Login, um die Gästeliste aufzurufen.<br /><br />Auch die QR Codes für Ihre Tische können Sie mit dieser Nummer und dem Passwort erstellen.<br /><br />';
	}

?>
</body></html>

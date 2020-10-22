<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">
</head><body>
<?php

/*
	db_list.php - Auflistung der Gaststätten.
	Interne Anwendung
*/

	require_once('utils.php');
	require_once('config.php');

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	$show_login = true; // Soll die Loginseite angezeigt werden?
	$staette = ""; // Initialisierung der Gaststättennummer
	$staette_name = ""; // Initialisierung Gaststättenname
	$password_crypt = ""; // Initialisierung des Passworts
	if ($_POST) { // wenn ein Post request abgesetzt wurde
		$found_key = false;
		require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
		$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
		$staette = $_POST["staette"];
		$password = $_POST["passwd"];
		$sql = "SELECT * FROM staette WHERE id = ?"; // Lade aus der DB die Daten der Gaststätte
		if ($staette_mail_activation) { // wenn die Aktivierung per Mail eingeschaltet ist, wird auch geprüft, ob die Gasstätte aktiv ist.
			$sql .= " AND aktiviert = 1";
		}
		$stmt = $link->prepare($sql);
		$stmt->bind_param('i', $staette);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result) {
			while ($row = $result->fetch_assoc()) { // es kann nur einen Datensatz geben, weil die Nr autoincrement in der DB ist.
				$password_crypt = $row["crypt_key"];
				if (!isset($_POST["oereg_list_delete"]) && password_verify($password, $password_crypt)) { // check, ob das Passwort korrekt ist.
					$staette_name = $row["name"];
					$staette_private_key_encrypted = $row["private_key"];
					$found_key = true;
					$show_login = false;
				}
				if (isset($_POST["oereg_list_delete"]) && $password == base64_encode(md5($password_crypt))) { // check, ob das Passwort korrekt ist.
					$staette_name = $row["name"];
					$staette_private_key_encrypted = $row["private_key"];
					$found_key = true;
					$show_login = false;
				}
				$password_crypt_md5 = base64_encode(md5($password_crypt));
			}
		} 
		$stmt->close();
		if (!$found_key) {
			echo '<div class="w3-container w3-red"><h1>Login nicht erfolgreich!</h1></div>';
		} 
		if ($found_key && isset($_POST["oereg_list_delete"])) {
			$sql = "DELETE FROM regs WHERE id = ?"; // Lösche den Eintrag in der Tabelle regs, der auf der Gästeliste markiert wurde
			$stmt = $link->prepare($sql);
			if ($stmt) {
				foreach ($_POST as $var_name=>$var_value) {
					if (substr($var_name, 0, 12) === "delete_gast_") {
						$del_id = substr($var_name, 12);
						$stmt->bind_param('i', $del_id);
						$stmt->execute();
					}
				}
				echo '<div class="w3-container w3-green"><h1>Datensätze gelöscht!</h1></div>';
			} else {
				echo '<div class="w3-container w3-red"><h1>Löschen der Datensätze fehlgeschlagen!</h1></div>';
			}
			$stmt->close();
			$found_key = false;
			$show_login = true;
		}
		if ($found_key) {
			$staette_private_key = decrypt_aes(base64_decode($staette_private_key_encrypted), $password); // der private key wird mit dem Passwort entschlüsselt
			include('phpseclib/RSA.php');
			$rsa = new Crypt_RSA();
			$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
			$rsa->loadKey($staette_private_key); // Lade den Privaten Schlüssel zum entschlüsseln
			$sql = "SELECT * FROM regs WHERE staette_nr = ? ORDER BY zeit DESC;"; // Lade aus der DB die Daten der Gästeliste
			$stmt = $link->prepare($sql);
			$stmt->bind_param('i', $staette);
			$stmt->execute();
			try {
				$result = $stmt->get_result();
				echo '<div class="w3-container w3-blue">';
				echo '<h1>Gästeliste "' . $staette_name . '" (' . $staette . ')</h1>'; // Ausgabe der Gästeliste - Header
				echo '<form name"delete_gastliste" method="POST">';
				if ($guest_delete) {
					echo 'Ausgewählte Einträge <button type="submit" name="oereg_list_delete" class="w3-btn w3-red">Löschen</button> <font size=2><i>Kann nicht rückgängig gemacht werden!
					</i></font><br /><br />'; // Löschen der Einträge, bei denen die Checkbox aktiviert ist
					echo '<input type="hidden" name="staette" value="' . $staette . '">';
					echo '<input type="hidden" name="passwd" value="' . $password_crypt_md5 . '">';
				}
				echo '<table border=1 class="w3-table-all">';
				echo '<tr><th>Tisch Nr</th><th>Datum/Uhrzeit</th><th>Name</th><th>Telefon Nr</th><th>Email</th><th>Nr</th>';
				if ($guest_delete) {
					echo '<th></th>';
				}
				echo '</tr>';
				while ($row = $result->fetch_assoc()) {
					$plain_name1 = $rsa->decrypt(base64_decode($row["name"])); // Entschlüsselung des Gästenamens
					$plain_telnr = $rsa->decrypt(base64_decode($row["telnr"])); // Entschlüsselung der Telefonnummer
					$plain_email = $rsa->decrypt(base64_decode($row["email"])); // Entschlüsselung der Emailadresse
					echo '<tr><td>' . $row["tischnr"] . '</td><td>' . $row["zeit"] . '</td><td>' . $plain_name1 . '</td><td>' . $plain_telnr . '</td><td>' . $plain_email . '</td><td>' . $row["id"] . '';
					if ($guest_delete) {
						echo '<td><input type="checkbox" name="delete_gast_' . $row["id"] . '" id="delete_gast_' . $row["id"] . '" class="w3-check"></td>';
					}
					echo '</td></tr>';
				}
				echo '</table>';
				echo '</form>';
				echo '<br /></div>';
			} catch (Exception $e) {
				echo 'Entschlüsselung der Daten fehlgeschlagen.';
			}
			$stmt->close();
		}
		mysqli_close($link);
	} 
	if ($show_login) { // Loginformular
		echo '<div class="w3-container w3-blue"><h3>Gästeliste abrufen</h3>';
		echo '<form method="POST" class="w3-container w3-blue">';
		echo '<p><label>Gaststätten Nr:</label><input type="text" name="staette" value="' . $staette . '" class="w3-input"></p>';
		echo '<p><label>Passwort:</label><input type="password" name="passwd" value="" class="w3-input"></p>';
		echo '<button type="submit" name="oereg_list" class="w3-btn w3-green">Weiter</button></p>';
		echo '</form></div>';
	}

?>
</body></html>

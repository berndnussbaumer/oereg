<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">
</head><body>
<?php

/*
	staette_qr.php - Ein QR Code pro Tisch wird erzeugt.
	Man muss sich einloggen und optional eine Tischnr angeben.
*/

	require_once('config.php');

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	$show_code = false; // Initialisierung der Variablen
	$staette = "";
	$staette_name = "";
	$tischnr = "";
	if ($_POST) { // Das Loginformular wurde abgeschickt
		$found_key = false;
		require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
		$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
		$staette = $_POST["staette"];
		$tischnr = $_POST["tischnr"];
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
			while ($row = $result->fetch_assoc()) {
				$password_crypt = $row["crypt_key"];
				if (password_verify($password, $password_crypt)) {
					$staette_name = $row["name"]; // zur späteren Ausgabe am Zettel mit dem QR Code
					$found_key = true;
					$show_code = true;
				}
			}
		} 
		$stmt->close();
		if (!$found_key) {
			echo '<h1>Login nicht erfolgreich!</h1>';
		}
		mysqli_close($link);
	} 
	if ($show_code) { // Login war Ok - der QR Code mit Text wird ausgegeben.
		include('./phpqrcode/qrlib.php');

		$codeDir = './qrcodes/';

		require_once('config.php');
		
		$codeContent = $codeUrl. '/?' . $staette . ($tischnr != "" ? '-' . $tischnr : '');
		
		if (!file_exists($codeDir . $staette)) {
			mkdir($codeDir . $staette, 0777, true); // Ein Unterverzeichnis wird für Gaststättennummer angelegt, soweit nicht vorhanden
		}
		
		QRcode::png($codeProtocoll . $codeContent, $codeDir . $staette . '/' . $tischnr . '.png', QR_ECLEVEL_L, 12); // Erzeuge den QR Code in Größe 12
			
		echo '<div style="text-align: center;">'; // Ausgabe des QR Codes und des Texts
		echo '<h1>COVID19 Gaststättenregistrierung</h1>';

		echo '<img src="' .$codeDir . $staette . '/' . $tischnr . '.png" />';
		
		echo '</br>';
//		echo '<h1>Gaststätte: ' . $staette_name . ' (' . $staette . ')</h1>';
//		echo '<h1>' . $staette_name . ' <i>(Nr. ' . $staette . ')</i></h1>';
		echo '<h1>' . $staette_name . '</h1>';
		if ($tischnr != "") {
			echo '<h1>Tisch Nr: ' . $tischnr . '</h1>';
		}
		echo '<h3>Bitte scannen Sie den QR Code mit Ihrem Smartphone ein und geben Sie dann Ihre Daten ein.</h3>';
		echo '<h3>Nach erfolgreicher Registrierung können Sie die Bestätigung herzeigen.</h3>';
		echo '<h3>Als Alternative können Sie die Registrierung auch im Browser unter</h3>';
		echo '<h1>' . $codeContent . '</h1>';
		echo '<h3>durchführen.</h3>';
		echo '</div>';
	} else { // Das Loginformular wird angezeigt, bei erfolglosem Login mit den zuvor eingegeben Daten der Gaststättennummer und der Tischnummer, Passwort ist leer
		echo '<div class="w3-container w3-blue"><h3>QR Code für Tische erstellen</h3></div>';
		echo '<form method="POST" class="w3-container w3-blue">';
		echo '<p><label>Gaststätten Nr:</label><input type="text" name="staette" value="' . $staette . '" class="w3-input"></p>';
		echo '<p><label>Passwort:</label><input type="password" name="passwd" value="" class="w3-input"></p>';
		echo '<p><label>Tisch Nr: <font size=2><i>(Falls Sie keine Tische haben, können Sie dieses Feld leer lassen. Alle Gäste werden dann auf Tisch 1 gebucht.)</i></font></label><input type="text" name="tischnr" value="' . $tischnr . '" class="w3-input"></p>';
		echo '<p><button type="submit" name="oereg_list" class="w3-btn w3-green">Weiter</button>';
		echo '</form>';
		
	}
?>
</body></html>
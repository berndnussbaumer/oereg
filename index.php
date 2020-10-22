<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">
</head><body>
<?php

/*
	index.php - wickelt die Registrierung durch einen Gast ab.
	Es wird entweder direkt aufgerufen oder über den QR Code der gescannt wird.
	Beim QR Code ist bereits die Gaststättennummer und eventuell die Tischnummer inkludiert.
	Falls keine Tischnummer mitkommt, dann wird angenommen, dass es Tischnummer 1 ist (Falls jemand nur einen Tisch hat - dadurch wird der Link kürzer, wenn man es händisch im Browser aufruft).
*/

require_once('config.php');
require_once('utils.php');

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	// Initialiserung der Variablen
	$staette = "";
	$staette_name = "";
	$staette_adresse = "";
	$staette_plz = "";
	$staette_ort = "";
	$staette_dsvgo = "";
	$tischnr = "";
	$name1 = "";
	$telnr = "";
	$email = "";
	$show_form = true;
	$show_reg = false;
	$save_data = false;
	$last_id = false;
	$gast_code = "";
	if ($_POST) { // wenn das Registrierungsforumlar abgeschickt wurde
		$show_form = false;
		$staette = $_POST["staette"];
		$tischnr = $_POST["tischnr"];
		$name1 = $_POST["name1"];
		$telnr = $_POST["telnr"];
		$email = $_POST["email"];
		$save_data = 1;
		if (!checks_gast($staette, $tischnr, $name1, $telnr, $email)) { // Prüfe eingegebene Daten
			$save_data = false;
			$show_form = true;
			$show_reg = false;
		}
	} elseif ($_GET) { // wenn die Seite erstmals aufgerufen wird (z.B. über den QR Code) und Parameter mitgegeben werden
		$vars = $_GET;
		if (count($vars) >= 1) {
			foreach($vars as $key => $value) { // damit der Link kürzer wird, wird der Variablenname aus dem GET request zum Wert. 
				$tmp = $key;
				$pos = strpos($tmp, '-');
				if ($pos === false) {
					$staette = $tmp;
					$tischnr = 1; // wenn keine Tischnummer angegeben wird, wird die Tischnummer auf 1 gesetzt.
				} else {
					$staette = substr($tmp, 0, $pos);
					$tischnr = substr($tmp, $pos + 1);
				}
			}
		}
	}
	
	if ($save_data) {
		$staette_public_key = "";
		require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
		$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
		$sql = "SELECT * FROM staette WHERE id = ?"; // Lade aus der DB die Daten der Gaststätte
		if ($staette_mail_activation) { // wenn die Aktivierung per Mail eingeschaltet ist, wird auch geprüft, ob die Gasstätte aktiv ist.
			$sql .= " AND aktiviert = 1";
		}
		$stmt = $link->prepare($sql);
		$stmt->bind_param('i', $staette);
		$stmt->execute();
		$found_staette = false;
		$result = $stmt->get_result();
		if ($result) {
			while ($row = $result->fetch_assoc()) { // es kann nur einen Datensatz geben, weil die Nr (id) autoincrement in der DB ist.
				$staette_name = $row["name"];
				$staette_adresse = $row["adresse"];
				$staette_plz = $row["plz"];
				$staette_ort = $row["ort"];
				$staette_dsvgo = $row["dsvgo"];
				$staette_public_key = $row["public_key"];
				$found_staette = true;
			}
		} 
		$stmt->close();
		if ($found_staette) {
			include('phpseclib/RSA.php');
			$rsa = new Crypt_RSA();
			$rsa->loadKey($staette_public_key); // Lade den public key
			$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
			$cipher_name = base64_encode($rsa->encrypt($name1)); // Daten werden mit dem Public Key der Gaststätte verschlüsselt und dann Base64 enkodiert und so in die DB gespeichert.
			$cipher_telnr = base64_encode($rsa->encrypt($telnr));
			$cipher_email = base64_encode($rsa->encrypt($email));
			$gast_code = base64_encode(password_hash(uniqid(), PASSWORD_DEFAULT));
			$sql = "INSERT INTO regs (staette_nr, name, telnr, email, tischnr, reg_code) VALUES (?, ?, ?, ?, ?, ?)";
			$stmt = $link->prepare($sql); // Speicher die Daten des Gastes in die DB
			$stmt->bind_param('isssss', $staette, $cipher_name, $cipher_telnr, $cipher_email, $tischnr, $gast_code);
			if ($stmt->execute()) {
				$last_id = $stmt->insert_id; 
				$show_reg = true; // Zeige erfolgreiche Registrierung
			}
			$stmt->close();
		} else {
			echo '<font color=red>Gaststätte nicht gefunden.</font><br />';
			$show_form = true;
		}
		mysqli_close($link);
	}
	if ($show_form) { // Zeige das Registrierungsformular
		echo '<div class="w3-container w3-blue"><b>Erhebung von Kontaktdaten von Gästen in der Gastronomie</b><br />gemäß der Verordnung betreffend Auskunftserteilung für Contact Tracing im Zusammenhang mit Verdachtsfällen von COVID-19</div>';
		echo '<form method="POST" class="w3-container w3-blue">';
		echo '<p' . ($staette !== "" ? ' style="display: none;"' : '') . '><label>Gaststätte Nr:</label><input type="text" name="staette" value="' . $staette . '" class="w3-input"></p>';
		echo '<p' . ($tischnr !== "" ? ' style="display: none;"' : '') . '><lable>Tisch Nr:</label><input type="text" name="tischnr" value="' . $tischnr . '" class="w3-input"></p>';
		echo '<p><label>Name:</label><input type="text" name="name1" value="' . $name1 . '" class="w3-input"></p>';
		echo '<p><label>Telefon Nr / Phone No:</label><input type="text" name="telnr" value="' . $telnr . '" class="w3-input"></p>';
		echo '<p><label>Email:</label><input type="text" name="email" value="' . $email . '" class="w3-input"></p>';
		echo '<p><button type="submit" name="oereg_submit" id="oereg_submit" class="w3-btn w3-green"> Absenden</button></p>';
		echo '</form>';
		if ($show_dsvgo) { 
			echo $dsvgo_text; 
		}
	}
	if ($show_reg) { // Zeige die Bstätigung für die erfolgreiche Registrierung
		include('./phpqrcode/qrlib.php');
		$codeDir = './qrcodes_gast/';
		if (!file_exists($codeDir . $staette)) {
			mkdir($codeDir . $staette, 0777, true); // Ein Unterverzeichnis wird für Gaststättennummer angelegt, soweit nicht vorhanden
		}
		$codeContent = $codeProtocoll . $codeUrlCheck. '/?nr=' . $last_id . '&code=' . $gast_code;
		QRcode::png($codeContent, $codeDir . $staette . '/' . $last_id . '.png', QR_ECLEVEL_L, 5); // Erzeuge den QR Code in Größe 5
		
		$datum = date("d.m.Y");
		$uhrzeit = date("H:i");
		echo '<div class="w3-container w3-green" style="text-align: center">';
		echo '<h1>Registrierung erfolgreich</h1>';
		echo '<table width="100%">';
		echo '<tr><td colspan=2><hr></td></tr>';
		echo '<tr><td>Gaststätte:</td><td>' . $staette_name . '</td></tr>';
		echo '<tr><td>Gaststätte Nr:</td><td><b>' . $staette . '</b></td></tr>';
		echo '<tr><td>Tisch Nr:</td><td>' . $tischnr . '</td></tr>';
		echo '<tr><td colspan=2><hr></td></tr>';
		if ($show_reg_name) {
			echo '<tr><td>Name:</td><td>' . $name1 . '</td></tr>';
			echo '<tr><td>Telefon Nr:</td><td>' . $telnr . '</td></tr>';
			echo '<tr><td>Email:</td><td>' . $email . '</td></tr>';
			echo '<tr><td colspan=2><hr></td></tr>';
		}
		echo '<tr><td>Zeit:</td><td>' . $datum . ' ' . $uhrzeit . '</td></tr>';
		echo '<tr><td colspan=2><hr></td></tr>';
		echo '</table>';
		echo '<p style="text-align: center"><img src="' . $codeDir . $staette . '/' . $last_id . '.png"></p>';
		if ($show_dsvgo) { 
			echo '<p><font size=2>Verantwortlicher für die Verarbeitung der erhobenen personenenbezogenen Daten:<br />' . $staette_dsvgo . '<br />' . $staette_name . '<br />' . $staette_adresse . '<br />' . $staette_plz .'<br />' . $staette_ort . '</font></p>'; 
		}
		echo '</div>';
	}
	
?>
</body></html>
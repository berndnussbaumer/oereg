<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">
</head><body>
<?php

/*
	check_code.php - Ein Gast bekommt nach erfolgreicher Registrierung einen Code angezeigt. Dieser kann gescannt werden und kontrolliert, ob es ein gültiger Code ist.
*/
	if (!isset($_GET["code"])) {
		die ("HTTP Error 404");
	}
	if (!isset($_GET["nr"])) {
		die ("HTTP Error 404");
	}
	$gast_id = $_GET["nr"]; // id der Tabelle regs 
	$code = $_GET["code"]; // Code 

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
	
	$staette = ""; // Initialisierung der Variablen
	$tischnr = "";
	$zeit = "";
	
	$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
	$sql = "SELECT * FROM regs WHERE id = ? AND reg_code = ?;"; // Schau nach, ob es einen Datensatz mit dem Code für die ID gibt
	$stmt = $link->prepare($sql);
	$stmt->bind_param('is', $gast_id, $code);
	$stmt->execute();
	$result = $stmt->get_result();
	$found_code = false;
	if ($result) {
		while ($row = $result->fetch_assoc()) { // es sollte nur einen Datensatz geben, weil der Code mittels uniqid() erstellt, gehasht und Base64 enkodiert wurde.
			$staette = $row["staette_nr"];
			$tischnr = $row["tischnr"];
			$zeit = $row["zeit"];
			$found_code = true;
		}
	} 
	$stmt->close();
	mysqli_close($link);
	if ($found_code) { // der Code wurde gefunden
		echo '<div class="w3-container w3-green" style="text-align:center">';
		echo '<h1>Korrekte Registrierung</h1><br />';
		echo '<b>' . $zeit . '</b><br /><br />';
		echo 'Gaststätte: <b>' . $staette . '</b><br />';
		echo 'Tisch Nr: <b>' . $tischnr . '</b><br /><br />';
		echo '</div>';
	} else { // der Code ist nicht in der Datenbank
		echo '<div class="w3-container w3-red" style="text-align:center">';
		echo '<br /><h1>Keine korrekte Registrierung!</h1><br /><br />';
		echo '</div>';
	}

?>
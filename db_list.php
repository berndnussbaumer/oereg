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

	$show_login = true; // Soll die Loginseite angezeigt werden?
	$staette = 45; // Initialisierung der Gaststättennummer
	if ($_POST) { // wenn ein Post request abgesetzt wurde
		$found_key = false;
		$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
		require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
		$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
		$password = $_POST["passwd"];
		$sql = "SELECT * FROM staette WHERE id = ?"; // Lade aus der DB die Daten der Gaststätte
		$stmt = $link->prepare($sql);
		$stmt->bind_param('i', $staette);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result) {
			while ($row = $result->fetch_assoc()) { // es kann nur einen Datensatz geben, weil die Nr autoincrement in der DB ist.
				$password_crypt = $row["crypt_key"];
				if (password_verify($password, $password_crypt)) {
					$found_key = true;
					$show_login = false;
				}
			}
		} 
		$stmt->close();
		if (!$found_key) {
			echo '<div class="w3-container w3-red"><h1>Login nicht erfolgreich!</h1></div>';
		} else {
			$oereg_sec_check = "SYmsPCJY8wt2xC7r"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
			require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
			$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
			$sql = "SELECT staette.*, regs.zeit FROM staette LEFT JOIN regs ON regs.staette_nr = staette.id ORDER BY staette.id, regs.id"; // Lade aus der DB die Daten der Gaststätten
			$stmt = $link->prepare($sql);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result) {
				echo '<div class="w3-container w3-blue">';
				echo '<h1>Gaststätteninfos</h1>'; // Ausgabe der Gästeliste - Header
				echo '<table border=1 class="w3-table-all">';
				echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Adresse</th><th>PLZ</th><th>Ort</th><th>UID</th><th>DSVGO</th><th>Aktivierungscode</th><th>Aktiv</th><th>Gäste Datum/Uhrzeit</th></tr>';
				while ($row = $result->fetch_assoc()) { // es kann nur einen Datensatz geben, weil die Nr autoincrement in der DB ist.
					echo '<tr><td>' . $row["id"] . '</td><td>' . $row["name"] . '</td><td>' . $row["email"] . '</td><td>' . $row["adresse"] . '</td><td>' . $row["plz"] . '</td><td>' . $row["ort"] . '</td><td>' . $row["uid"] . '</td><td>' . $row["dsvgo"] . '</td><td>' . $row["aktiv_code"] . '</td><td>' . $row["aktiviert"] . '</td><td>' . $row["zeit"] . '</td></tr>';
				}
				echo '</table><br /></div>';
			} 
			$stmt->close();
		}
		mysqli_close($link);
	}
	if ($show_login) { // Loginformular
		echo '<div class="w3-container w3-blue"><h3>Gästeliste abrufen</h3>';
		echo '<form method="POST" class="w3-container w3-blue">';
		echo '<p><label>Passwort:</label><input type="password" name="passwd" value="" class="w3-input"></p>';
		echo '<button type="submit" name="oereg_list" class="w3-btn w3-green">Weiter</button></p>';
		echo '</form></div>';
	}
 

?>
</body></html>

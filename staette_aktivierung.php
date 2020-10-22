<html><head><title>OEREG - COVID19 Gaststättenregistrierung</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="oereg.css">

<?php
	if(strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') != 0){
		die('HTTP Error 404');
	}
	  
	$code = 0;
	if (!isset($_GET['code'])) { 
		die('HTTP Error 404');
	} else {
		$code = $_GET['code'];
	}

	$oereg_sec_check = "Mb&ts3rWx79?vUEn"; // Passwort zum Auslesen der Datenbankverbindung aus set_db_vars.php
	require_once('set_db_vars.php'); // Datei mit Infos zur Datenbankverbindung
	$link = mysqli_connect($myServer, $myUser, $myPasswd, $myDB); // Aufbau der DB Verbindung
	if($link)
	{
		$sql = "SELECT COUNT(*) AS Anzahl FROM staette WHERE aktiv_code = ?"; // Schaue nach, ob es den Code genau einmal gibt
		$stmt = $link->prepare($sql);
		$stmt->bind_param('s', $code);
		$stmt->execute();
		$result = $stmt->get_result();
		$anzahl = 0;
		if ($result) {
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$anzahl = $row['Anzahl'];
				}
			}
		}
		$stmt->close();
		if ($anzahl != 1) {
			echo '<div class="w3-container w3-red" style="text-align:center">';
			echo '<br /><h1>Aktivierungungscode ungültig!</h1><br /><br />';
			echo '</div>';
		} else {
			$sql = "UPDATE staette SET aktiv_code = '', aktiviert = 1 WHERE aktiv_code = ?"; // Aktivere die Gaststätte
			$stmt = $link->prepare($sql);
			$stmt->bind_param('s', $code);
			$stmt->execute();
			$result = $stmt->get_result();
			$stmt->close();
			echo '<div class="w3-container w3-green" style="text-align:center">';
			echo '<br /><h1>Aktivierung erfolgrerich!</h1>';
			echo 'Sie und Ihre Gäste können die Funktionen jetzt nutzen!<br /><br />';
			echo '</div>';
		}
	} else {
		echo '<div class="w3-container w3-red" style="text-align:center">';
		echo '<br /><h1>Aktivierung fehlgeschlagen!</h1><br /><br />';
		echo '</div>';
	}
	mysqli_close($link);


?>
</body></html>
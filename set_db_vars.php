<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) { // kein direkte Aufruf erlaubt
	die ("HTTP Error 404");
}
if (!isset($oereg_sec_check) || $oereg_sec_check != "Mb&ts3rWx79?vUEn") { // zur Sicherheit wird eine bestimmte Variable und deren Wert abgefragt
	die ("HTTP Error 404");
}

$myServer = "localhost";
$myUser = "root";
$myPasswd = "";
$myDB = "oereg";

?>

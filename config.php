<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) { // kein direkte Aufruf erlaubt
	die ("HTTP Error 404");
}

// Das Protokoll und die Url, die am QR Code der Tische angezeigt wird. Im Text wird nur die Url angezeigt.
$codeProtocoll = 'http://';
$codeUrl = '192.168.0.80/oereg';
// Der Link zu check_code.php, der im QR Code der erfolgreichen Gastregistrierung angezeigt wird.
$codeUrlCheck = '192.168.0.80/oereg/check_code.php';

$show_reg_name = false; // wenn true, werden Name, TelNr und Mail auf der Seite der erfolgreichen Gastregistrierung angezeigt.

$show_dsvgo = true; // wenn true, werden die DSVGO Texte angezeigt.

$staette_mail_activation = false; // Gaststätte muss nach der Registrierung noch eine Mailaktivierung vornehmen.
$staette_mail_server = '';
$staette_mail_user = '';
$staette_mail_passwd = '';
$staette_mail_from = '';
$staette_mail_from_name = 'OEREG';
$staette_mail_reply = '';

$show_agb = false; // Gaststätte muss AGBs beim Registrieren bestätigen.

$show_uid = false; // UID der Gaststätte wird abgefragt.

$guest_delete = true; // wenn aktiviert kann auf der Gästeliste jeder Eintrag markiert und gelöscht werden.

// Text, der unter dem Formular für die Gästeregistrierung angezeigt wird.
$dsvgo_text = '<p><font size=1>Datenschutzrechtliche Information nach Art. 13 DSGVO:<br />
<b>Zweck und Rechtsgrundlagen der Verarbeitung:</b><br />
Zweck: Contact Tracing zur Verhinderung der (Weiter-)Verbreitung von
COVID-19 im Fall des Auftretens eines Verdachtsfalles von COVID-19
Rechtsgrundlagen: Art. 6 Abs. 1 lit. d DSGVO (Verarbeitung
personenbezogener Daten zum Schutz lebenswichtiger Interessen der
betroffenen Person oder einer anderen natürlichen Person).
§ 5 Abs. 3 Epidemiegesetz 1950, BGBl. Nr. 186/1950 idF BGBl. I Nr. 103/2020
Verordnung des Magistrats der Stadt Wien betreffend Auskunftserteilung
für Contact Tracing im Zusammenhang mit Verdachtsfällen von COVID-19<br />
<b>Verantwortlicher für die Verarbeitung der erhobenen personenbezogenen
Daten:</b><br />
Sie erhalten die Kontaktdaten der Person, an die Sie sich zur Wahrnehmung 
Ihrer datenschutzrechtlichen Rechte wenden können, nach erfolgreicher Registrierung.<br />
<b>Die verarbeiteten personenbezogenen Daten werden zu folgendem
Zweck an folgende Empfänger weitergeleitet:</b><br />
Zweck: Contact Tracing zur Verhinderung der (Weiter-)Verbreitung von
COVID-19 im Fall des Auftretens eines Verdachtsfalles von COVID-19
Empfänger: die Daten sind auf Verlangen der MA 15-Gesundheitsdienst der
Stadt Wien an diese weiterzuleiten.<br />
<b>Hinweise:</b><br />
Ihre personenbezogenen Daten werden gem. § 2 der Verordnung des
Magistrats der Stadt Wien betreffend Auskunftserteilung für Contact
Tracing im Zusammenhang mit Verdachtsfällen von COVID-19 4 Wochen
nach ihrer Aufnahme gelöscht.<br />
Betroffenenrechte: Als betroffene Person haben Sie das Recht auf
Auskunft über die Sie betreffenden personenbezogenen Daten sowie auf
Berichtigung, Löschung oder Einschränkung der Verarbeitung oder auf
Widerspruch gegen die Verarbeitung.<br />
Wenn die Verarbeitung auf einer Einwilligung im Sinne des Art. 6 Abs. 1 lit. a
oder Art. 9 Abs. 2 lit. a DSGVO beruht, haben Sie das Recht, Ihre Einwilligung
jederzeit zu widerrufen. Wir weisen aber darauf hin, dass die Verarbeitung
aufgrund der Einwilligung bis zum Widerruf rechtmäßig war.
Wenn Sie der Auffassung sind, dass Ihren Rechten nicht oder nicht
ausreichend nachgekommen wird, haben Sie die Möglichkeit einer
Beschwerde bei der Österreichischen Datenschutzbehörde:
Barichgasse 40-42, 1030 Wien, E-Mail: dsb@dsb.gv.at</font>';


?>
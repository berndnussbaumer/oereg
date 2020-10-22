<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

function send_activation_mail($email, $name, $code, $protocol, $url, $server, $user, $passwd, $from, $from_name, $reply) {
	$mail = new PHPMailer(true);

	try {
		$mail->CharSet  =  "utf-8";
	//	$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = $server;                    		// Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = $user;       				// SMTP username
		$mail->Password   = $passwd;                           	// SMTP password
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 		// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

		$mail->setFrom($from, $from_name);
		$mail->addAddress($email, $name);     // Add a recipient
		$mail->addReplyTo($reply, $from_name);

		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'COVID19 Gasstättenregistrierung aktivieren';
		$mail->Body    = 'Bitte klicken Sie auf folgenden Link, um die Registrierung abzuschließen.<br><br>' . $protocol . $url . '/staette_aktivierung.php?code=' . $code . '<br /><br />';
		$mail->AltBody = 'Bitte klicken Sie auf folgenden Link, um die Registrierung abzuschließen.\n\n' . $protocol . $url . '/mail_aktivierung.php?code=' . $code . '';

		$mail->send();
		return true;
	} catch (Exception $e) {
		return false;
	}

}

?>
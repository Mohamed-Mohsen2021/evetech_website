<?php
/**
 * This is email script based on PHPMailer library. Please congigure this SMTP details (below) to make the contact form working.
 */


/* ========================================================================== */
/* SMTP Email Settings: Please change this variables                          */
/* Make sure you done Gmail Account (or your server email account details)    */
/* changes as described here: https://mailtrap.io/blog/phpmailer-gmail/       */

// SMTP Server Credentials (use App Password for Gmail)
$smtp_username          = "info@eve-tech.net";      // TODO: Change to your Gmail address
$smtp_password          = "Info@2026#";         // TODO: Change to your Gmail App Password

// Email recipient settings
$to_email               = "shereen-samy@eve-tech.net";
$to_email_name          = "EveTech Team";

// Form data from user
$from_email             = isset($_POST['email']) ? $_POST['email'] : '';
$from_email_name        = isset($_POST['name']) ? $_POST['name'] : '';
$email_subject          = isset($_POST['subject']) ? $_POST['subject'] : 'Contact Form Submission';
$email_message          = isset($_POST['message']) ? $_POST['message'] : '';
/* ========================================================================== */

/* ========================================================================== */
/* Form fields you want to receive in email                                   */
/* This is array so just write the name set in the form                       */

// All form fields
$field_list = array(
	'name',
	'email',
	'number',
	'subject',
	'message',
);

// required fields in the form
$required_fields = array(
	'name',
	'email',
	'number',
	'subject',
	'message',
);

// to verify the valid email address
$email_fields = array(
	'email'
);

/* ========================================================================== */
/* Google reCAPTCHA Settings                                                  */
/* Get your keys from: https://www.google.com/recaptcha/admin                 */
/* ========================================================================== */
$recaptcha_secret_key = "6LfOg04sAAAAAAXj_LIgcR6RQt1Xzp0lTgKW_dRD"; // TODO: Replace with your reCAPTCHA secret key

/* ========================================================================== */

require "vendor/autoload.php";
/* Do not modify after this line */


//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/SMTP.php';



function isValidEmail($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL) 
		&& preg_match('/@.+\./', $email);
}

function field_name($key) {
	$return = '';
	if( !empty($key) ){
		$return = str_replace('_', ' ', $key);
		$return = str_replace('-', ' ', $return);
		$return = ucwords($return);
	}
	return $return;

}

function save_mail($mail) {
	//You can change 'Sent Mail' to any other folder or tag
	$path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

	//Tell your server to open an IMAP connection using the same username and password as you used for SMTP
	$imapStream = imap_open($path, $mail->Username, $mail->Password);

	$result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
	imap_close($imapStream);

	return $result;
}

$email_status = false;
$email_status_message = '';

if( !empty($_POST) ){

	// Verify reCAPTCHA
	$recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
	
	if(empty($recaptcha_response)) {
		echo '<div class="alert alert-danger" role="alert">Please complete the reCAPTCHA verification.</div>';
		die();
	}
	
	// Verify with Google
	$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
	$verify_data = array(
		'secret' => $recaptcha_secret_key,
		'response' => $recaptcha_response,
		'remoteip' => $_SERVER['REMOTE_ADDR']
	);
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($verify_data)
		)
	);
	
	$context  = stream_context_create($options);
	$verify_response = file_get_contents($verify_url, false, $context);
	$response_data = json_decode($verify_response);
	
	if(!$response_data->success) {
		echo '<div class="alert alert-danger" role="alert">reCAPTCHA verification failed. Please try again.</div>';
		die();
	}

	// check if all required fields are not empty
	$error_message = '';

	foreach( $_POST as $key=>$value ){
		if( !empty($key) && in_array( $key, $required_fields ) ){
			$field_name = field_name($key);
			if( empty($value) ){
				$error_message .= '<li>The "'.$field_name.'" Field is required. Please fill it and submit again</li>' ;
			} else if( in_array( $key, $email_fields ) && isValidEmail($value) == false ){
				$error_message .= '<li>The "'.$field_name.'" email is not valid email</li>' ;
			}
		}
	} // foreach



	
	if( !empty($error_message) ){

		// Required fields are empty
		echo '<div class="alert alert-danger" role="alert">Please fill required fields: <br> <ul>' . $error_message . '</ul> <br> </div>';
		die();

	} else {

		// all required files are valid.. continue to send email
		$email_body = '<h2>Contact Form Data</h2><table style="border: 1px solid #b5b5b5; padding: 5px;">';
		foreach( $_POST as $key=>$value ){
			if( in_array( $key, $field_list ) ){
				$field_name = field_name($key);
				$email_body .= '<tr>
					<td style="border: 1px solid #b5b5b5; padding: 5px;"><strong>'.$field_name.'</strong> </td>
					<td style="border: 1px solid #b5b5b5; padding: 5px;">: '.$value.'</td>
				</tr>';
				
			}
		} // foreach
		$email_body .= '</table>';

		/**** Trying to send email ****/

		try {
			//Create a new PHPMailer instance
			$mail = new PHPMailer(true);
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
			//Enable SMTP debugging
			//SMTP::DEBUG_OFF = off (for production use)
			//SMTP::DEBUG_CLIENT = client messages
			//SMTP::DEBUG_SERVER = client and server messages
			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->SMTPDebug = SMTP::DEBUG_OFF;

			//Set the hostname of the mail server
			$mail->Host = 'premium294.web-hosting.com';
			//Set the SMTP port number - 465 for SSL, 587 for TLS
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
			$mail->Username   = $smtp_username;
			$mail->Password   = $smtp_password;
			//Set who the message is to be sent from
			$mail->setFrom( $smtp_username, 'EveTech Contact Form');
			//Set an alternative reply-to address
			$mail->addReplyTo($from_email, $from_email_name);
			//Set who the message is to be sent to
			$mail->addAddress( $to_email, $to_email_name);
			//Set the subject line
			$mail->Subject = $email_subject;
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$mail->msgHTML( $email_body );

			if ( $mail->send() ) {
				
				// Send auto-reply confirmation email to user
				try {
					$autoReply = new PHPMailer(true);
					$autoReply->isSMTP();
					$autoReply->SMTPDebug = SMTP::DEBUG_OFF;
					$autoReply->Host = 'premium294.web-hosting.com';
					$autoReply->Port = 587;
					$autoReply->SMTPAuth = true;
					$autoReply->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
					$autoReply->Username = $smtp_username;
					$autoReply->Password = $smtp_password;
					$autoReply->setFrom($smtp_username, 'EveTech');
					$autoReply->addAddress($from_email, $from_email_name);
					$autoReply->Subject = 'Thank you for contacting EveTech';
					
					$autoReplyBody = '
					<html>
					<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
						<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
							<h2 style="color: #363533;">Thank You for Contacting Us!</h2>
							<p>Dear ' . htmlspecialchars($from_email_name) . ',</p>
							<p>Thank you for reaching out to EveTech. We have received your message and our team will get back to you as soon as possible.</p>
							<p>We typically respond within 24-48 business hours.</p>
							<hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
							<p style="color: #666; font-size: 14px;">Best regards,<br><strong>EveTech Team</strong></p>
						</div>
					</body>
					</html>';
					
					$autoReply->msgHTML($autoReplyBody);
					$autoReply->send();
				} catch (Exception $e) {
					// Auto-reply failed, but main email was sent - continue silently
				}
				
				echo '<div class="alert alert-success" role="alert">Thank for filling the form. <br> Our team will contact you soon !!! </div>';
				
			} else {
				echo '<div class="alert alert-danger" role="alert">Error: Cannot send email. Please try again later.</div>';
			}
			
		} catch (Exception $e) {
			echo '<div class="alert alert-danger" role="alert">Error: Could not send email. Please contact us directly at info@eve-tech.net</div>';
			// Debug info:
			echo '<br><small style="color:#999;">Debug: ' . $e->getMessage() . '</small>';
		}

	}



} else {

	die('<p>Please go to Contact page and fill the contact form.</p>');

}




#!/usr/bin/php -q
<?php
  
/*************************************************************************************
* ===================================================================================*
*                                                                                    *
* Software by: hetGE                                                                 *
*                                                                                    *
**************************************************************************************
*
* Created by Tolga Y. Ozudogru on Mar 11, 2013.
* Copyright (c) 2013 hetGE. All rights reserved.
*
* **************************************************************************************/

/**
 *
 * mailchimp.php
 *
 * E-mail piping script which is triggered by a received e-mail to subscribe@hetge.com.
 *
 *
 * WORKFLOW
 *
 * 1) Processes the received e-mail message and parses the name, surname and from address.
 * 2) Registers the user to the newsletter by sending the data via MailChimp API.
 * 3) Writes log files mailchimp.log and emails.log (optional, see config) for diagnostics.
 *
 * 
 * IMPORTANT NOTICE
 *
 * The first line has to point to the PHP command line on the server, something like:
 * #!/usr/bin/php -q or #!/usr/local/bin/php -q
 *
 * Immediately after this line, <?php tag should start. This is to ensure that the
 * file does not have any output. Make sure that there aren't any extra spaces and 
 * line feeds outside <?php ... ?>, and also avoid using output functions like 
 * echo() or var_dump(). Otherwise, even though the script will work as expected,
 * the sender of the e-mail will receive an e-mail indicating that his message
 * was not delivered along with the output of the PHP script.
 * You definitely don't want that.
 * 
 * 
 * This script uses PlancakeEmailParser by Danyuki Software Limited.
 * For more info, check:
 * https://github.com/plancake/official-library-php-email-parser
 * 
 * This script uses MailChimp API 1.3 and MCAPI PHP Wrapper 1.3.2
 * For more info, check:
 * http://apidocs.mailchimp.com/api/1.3/
 * http://apidocs.mailchimp.com/api/downloads/#php
 * 
 * 
 * Update by @tolga on Mar 13, 2013.
 *   Since 'First Name' and 'Last Name' are both required for subscribing to MailChimp,
 *   I added code to assign them to 'UNDEFINED' if empty.
 *   Added to github:
 *   https://github.com/hetge/
 */


/**
 * Basic configuration and set-up
 * 
 * Load PlancakeEmailParser library and MailChimp API
 * 
    Get current date and time for logging.

    $detailedLogging: if set to true, the details of 
    the received emails will be logged in a separate log file.
 */
$detailedLogging = true;
$date = date("M j, Y");
$time = date("h:i A");

require_once 'PlancakeEmailParser.php';
require_once 'MCAPI.class.php';
require_once 'config.inc.php'; //contains apikey


/**
 *
 * Getting the email data and parsing
 * 
 */
$sock = fopen("php://stdin", 'r');
$email = '';
while(!feof($sock))
{
    $email .= fread($sock, 1024);
}
fclose($sock);

$emailParser = new PlancakeEmailParser($email);

$emailFromFullName = $emailParser->getFromName();
if (preg_match ("/^.+ /", $emailFromFullName, $match)) $emailFromFirstName = $match[0];
if (preg_match ("/[^ ]*$/", $emailFromFullName, $match)) $emailFromSurname = $match[0];	

if (!$emailFromFirstName) $emailFromFirstName = "UNDEFINED";
if (!$emailFromLastName) $emailFromLastName = "UNDEFINED";

$emailFromAddress = $emailParser->getFromAddress();
$emailTo = $emailParser->getTo();
$emailSubject = $emailParser->getSubject();
/* 	$emailCc = $emailParser->getCc(); */

$emailBody = $emailParser->getPlainBody();


/**
 *
 * MailChimp stuff
 * 
 */
$api = new MCAPI($apikey);

$merge_vars = array('FNAME'=> $emailFromFirstName, 'LNAME'=> $emailFromSurname);
$retval = $api->listSubscribe($listId, $emailFromAddress, $merge_vars, 'html', false);

if ($api->errorCode){
	$mcLogMessage = "Unable to load listSubscribe()!\n";
	$mcLogMessage = $errorLog . "\tCode=".$api->errorCode."\n";
	$mcLogMessage = $errorLog . "\tMsg=".$api->errorMessage;
} else {
    $mcLogMessage = "User successfully subscribed to the newsletter.";
}


/**
 *
 * Logging stuff
 * 
 */
$mcLog = "
============================================================================
$emailFromSurname, $emailFromFirstName <$emailFromAddress> on $date at $time
----------------------------------------------------------------------------
$mcLogMessage";
saveLogFile('mailchimp.log', $mcLog);

if ($detailedLogging) {
	$parsedEmail = "
============================================================================
Date/time: $date at $time
From name: $emailFromFirstName
From surname: $emailFromSurname
From email: $emailFromAddress
To: $emailTo[0]
Subject: $emailSubject
----------------------------------------------------------------------------
$emailBody
============================================================================
";
	saveLogFile('emails.log', $parsedEmail);
}


function saveLogFile($fileName, $data) {
	$handle = fopen($fileName, 'a+') or die('Cannot open file:  '.$fileName); //creates the file if not exists, otherwise appends
	fwrite($handle, $data);
	fclose($handle);
}
?>

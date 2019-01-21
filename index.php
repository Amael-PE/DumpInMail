<?php

// Import PHPMailer classes into the global namespace
// These must be at the top of the script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';

$head = '
----------------------------------------------------
	DumpInMail v0.1, 01-21-2018
	by Amael Parreaux-Ey, GitHub
----------------------------------------------------
';
							

# ----------------------------------------------------
#                	Settings
# ----------------------------------------------------

# == DataBase ==
 	$domain            = 'domain.tld';		    			// Your domain (without www.)
	$to 		       = 'your.name1@mail.tld';				// Your destination email
	$from 		       = 'another.name@' . $domain;			// Email adress that you want send from
	$from_name 		   = 'DumpInMail Auto MYSQL BackUp';	// Email contact name that you want send from
# == Others ==
    $path_script      = '/home/you/html';					// Full path to parent folder of the script like "/home/you/html"
 	$delete_backup	= true;	 								// true to delete the dump archive after sending the mail, false to keep it on the server
    $send_log       = true; 								// true to send a mail with the log file of the script, false to keep it on the server
# == DataBase ==
	$db_server			= 'localhost';			// Database server, most of time "localhost", 
 	$db_name 			= '';					// Database name, (for 'all databases', leave it empty)
	$db_user 		    = 'username';			// Database username
	$db_pass 		    = 'password';			// Database password



# ----------------------------------------------------
#                	Core
# ----------------------------------------------------
# Be careful to only change if you are sur of what you're doing

error_reporting(E_ALL);

echo '<!DOCTYPE html>
<html>
	<head>
		<title>DumpInMail Backup status for ' . $domain . '</title>
		<style type="text/css">body { background: #dedede; color: #66b3ff; font-family: \'Liberia Sans\', Arial; }</style>
	</head>
	<body>';

function date_stamp() {
	global $output_web;
	$backup_date = date('m-d-Y-H-i');
	echo 'Date of DataBase backup : ' . $backup_date . '<br />';
	return $backup_date;
}

function backup_filename() {
    global $db_name, $date_stamp, $output_web;
	$db_backup_filename = ($db_name == '' ? 'all_databases' : $db_name) . '_' . $date_stamp . '.sql.gz';
	echo 'Database backup file: ' . $db_backup_filename . '<br />';
	return $db_backup_filename;
}

function db_dump() {
	global $db_server, $db_name, $db_user, $db_pass, $backup_filename, $output_web;
	$cmd = 'mysqldump -u ' . $db_user . ' -h ' . $db_server . ' --password=' . $db_pass . ' ' . ($db_name == '' ? '--all-databases' : $db_name) . ' | gzip > ' . $backup_filename;
	$dump_status = (passthru($cmd) === false) ? 'No' : 'Yes';
	echo 'Command: ' . $cmd . '<br />';
	echo 'Command executed? ' . $dump_status . '<br />';
	return $dump_status;
}

function send_attachment($file, $file_is_db = true) {
	global $to, $from, $domain, $delete_backup, $output_web;

	$sent       = 'No';

	$mail = new PHPMailer(true);                               // Passing `true` enables exceptions
	try {

	    //Recipients
	    $mail->SetFrom($from, 'Auto mail Backup'); // Name is optional
	    $mail->addAddress($to);                                  // Add a recipient

	    //Attachments
	    $mail->addAttachment($file);         // Add attachments

	    //Content
	    $mail->isHTML(true);                                  // Set email format to HTML
	    $mail->Subject = 'MySQL backup - ' . ($file_is_db ? 'db dump' : 'report') . ' [' . $domain . ']';
	    $mail->Body    = 'Database backup file:' . "\n" . ' - ' . $file . "\n\n";
	    $mail->AltBody = 'Database backup file:' . "\n" . ' - ' . $file . "\n\n";

	    if ($mail->Send()) {
			$sent = 'Yes';		
			echo ($file_is_db ? 'Backup file' : 'Report') . ' sent to ' . $to . '.<br />';
			if ($file_is_db) {
				if ($delete_backup) {
		            unlink($file);
					echo 'Backup file REMOVED from disk.<br />';
				} else {
					echo 'Backup file LEFT on disk.<br />';
				}
			}
		} else {
			echo '<span style="color: #f00;">' . ($file_is_db ? 'Database' : 'Report') . ' not sent! Please check your mail settings.</span><br />';
		}
	    echo 'Message has been sent';
	} catch (Exception $e) {
	    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}

	echo 'Sent? ' . $sent;
	
	return $sent;
}

function write_log() {
    global $backup_filename, $date_stamp, $send_log, $head, $path_script;

    $log_file = $path_script . '/backup_log.txt';
    if (!$handle = fopen($log_file, 'a+')) exit;
	if (chmod($log_file, 0644) && is_writable($log_file)) {

    	echo '<h2>Mysqldump...</h2>';
		$dumped         = db_dump();

		echo '<h2>Sending db...</h2>';
	    $log_content    = "\n" . $date_stamp . "\t\t\t" . $dumped . "\t\t\t" . send_attachment($backup_filename);

        echo '<h2>Writing log...</h2>';
        
        $log_header = '';
        if (filesize($log_file) == '0') {
			$log_header .= $head . "\n\n";
			$log_header .= 'Backup log' . "\n";
			$log_header .= '----------------------------------------------' . "\n";
			$log_header .= 'DATESTAMP:					DUMPED		MAILED' . "\n";
			$log_header .= '----------------------------------------------';
			
			if (fwrite($handle, $log_header) === false) exit;
		}
        
        echo 'Log header written: ';
		if (fwrite($handle, $log_header) === false) {
		    echo 'no<br />' . "\n";
		    exit;
		} else {
		    echo 'yes<br />' . "\n";
		}
		                            
		echo 'Log status written: ';    
	    if (fwrite($handle, $log_content) === false) {
		    echo 'no<br />' . "\n";
		    exit;
		} else {
		    echo 'yes<br />' . "\n";
		}

	}

	fclose($handle);
	
	if ($send_log) {
	    echo '<h2>Sending log...</h2>';
		send_attachment($log_file, false);
	}
}



echo '<h2>Setup</h2>';
$date_stamp         = date_stamp();
$backup_filename    = backup_filename();
$init               = write_log();

echo '<br /><br />...<br /><br />If all letters are blue and you received the files, you\'re good to go!<br />Remove '#' from this folder’s .htaccess file NOW.</body></html>';

?>
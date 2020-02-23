<?php 
#  Author:  James Terry Riendeau, james.terry.riendeau@gmail.com

require 'SignageOptions.php';

function getSQLHandle() {
	global $options;

	$sql = "CREATE TABLE IF NOT EXISTS Slides (
		left_pane varchar(256) NOT NULL,
		right_pane varchar(256),
		exp_date varchar(10),
		exp_time varchar(8),
		orientation char(1),
		screens varchar(".($options['num_screens'] * 4)."),
		user varchar(64)
	);";
	
	$db = new SQLite3($options['files']['db']);
	$db->exec($sql);

	return $db;
}

function log_action($action) {
	global $options;

	$timestamp = date("Y-m-d H:i:s");
	$user = getUser();
	$user = $user ? " $user " : ' - ';
    file_put_contents($options['directories']['log'].DIRECTORY_SEPARATOR.'log_'.date("Y-m").'.txt', $timestamp.$user.$action.PHP_EOL, FILE_APPEND | LOCK_EX);
}

#  Prints out error in a HTML page, and then kills the execution of the script.
function fatalError($error) {
	$error_HTMLified = '<p>Error: '.$error;
	preg_replace("\n|\f|\r", "</p><p>", $error_HTMLified);
	$error_HTMLified .= '</p>';

	$os_error = error_get_last();

	if($os_error) {
		$error_HTMLified .= '<p>'.$os_error['message'].'</p>';
	}
	
	echo '<html><head><title>Error</title></head><body>';
	echo '<h2>'.$error_HTMLified.'</h2>';
	echo '<h3>Please <a href="javascript:history.back();">go back</a>, and try again.</h3>';
	echo '</body></html>';
	exit(0);
}

function deleteFile($filename) {
	global $options;

	if ($filename && strpos($filename, '/') === false) {
		unlink($options['directories']['uploaded_files'].DIRECTORY_SEPARATOR.$filename);
	}
}

function uploadedFilesURL($filename) {
	global $options; 
	
	return ($filename == '' || strpos($filename, '/') ? '' : $options['urls']['uploaded_files'].'/').$filename;
}

function getUser() {
	return array_key_exists('REMOTE_USER', $_SERVER) ? $_SERVER['REMOTE_USER'] : '';
}

function sendEmail($recipient, $from, $subject, $message) {
	$headers = "From: ".$from;

	mail($recipient,$subject,$message,$headers);
}
?>
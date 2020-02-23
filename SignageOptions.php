<?php 
#  Author:  James Terry Riendeau, james.terry.riendeau@gmail.com

$webserver_docs_path = '/var/www/html/signage';
$webserver_url = 'http://localhost/signage';
$webserver_ssl = 'https://localhost/signage';

$options = array(
	'num_screens' => 1,
	'screen_width' => 1920,   # pixels
	'screen_height' => 1080,  # pixels

	# Typically you would display 2 side-by-side (portrait) signs longer than 1 landscape sign
	'display_times' => array(
		'portrait' => 15,   # seconds
		'landscape' => 10	# seconds
	),

	'directories' => array(
		'uploaded_files' => $webserver_docs_path.DIRECTORY_SEPARATOR.'files',
		'log' => $webserver_docs_path.DIRECTORY_SEPARATOR.'log'
	),

	'files' => array(
		'db' => $webserver_docs_path.DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'slides.db'
	),
	
	'urls' => array(
		'upload_form' => $webserver_ssl.'/forms/upload.php',
		'remove_form' => $webserver_ssl.'/forms/remove.php',
		'upload' => $webserver_ssl.'/upload.php',
		'remove' => $webserver_ssl.'/remove.php',
		'get' => $webserver_url.'/get.php',
		'uploaded_files' => $webserver_url.'/files',
	),
	
	'admin_usernames' => array(
	    '',
	),
	
	# repeating_slides is an array of arrays formatted like so:
	# 'repeating_slides' => array(
	#	array(
	#		'interval' => 3,       # repeats every 'interval' of slides.  The first slide is always the repeating slide.  Setting to 1 will cause erratic behavior.
	#		'orientation' => 'l',  # l or p for landscape or portrait, usually 'l'
	#		'screens' => '1,2',    # comma separated list of screens this slide should repeat on 
	#		'left_pane' => 'http://url',
	#		'right_pane => 'http://right.side.of.slide.url'  # or delete this line if you want to fill the screen with left_pane.
	#	),
	#	array(
	#		...
	#	)
	# )
	'repeating_slides' => array(
		array(
			'interval' => 4,
			'orientation' => 'l',
			'screens' => '1,2',
			'left_pane' => 'http://localhost/'
		)
	)
);

?>
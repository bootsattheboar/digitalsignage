<?php 
#  Author:  James Terry Riendeau, james.terry.riendeau@gmail.com

require 'SignageLib.php';

if (empty($_POST)) {
    header( "Location: ".$options['urls']['remove_form']);
	exit(0);
}

$user = getUser();
$db = getSQLHandle();

foreach ($_POST as $slide_num => $panes) {
	$num = (int) str_replace('slide', '', $slide_num) or fatalError('Unexpected slide number '.$slide_num);
	$panes = filter_var($panes, FILTER_SANITIZE_STRING) or fatalError('Unexpected slide reference '.$panes);
	list($left_filename, $right_filename) = explode(',', $panes);
	$sql = "SELECT * FROM Slides WHERE left_pane='".$left_filename.($right_filename ? "' AND right_pane='".$right_filename."';" : "';");
	$results = $db->query($sql);
	$row = $results->fetchArray(SQLITE3_ASSOC);

	if (!$user or $row['user'] == $user or array_search($user, $options['admin_usernames']) !== false) {
		$sql = "DELETE FROM Slides WHERE left_pane='".$row['left_pane'].($row['right_pane'] ? "' AND right_pane='".$row['right_pane']."';" : "';");
		$db->exec($sql);
		deleteFile($row['left_pane']);
		deleteFile($row['right_pane']);
		log_action('Manual: '.$sql);
	}
}

$db->close();
header("Location: ".$options['urls']['remove_form']);
?>
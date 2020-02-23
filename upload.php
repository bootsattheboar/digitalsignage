<?php 
#  Author:  James Terry Riendeau, james.terry.riendeau@gmail.com

require 'SignageLib.php';

if (empty($_POST)) {
    header("Location: ".$options['urls']['upload_form']);
	exit(0);
}

$slide = array();
saveFileOrURL($slide, 'left');

if (array_key_exists('split_slide', $_POST) && $_POST['split_slide']) {
	saveFileOrURL($slide, 'right');
	$slide['orientation'] = 'l';
}

$exp_date = 0;

if (!array_key_exists('never_expire', $_POST) || $_POST['never_expire'] != 1) {
	$slide['exp_time']= $_POST['exp_hour'].':'.$_POST['exp_minute'].' '.$_POST['exp_meridian'];
	$slide['exp_date'] = $_POST['exp_year'].'-'.$_POST['exp_month'].'-'.$_POST['exp_date'];

	$exp_date = date_create($slide['exp_date'].' '.$slide['exp_time']) or fatalError('The experation date '.$slide['exp_date'].' or time '.$slide['exp_time'].' was incorrect.');
}

$slide['screens'] = '';

for ($screen_num = 1; $screen_num <= $options['num_screens']; $screen_num++) {
	if ($_POST['screen'.$screen_num]) {
		$slide['screens'] .= $screen_num.',';
	}
}

$slide['screens'] = $slide['screens'] ? substr($slide['screens'], 0, strlen($slide['screens']) - 1) : '';  // Lop off the last comma

if (array_key_exists('REMOTE_USER', $_SERVER)) {
	$slide['user'] = $_SERVER['REMOTE_USER'];
} 

$sql = 'INSERT INTO Slides (left_pane, '.
	(array_key_exists('right_pane', $slide) ? 'right_pane, ' : '').
	(array_key_exists('exp_date', $slide) ? 'exp_date, exp_time, ' : '').
	'orientation, screens'.
	(array_key_exists('user', $slide) ? ', user' : '').
') VALUES ('.
	"'".$slide['left_pane']."', ".
	(array_key_exists('right_pane', $slide) ? "'".$slide['right_pane']."', " : '').
	(array_key_exists('exp_date', $slide) ? "'".$slide['exp_date']."', '".$slide['exp_time']."', " : '').
	"'".$slide['orientation']."', ".
	"'".$slide['screens']."'".
	(array_key_exists('user', $slide) ? ", '".$slide['user']."'" : '')
.');';

$db = getSQLHandle();
$db->exec($sql);
$db->close();
log_action($sql);
header("Location: ".$options['urls']['remove_form']);


function saveFileOrURL(&$slide, $right_or_left) {
	if (array_key_exists($right_or_left.'_url_upload', $_POST) && $_POST[$right_or_left.'_url_upload']) {
		$url = filter_var($_POST[$right_or_left.'_url'], FILTER_SANITIZE_URL);
		
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			if (!strpos($url, '/')) {
				$url = 'http://'.$url;
			}
			$slide[$right_or_left.'_pane'] = $url;
		} else {
			fatalError('The URL '.$url.' was invalid.');
		}

		$slide['orientation'] = 'l';
	} else if ($_FILES[$right_or_left.'_file']['error'] == UPLOAD_ERR_OK) {
		list($slide[$right_or_left.'_pane'], $slide['orientation']) = saveFile($right_or_left.'_file');
	} else {
		fatalError('The '.$right_or_left.' file failed to upload.');
	}
}

function saveFile($param_name) {
	global $options;
	$filename = basename($_FILES[$param_name]['name']);
	
	if (!$filename) {
		fatalError('There was a problem uploading "'.$param_name.'".  Error code '.$_FILES[$param_name]['error'].'.');
	}

	$filename = preg_replace("/ /", "_", $filename);

	if (preg_match('/[^a-zA-Z0-9_.-]/', $filename, $matches)) {
		fatalError('The filename '.$filename.' contains some unallowed characters "'.join('', $matches).'".  Only letters, numbers, periods, underscores, and hyphens are allowed.');
	}
	
	$target_file = $options['directories']['uploaded_files'].DIRECTORY_SEPARATOR.$filename;

	if (file_exists($target_file)) {
		fatalError('The file '.$filename.' already exists.');
	}

	move_uploaded_file($_FILES[$param_name]['tmp_name'], $target_file) or fatalError('There was a problem saving "'.$filename.'".');

	$file_type = $_FILES[$param_name]['type'];
	$orientation = 'l';

	if (stripos($file_type, 'image') or stripos($file_type, 'pdf')) {
		$im = new Imagick();
		$im->setResolution(200, 200);
		$im->readImage($target_file);
		$im->setImageMatte(false);
		$im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
		$im->resizeImage($options['screen_width'], $options['screen_height'], imagick::FILTER_CUBIC, 0.5, true);
		$width = $im->getImageWidth();
		$orientation = $width > $options['screen_width'] / 2 ? 'l' : 'p';
		$old_target_file = $target_file;
		$filename = pathinfo($target_file, PATHINFO_FILENAME).'.png';
		$target_file = $options['directories']['uploaded_files'].DIRECTORY_SEPARATOR.$filename;
		
		if (file_exists($target_file)) {
			unlink($old_target_file);
			fatalError('The file '.$filename.' already exists.');	
		} else {
			$im->writeImage($target_file) or fatalError('There was a problem saving the image "'.$filename.'".');
			unlink($old_target_file);
		}
	}

	return array($filename, $orientation);
}
?>
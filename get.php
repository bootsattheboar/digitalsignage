<?php 
#  Author:  James Terry Riendeau, james.terry.riendeau@gmail.com

require 'SignageLib.php';

$screen_num = array_key_exists('screen', $_GET) ? $_GET['screen'] : 1;

if ($screen_num != 'all') {
	if (!filter_var($screen_num, FILTER_VALIDATE_INT, array("options" => array("min_range"=>1, "max_range"=>$options['num_screens'])))) {
		fatalError("Screen $screen_num is invalid.");
	}
}

$index = array_key_exists('index', $_GET) ? $_GET['index'] : 0;

if (filter_var($index, FILTER_VALIDATE_INT) === false) {
	fatalError("Slide index $index is invalid.");
}

$cursor = array_key_exists('cursor', $_GET) ? $_GET['cursor'] : 0;

if (filter_var($cursor, FILTER_VALIDATE_INT) === false) {
	fatalError("Cursor $cursor is invalid.");
}

$clock = array_key_exists('clock', $_GET) ? $_GET['clock'] : 1;

if (filter_var($clock, FILTER_VALIDATE_INT) === false) {
	fatalError("Clock $clock is invalid.");
}

$slides = array();
$now = date_create();
$row_count = 1;
$repeat = 0;
$db = getSQLHandle();
$results = $db->query('SELECT * FROM Slides');

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
	foreach ($options['repeating_slides'] as $rslide) {
		if (strpos($rslide['screens'], (string) $screen_num) !== false) {
			if ($repeat % $rslide['interval'] == 0) {
				array_push($slides, $rslide);
				$repeat++;
			}
		}
	}

	$expiration = date_create($row['exp_date'].' '.$row['exp_time']);

	if ($expiration < $now) {
		$sql = "DELETE FROM Slides WHERE left_pane='".$row['left_pane']."' AND right_pane='".$row['right_pane']."';";
		$db->exec($sql);
		deleteFile($row['left_pane']);
		deleteFile($row['right_pane']);
		log_action('Automatic: '.$row['exp_date'].' '.$row['exp_time'].': '.$sql);
	} else if (strpos($row['screens'], (string) $screen_num) !== false || $screen_num == 'all') {
		$found = false;

		if ($row['orientation'] == 'p') {
			foreach ($slides as &$ptr) {
				if ($ptr['orientation'] == 'p') {
					$ptr['right_pane'] = $row['left_pane'];
					$ptr['orientation'] = 'l';
					$found = true;
					break;
				}
			}
			unset($ptr);
		} 

		if (!$found) {
			array_push($slides, $row);
			$repeat++;
		}
	}

	$row_count++;
}

$db->close();
$slides_count = count($slides);

if ($slides[$slides_count - 1] === $slides[0]) {  //If first and last slides are the same (ie. a repeating slide), ignore the last one.
	$slides_count--;
}

if ($index < 0) {
	$index = abs(($slides_count + $index) % $slides_count);
} else {
	$index = $index % $slides_count;
}

$target_slide = $slides[$index++];
$index = $index % $slides_count;

$frames = '<iframe id="LeftPane" name="LeftPane" src="'.uploadedFilesURL($target_slide['left_pane']).'" marginwidth="0" marginheight="0" hspace="0" vspace="0">'.
	'You must use a browser that supports frames, such as <a href="http://www.getfirefox.com/">Firefox</a>.</iframe>';

if (array_key_exists('right_pane', $target_slide) && $target_slide['right_pane']) {
	$width = '50%';
	$frames .= '<iframe id="RightPane" name="RightPane" src="'.uploadedFilesURL($target_slide['right_pane']).'" marginwidth="0" marginheight="0" hspace="0" vspace="0"></iframe>';
	$display_time = $options['display_times']['portrait'];
} else {
	$width = '100%';
	$display_time = $options['display_times']['landscape'];
}
?>

<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title></title>
		<script type="text/javascript"><!--
		var intervalID = 0;	
		var screen_num = <?php echo $screen_num; ?>;
		var next_index = <?php echo $index; ?>;
		var cursor = <?php echo $cursor; ?>;
		var clock = <?php echo $clock; ?>;
		var display_time = <?php echo $display_time; ?>;
		
		function nextSlide() {
			loadNextSlide(screen_num, next_index, cursor, clock);
		}

		function prevSlide() {
			loadNextSlide(screen_num, next_index - 2, cursor, clock);
		}

		function loadNextSlide(screen_num, slide_index, cursor, clock) {
			url = "<?php echo $options['urls']['get']; ?>";
			setTimer(display_time);
			window.location = url + '?' + "screen=" + screen_num + "&index=" + slide_index + "&cursor=" + cursor + "&clock=" + clock;
		}

		function setTimer(seconds) {
			window.clearInterval(intervalID);
			intervalID = window.setInterval("nextSlide()", seconds * 1000);
		}

		function init() {
			addImgStyle("LeftPane");
			addImgStyle("RightPane");
			<?php if ($clock) echo 'startTime();'."\n";?>
			setTimer(display_time);
		}

		function addImgStyle(el) {
			style = "<style>img { display: block; margin-left: auto; margin-right: auto; } <?php if (!$cursor) echo '* { cursor: none; }'; ?></style>";
			myiFrame = document.getElementById(el);
			
			if (myiFrame) {
 	      		doc = myiFrame.contentDocument;
				doc.body.innerHTML = doc.body.innerHTML + style;
				myiFrame.style.visibility = "visible";
			}
		}

		function checkArrow(evt) {
			var evt  = (evt) ? evt : ((event) ? event : null);

			switch (evt.keyCode) {
				case 33:  // page up
				case 38:  // right arrow
				case 39:  // up arrow
					nextSlide();
					return;
				case 34:  // page down
				case 37:  // left arrow
				case 40:  // down arrow
					prevSlide();
					return;
			}
		}
		
		document.onkeydown = checkArrow;
		
		function startTime() {  
			var today = new Date();
			var h = today.getHours();
			var m = today.getMinutes();
			var s = today.getSeconds();
			var mer = h < 12 ? 'AM' : 'PM';
			h = h == 0 ? 12 : (h > 12 ? h % 12 : h);
			m = addZero(m);
			s = addZero(s);
			document.getElementById('clock').innerHTML = h + ":" + m + ":" + s + " " + mer;
			var t = setTimeout(startTime, 500);
		}

		function addZero(i) {
			if (i < 10) {i = "0" + i};
			return i;
		}
		//-->
		</script>
		<style>
		body {
			margin: 0;
			padding: 0;	
		}

		iframe {
			background-color: #000;
			width: <?php echo $width; ?>;
			height: 100%;
			border: none;
			margin: 0;
			padding: 0;
			visibility: hidden;
		}

		#clock {
			position: absolute;
			bottom: 0;
			right: 0;
			opacity: 0.5;
			padding: 10px;
			background-color: #000;
			color: #FFF;
			font: 30px arial, sans-serif;
		}
		</style>
	</head>
	<body onload="javascript:init();">
		<?php echo $frames."\n"; ?>
		<?php if ($clock) echo '<div id="clock"></div>'."\n";?>
	</body>
</html>
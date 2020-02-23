<?php require '../SignageLib.php'; ?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Remove Slides</title>
		<script type="text/javascript"><!--
		function enable(theform) {
			theform.resetButton.style.visibility = "hidden";
			theform.submitButton.value = "Remove";
			theform.submitButton.disabled = false;
		}

		function disable(theform) {
			theform.submitButton.disabled = true;
			theform.submitButton.value = "Please wait...";
			theform.resetButton.style.visibility = "visible";
		}

		function validate(theform) {
			disable(theform);
			theform.submit();
		}

		// Disable form submission upon depression of the Return key
		function checkCR(evt) {
			var evt  = (evt) ? evt : ((event) ? event : null);
			var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
			if ((evt.keyCode == 13) && (node.type=="text")) { return false; }
		}

		// Register checkCR event handler.
		document.onkeypress = checkCR;
		//-->
		</script>
	</head>
	<body onload="javascript:enable(document.theform)">
		<form id="theform" action="<?php echo $options['urls']['remove']; ?>" method="post" name="theform" enctype="multipart/form-data">
			<table border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td>&nbsp;</td>
					<td><b>Filename</b></td>
					<td><b>Orientation</b></td>
					<td><b>Screens</b></td>
					<td><b>Expiration Date/Time</b></td>
					<td><b>User</b></td>
				</tr>
<?php 
$db = new SQLite3($options['files']['db']);
$results = $db->query('SELECT * FROM Slides');
$count = 1;
$tabs = "\t\t\t\t";
$user = getUser();

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
	echo $tabs.($count % 2 == 0 ? '<tr>' : '<tr bgcolor="#eee">')."\n";

	if (!$user or $user == $row['user'] or array_search($user, $options['admin_usernames']) !== false) {
		echo $tabs."\t".'<td><input type="checkbox" name="slide'.$count.'" value="'.$row['left_pane'].($row['right_pane'] ? ','.$row['right_pane'] : '').'" /></td>'."\n";
	} else {
		echo $tabs."\t".'<td>&nbsp;</td>'."\n";
	}

	echo $tabs."\t".'<td><a href="'.uploadedFilesURL($row['left_pane']).'" target="_blank">'.$row['left_pane'].'</a>'.
			($row['right_pane'] ? ', <a href="'.uploadedFilesURL($row['right_pane']).'" target="_blank">'.$row['right_pane'].'</a></td>' : '')."\n".
		$tabs."\t".'<td>'.($row['orientation'] == 'p' ? 'portrait' : 'landscape').'</td>'."\n".
		$tabs."\t".'<td>'.$row['screens'].'</td>'."\n".
		$tabs."\t".'<td>'.($row['exp_date'] ? $row['exp_date'].' '.$row['exp_time'] : 'Never expires.').'</td>'."\n".
		$tabs."\t".'<td>'.($row['user'] ? $row['user'] : '-').'</td>'."\n".
		$tabs.'</tr>'."\n";

	$count++;
}

$db->close();
?>
				<tr>
					<td colspan="6" align="center" valign="middle">
						<input id="submitButton" onclick="javascript:validate(document.theform);" type="button" name="submitButton" value="Remove" />
						<input id="resetButton" class="hidden" onclick="javascript:enable(document.theform);" type="button" name="resetButton" value="Reset Remove Button" />
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>
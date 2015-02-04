<?php 
	$wrong_path = $_SERVER['REQUEST_URI'];
	$nocase_wrong_path = strtolower($wrong_path);
	if(is_file($_SERVER['DOCUMENT_ROOT'].$nocase_wrong_path) || is_dir($_SERVER['DOCUMENT_ROOT'].$nocase_wrong_path)) {
		header('location:'.$nocase_wrong_path);	
	}
	include $SITE_ROOT.'/_index1.php';
?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<title>Page Not Found</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $css_extra_style_name; ?>">
</head>
<body>
	<?php
		include_once $SITE_ROOT.'/a/_login_index.php';
		echo '<br><h1 style="font-size:100px;"> 4 0 4 </h1><br><br>';
		if (!isset($_SERVER['HTTP_REFERER'])) {
			echo '<p>The address you just typed into your browser is incorrect.</p>';
		} else {
			echo '<p>The link you just clicked points to the wrong place.</p>';
		}
		$correct_path = find_valid_path_in_request($wrong_path);
		if ($correct_path != false) {
			echo '<p>You may have been looking for this:</p><br>';
			echo '<p><strong><a href="'.$correct_path.'">';
			echo 'http://'.$_SERVER['HTTP_HOST'].$correct_path.'</a></strong></p>';
		}
		echo "<br><br><p>If you still think the page or file is here somewhere, try clicking Search in the top right corner of this page.</p>";
		include $SITE_ROOOT.'/_index2.php';
	?>
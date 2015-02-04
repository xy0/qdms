<?php
/* display a list of files, ~CCMS */
global $filename;
global $filenames;
$filedir = "./"; 
$i=0;
$k=0;
$m=0;

$play_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAOCAYAAAA8E3wEAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzRFREYyNkY4REE2MTFFMEE1Q0JFMkZBNzVCNUVGRTYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzRFREYyNkU4REE2MTFFMEE1Q0JFMkZBNzVCNUVGRTYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5kaWQ6MDhCRkQ0NzJBMzhERTAxMTkyMEFBQUREQUVCNzlBQUUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MDhCRkQ0NzJBMzhERTAxMTkyMEFBQUREQUVCNzlBQUUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6MLdSUAAABsElEQVR42qSUT0sCYRDGR9OyCIwSUSK6VqfyS3SObmG3olN1KLp4ji7RpWu3oENFpH0Pr3UsIoSwwjJXXfedntEx/4C66is/XnZ8d56ZZ3fHw8zUbaXT6VFsk2AKTCsSK4Mf8AU+QF5jTvP9sVisJZ+Peq8RMKFCs4nowb0hppPM2SGuP0FGz1An0ebldSHo1Y6CIugh+REdRfZOsa2AJTAPwurEaFMBQwlKspAE6g9hP7Kzi20ZLIA5MKNuiHOeYQQlwZh0aUgMNfDMIcOGtsPxTcQXtcuQFubvlNuNYP2cJAmIkM02lblMRS5WiQfXt9o69A/TYcv5ClcaYsYikzdVNrxrCe0u0M1SX5+CVCERLP2LOYDLeKoe5B/vfX+/gkbELIbYL8S+TXVnfAQ3oeSxfhbFal2Nd2sgSw2wJZlY6VhiI16dAigyxFIX+O9VB0BBzw4saLTiEsjZtk1cgKBVs/I2+nCJ+CN4AVnt0tb7BrLU6PSQRFlGR6bENRvDqXPEnsFTW4cdLe1HMAfeWGu/DiabR5uIvbsZbW4EHa1aktNV5W4VI8D18G5ffwIMALRT0kXkTqTUAAAAAElFTkSuQmCC';

if (isset($_GET['st'])){
	$st = $_GET['st'];
}else{
	$st = 1;
}

$handle = opendir($filedir); // open dir get file data
while (false !==($file = readdir($handle))){ // list of things not to display
	if ($file != "." 
		&& $file != ".." 
		&& $file != "index.php" 
		&& $file != "cgi-bin" 
		&& $file != ".htaccess" 
		&& $file != "error_log" 
		&& $file != ".Trash-0"
		&& $file != ".htpasswd"
		&& $file != "!above.php"
		&& $file != "!below.php"
		&& $file != "System Volume Information" 
		&& !(strpos($file, '_')===0) 
		&& !(strpos($file, '$')===0) 
		) 
	{
		$filenames[$i]["1"] = $file;
		$filenames[$i]["2"] = filemtime($filedir.$file);
		$filenames[$i]["3"] = recursive_directory_size($filedir.$file);
		++$i;
	}
}
closedir($handle);

$filenames = csort($filenames,$st); //sort

// Start File Display
if (!isset($_file_index_hide) || $USER->loggedIn){
?>
	<!-- START FILE LIST WIDGET -->
	<div id="list_col"> 
		<!-- Column one start -->
		<form method="post" action="">
		<?php
		if ($USER->loggedIn && check_restricted('edit', $CURRENT_DIR)) {
			echo "<input type='submit' name='list' onclick='return confirmDelete()' value='delete' class='list_delete' />";
			echo "<input type='submit' name='list' value='unzip' class='list_zip' />";
			echo "<input type='submit' name='list' value='zip' class='list_zip' />";
			echo "<input type='submit' name='list' alt='Make a folder nice and pretty like this one' value='extend' class='list_extend' />";
			if (isset($IS_HIDDEN)){
				echo "<input type='submit' name='list' alt='Make folder public' value='private' class='list_private' />";
			} else {
				echo "<input type='submit' name='list' alt='Protect this folder from prying eyes.' value='public' class='list_forbid_button' />";
				/*echo "<input type='text' name='allowed' alt='Protect this folder from prying eyes.' class='list_forbid_text' />";*/
			}
		}
		echo "<br />"; 
		echo "<a href=\"?st=1\"><u><b>File Name</b></u></a>&#160;&#160;&#160;<a href=\"?st=3\"><u>Size</u></a><span style='float:right'><a href=\"?st=2\"><u><b> Date </b></u></a></span><br>";
		echo "<br />"; 
		while($m<(count($filenames))){
			echo "
					<div class=";
			if($m%2){
				echo "list_item0";
			}else{ 
				echo"list_item1";
			}
			echo ">";
			if ($USER->loggedIn && check_restricted('edit', $CURRENT_DIR)){
				echo "	
						<input type='checkbox' class='list_delete' name='list_delete_".$m."' value='".$filenames[$m]["1"]."'/>
						<span class='list_rename'>
							<a onclick=\"show_rename(document.getElementById('edit_".$m."'))\">
								rename
							</a>
						</span>";
			}
			if (is_dir($filenames[$m]["1"])){
				echo "	
						&#160;&#160;";
			}
			echo "		
						<span class=\"pre_rename\" id='edit_".$m."'>";
			if (is_dir($filenames[$m]["1"])){
				echo "	
							<b>";
			}
			if (strpos($filenames[$m]["1"],".mp3") || strpos($filenames[$m]["1"],".flv") || strpos($filenames[$m]["1"],".mp4") || strpos($filenames[$m]["1"],".m4a") || strpos($filenames[$m]["1"],".wav")){
				echo "		<a href='?view=".$filenames[$m]["1"]."'>
								<img src='".$play_image."' alt='Play Media' title='Play ".$filenames[$m]["1"]."' class='list_view'/>
							</a>";
			}
			echo "		
							<a title='".$filenames[$m]["1"]."' href=\"$filedir".$filenames[$m]["1"]."\">
								".$filenames[$m]["1"]."
							</a>
							<input type='text' name='rename_".$m."' value='".$filenames[$m]["1"]."' />
							<input type='hidden' name='old_name_".$m."' value='".$filenames[$m]["1"]."' />
							<input type='submit' value='ok' /> ";
			if (is_dir($filenames[$m]["1"])){
				echo "		
							</b>";
			}
			if ($USER->loggedIn && check_restricted('edit', $CURRENT_DIR)) if (strpos($filenames[$m]["1"],".text") || strpos($filenames[$m]["1"],".html") || strpos($filenames[$m]["1"],".txt") || strpos($filenames[$m]["1"],".nfo") || strpos($filenames[$m]["1"],".js") || strpos($filenames[$m]["1"],".css")){
				 echo "		 - 
							<a href='?ef=".$filenames[$m][1]."'>
								edit
							</a>";
			}		
			
			echo "		</span>
						<span class='";
			if($m%2){
				echo "list_size0";
			}else{ 
				echo"list_size1";
			}
			echo "'>
							".recursive_directory_size($filenames[$m]["1"],TRUE)."
						</span>
					";
			
			echo "
					<div class='";
			if($m%2){
				echo "list_date0";
			}else{ 
				echo"list_date1";
			}
			echo "'>
						".date ("Y m d - H:i ", $filenames[$m]["2"])."
					</div>
				</div>";
			$m++;
		}
		$m=0;
		?>
		</form>
		<!-- Column one end --> 
	</div> 
	<!-- END FILE LIST WIDGET -->
<?php } ?>

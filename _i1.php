<?php //	2014.08.07*	cylab.info - Collective Content Management System ~CCMS
     //    Special thanks to LSD, DMT, Plupload, nicEdit, HTMLPurifier
    //    For complete documentation, see the README
   //    Still in the ALPHA phase... c@cylab.info
  //
 //	   This is the first file of the two CMS files to be included on pages
//
#INIT
//*
error_reporting(-1);	// -1: report all errors, 0: report no errors
ini_set('display_errors', 'On');	// also reports all errors, set to OFF to suppress
//*/
isset($_SESSION)? '': session_start();	// if no session, create one.
/*
session_regenerate_id(true);	// prevents session fixation attack, disabled currently
//*/
$START_TIME = microtime(true);
$_SESSION['chatTime'] = microtime(true);	// start the page load time sensing and let the chat script known when the chat was viewed
require_once(dirname(__FILE__)."/a/config.php"); // this script will always look at the directory /a/ below this file for config
require_once(dirname(__FILE__)."/a/functions.php"); // and functions

#REQUEST HANDELING
if(isset($_POST['registration_sent'])){ // user has filled out the registration form
   if($_SESSION['captcha']!=md5($_POST['captcha'])){ /* was the captcha filled out correctly? */
	   die('You typed the CAPTCHA wrong');
   }
   if(!$_POST['user'] || !$_POST['pass']){ /* Make sure all fields were entered */
	  die('You didn\'t fill in a required field.');
   }
   if (strstr($_POST['user'],'-')){
		die("Sorry, no hyphens allowed");
   }
   $_POST['user'] = trim(safe_text($_POST['user']));/* Spruce up username, check length */
   $_POST['email'] = $_POST['email']; // and the email
   if (!preg_match('|^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$|i', $_POST['email'])) {
		$_POST['email']="";
	}
   if(strlen($_POST['user']) > 30){
		die("Sorry, the username is longer than 30 characters, please shorten it.");
   }
   if(usernameTaken($_POST['user']) || $_POST['user']=='server'){ /* Check if username is already in use */
	  $use = $_POST['user'];
	  die("Sorry, the username: <strong>$use</strong> is already taken, please pick another one.");
   }
	$md5pass = md5($_POST['pass']);
	$_SESSION['reguname_only'] = $_POST['user'];
	$_SESSION['regresult'] = addNewUser($_POST['user'], $md5pass, $_POST['email']); /* Add the new account to the database */
	$_SESSION['reg_submit'] = true;
	mkdir($SITE_ROOT.'u/'.$_SESSION['reguname_only'],0777); // makes the users home folder
	if (file_exists($SITE_ROOT.'/_index.php')) { 
		copy($SITE_ROOT.'/_index.php',$SITE_ROOT.'u/'.$_SESSION['reguname_only'].'/index.php');
	}else if (file_exists('index.php')){
		copy($SITE_ROOT.'/index.php',$SITE_ROOT.'u/'.$_SESSION['reguname_only'].'/index.php');
	}
	//update_the_feed('register',$_SESSION['reguname_only']);
	header("location:".$_SERVER['PHP_SELF']."/?reg=1");
}elseif(isset($_GET['cap'])){ // returns the captcha image
	//session_start();
	$do_not_show = array("0","o","O","l","L","B","8","1","i","I"); // hard to identify
	$rand_captcha = str_replace($do_not_show,"X",generateRandom(6));
	// $replacers = array("0", "1", "2", "3", "4", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U");
  	// $_SESSION['captcha'] = md5(str_replace($replacers, '8', strrev($rand_captcha)));
  	$_SESSION['captcha'] = md5($rand_captcha);
	$my_img = imagecreate( 200, 80 );
	$background = imagecolorallocate( $my_img, 255, 255, 255 );
	$text_colour = imagecolorallocate( $my_img, rand(0, 255),rand(0, 50), rand(0, 50) );
	$line_colour = imagecolorallocate( $my_img, rand(0, 50), rand(0, 255), rand(0, 255));
	imagesetthickness ( $my_img, 5 );
	imageline( $my_img, rand(0, 200), rand(0, 200), rand(0, 200), rand(0,50), $line_colour );
	for($i = 0; $i <= 333; $i++) {
		imagestring( $my_img, rand(1, 5), rand(0, 150), rand(0, 150),  generateRandom(2),imagecolorallocate( $my_img, rand(120, 255),rand(200, 255), rand(200, 255)));
	}
	imagestring( $my_img, rand(1, 5), rand(0, 25), rand(0, 25),  generateRandom(3),imagecolorallocate( $my_img, rand(100, 255),rand(100, 255), rand(100, 255) ));
	imagestring( $my_img, rand(1, 5), rand(0, 50), rand(0, 50), $rand_captcha,$text_colour );
	header( "Content-type: image/png" );
	imagepng( $my_img );
	imagecolordeallocate( $line_colour );
	imagecolordeallocate( $text_colour );
	imagecolordeallocate( $background );
	imagedestroy( $my_img );
}elseif(isset($_GET["l"]) && $_GET["l"] == 1){	// DataBase confirmation of login data
	$author = md5($_POST['myusername'].$CRYPTO_SALT.$_POST['mypassword']);
	if ($DB->findUser($author,$DOMAIN)){
		$USER->login($_POST['myusername'],$author);
		//update_the_feed('login');
		header("location:".$_SERVER['PHP_SELF']);
	}else{
		$_SESSION = array();
		session_destroy();
		header('location:'.$_SERVER['PHP_SELF'].'?error=1');
	}
}elseif (isset($_GET["l"]) && $_GET['l'] == 2){ //logout
	$_SESSION = array();
	session_destroy();
	header("location:".$_SERVER['PHP_SELF']);
}else{
	#START IO
	if (isset($_POST["create_file_name"]) && $_POST["create_file_name"] != "") { // passes creation name to script
		$create_file_name = $_POST["create_file_name"];
	}
	if (isset($_POST["current_dir"]) && $_POST["current_dir"] != "") { // if we are manually overriding the "CURRENT_DIR" value
		$CURRENT_DIR = $_POST["current_dir"];
	}
	if (isset($_GET['n']))	// If an article has been selected to be edited
		$article_edit_number = $_GET['n'];
	if (!isset($empty_dir)){	//unless we just deleted the dir, create File List  
		
		// list files in current directory
		$FILES = array();
		foreach (new DirectoryIterator($CURRENT_DIR) as $fileInfo) {
		    if($fileInfo->isDot()) continue;
		    array_push($FILES, $fileInfo->getFilename());
		}

		array_multisort(array_map('filemtime', $FILES), SORT_DESC, $FILES);	// sort files from oldest to newest
		foreach ($FILES as $name) {	//find number of articles, articles have a (-) dash in front of their directory.
			if ( !(strpos($name, '_')===0) && (is_dir($name)) && $name!='.' && $name!='..' && $name!='cgi-bin' && strpos($name, '-')===0){ // list of files not to list
				$article_number++;
				$articles[$article_number] = $name;
			}
			if (pathinfo($name, PATHINFO_EXTENSION)=='txt' || pathinfo($name, PATHINFO_EXTENSION)=='nfo'){ // find number of text files in CURRENT_DIR
				$text_file_number++;
				$last_text_file = $name;
			}
		}
	}

	if( file_exists($CURRENT_DIR.'/_CONTENT.txt')){	// if no _CONTENT.txt file then just use any ol text file to display in the CURRENT_DIR
		$content_file_exists = $CURRENT_DIR.'/_CONTENT.txt';
	}else if(isset($last_text_file)){
		$content_file_exists = $CURRENT_DIR.$last_text_file;
	}
	if (isset($content_file_exists)){	// get data from _CONTENT file to display appropriate page title and meta
		$blog = fopen($content_file_exists, 'r');
		if (filesize($content_file_exists)>0){
			$blog_content = fread($blog, filesize($content_file_exists));
		}else{
			$blog_content = " ";
		}
		if (strstr($blog_content,'title{([')){ $page_title = get_string_between($blog_content,'title{([','])}');}
		if (strstr($blog_content,'tags{([')){ $page_tags = get_string_between($blog_content,'tags{([','])}');}
		fclose($blog);
	}else if (strlen($CURRENT_DIR) > 1){ // if not in root, and the content file was not found, page title is name of directory
		$page_title = basename($CURRENT_DIR);
	}
	if(file_exists($CURRENT_DIR.'style.css')) {	// look for user-uploaded css file % need to make this more generalized
	   $css_extra_style_name = 'style.css';
	   $S->add('css file to look for:'.$css_extra_style_name);
	} elseif(file_exists($CURRENT_DIR.'_style.css')) {
	   $css_extra_style_name = '_style.css';
	   $css_change = true;
	}
	if (file_exists($CURRENT_DIR.'_hidden.log')) { // has someone claimed this directory and hid it? % need to work on this...
		$IS_HIDDEN = true;
		$hidden_txt = fopen($CURRENT_DIR.'_hidden.log', 'r');
		if (filesize($CURRENT_DIR.'_hidden.log')>0){
			$hidden_txt_contents = fread($hidden_txt, filesize($CURRENT_DIR.'_hidden.log'));
		}else{
			$hidden_txt_contents ='';
		}
		if ( isset($_SESSION['reguname']) && strpos($hidden_txt_contents,md5($CRYPTO_SALT.$_SESSION['reguname'])) !==FALSE ) { //% add salt
			//yay
		} else {
			header("location:.././?error=2"); // User is not allowed to be viewing this directory, shoo them away
		}
	}
	if ($USER->loggedIn){ // Things that only logged in users can do
		#RENAME
		if(isset($_POST['rename_0']) && $_POST['rename_0'] != ''){
			//print_r ($_POST);
			for( $i=0 ; $i < (count($_POST)/2)+((count($_POST)/2)%2) ; $i++ ){
				if (isset($_POST['rename_'.$i]) && $_POST['rename_'.$i] != $_POST['old_name_'.$i]) {
					if (substr($_POST['rename_'.$i],0,1) === '/' && substr_count($_POST['rename_'.$i],'/') > 1) {
						$last_slash = strripos($_POST['rename_'.$i],'/');
						$into_folder = substr($_POST['rename_'.$i],1,$last_slash);
						if ( file_exists($into_folder) && !file_exists($into_folder.$_POST['rename_'.$i]) && check_restricted('edit',$CURRENT_DIR)) {
							rename($_POST['old_name_'.$i],$into_folder.safe_text(substr($_POST['rename_'.$i],$last_slash,strlen($_POST['rename_'.$i])-$last_slash)));
						}
					}else if (substr($_POST['rename_'.$i],0,3) == '../') {
						$uplvl_start = 0;
						$rename_check_dir = getcwd();
						while (substr($_POST['rename_'.$i],$uplvl_start,3) == '../') {
							$rename_check_dir = dirname($rename_check_dir);
							$uplvl_start = $uplvl_start + 3;
						}
						if (check_restricted('create',$rename_check_dir.'/') && substr_count($rename_check_dir,'/') > 3){
							rename($_POST['old_name_'.$i],$rename_check_dir.'/'.safe_text(substr($_POST['rename_'.$i],$uplvl_start)));
						}
					}else{
						if ( check_restricted('edit',$CURRENT_DIR) ) {
							rename($_POST["old_name_".$i],safe_text($_POST["rename_".$i]));
							$renamed_file_old = $_POST["old_name_".$i];
							$renamed_file_new = safe_text($_POST["rename_".$i]);
						}
					}
				}
			}
			if (isset($renamed_file_new)){}
				//update_the_feed('rename',$renamed_file_old,$renamed_file_new);
		}
		#DELETE
		if(isset($_POST['list']) && $_POST['list']=='delete' && check_restricted('edit',$CURRENT_DIR)) {
			for( $i=0 ; $i < (count($_POST))/2; $i++){
				if (isset($_POST["list_delete_".$i]) && $file_to_be_deleted = $_POST["list_delete_".$i] ){
					if (!is_dir($SITE_ROOT.$DELETE_LOCATION)){
						mkdir($SITE_ROOT.$DELETE_LOCATION);
						if (file_exists($SITE_ROOT.$DELETE_LOCATION.'_index.php')) { 
							copy($SITE_ROOT.'/_index.php',$SITE_ROOT.$DELETE_LOCATION."index.php");
						}else if (file_exists($SITE_ROOT.'index.php')){
							copy($SITE_ROOT.'/index.php',$SITE_ROOT.$DELETE_LOCATION."index.php");
						}
					}
					//echo $file_to_be_deleted.' '.$SITE_ROOT.$DELETE_LOCATION.date("H:i-").$file_to_be_deleted;
					rename($file_to_be_deleted,$SITE_ROOT.$DELETE_LOCATION.date("Y.m.t.h.i-").$file_to_be_deleted);
					//update_the_feed('delete',$file_to_be_deleted);
				}
			}
			//header("location:".$_SERVER['PHP_SELF']);
		#UNZIP 
		}else if(isset($_POST['list']) && $_POST['list']=='unzip' && check_restricted('create',$CURRENT_DIR)) {
			for( $i=0 ; $i < (count($_POST))/2; $i++){
				if ($file_to_be_unzipped = $_POST["list_delete_".$i]) {
					unzip($CURRENT_DIR.$file_to_be_unzipped);
					//header("location:".$_SERVER['PHP_SELF']);
				}
			}
		#ZIP
		}else if(isset($_POST['list']) && $_POST['list']=='zip' && check_restricted('create',$CURRENT_DIR)) {
			for( $i=0 ; $i < (count($_POST))/2; $i++){
				if ($file_to_be_zipped = $_POST["list_delete_".$i]) {
					zip($CURRENT_DIR.$file_to_be_zipped,$CURRENT_DIR.safe_text($file_to_be_zipped).".zip");
					//header("location:".$_SERVER['PHP_SELF']);
				}
			}
		#EXTEND - _index.php copy //
		}else if(isset($_POST['list']) && $_POST['list']=='extend' && check_restricted('create',$CURRENT_DIR)) {
			for( $i=0 ; $i < (count($_POST))/2; $i++){
				//print_r($i.' '.$_POST["list_delete_".$i]);
				if (isset($_POST["list_delete_".$i]) && $folder_to_be_extended = $_POST["list_delete_".$i]) {
					if (is_dir($folder_to_be_extended) && !file_exists($folder_to_be_extended."/index.php")){
						chmod_r($folder_to_be_extended,0755,0755);
						if (file_exists($SITE_ROOT.'/_index.php')) { 
							copy($SITE_ROOT.'/_index.php',$folder_to_be_extended.'/index.php');
						}else if (file_exists('index.php')){
							copy($SITE_ROOT.'/index.php',$folder_to_be_extended.'/index.php');
						}
					}else{
						$error_message .= $folder_to_be_extended." - Not a directory or index.php already exists.<br>";
					}
				}
			}
			if ($error_message){
				echo $error_message."<br><br> Use the *extend* tool if you want to convert a plain folder into this fancy Content Management System" ;
			}else{
				header("location:".$_SERVER['PHP_SELF']);
			}
		#FORBIDDING DIRECTORIES
		}else if(isset($_POST['list']) && $_POST['list']=='public' && check_restricted('edit',$CURRENT_DIR)) {
			$Allowed_File = fopen('_hidden.log', 'a') or die("cant create file");
			// if md5+salt of username is NOT in the file
				$allow_list = md5($CRYPTO_SALT.$_SESSION['reguname']).'~'.file_get_contents('_hidden.log'); // % add salt here
			fwrite($Allowed_File,$allow_list);
			fclose($Allowed_File);
			header("location:".$_SERVER['PHP_SELF']);
		#MAKING DIR PUBLIC
		} else if (isset($_POST['list']) && $_POST['list']=='private' && check_restricted('edit',$CURRENT_DIR)) {
			rename('_hidden.log','_hidden_to_public-'.date('YmdHis').'.log');
			header("location:".$_SERVER['PHP_SELF']);
		}
		#BUFFERED EDITORS/DISPLAYERES
		if(isset($_GET["ef"])) {
			$editfile = $_GET["ef"];
			if (file_exists($editfile) && is_writeable($editfile)) {
				$filecontent = implode ("", file($editfile));
				$filecontent = htmlentities($filecontent, ENT_QUOTES, 'utf-8');
				$view_output_buffer .= "<center><br /><br /><br />";
				if (!isset($_GET["simple"]) || $_GET["simple"]<1){
					$view_output_buffer .= '
						<a href="'.$_SERVER["PHP_SELF"].'?ef='.$editfile.'&simple=1">Switch to simple editor</a>
						<script type="text/javascript">
							tinymce.init({
							    selector: "textarea",
							    content_css :  ["/css/base.css","/css/_main_style.css","/css/editorOverride.css"],
							    plugins: [
							        "advlist autolink lists link image charmap print preview anchor",
							        "searchreplace visualblocks code fullscreen",
							        "insertdatetime media table contextmenu paste"
							    ],
							    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
							});
						</script>'; 
				}else{
					$view_output_buffer .= '<a href="'.$_SERVER["PHP_SELF"]."?ef=".$editfile."&simple=0".'">Switch to advanced editor</a>';
				}
				$view_output_buffer .= '
					<form class="newedit" action="?e=2" method="post">
						<textarea rows="15" cols="200" name="filecontent">'.$filecontent.'</textarea><br />
						<input type="hidden" name="editfile" value="'.$editfile.'" />
						<input type="hidden" name="action" value="savefile" />
						<input type="submit" value="submit" name="submittype" class="submit_button" />
						<input type="reset" name="reset" value="reset" class="button" />
						<input type="submit" value="Cancel" name="submittypecancel"  class="button" />
						<input type="submit" value="Delete" onClick="return confirmDelete()" name="submittypedelete"  class="button"/>
					</form>
					</center>
				';
			}
		}
		#EDIT an article
		if(isset($_GET["e"]) && $_GET["e"] == 2){ 
			$editfile = (isset($_REQUEST['editfile'])) ? stripslashes($_REQUEST['editfile']) : '' ; // %TODO copy old file contents and put into folder '_old/.'$editfile.'.date("Y m d - H:i ", filemtime($editfile))
			if (isset($_POST["submittype"]) && $_POST["submittype"] != "") {
				$filecontent = stripslashes($_POST["filecontent"]);
				if (is_writeable("{$editfile}")) {
					$fp = fopen("{$editfile}", "wb");
					fputs ($fp, $filecontent);
					fclose($fp);
					//update_the_feed('edit',$editfile);
					header("location:".$_SERVER['PHP_SELF']);
				}else{ echo "ERROR editing file: ".$editfile;
				}
			}else if (isset($_POST["submittypedelete"]) && $_POST["submittypedelete"] != ""){ // If this script is called to delete an article, then let it be:
				$folder_position = backwardStrpos($editfile, '/_', -4); //delete the folder that _CONTENT is in.
				$truncated = substr($editfile, 0, $folder_position);
				echo $truncated." Deleted";
				if ($truncated!='./'|| $truncated!='/'|| $truncated!='.'){
					rename($truncated,$DELETE_LOCATION.date("H:i-").basename($truncated));
					$empty_dir = 1;
					//update_the_feed('delete',$truncated);
				}else{echo "ERROR: oops! I tried to delete myself...";}
			}else{ 
			}
		}
		if (isset($_GET["e"]) && $_GET["e"] == 1 && check_restricted('edit',$CURRENT_DIR)){
			if($article_number==0){
				$editfile = $CURRENT_DIR."_CONTENT.txt";
			}else if($article_number==1){
				$editfile = $CURRENT_DIR.$articles[1]."/_CONTENT.txt";
			}else if($article_number>1){
				if(file_exists($CURRENT_DIR.$articles[$article_edit_number]."/_CONTENT.txt")){
					$editfile = $CURRENT_DIR.$articles[$article_edit_number]."/_CONTENT.txt";
				}else{
					$dh3  = opendir($CURRENT_DIR.$articles[$article_edit_number]); 	
					while (false !== ($name3 = readdir($dh3))) {
						$files3[] = $name3;
					}
					//array_multisort(array_map('filemtime', $files2), SORT_DESC, $files2);   // sort the blogs from newest to oldest
					foreach ($files3 as $name3) {
						if (pathinfo($CURRENT_DIR.$name3, PATHINFO_EXTENSION)=='txt'){
							$last_text_file3 = $name3;
						}
					}
					$editfile = $CURRENT_DIR.$articles[$article_edit_number].'/'.$last_text_file3;
				}		
			}
			if (file_exists($editfile) && is_writeable($editfile)) {
				$filecontent = implode ("", file($editfile));
				$filecontent = htmlentities($filecontent, ENT_QUOTES, 'utf-8');
				$view_output_buffer .= "<center><br /><br /><br />";
				if (!isset($_GET["simple"]) || $_GET["simple"]<1){
					$view_output_buffer .= '
						<a href="'.$_SERVER["PHP_SELF"].'?ef='.$editfile.'&simple=1">Switch to simple editor</a>
						<script type="text/javascript">
							tinymce.init({
							    selector: "textarea",
									content_css :  ["/css/base.css","/css/_main_style.css","/css/editorOverride.css"],							    plugins: [
							        "advlist autolink lists link image charmap print preview anchor",
							        "searchreplace visualblocks code fullscreen",
							        "insertdatetime media table contextmenu paste"
							    ],
							    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
							});
						</script>
					'; 
				}else{
					$view_output_buffer .= '<a href="'.$_SERVER["PHP_SELF"]."?ef=".$editfile."&simple=0".'">Switch to advanced editor</a>';
				}
				$view_output_buffer .= '
					<form id="newEdit" action="?e=2" method="post">
						<textarea rows="15" cols="200" name="filecontent">'.$filecontent.'</textarea><br />
						<input type="hidden" name="editfile" value="'.$editfile.'" />
						<input type="hidden" name="action" value="savefile" />
						<input type="submit" value="submit" name="submittype" class="submit_button" onClick="tinyMCE.triggerSave();" />
						<input type="reset" name="reset" value="reset" class="button" />
						<input type="submit" value="Cancel" name="submittypecancel"  class="button" />
						<input type="submit" value="Delete" onClick="return confirmDelete()" name="submittypedelete"  class="button"/>
					</form>
					</center>
				';
			}
		#CREATE
		}else if (isset($_GET["c"]) && $_GET["c"] > 0) { 
			if ($_GET["c"] == 2 && check_restricted('create',$CURRENT_DIR)){
				// CREATE ARTICLE
				$is_article_or_not = "-"; //  this means it IS an article
				$create_file_name = $is_article_or_not.safe_text($create_file_name); // just in case :)
				mkdir($CURRENT_DIR.$create_file_name,0777);
				if (file_exists($SITE_ROOT.'/_index.php')) { 
					copy($SITE_ROOT.'/_index.php',$CURRENT_DIR.$create_file_name.'/index.php');
				}else if (file_exists($SITE_ROOT.'index.php')){
					copy($SITE_ROOT.'/index.php',$CURRENT_DIR.$create_file_name.'/index.php');
				}
				$new_file = $CURRENT_DIR.$create_file_name.'/_CONTENT.txt';
				$fh = fopen($new_file, 'w') or die("can't open file");
				fwrite($fh, $default_file_text);
				if (file_exists($new_file) && is_writeable($new_file)) {
					$filecontent = implode ("", file($new_file));
					$filecontent = htmlentities($filecontent, ENT_QUOTES, 'utf-8');
					//update_the_feed('article','<a href="http://'.$DOMAIN.'/'.substr($new_file, 26, -13).'">'.substr($new_file, 26, -13).'</a>');
					$view_output_buffer .= "<center><br /><br /><br />";
					if (!isset($_GET["simple"]) || $_GET["simple"]<1){
						$view_output_buffer .= '
							<a href="'.$_SERVER["PHP_SELF"].'"?ef='.$editfile.'&simple=1">Switch to simple editor</a>
							<script type="text/javascript">
								tinymce.init({
								    selector: "textarea",
								    content_css :  ["/css/base.css","/css/_main_style.css","/css/editorOverride.css"],
								    plugins: [
								        "advlist autolink lists link image charmap print preview anchor",
								        "searchreplace visualblocks code fullscreen",
								        "insertdatetime media table contextmenu paste"
								    ],
								    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
								});
							</script>
						';
					}else{
						$view_output_buffer .= '<a href="'.$_SERVER["PHP_SELF"]."?ef=".$editfile."&simple=0".'">Switch to advanced editor</a>';
					}
					$view_output_buffer .= '
						<form class="newedit" action="?e=2" method="post">
							<textarea rows="15" cols="200" name="filecontent">'.$filecontent.'</textarea><br />
							<input type="hidden" name="editfile" value="'.$new_file.'" />
							<input type="hidden" name="action" value="savefile" />
							<input type="submit" value="submit" name="submittype" class="submit_button" />
							<input type="reset" name="reset" value="reset" class="button" />
							<input type="submit" value="Cancel" name="submittypecancel"  class="button" />
							<input type="submit" value="Delete" onClick="return confirmDelete()" name="submittypedelete"  class="button"/>
						</form>
						</center>
					';
				}
			}else if($_GET["c"] == 1 && check_restricted('create',$CURRENT_DIR)){ 
				// CREATE DIRECTORY
				$is_article_or_not = ""; //  this means it is NOT an article
				$create_file_name = $is_article_or_not.safe_text($create_file_name); // just in case :)
				mkdir($CURRENT_DIR.$create_file_name,0777);
				if (file_exists($SITE_ROOT.'/_index.php')) { 
					copy($SITE_ROOT.'/_index.php',$CURRENT_DIR.$create_file_name.'/index.php');
				}else if (file_exists('/index.php')){
					copy($SITE_ROOT.'/index.php',$CURRENT_DIR.$create_file_name.'/index.php');
				}
				header("location:".$_SERVER['PHP_SELF']);
			}else if($_GET["c"] == 3  && check_restricted('create',$CURRENT_DIR)){
				// CREATE TEXT FILE
				if($text_file_number > 0 ){
					$is_article_or_not = ""; //  this means it is the dir contains at least one text file
				}else{ // dir contains no text files and has no default display and the first txt file will be used as such
					$is_article_or_not = ""; //  this means it is the FIRST TEXT FILE in the dir
				}
				$create_file_name = $is_article_or_not.safe_text($create_file_name).'.txt'; // just in case :)
				fopen($CURRENT_DIR.$create_file_name, 'w') or die("can't open file");
				header("location:".$_SERVER['PHP_SELF'].'?ef='.$create_file_name);
			}
		}
	}
	#MAIN (aka displayed tasks before main page body)
	if(isset($_GET['reg'])){ // if user clicked on register
		$view_output_buffer .= '<div id="register">'; 
		if( isset($_SESSION['reg_submit']) ){  // if user submitted his/her application
			if($_SESSION['regresult']){
				$view_output_buffer .= ' 
					<h1>Registered!</h1>
					<p>
						Thank you <b>'.$_SESSION['reguname_only'].'</b>, your information has been added 
						to the database, you may now 
						<a href="javascript:location.reload(true);">Refresh Page</a> 
						and Login.
					</p>
				';  
			}else{
				$view_output_buffer .= '
					<h1>Registration Failed</h1>
					<p>
						We are sorry, but an error has occurred and your registration for the username <b>'.$_SESSION['reguname'].'</b>, 
						could not be completed.<br>
						Please try again at a later time.
					</p>
				 ';
			}
			unset($_SESSION['reguname']);
			unset($_SESSION['reg_submit']);
			unset($_SESSION['regresult']);
		}else{ // user is going to fill out registration application
			$view_output_buffer .= ' 
				<div>
					<form class="register_form" action="'.$_SERVER['PHP_SELF'].'" method="post">
						<fieldset>
							<legend><b>Create Account</b></legend>
							<ol>
								<li><label for="user">Username</label><input type="text" name="user" maxlength="30"></li>
								<li><label for="pass">Password</label><input type="password" name="pass" maxlength="30"></li>
								<li><label for="email">Email</label><input type="text" name="email" maxlength="70"></li><br />
								<img style="width:250px;height:100px;" src="?cap=1" />
								<li><label for="captcha">CAPTCHA</label><input type="text" name="captcha" /></li>
							</ol>
							<input type="submit" name="registration_sent" value="Join!">
						</fieldset>
					</form>
				</div>
			'; 
		}
		?>
		</div>
		<?php
	}
	if(isset($_GET['upload'])) $disp_upload = 1;	// determine if the advanced uploader should be displayed
	if(isset($_GET['view']) && $_GET['view']!='') {		// determine if the video player should be loaded
		$file = $_GET['view'];
		$view_output_buffer = "
			<div id='mediaplayer_head'>
				".$file."
			</div>
			<video id=".$file." class=\"video-js vjs-default-skin\ style=\"width: 100%; height: 100%;\"  
				  controls preload=\"auto\" width=\"640\" height=\"264\"  
				  poster='".substr($file,0,-4).".jpg';
				  data-setup='{\"example_option\":true}'>  
				 <source src=".$file." type='video/mp4' />  
				 <source src=".$file." type='video/webm' />  
				 <source src=".$file." type='video/ogg' />  
			</video>
			<br>";
		//update_the_feed('view','<a href="http://'.$DOMAIN.substr($CURRENT_DIR,25).'?view='.$file.'">'.$file.'</a>');
	}
} 
// Don't add any additional white space after the closing php tag, it will screw up the headers
?>
<?php
//supply up link ~CCMS
if (strlen($CURRENT_DIR) > strlen($SITE_ROOT)+1){ // as long as we arn't in the root directory, provide up link.
	echo '	<!-- START LOGIN  WIDGET -->
	<div class="top_link_up"><a href="../">Up^</a></div>
	';
}
if(!$USER->loggedIn){	//display login/register field if user is not logged in.
	?>
	<div class="qdmsLogin">
		<a href="index.php?reg=1">Register</a>&nbsp;
		<a id="show_login" href="JavaScript:void(0);">Login</a>
		<div id="login" style="display:none;">
			<form name="login" method="post" action="?l=1">
				<input name="myusername" type="text" id="myusername"/> 
				<input name="mypassword" type="password" id="mypassword"/>
				<input name="mytimezone" type="hidden" id="mytimezone" value="2"/>
				<input type="submit" name="Submit" value="Login"/>
			</form>
		</div>
	</div>
	<?php 
}else if ($USER->loggedIn){ // if the user IS Logged in, then display functions that the user has in this CURRENT_DIR
	if(check_restricted('edit', $CURRENT_DIR)){
	?>
<div class="top_link_edit"><a href="JavaScript:void(0);" onClick= "document.editform.submit()" >Edit</a></div>
	<?php
	}
	if(check_restricted('create', $CURRENT_DIR)){
	?>
<div class="top_link_create"><a class="show_create" href="JavaScript:void(0);">Create</a></div>  
	<?php 
	}
	if(check_restricted('upload', $CURRENT_DIR)){
	?>
<div class="top_link_upload"><a href="index.php?upload=1">Upload</a></div>
	<?php
	}
	if (strpos($CURRENT_DIR,$SITE_ROOT.'/u/'.$_SESSION['username'])===false){
	?>
<div class="top_link_my"><a href="<?php echo 'http://'.$DOMAIN.'/u/'.$_SESSION['reguname']; ?>">My Folder</a></div>
	<?php
	} 
	?>
<div class="top_link_logout"><a href="?l=2">Bye</a></div><br />
	<form name="editform" action="?e=1&amp;n=1" method="post">
		<input type="hidden" name="current_dir" value="<?php echo $CURRENT_DIR?>" />
	</form>
	<br />
	<div class="create" style="display:none;">
		<form name="createform" class="createform" action="?c=1" method="post">Directory: 
			<input type="radio" name="create_type" onClick="document.createform.action='?c=1';"/><br  />Article: 
			<input type="radio" name="create_type" onClick="document.createform.action='?c=2';" /><br />Text File: 
			<input type="radio" name="create_type" onClick="document.createform.action='?c=3';" /><br />
			<input type="text" name="create_file_name" id="create_file_name"/>
			<input type="submit" name="Submit" onClick="return notEmpty(document.getElementById('create_file_name'), 'Please Enter a Value')" class="button" value="Create"/>
		</form>
	</div>
	<?php if(isset($disp_upload) && check_restricted('upload',$CURRENT_DIR)){
			echo "<div class=\"upload\">";
			include '_upload_index.php'; 
			echo "</div>";
		  }
}
echo '
	<div>
		<div id="breadcrumbs">
	';
	$breadcrumbs = explode('/', substr($CURRENT_DIR,strlen($SITE_ROOT)));
	if(($bsize = sizeof($breadcrumbs))>0) {
		$sofar = '';
		echo '		<a href="http://'.$DOMAIN.'">root</a>';
		for($bi=1;$bi<($bsize-1);$bi++) {
			$sofar = $sofar . $breadcrumbs[$bi] . '/';
			echo ' > <a href="http://'.$DOMAIN.'/'.$sofar.'">'.$breadcrumbs[$bi].'</a>';
		}
	}
echo '
	</div><br>
	<!-- END OF LOGIN WIDGET -->
';

// if the buffer has anything in it then flush it
if($view_output_buffer){
	echo "<!-- OUTPUT BUFFER -->";
	echo $view_output_buffer;
	echo "<!--END OF OUTPUT BUFFER -->";
	$view_output_buffer = false;
}
?>
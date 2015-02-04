<?php // functions.php, provides functions, classes, and runs the Initialization
     // cylab.info 2014.12.27 
    // https://github.com/cyroxos/QueryDisplay
   //
  //
 //
// 
#CLASSES
class S{ // Server Status Text Buffer
	public $buffer = "\n";
	public function add($result){
		$this->buffer .= $result." \n";
	}
	public function dump(){
		print nl2br($this->buffer);
		$this->buffer = '';
	}
}
class Post{
	public $divContent = '';
	public function add($stuff,$user,$location,$uniqueIterator){
		global $DISPLAY_MODE; global $DOMAIN; global $SITE_ROOT; global $CURRENT_DIR; global $USER;

		if($DISPLAY_MODE == 'flat'){
			$location2 = str_replace(substr($CURRENT_DIR,1),'',$location);
			$location = str_replace(substr($SITE_ROOT,0),'',$location);
			$bottomBarSize = -1905+10*(strlen($user)+strlen($location2));
			$loggedInEditOptions = '';
			if ($USER->loggedIn){
				if ($user == $USER->userName){
					$loggedInEditOptions .= "<a href='?q=~pedit ".$uniqueIterator."'>edit</a> <a href='?q=~pdelete ".$uniqueIterator."'>delete</a>";
				}
			}
			if (strlen($location2) > 1)
				$in_or_not = 'in <b><a href="http://'.$DOMAIN.$location.'">'.$location2.'</a></b> ';
			else
				$in_or_not = '';
			$this->divContent .= '<li class="flat" id="editable-'.$uniqueIterator.'" style="margin-left:40px;">
									<div class="post_container" style="background-position: left '.$bottomBarSize.'px bottom, left top -5px, left top, left bottom, right -1500px bottom, right bottom -1770px, right bottom;">
										'.$stuff.'
									</div>'.$in_or_not.'by ~<b>'.$user.'</b>
									<span style="float:right;margin-right:10px;">
										<!-- comments -->
									</span>
									<span style="float:right;margin-right:40px;">
										'.$loggedInEditOptions.'
									</span>
								</li>';
		}
	}
	public function dump(){
		$this->divContent = '
			<!-- START POST DISPLAY WIDGET -->
				<ul id="post_scene" class="post_scene">'.$this->divContent.'</ul>
			<!-- END POST DISPLAY WIDGET -->
		';
		print_r($this->divContent);
	}
}
class Text{
	public $divContent = '';
	public function add($stuff){
		$this->divContent = 
			$this->divContent.'<div>'.$stuff.'</div>';
	}
	public function dump(){
		global $CRYPTO_KEY;
		$this->divContent = '<div id="txt">'.bin2hex(string_encrypt($this->divContent,$CRYPTO_KEY)).'</div><br>';
		print_r($this->divContent);
	}
}
class DataBase{
	private $host;
	private $user;
	private $pass;
	private $name;
	private $table;
	private $Mysqli;

	public function DataBase(){
		global $DB_PARAMETERS; global $S;
		$this->host = $DB_PARAMETERS['host'];
		$this->user = $DB_PARAMETERS['user'];
		$this->pass = $DB_PARAMETERS['pass'];
		$this->name = $DB_PARAMETERS['name'];
		$this->table = $DB_PARAMETERS['table'];

		if($this->connect())
			$this->test();
		else
			$S->add("Connected to MYSQL, but not the proper database, check config.php");
	}
	public function connect(){ // connects and selects the database
		$this->Mysqli = new mysqli($this->host,$this->user,$this->pass)or die("Unable to connect to the database");
		if ( mysqli_select_db($this->Mysqli, $this->name) ){
			return 1;
		}else{
			return 0;
		}
	}
	public function test(){ // probes the database for table, otherise creates it for the first time
		global $S;
		if ($this->check()){
			$S->add('Connected to Database, Table exists');
		}else{
			$S->add('Creating the table for the first time...');
			$S->add((  $this->genesis()  )?'Success<br><br>':'<b>Failed</b><br><br>');
			$S->dump();
		}

		if ($this->confirmHash()){
			$S->add('Last Record Hash is Valid');
		}else{
			$S->add('The Last Record has an invalid hash! (the database is not valid).');
		}
	}
	public function check(){ 	//check to see if the feed table exists already
		$q = "SHOW TABLES LIKE '".$this->table."'";
		$result = mysqli_num_rows(mysqli_query($this->Mysqli, $q));
		if ($result){
			return 1;
		}else{
			return 0;
		}
	}
	public function genesis(){	// Create table, then populate with initial row
		global $CRYPTO_SALT;
		$q = "	CREATE TABLE IF NOT EXISTS `".$this->table."` (
				`i` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  	`type` tinyint(4) NOT NULL,
			  	`content` MEDIUMBLOB NOT NULL ,
			  	`rating_positive` int(10) unsigned NOT NULL DEFAULT '0',
			  	`rating_negative` int(10) unsigned NOT NULL DEFAULT '0',
			  	`location` varchar(256) NOT NULL DEFAULT '' ,
			  	`author` varchar(256) NOT NULL DEFAULT 'server',
			  	`domain` varchar(256) NOT NULL DEFAULT 'cylab.info',
			  	`keywords` text,
			  	`phash` varchar(256) DEFAULT NULL,
			  	UNIQUE (`i`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$q2 = "INSERT INTO `".$this->name."`.`".$this->table."` (`ts`, `type`, `content`, `rating_positive`, `rating_negative`, `location`, `author`, `domain`, `keywords`, `phash`) VALUES (CURRENT_TIMESTAMP, '0', '".mysqli_real_escape_string($this->Mysqli, string_encrypt('Hello, this is the first record.',$CRYPTO_SALT))."', '0', '0', '', 'server', 'cylab.info', 'genesis, first, hello world,', '".$CRYPTO_SALT."');";
		mysqli_query($this->Mysqli, $q2)or die(mysqli_error($this->Mysqli));
		return 1;
	}
	public function confirmHash(){
		global $CRYPTO_SALT;
		$q = "SELECT * FROM `".$this->table."` ORDER BY `i`  DESC LIMIT 0,2";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		if (mysqli_num_rows($result) > 1){
			$currentHash = $row['phash'];
			$old_row = mysqli_fetch_array($result);
			$old_row_hash = md5($old_row['phash']);
			if($old_row_hash == $row['phash']){
				return 1;
			}else{
				return 0;
			}
		}else{
			if($CRYPTO_SALT == $row['phash']){
				return 1;
			}
			return 0;
		}
		return 0;
	}
	public function add($type, $content, $location, $author, $domain, $keywords){ // types: -4       -3        -2      -1      0       1     2    3     4  
		global $CRYPTO_SALT;							     //   						     deleted  cryptmsg  PrvPost FrmUsr FrmSvr  Chat  Post  User Domain
		$q2 = "INSERT INTO `".$this->name."`.`".$this->table."` (`ts`, `type`, `content`, `rating_positive`, `rating_negative`, `location`, `author`, `domain`, `keywords`, `phash`) VALUES (CURRENT_TIMESTAMP, '$type', '".mysqli_real_escape_string($this->Mysqli, $content)."', '0', '0', '".mysqli_real_escape_string($this->Mysqli, $location)."', '$author', '$domain', '$keywords', '".$this->makePhash()."');";
		mysqli_query($this->Mysqli, $q2)or die(mysqli_error($this->Mysqli));
	}
	public function editUserGroups($author, $domain, $keywords){
		global $CRYPTO_SALT;
		$q = "UPDATE `".$this->table."` SET 'keywords' = concat(ifnull(keywords,''), '".$keywords."') WHERE 'author' = '".$author."' AND 'domain' = '".$domain."';";
		mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
	}
	public function editPostType($newType, $author, $domain, $uniqueIterator){
		global $CRYPTO_SALT;
		$q = "UPDATE `".$this->table."` SET `type` = '".$newType."' WHERE `author` = '".$author."' AND `domain` = '".$domain."' AND `i` = '".$uniqueIterator."';";
		mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		return 1;
	}
	public function makePhash(){
		$q = "SELECT * FROM `".$this->table."` ORDER BY `i`  DESC LIMIT 0,2";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		return md5($row['phash']);
	}
	public function findUser($name,$domain){
		global $USER;
		$q = "SELECT keywords FROM `".$this->table."` WHERE author='$name' and domain='$domain'";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		if (mysqli_num_rows($result) > 0){
			$result_array = mysqli_fetch_array($result);
			$groups = explode(',',$result_array['keywords']);
			for ($i=0;$i < count($groups); $i++){
				array_push($USER->groups,(string)$groups[$i]);
			}
			return 1;
		}
		return 0;
	}
	public function disp_post($limit,$location){
		global $CRYPTO_SALT; global $CURRENT_DIR;
		$q = "SELECT * FROM `".$this->table."` 	WHERE (`type` = '2'
												OR (`type` = '-2' AND `location` = '".$location."'))
												AND `location` LIKE  '%".mysqli_real_escape_string($this->Mysqli, $location)."%'  
												ORDER BY `i`  DESC LIMIT ".$limit.";";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		$Post = new Post;
		for ($i = mysqli_num_rows($result); $i>0; $i--){
			$Post->add("".$row['content']."",$row['author'],$row['location'],$row['i']);
			$row = mysqli_fetch_array($result);
		}
		$Post->dump();
		return 1;
	}
	public function disp_text($limit,$location,$type_low){
		global $CRYPTO_SALT;
		$q = "SELECT * FROM `".$this->table."` 	WHERE ( (`type` = '".$type_low."' AND `ts` BETWEEN FROM_UNIXTIME(".$_SESSION['chatTime'].") AND NOW() )
												OR(`type` = 1) )
												AND `location` = '".mysqli_real_escape_string($this->Mysqli, $location)."' 
												ORDER BY `i`  DESC LIMIT ".$limit.";";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		$Text = new Text;
		for ($i = mysqli_num_rows($result); $i>0; $i--){
			if($row['type'] == '0'){
				$author = 'server';
				$content = $row['author']." >> ".$row['content'];
				
			}else{
				$author = $row['author'];
				$content = $row['content'];
			}
			$Text->add("<p><span style='font-weight:bold;color:".genColorCodeFromText($author)."'>".$author.'</span>: '.$content."</p>");
			$row = mysqli_fetch_array($result);
		}
		$Text->dump();
		if(isset($_POST['q']) )
			echo " <script>decrypttxt();</script>";
		return 1;
	}
	public function disp_text_all($limit){
		global $CRYPTO_SALT;
		$q = "SELECT * FROM `".$this->table."` ORDER BY `i` DESC LIMIT ".$limit.";";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		$Text = new Text;
		for ($i = mysqli_num_rows($result); $i>0; $i--){
			if($row['type'] == '0'){
				$author = 'server';
				$content = $row['author']." >> ".$row['content'];
			}else{
				$author = $row['author'];
				$content = $row['content'];
			}
			$Text->add("<p style='color:white;background-color:black;margin-bottom:-20px;'><span style='font-weight:bold;color:".genColorCodeFromText($author)."'>".$author.'</span>: '.$content." <span style='color:#555555'>(".$row['location'].")</span></p>");
			$row = mysqli_fetch_array($result);
		}
		$Text->dump();
		if(isset($_POST['q']) )
			echo " <script>decrypttxt();</script>";
		return 1;
	}
	public function verify(){
		global $CRYPTO_SALT;
		$q = "SELECT * FROM `".$this->table."` ORDER BY `i`  DESC";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		$row = mysqli_fetch_array($result);
		for ($i = mysqli_num_rows($result); $i>1; $i--){
			$currentHash = $row['phash'];
			$old_row = mysqli_fetch_array($result);
			$old_row_hash = md5($old_row['phash']);
			if($old_row_hash == $row['phash']){
				$row = $old_row;
			}else{
				echo "It seems that record ".$i." broke the blockchain! The database is FAIL. ";
				return 0;
			}
		}
		echo mysqli_num_rows($result)." entries passed verification.";
		return 1;
	}
	public function update($domain, $name, $lastPhash){
		global $S;
		$S->add('Looking for last sync...');
		$q = "SELECT * FROM `".$this->table."` WHERE author='$name' AND domain='$domain' AND type='0' ORDER BY `i` DESC LIMIT 1;";
		$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
		if (mysqli_num_rows($result) > 0){
			$S->add('Sync found, validating last hash...');
			$result_array = mysqli_fetch_array($result);
			if($result_array['phash'] == $lastPhash){
				$rowNum = $result_array['i'];
				$result_array = array();
				$this->add('0', 'sync event', '', $name, $domain, 'normal,');
				$q = "(SELECT * FROM `".$this->table."` WHERE `i` >'".$rowNum."') ORDER BY `i` ASC;";
				$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
				$S->add('Sync validated, dumping '.mysql_num_rows($result).' new rows');
				for ($i = mysqli_num_rows($result); $i>0; $i--){
					array_push($result_array, mysqli_fetch_array($result));
				}
				return serialize($result_array);
			}else{
				$S->add('sync NOT validated, check your last hash');
				return 'ERROR: sync NOT validated, check your last hash';
			}
		}else{
			$result_array = array();
			$S->add('sync info not found, dumping entire DataBase!');
			$this->add('0', 'sync event', '', $name, $domain, 'first,');
			$q = "SELECT * FROM `".$this->table."` ORDER BY `i` ASC;";
			$result = mysqli_query($this->Mysqli, $q)or die(mysqli_error($this->Mysqli));
			for ($i = mysqli_num_rows($result); $i>0; $i--){
				array_push($result_array, mysqli_fetch_array($result));
			}
			return serialize($result_array);
		}
	}
}
class Query{
	private $content;
	private $other;
	private $location;
	private $author;
	private $domain;

	private $user;
	private $userGroups;
	private $result;

	public function Query($content, $other){
		global $CRYPTO_KEY;	global $S;

		if (isset($other) && $other!=''){
			$this->content = string_decrypt(hexToStr($content), $CRYPTO_KEY);
			$this->other = $other;
			$S->add('Other Field Populated in Query');
		}else{
			$this->content = $content;
		}
	}
	public function do_print(){
		return $this->content;
	}
	public function run(){
		global $DB; global $S; global $DOMAIN; global $CRYPTO_SALT; global $USER; global $COMMANDS; global $CURRENT_DIR; global $DELETE_LOCATION; global $PUREIFIER;
		
		if(substr($this->content,0,1) == '~'){ 									// if the query started with the magic command character
			$query = substr($this->content,1).' '; 								// cut out the tilde and add a space at the end for searching
			$pos1 = strpos($query, 'login ');
			if( strpos($query, 'login ') !== 0 && strpos($query, 'register ') !== 0 && strpos($query, 'mkdir ') !== 0 && strpos($query, 'add_domain ') !== 0 && strpos($query, 'sync ') !== 0 && strpos($query, 'update ') !== 0) // Keep private stuff out of the feed
				$DB->add('0',$query,$this->other,$USER->userName,$DOMAIN,'');
			if(strpos($query,'post ') === 0){									//  is the query a post query
				$S->add('Location data sent with the post: '.$this->other);		// add location information to the query
				$temp_var_for_location = $this->other;			
				$this->other = substr($query,4);								// remove the word post from the query and assign it to the other field
				$videoTags = get_string_between($this->other,'<video','</video>');
				if (strpos($this->other, "~disablePureifier"))
					$this->other = $this->other."***";	// Pureifier has been disabled, content that was posted will go directly into the db (dangerous)
				else
					$this->other = $PUREIFIER->purify($this->other).'<video'.$videoTags.'</video>';		// Pureifies user submitted HTML of scripts and adds <video> tagged objects to the end
				$query = 'post '.$temp_var_for_location.' ';					// add the word post and the location and reassign it to the query 
			}
			$queryWords = explode(' ',$query);									// isolates each word of the query
			$S->add('`~` Used, running `'.$queryWords[0].'` as a command.');
			if($COMMANDS[$queryWords[0]]){										// is the first word found in the commands array?
				if(array_intersect($COMMANDS[$queryWords[0]],$USER->groups)){	// is the command's permission group one that the user is a part of?
					switch($queryWords[0]){
						case 'hello':
						case 'hi':
									$S->add("Hello to you too! I hope you have a wonderful day.");
									echo "Well hi!";
									return 1;
									break;
						case 'register':
						case 'signup':
									if(count($queryWords) > 3){ //require there to be two more variables.
										$S->add('Adding user '.$queryWords[1].' to the database. Be sure to remember your password');
										$DB->add('3',$queryWords[1],$queryWords[4],md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]),$DOMAIN,'registered,');
										echo "You are hereby registered, ".$queryWords[1].". You should now ~login.";
										return 1;
									}
									echo "Not enough arguements, must have `~register username password`";
									return 0;
									break;
						case 'login':
						case 'l':
									if(count($queryWords) > 3){ //require there to be two more variables.
										$author = md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]);
										if ($DB->findUser($author,$DOMAIN)){
											$USER->login($queryWords[1],$author);
											echo "Logged In";
											echo " <script>window.location = window.location.pathname;</script>"; // refresh without GET data
											return 1;
										}
										echo "Your login information was not found.";
										return 0;
									}
									echo "Not enough arguements";
									return 0;
									break;
						case 'whoami':
									echo "you are ".$USER->userName.", and you are part of these groups: ";
									print_r($USER->groups);
									echo " permission level ".$_SESSION['permission_level'];
									return 1;
									break;
						case 'post':
									if($this->other){
										$postType = 2;
										if(strpos($this->other,'~') != false && strpos($this->other,'~') <= 2){ // see if the post has extra flags.
											if (strpos($this->other, '~~') != false){ // make sure the post has the double ~~ (to indicate post content)
												$post = explode('~~',$this->other,2); // seporate out the post from the commands
												$postWords=explode(' ~',$post[0]); // seporate out each command
												$postWords[count($postWords)-1] = substr($postWords[count($postWords)-1],0,-1); // remove the space from last word
												if(in_array('private',$postWords)){ // if the user decided to use the private flag
													echo " Private Post ";
													$postType = -2; // post is private to just the directory it was posted into
												}
												if(in_array('secret',$postWords)){ // if the user decided to keep the post a mystery, viewble only to the group specified
													echo " SECRET POST ";
													$postType = -3; // post is private to just the directory it was posted into, and encrypted with the user's name %%TODO
												}
											}else{
												echo "use '~~' to indicate the content of the post.";
												return 0;
											}
										}else{
											echo "use '~~' to indicate the content of the post.";
											return 0;
										}
										$DB->add($postType,$post[1],$queryWords[1],$USER->userName,$DOMAIN,$queryWords[2]);
										echo "Content Posted";
										echo " <script>window.location = window.location.pathname;</script>"; // refresh without GET data
										return 1;
									}else{
										echo "malformed query, no location data sent with this post (&q2= may not be set)";
									}
									return 0;
									break;
						case 'disp_post':
									if(count($queryWords) > 2){
										$DB->disp_post($queryWords[1],$queryWords[2],$queryWords[3]);
										return 1;
									}else{
										echo "disp_post `number of posts to display` [location]";
										return 0;
									}
									break;
						case 'disp_text':
									$DB->disp_text($queryWords[1],$queryWords[2],$queryWords[3]);
									return 1;
									break;
						case 'verify':
									if ($DB->verify()){
										return 1;
									}else{
										echo "did not verify";
										return 0;
									}
									break;
						case 'addgroup':
									if(count($queryWords) > 3){ // auth / domain / groups
										$DB->editUserGroups($queryWords[1],$DOMAIN,$queryWords[2]);
									}
									echo "not enough arguements, `~addgroup user groupname`";
									break;
						case 'add_domain':
									if(count($queryWords) > 3){
										$S->add('Adding a new Domain to the database: '.$queryWords[1].' added. with the password provided. Confirmation: '.md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]));
										$DB->add('4',$queryWords[1],$queryWords[4],md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]),$DOMAIN,'default,');
										echo "You have added the domain: ".$queryWords[1].". Confirmation: ".md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]);
										return 1;
									}
									return 0;
									break;
						case 'mkdir':
									$path = $this->other.$queryWords[1];
									if (!is_dir($path)){
										mkdir($path);
										chmod($path, 0755);
										if (file_exists($CURRENT_DIR.'_index.php')) { 
											copy($CURRENT_DIR.'_index.php',$path."/index.php");
											$S->add('copied '.$CURRENT_DIR.'_index.php file to new directory '.$path);
										}else if (file_exists('index.php')){
											copy('index.php',$path."/index.php");
											$S->add('copied '.$CURRENT_DIR.'index.php file to new directory '.$path);
										}else{
											echo "Could not copy index file. ";
											$S->add('Could not find index file _index.php, or index.php in'.$CURRENT_DIR);
											return 0;
										}
										echo "Created ".str_replace(substr($CURRENT_DIR,0,-3),'',$path);
										return 1;
									}else{
										$S->add($path.' is a directory already.');
										echo "Could not create ".str_replace(substr($CURRENT_DIR,0,-3),'',$path)." because it is already a directory here.";
										return 0;
									}
									break;
						case 'rm':
									if (!is_dir($CURRENT_DIR.$DELETE_LOCATION)){
										mkdir($CURRENT_DIR.$DELETE_LOCATION);
										if (file_exists($CURRENT_DIR.'_index.php')) { 
											copy($CURRENT_DIR.'_index.php',$CURRENT_DIR.$DELETE_LOCATION."index.php");
										}else if (file_exists($CURRENT_DIR.'index.php')){
											copy($CURRENT_DIR.'index.php',$CURRENT_DIR.$DELETE_LOCATION."index.php");
										}
									}
									rename($this->other.$queryWords[1],$CURRENT_DIR.$DELETE_LOCATION.date("H:i-").$queryWords[1]);
									echo $queryWords[1]." was deleted.";
									return 1;
									break;
						case 'bcadd':
									if (count($queryWords) > 4)
										echo bcadd($queryWords[1],$queryWords[2],$queryWords[3]);
									else if (count($queryWords) == 4)
										echo bcadd($queryWords[1],$queryWords[2],0);									
									return 1;
									break;
						case 'wysiwyg':
									echo '<script>wysiwygfunc();</script>';
									return 1;
									break;
						case 'update':
									if(count($queryWords) > 4){
										$author = md5($queryWords[1].$CRYPTO_SALT.$queryWords[2]);
										if ($DB->findUser($author,$DOMAIN)){
											echo( $DB->update($queryWords[1],$author,$queryWords[3]) );
											return 1;
										}
										echo "ERROR: Your information was not found.";
										return 0;
									}
									echo "ERROR: Not enough arguements.";
									return 0;
									break;
						case 'sync':
									if(count($queryWords) > 0){
										$syncData = unserialize(file_get_contents('http://'.$queryWords[1].'/a/p.php?q=~update+'.$DOMAIN.'+'.$queryWords[2].'+'.$queryWords[3]));
										print_r($syncData);
										echo "<br><br>";
										return 1;
									}
									echo "ERROR: Not enough arguements.";
									return 0;
									break;
						case 'email':
									if(count($queryWords) > 2){
										$to      = $queryWords[1];
										$subject = 'a message m8';
										$message = $queryWords[2];
										$headers = 'From: Jehovah@'.$DOMAIN . "\r\n" .
										    'Reply-To: Jehovah@'.$DOMAIN . "\r\n" .
										    'X-Mailer: PHP/' . phpversion();
										
										if (mail($to, $subject, $message, $headers)){
											echo "Email sent to $to";
											return 1;
										}else{
											print_r(error_get_last());
											return 0;
										}
										return 1;
									}else{
										echo "use ~email address message";
									}
									return 0;
									break;
						case 'logout':
						case 'bye':
									$_SESSION = array();
									session_destroy();
									echo "GoodBye!";
									echo " <script>window.location = window.location.pathname;</script>"; // refresh without GET data
									return 1;
									break;
						case 'pedit':
									if(count($queryWords) > 1){
										echo '
										here it is
										<script type="text/javascript">
											tinymce.init({
											    selector: "#editable-'.$queryWords[1].'",
											    inline: true,
											    plugins: [
											        "advlist autolink lists link image charmap print preview anchor",
											        "searchreplace visualblocks code fullscreen",
											        "insertdatetime media table contextmenu paste"
											    ],
											    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
											});
										</script>';
										//echo " <script>window.location = window.location.pathname;</script>"; // refresh without GET data
									}
									return 0;
									break;
						case 'pdelete':
									if(count($queryWords) > 1){
										if ( $DB->editPostType('-4', $USER->userName, $DOMAIN, $queryWords[1]) ){ // $newType, $author, $domain, $uniqueIterator
											echo "Deleted post.";
											echo " <script>window.location = window.location.pathname;</script>"; // refresh without GET data
											return 1;
										}else{
											echo "Something went wrong";
											return 0;
										}
									}else{
										echo "Not enough params, try `~pdelete <location> <post number>`";
									}
									return 0;
									break; 
						default:
									echo "Command not configured.";
									return 0;
									break;
					}
				}else{
					echo "Permission Denied, ".$USER->userName;
				}
			}else{
				echo "Command Not found";
			}
		}else{ // user is chatting
			$content = htmlspecialchars($this->content);
			$DB->add('1',$content,$this->other,$_SESSION['username'],$DOMAIN,'');
			$S->add('chat msg entered into database');
			return 1;
		}
		return 0;
	}
	public function result(){
		return $this->result;
	}
}
class User{
	public $loggedIn;
	public $userName;
	public $groups = array();

	public function User(){
		global $S;
		if (isset($_SESSION['groups']) && in_array('loggedin',$_SESSION['groups']) && isset($_SESSION['username'])){	// is the user logged in with session variables?
			$this->loggedIn = true;
			$this->userName = $_SESSION['username'];
			$this->groups = $_SESSION['groups'];
			$S->add('user is logged in as '.$this->userName);
		}else{
			$this->loggedIn = false; 
			if(!isset($_SESSION['username'])){
				$_SESSION['username'] = substr(md5(getIP()), 0, 7); // assign an id based on the hash of their ip
			}
			$this->userName = $_SESSION['username'];
			if(!isset($_SESSION['groups'])){
				$_SESSION['groups'] = array();
				array_push($_SESSION['groups'],'anon');
			}
			$S->add('Anonomous User: '.$_SESSION['username']); // alternate session variable use
			array_push($this->groups, 'anon'); // USER joins group 'anon'
		}
	}
	public function login($name){
		global $DB;
		$this->userName = $name;
		$_SESSION['username'] = $name;
		$_SESSION['groups'] = $this->groups;
		array_push($_SESSION['groups'],'loggedin');
		array_push($this->groups,'loggedin');
		$_SESSION['permission_level'] = 2;
		if (in_array( 'admin',$this->groups ))
			$_SESSION['permission_level'] = 5;	
		$_SESSION['reguname'] = $name.'';
	}
}
class PostItem{
	private $type;
	private $id;
	private $style;
	private $content;
	private $tags;
	
	public function do_print(){
		print_r($this->$content);
	}
}

// FUNCTIONS
if (!function_exists('getIP')){
	function getIP() {
		$ip;
		if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else
			$ip = "UNKNOWN";
		return $ip;
	}
}

function string_encrypt($string, $key) {
    $crypted_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB, '86E96EF8E1D71AF61E98B161C6281FFF');
    return $crypted_text;
}

function string_decrypt($encrypted_string, $key) {
    $decrypted_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encrypted_string, MCRYPT_MODE_ECB, '86E96EF8E1D71AF61E98B161C6281FFF');
    return trim($decrypted_text);
}
function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}
function randomColor($spread, $base){
	$spread = 25;
	for ($row = 0; $row < $base; ++$row) {
			for($c=0;$c<3;++$c) {
			$color[$c] = rand(0+$spread,255-$spread);
		}
		for($i=0;$i<92;++$i) {
		$r = rand($color[0]-$spread, $color[0]+$spread);
		$g = rand($color[1]-$spread, $color[1]+$spread);
		$b = rand($color[2]-$spread, $color[2]+$spread);    
		}    
	}
	return $r.','.$g.','.$b;
}
function getWorkingDirectory() {
	global $CURRENT_DIR; global $S;
		$obtain_dir = $_SERVER['SCRIPT_FILENAME'];
		$obtain_dir = substr($obtain_dir, 0, (strlen ($obtain_dir))+1 - (strlen (strrchr($obtain_dir,'/'))));
		$CURRENT_DIR = $obtain_dir;
		$S->add('working in '.$CURRENT_DIR);
	return 1;
}
// FUNCTIONS
function generateRandom($length=1){
	$_rand_src = array(
		array(48,57) //digits
		//, array(97,122) //lowercase chars
		  , array(65,90) //uppercase chars
	);
	srand ((double) microtime() * 1000000);
	$random_string = "";
	for($i=0;$i<$length;$i++){
		$i1=rand(0,sizeof($_rand_src)-1);
		$random_string .= chr(rand($_rand_src[$i1][0],$_rand_src[$i1][1]));
	}
	return $random_string;
}
function check_restricted($action, $dir){
	global $RESTRICTED_AREAS; global $SITE_ROOT; global $sr_perm; global $USER;
	if($USER->loggedIn){
		for ($row = 0; $row < count($RESTRICTED_AREAS); $row++){
			if (strpos($dir,$SITE_ROOT.$RESTRICTED_AREAS[$row]['location']) !== false && $RESTRICTED_AREAS[$row][$action]>$_SESSION['permission_level']){
				if (strpos($dir,$SITE_ROOT.'u/'.$_SESSION['username'])===false)
					return 0;
			}
		}
		if($dir == $SITE_ROOT && $_SESSION['permission_level']<$sr_perm)
			return 0;
		return 1;
	}else{
		return 0;
	}
}
function usernameTaken($reg_username){ // Returns true if the username has been taken by another user, false otherwise.
	global $DB_PARAMETERS;
	$MYSQL_CONNECT = mysql_connect($DB_PARAMETERS['host'],$DB_PARAMETERS['user'],$DB_PARAMETERS['pass'])or die("CAN NOT CONNECT");
	mysql_select_db($DB_PARAMETERS['name'], $MYSQL_CONNECT);
	//if(!get_magic_quotes_gpc()){
	//	$reg_username = addslashes($reg_username);
	//}
	$reg_username =  mysql_real_escape_string($reg_username);
	$q = "SELECT * FROM `".$User_Table_Name."` WHERE `username` = '$reg_username' LIMIT 0, 30 ";
	$result = mysql_query($q,$MYSQL_CONNECT);
	return (mysql_numrows($result) > 0);
}
function update_the_feed($action,$param1='',$param2=''){
	global $DB_PARAMETERS;
	$MYSQL_CONNECT = mysql_connect($DB_PARAMETERS['host'],$DB_PARAMETERS['user'],$DB_PARAMETERS['pass'])or die("CAN NOT CONNECT");
	mysql_select_db($DB_PARAMETERS['name'], $MYSQL_CONNECT);
	if ($MYSQL_CONNECT){
		switch ($action){
			case 'register':
				$q_text = $param1." just signed up";
				break;
			case 'rename':
				$q_text = $_SESSION['reguname']." just renamed ".$param1." to ".$param2;
				break;
			case 'delete':
				$q_text = $_SESSION['reguname']." just deleted ".$param1;
				break;
			case 'edit':
				$q_text = $_SESSION['reguname']." is editing ".$param1;
				break;
			case 'article':
				$q_text = $_SESSION['reguname']." is editing the article: ".$param1;
				break;
			case 'view':
				$q_text = $_SESSION['reguname']." is watching: ".str_replace('<div>','',str_replace('</div>','',$param1));
				break;
			case 'rate':
				$q_text = $_SESSION['reguname']." has rated ".$param1." a ".$param2;
				break;
			case 'upload':
				if ($_SESSION['reguname']." is uploading ".$param1 != $_SESSION['uploading_mail_chunks']){
					$_SESSION['uploading_mail_chunks'] = $_SESSION['reguname']." is uploading ".$param1;
					$q_text = $_SESSION['uploading_mail_chunks'];
				}
				break;	
			case 'login':
				$q_text = $_SESSION['reguname']." just logged on!";
				break;
		}
		if ($q_text){
			$q2="INSERT INTO `".$DB_PARAMETERS['db_name']."`.`webchat_lines` (`id`, `author`, `gravatar`, `text`, `ts`) VALUES ('', 'server', '$action', '$q_text', CURRENT_TIMESTAMP)";
			mysql_query($q2); // update database
			if( $action != 'rate' && $action != 'view' ){
				$from = $SR; // begin SMS send
				$to = $TEXT_NUMBER;
				$carrier = $PHONE_CARRIER;
				$formatted_number = $to.$CARRIER_EMAIL;
				$message = stripslashes($q_text);
				mail("$formatted_number", $_SESSION['reguname'], "$message");
			}
		}
	}
}
function find_valid_path_in_request($wrong_path) {
	if(preg_match("#^((/(?:[a-z]+/)*)([a-z0-9][a-z0-9_-]*\.html?))#i", $wrong_path, $match)) {
		if (substr($match[1], -4) == '.htm') {
			$match[1] .= 'l';
		}
	}
	if(is_file($_SERVER['DOCUMENT_ROOT'].$match[1])) {
		echo "Your link ends with '.htm' change it to '.html'.";
		return $match[1];
	}
	$supplied_directory = $_SERVER['DOCUMENT_ROOT'].$wrong_path;
	while (substr_count($wrong_path,'/') > 1 ) {
		$wrong_folder = basename($wrong_path);
		$wrong_path = dirname($wrong_path);
		$supplied_directory = $_SERVER['DOCUMENT_ROOT'].$wrong_path;
		if (is_dir($supplied_directory)) {
			$ls_dir = scandir($supplied_directory);
			foreach ($ls_dir as $item) {
				if (!strnatcasecmp($item,$wrong_folder)) {
					return $wrong_path.'/'.$item;
				}
			}
		}
	}
	// No match has been found, one way or another.
	return false;
}
function htmltrim($string){
	$pattern = '(?:[ \t\n\r\x0B\x00\x{A0}\x{AD}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}]|&nbsp;|<br\s*\/?>)+';
	return preg_replace('/^' . $pattern . '|' . $pattern . '$/u', '', $string);
}
function parse_links ( $m ){
	$href = $name = html_entity_decode($m[0]);
    if ( strpos( $href, '://' ) === false ) {
        $href = 'http://' . $href;
    }
    if( strlen($name) > LINK_LIMIT ) {
        $k = ( LINK_LIMIT - 3 ) >> 1;
        $name = substr( $name, 0, $k ) . '...' . substr( $name, -$k );
    }
    return sprintf( LINK_FORMAT, htmlentities($href), htmlentities($name) );
}
function unzip($src_file, $dest_dir=false, $create_zip_name_dir=false, $overwrite=false){
	echo "UNPACKING:<br /><br /><br />";
  	if(function_exists("zip_open")){ 
    	if(!is_resource(zip_open($src_file))){ 
        	$src_file=dirname($_SERVER['SCRIPT_FILENAME'])."/".$src_file; 
     	}
      	if (is_resource($zip = zip_open($src_file))){ 
         	$splitter = ($create_zip_name_dir === true) ? "." : "/";
          	if ($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
        	 create_dirs($dest_dir);// Create the directories to the destination dir if they don't already exist
          	while ($zip_entry = zip_read($zip)) {// For every file in the zip-packet
				$pos_last_slash = strrpos(zip_entry_name($zip_entry), "/"); // Now we're going to create the directories in the destination directories if the file is not in the root dir
				if ($pos_last_slash !== false)
				{
				  create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1)); // Create the directory where the zip-entry should be saved (with a "/" at the end)
				}
				if (zip_entry_open($zip,$zip_entry,"r")){ // Open the entry
              		$file_name = $dest_dir.zip_entry_name($zip_entry); // The name of the file to save on the disk
              		if ($overwrite === true || $overwrite === false && !is_file($file_name)){ // Check if the files should be overwritten or not	
               			$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)); // Get the content of the zip entry
                		if(!is_dir($file_name))            
                			file_put_contents($file_name, $fstream );
               			 if(file_exists($file_name)){ // Set the rights
                   			chmod($file_name, 0755);
                    		echo "<span style=\"color:#1da319;\">file saved: </span>".$file_name."<br />";
                		}else{
                    		echo "<span style=\"color:red;\">file not found: </span>".$file_name."<br />";
                		}
              		}
              		zip_entry_close($zip_entry); // Close the entry
           		}      
          	}
         	zip_close($zip); // Close the zip-file
      	}else{
        	echo "No Zip Archive Found.";
        	return false;
		}
		return true;
	}else{
		if(version_compare(phpversion(), "5.2.0", "<"))
			$infoVersion="(use PHP 5.2.0 or later)";
		echo "You need to install/enable the php_zip.dll extension $infoVersion"; 
	}
}
function create_dirs($path){
	if (!is_dir($path)){
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);
		foreach($directories as $directory){
			$directory_path .= $directory."/";
		  	if (!is_dir($directory_path)){
				mkdir($directory_path);
				chmod($directory_path, 0755);
				if (file_exists($SR.'_index.php')) { 
					copy($SR.'_index.php',$path."index.php");
				}else if (file_exists('index.php')){
					copy($SR.'index.php',$path."index.php");
				}
		 	}
		}
	}
}
function zip($source, $destination){
    if (extension_loaded('zip') === true){
        if (file_exists($source) === true){
			echo "s: ".$source." d: ".$destination;
        	$zip = new ZipArchive();
           	if ($zip->open($destination, ZIPARCHIVE::CREATE) === true){
                $source = realpath($source);
                if (is_dir($source) === true){
                   	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                    foreach ($files as $file){
                        $file = realpath($file);
                        if (is_dir($file) === true){
                             $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                        }
                        else if (is_file($file) === true){
                            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                        }
                    }
                }
                else if (is_file($source) === true){
                    $zip->addFromString(basename($source), file_get_contents($source));
                }
            }
            return $zip->close();
        }
    }
    return false;
}
function safe_text($str, $delimiter='') {
	if(strlen($str) > 100) {
		$str = substr($str, 0, 100);
	} else if ($str=="") {
		$str = "_";
	}
	$pattern="/([[:alnum:]_\.-]*)/";
	$bad_chars=preg_replace($pattern,$delimiter,$str);
	$bad_arr=str_split($bad_chars);
	$str=str_replace($bad_arr,$delimiter,$str);
	return $str;
}
function get_string_between($string, $start, $end){
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}
function backwardStrpos($haystack, $needle, $offset = 0){
    $length = strlen($haystack);
    $offset = ($offset > 0)?($length - $offset):abs($offset);
    $pos = strpos(strrev($haystack), strrev($needle), $offset);
    return ($pos === false)?false:( $length - $pos - strlen($needle) );
}
function csort($array, $column){
	$i=0; 
	for($i=0; $i<count($array); $i++){ 
		$sortarr[]=$array[$i][$column]; 
	}
	if(count($array)>1){
		array_multisort($sortarr, $array);
	}
	return($array); 
}
function recursive_directory_size($directory, $format=FALSE){
	$dsize = 0;
	if(substr($directory,-1) == '/'){ // if the path has a slash at the end we remove it here
		$directory = substr($directory,0,-1);
	}
	if(!file_exists($directory) || !is_readable($directory)){ // if the path is not valid or is not a directory ...
		return -1; // ... we return -1 and exit the function
	}
	if(false){ #is_dir($directory)){ // we open the directory
		if($handle = opendir($directory)){
			while(($file = readdir($handle)) !== false){ // and scan through the items inside
				$path = $directory.'/'.$file; // we build the new path
				if($file != '.' && $file != '..'){ // if the filepointer is not the current directory or the parent directory
					// if the new path is a file
					if(is_file($path)){
						$dsize += filesize($path); // we add the filesize to the total size
					}elseif(is_dir($path)){ // if the new path is a directory
						$handlesize = recursive_directory_size($path); // we call this function with the new path
						if($handlesize >= 0){ // if the function returns more than zero
							$dsize += $handlesize; // we add the result to the total size
						}else{ // else we return -1 and exit the function
							return -1;
						}
					}
				}
			}
			closedir($handle); // close the directory
		}
	}else{
		$dsize = filesize($directory);
	}
	if($format == TRUE){ // if the format is set to human readable
		if($dsize / 1073742000 > 1){ // if the total size is bigger than 1GB
			return round($dsize / 1073742000, 2).' GiB';
		}elseif($dsize / 1048576 > 1){ // if the total size is bigger than 1 MB
			return round($dsize / 1048576, 2).' MiB';
		}elseif($dsize / 1024 > 1){ // if the total size is bigger than 1 KB
			return round($dsize / 1024, 2).' KiB';
		}else{ // else return the filesize in bytes
			return round($dsize, 2).' bytes';
		}
	}else{
		return $dsize;// return the total filesize in bytes
	}
}
function copy_r( $path, $dest ){
	if( is_dir($path) ){
		@mkdir( $dest );
		$objects = scandir($path);
		if( sizeof($objects) > 0 ){
			foreach( $objects as $file ){
				if( $file == "." || $file == ".." )
					continue; // go on
				if( is_dir( $path."/".$file ) ){
					copy_r( $path."/".$file, $dest."/".$file );
				}else{
					copy( $path."/".$file, $dest."/".$file );
				}
			}
		}
		return true;
	}elseif( is_file($path) ){
		return copy($path, $dest);
	}else{
		return false;
	}
}
function chmod_r($path, $filemode, $dirmode) {
    if (is_dir($path) ) {
        if (!chmod($path, $dirmode)) {
            $dirmode_str=decoct($dirmode);
            print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            return;
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {  // skip self and parent pointing directories
                $fullpath = $path.'/'.$file;
                chmod_r($fullpath, $filemode,$dirmode);
            }
        }
        closedir($dh);
    } else {
        if (is_link($path)) {
            print "link '$path' is skipped\n";
            return;
        }
        if (!chmod($path, $filemode)) {
            $filemode_str=decoct($filemode);
            print "Failed applying filemode '$filemode_str' on file '$path'\n";
            return;
        }
    }
}
function genColorCodeFromText($text,$min_brightness=0,$spec=10){
	// Check inputs
	if(!is_int($min_brightness)) throw new Exception("$min_brightness is not an integer");
	if(!is_int($spec)) throw new Exception("$spec is not an integer");
	if($spec < 2 or $spec > 10) throw new Exception("$spec is out of range");
	if($min_brightness < 0 or $min_brightness > 255) throw new Exception("$min_brightness is out of range");
	$hash = md5($text); //Gen hash of text
	$colors = array();
	for($i=0;$i<3;$i++)
	$colors[$i] = max(array(round(((hexdec(substr($hash,$spec*$i,$spec)))/hexdec(str_pad('',$spec,'F')))*255),$min_brightness)); //convert hash into 3 decimal values between 0 and 255
	if($min_brightness > 0) //only check brightness requirements if min_brightness is about 100
	while( array_sum($colors)/3 < $min_brightness ) //loop until brightness is above or equal to min_brightness
	for($i=0;$i<3;$i++)
	$colors[$i] += 10; //increase each color by 10
	$output = '';
	for($i=0;$i<3;$i++)
	$output .= str_pad(dechex($colors[$i]),2,0,STR_PAD_LEFT); //convert each color to hex and append to output
	return '#'.$output;
}
function getIP() {
	$ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else if($_SERVER['REMOTE_ADDR'])
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "UNKNOWN";
	return $ip;
}
function display_hits(){
	$log = "_hit.log";
	$IP =  getIP();
	$add = true;
	$hits = 0;
	if (!file_exists ($log)) {
		$h = fopen($log, 'w') or die("can't create hit log");
		fclose($h);
	}
	$h = fopen ($log, 'r');
	while (!feof ($h)) {
		$line = fgets ($h, 4096);
		$line = trim ($line);
		if ($line != '')
			$hits++;
		if ($line == $IP)
			$add = false;
	}
	fclose($h);
	if ($add == true) {
		$h = fopen ($log, 'a');
		fwrite($h, "
	$IP");
		fclose($h);
		$hits++;
	}
	return "Hits ".$hits;
}

// INITIALIZE ///\\\///\\\///\\\///\\ sets up objects
if(!isset($_SESSION['cryptokey'])){	// If the user's unique session cryptokey has not been set,
	$_SESSION['cryptokey'] = md5($START_TIME.$CRYPTO_KEY); //  go ahead and generate a new key and store it in the session
	$CRYPTO_KEY = $_SESSION['cryptokey'];
}else{
	$CRYPTO_KEY = $_SESSION['cryptokey'];
}
$S  =   new S;	// start the status buffer
$DB  =   new Database;	// Starts, and tests connection to database, builds table if needed
$USER  =   new User;	// Creates and sets up all the info about the User and Session
$PUREIFIER  = new HTMLPurifier($pure_config);	// starts the pureifier plugin to clean html POSTS

  // to be done:
 /* 
| 
| add more commands including the shell command
| swap the echoing and the buffered text "$S" and provide a sample interface textbox
| allow for multiple entries from the same location to onyl display the first one, then provide button that will slide the new one into view
|     then add a link that says "comments" "date" and "rating" to bottom bar.
 */
    // //// # / #
?>
<?php
/* display a blog ~CCMS*/
if ($article_number == 0){
	if (isset($content_file_exists)){
		include dirname(__FILE__).'/_article_index.php';
	}
}else{
	echo '<div class="outer_container">';

	$num=0;
	foreach ($FILES as $name) {
		if (!(strpos($name, '_')===0) && (is_dir($name)) && $name!='.' && $name!='..' && $name!='cgi-bin' && strpos($name, '-')===0){
			$num++;
			$n = $num;
			if (file_exists($CURRENT_DIR.$name.'/_CONTENT.txt')){
				$blog_location = $CURRENT_DIR.$name.'/_CONTENT.txt';
				$blog = fopen($blog_location, 'r');
				$blog_content = fread($blog, filesize($blog_location));
				fclose($blog);
			}else{
				$dh2  = opendir($CURRENT_DIR.$name); 	
				while (false !== ($name2 = readdir($dh2))) {
					$files2[] = $name2;
				}
				//array_multisort(array_map('filemtime', $files2), SORT_DESC, $files2);   // sort the blogs from newest to oldest
				foreach ($files2 as $name2) {
					if (pathinfo($CURRENT_DIR.$name2, PATHINFO_EXTENSION)=='txt'){
						$last_text_file2 = $name2;
					}
				}
				$blog_location = $CURRENT_DIR.$name.'/'.$last_text_file2;
				if (file_exists($blog_location)){
					if (filesize($blog_location)>0){
						$blog = fopen($blog_location, 'r');
						$blog_content = fread($blog, filesize($blog_location));
						fclose($blog);
					}else{
						$blog_content = " ";
					}
				}
			}
			// Begin parsing the CONTENT file
			$style_format_top='cboxFtop';
			$style_format_bottom='cboxF';
			$style_effect='1';
			$date='';
			$title=$name;
			$content='Content Missing :(';
			
			if (strstr($blog_content,'align{([left])}')){ $style_format_top='cboxLtop'; $style_format_bottom='cboxL';}
			if (strstr($blog_content,'align{([right])}')){ $style_format_top='cboxRtop'; $style_format_bottom='cboxR';}
			
			if (strstr($blog_content,'effect{([3])}')){ $style_effect='appear';}
			if (strstr($blog_content,'effect{([2])}')){ $style_effect='slide';}
			if (strstr($blog_content,'effect{([1])}')){ $style_effect='blind';}

			if (strstr($blog_content,'tags{([')){
				$tags = get_string_between($blog_content,'tags{([','])}');
			}
			
			if (strstr($blog_content,'date{([')){ $date=get_string_between($blog_content,'date{([','])}');}
			if (($date=='current')||($date=='')){$date=date ("l jS \of F, Y", filemtime($blog_location)); }
			if (strstr($blog_content,'title{([')){ $title=get_string_between($blog_content,'title{([','])}');}
			if (strstr($blog_content,'content{([')){
				$content=get_string_between($blog_content,'content{([','])}');
			}else if(strstr($blog_content,'{([')){
				$content="";
			}else{
				$content=$blog_content;
			}			
																										  
			$start_timeout = microtime(true);
			while (strstr($content,'====') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'====','====');
				$content = str_replace('===='.$temp_string.'====', '<h1>'.$temp_string.'</h1>', $content);
			}		
			$start_timeout = microtime(true);
			while (strstr($content,'===') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'===','===');
				$content = str_replace('==='.$temp_string.'===', '<h2>'.$temp_string.'</h2>', $content);
			}	
			$start_timeout = microtime(true);
			while (strstr($content,'==') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'==','==');
				$content = str_replace('=='.$temp_string.'==', '<h3>'.$temp_string.'</h3>', $content);
			}	
			$start_timeout = microtime(true);
			while (strstr($content,'{{') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'{{','}}');
				$content = str_replace('{{'.$temp_string.'}}', '<h4>'.$temp_string.'</h4>', $content);
			}
			$start_timeout = microtime(true);
			while (strstr($content,'[[[') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'[[[','|');
				$content = str_replace('[[['.$temp_string.'|', '<div class="box1out"><div class="box1head">'.$temp_string.'</div><div class="box1in">', $content);
				$content = str_replace(']]]','</div></div>',$content);
			}
			$start_timeout = microtime(true);
			while (strstr($content,'(((') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'(((','|');
				$content = str_replace('((('.$temp_string.'|', '<div class="box2out"><div class="box2head">'.$temp_string.'</div><div class="box2in">', $content);
				$content = str_replace(')))','</div></div>',$content);
			}	
			
			$start_timeout = microtime(true);
			while (strstr($content,'[[img[') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'[[img[',']right]]');
				$content = str_replace('[[img['.$temp_string.']right]]', '<img src="'.$temp_string.'" class="floatRight">', $content);
			}
			$start_timeout = microtime(true);
			while (strstr($content,'[[img[') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'[[img[',']left]]');
				$content = str_replace('[[img['.$temp_string.']left]]', '<img src="'.$temp_string.'" class="floatLeft">', $content);
			}
			
			$start_timeout = microtime(true);
			while (strstr($content,'[[vid[') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'[[vid[',']right]]');
				$content = str_replace('[[vid['.$temp_string.']right]]', '
						<!-- START OF THE PLAYER EMBEDDING --> 
						<div id="mediaplayer_embed_right">:loading:</div>
						<script type="text/javascript" src="http://cylab.info/_jwplayer/jwplayer.js"></script> 
						<script type="text/javascript"> 
							jwplayer("mediaplayer_embed_right").setup({
								flashplayer: "http://'.$DOMAIN.'/_jwplayer/player.swf",
								file: "'.$temp_string.'",
								image: "'.substr($temp_string,0,-4).'.jpg"
							});
						</script> 
						<!-- END OF THE PLAYER EMBEDDING -->', $content);
			}
			$start_timeout = microtime(true);
			while (strstr($content,'[[vid[') && (microtime(true) - $start_timeout < .001)) {
				$temp_string=get_string_between($content,'[[vid[',']left]]');
				$content = str_replace('[[vid['.$temp_string.']left]]', '
						<!-- START OF THE PLAYER EMBEDDING --> 
						<div id="mediaplayer_embed_left">:loading:</div>
						<script type="text/javascript" src="http://'.$DOMAIN.'/_jwplayer/jwplayer.js"></script> 
						<script type="text/javascript"> 
							jwplayer("mediaplayer_embed_left").setup({
								flashplayer: "http://'.$DOMAIN.'/_jwplayer/player.swf",
								file: "'.$temp_string.'",
								image: "'.substr($temp_string,0,-4).'.jpg"
							});
						</script> 
						<!-- END OF THE PLAYER EMBEDDING -->', $content);
			}
			
			$unique_id=0;
			while (strstr($content,'"')) {
				$temp_string2[$unique_id]=get_string_between($content,'"','"');
				$content = str_replace('"'.$temp_string2[$unique_id].'"','##LINK'.$unique_id.'##',$content);
				++$unique_id;
			}
			
			$content = preg_replace_callback( '~((?:https?://|www\d*\.)\S+[-\w+&@#/%=\~|])~', 'parse_links', $content );
			
			$start_timeout = microtime(true);
			$unique_id=0; 
			while (strstr($content,'##LINK')  && (microtime(true) - $start_timeout < .005)) {
				$content = str_replace('##LINK'.$unique_id.'##','"'.$temp_string2[$unique_id].'"',$content);
				++$unique_id;
			}
			
			$content=str_replace("\r",'<br />',$content);
			
			// end parsing 	

			echo '<div class ="'.$style_format_top.'">';
			if ($USER->loggedIn){
				echo "<input type=\"radio\" class=\"radio\" name=\"blog_select\" onclick=\"document.editform.action='?e=1&amp;n=".$n."';\"/>";
			}
			echo '<span class="ArtNum">'.$n.'</span>';
			if ($style_effect=='appear'){echo '<a href="#" onclick="Effect.toggle(\'s'.$n.'\',\'appear\'); return false;">';}else{echo '<a href="'.$name.'">';}
			echo "<b>".$title.'</b>';
			echo '</a><span class="ArtDate">';
			echo ' '.$date.'</span>';
			echo '</div><div class ="'.$style_format_bottom.'" id="s'.$n;
			if ($style_effect=='appear'){echo'" style="display:none;" >';}else{ echo '" >';}
			echo $content;
			echo '</div>';
		}
	}
	echo "</div>";
}
?>
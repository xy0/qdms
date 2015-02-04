<?php // Continuation of _index1.php running CCMS, index1 is essencial for this file to work
/// IF the index.php file calling the script has sent out headers already (eg. the script
 // is being called from a custom index file) then the script will not display page headers
 // otherise, it will create a default page
if(!headers_sent()){
	$headers_sent = 0;
	?>
	<!-- START QDCCMS ~C -->
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=UTF-8">
			<title><?php echo $page_title;?></title>
			<meta name="description" content="<?php $page_tags; ?>">
			<meta name="ROBOTS" content="ALL" > 
			<link rel="stylesheet" type="text/css" href="<?php echo '/'.$css_main_style_name; ?>">
			<link rel="stylesheet" type="text/css" href="<?php echo $css_extra_style_name; ?>">
		</head>
		<body>
	<?php
}

include_once dirname(__FILE__).'/_login_index.php'; 	// the "up", login link, breadcrumbs, and registration deal.

// if the buffer has anything in it (only if login index wasn't called) then flush it
if($view_output_buffer)
	echo $view_output_buffer;

if(isset($headers_sent) && $headers_sent == 0){
	// include the displayers //
	if ($article_number == 0){
		if (isset($content_file_exists)){
			include dirname(__FILE__).'/_article_index.php';
			include dirname(__FILE__).'/_image_index.php';
			include dirname(__FILE__).'/_file_index.php';
			echo "<div id='chat_col'>";
			include dirname(__FILE__).'/a/p.php';
			echo "</div>";
		}else{
			include dirname(__FILE__).'/_image_index.php';
			include dirname(__FILE__).'/_file_index.php';
			echo "<div id='chat_col'>";
			include dirname(__FILE__).'/a/p.php';
			echo "</div>";		}
	}else if($article_number==1){
		include dirname(__FILE__).'/_article_index.php';
		include dirname(__FILE__).'/_file_index.php';
		echo "<div id='chat_col'>";
		include dirname(__FILE__).'/a/p.php';
		echo "</div>";	}
	else if($article_number>1){
		include dirname(__FILE__).'/_blog_index.php';
		echo '<br /><div id="files">';
		include dirname(__FILE__)."/_file_index.php";
		echo "</div>";
		echo "<div id='chat_col'>";
		include dirname(__FILE__).'/a/p.php';
		echo "</div>";	
	}	
}

//$S->dump();	// Dump the Server Status Buffer Log, for troubleshooting.
$DIRECTORY_SIZE = recursive_directory_size($CURRENT_DIR,TRUE);
$END_TIME = microtime(true) - $START_TIME;
?>

<div id="footer"> 
	<p><?php echo $DIRECTORY_SIZE."&#160;&#160;&#160;".number_format($END_TIME,3)."s&#160;&#160;&#160;".display_hits().'<a href="http://'.$DOMAIN.'/_logs_index.php?logs=all&amp;location='.substr(preg_replace("/[^A-Za-z0-9 ]/", "",$CURRENT_DIR),21).'">.</a>'; ?></p>
</div> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src='/a/rijndael.js'></script>
<script src='/a/mcrypt.js'></script>
<script type="text/javascript">

	$("#login").hide();
	$("#show_login").show();
	$('#show_login').click(function(){
		$("#login").slideToggle('fast');
	}); 
	$(".create").hide();
	$(".show_create").show();
	$('.show_create').click(function(){
		$(".create").slideToggle();
	}); 
	
	function makeExpandingArea(container) {
		var area = container.querySelector('textarea');
		var span = container.querySelector('span');
		if (area.addEventListener) {
			area.addEventListener('input', function() {
				span.textContent = area.value;
			}, false);
			span.textContent = area.value;
		} else if (area.attachEvent) {
		 // IE8 compatibility
			area.attachEvent('onpropertychange', function() {
				span.innerText = area.value;
			});
			span.innerText = area.value;
		}
		// Enable extra CSS
		container.className += ' active';
	}
	var areas = document.querySelectorAll('.expandingArea');
	var l = areas.length;
	while (l--) {
		makeExpandingArea(areas[l]);
	}
	function autoResize(id){
		var newheight;																				
		if(document.getElementById){
			newheight=document.getElementById(id).contentWindow.document .body.scrollHeight;
		}
		document.getElementById(id).height= (newheight) + "px";
	}
	
	function confirmDelete(){
		var agree=confirm("For Real?");
		if (agree)
			return true ;
		else
			return false ;
	}
	function notEmpty(elem, helperMsg){
		if(elem.value.length == 0){
			alert(helperMsg);
			elem.focus();
			return false;
		}
		return true;
	}
	function show_rename(obj){
		if (obj.className=='pre_rename') {
			obj.className = 'post_rename';
			document.getElementById(obj.id + '.ipt').focus();
		}
		else if (obj.className=='post_rename') {
			obj.className = 'pre_rename';
		}
	}

	var last_data = '';var old_title = document.title;var sent_alerts=0;var poll_time=0;
	$(window).focus(function() {
		document.title = old_title;
	});
	document.onmousemove = function(){
		document.title = old_title;
	}
	function send_alert(){
		sent_alerts++;
		if (sent_alerts > 1){
			document.title = "New Message - "+old_title+" !";
		}
	}
	function toHex(str) {
		var hex = '';
		for(var i=0;i<str.length;i++) {
			var val = ''+str.charCodeAt(i).toString(16);
			if(val.length == 1)
				hex += '0'+val;
			else
				hex += val;
		}
		return hex;
	};
	function hexToString (hex) {
		var str = '';
		for (var i=0; i<hex.length; i+=2) {
			str += ''+String.fromCharCode(parseInt(hex.charAt(i)+hex.charAt(i+1), 16));
		}
		return str;
	};
	
	var a858072b9 = "<?php echo $CRYPTO_KEY; ?>";
	var LOCATION = "<?php echo $CURRENT_DIR; ?>";
	
	function qdpoll(){
		$.post("/a/p.php", {d: "20", l: LOCATION },
			function(data) {
				if(last_data!=data){
					$("#queryComments").html(data);
					decrypttxt();
				}
				last_data = data;
			});
		setTimeout('qdpoll()',poll_time*poll_time+2000);
		poll_time = poll_time+5;
	}
	function qd(){
		var from_chat = toHex(mcrypt.Encrypt( $("#queryBox").val(), '', a858072b9, 'rijndael-256', 'ecb'));
		poll_time = 0;
		if($("#queryBox").val()){
			$.post("/a/p.php", { q: from_chat, q2: LOCATION }, 
				function(data) {
					$("#queryResult").html(data);
				});
		};
		document.getElementById("queryBox").value = "";
		qdpoll();
	}
	function wysiwygfunc() {
		alert('not configured');
	}	
</script>

<?php 	
	if(isset($disp_upload)){ 
?>
<!-- uploader -->
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="/lib/uploader/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.gears.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.html5.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.silverlight.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.flash.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.browserplus.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/plupload.html4.js"></script>
<script type="text/javascript" src="/lib/uploader/plupload/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>
<script type="text/javascript">
	// Convert divs to queue widgets when the DOM is ready
	$(function() {
		$("#uploader").plupload({
		// General settings
		runtimes : 'gears,html5,silverlight,flash,browserplus,html4',
		url : '/lib/uploader/plupload/examples/upload.php?d=<?php echo $CURRENT_DIR; ?>',
		max_file_size : '12000mb',
		max_file_count: 200, // user can add no more then 200 files at a time
		chunk_size : '1mb',
		unique_names : false,
		multiple_queues : true,
		// Rename files by clicking on their titles
		rename: true,
		// Sort files
		sortable: true,
		// Specify what files to browse for


		filters : [
			{title : "Allowed Types", extensions : "css,zip,rar,7z,jpg,gif,png,jpeg,bmp,tiff,avi,mpg,mp3,mp4,flv,ogg,wav,js,ogv,alp,als,rm,mpeg,mov,wmv,doc,dot,pdf,pgp,ps,ai,eps,rtf,xls,xlb,ppt,pps,pot,swf,swfl,docx,pptx,xlsx,mpeg,mpga,mpega,mp2,m4a,svg,svgz,tif,html,xhtml,text,rtf,mpe,qt,mov,m4v,rv,asc,txt,diff,log,exe,vst,vsti,dll,rtas,alc,ald,bup,vob,ico,iso,img,sfv,nfo,m3u,mts"}
		],
		// Flash settings
		flash_swf_url : '/lib/uploader/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : '/lib/uploader/plupload/js/plupload.silverlight.xap'
		});
		// Client side form validation
		$('form').submit(function(e) {
			var uploader = $('#uploader').plupload('getUploader');
			// Files in queue upload them first
			if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind('StateChanged', function() {
					if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
						$('form')[0].submit();
					}
				});
				uploader.start();
			} else
			   /* alert('You must at least upload one file.');*/
			return false;
		}); 
	});
</script>
<?php }else{ ?>
<script src="/lib/player/build/mediaelement-and-player.min.js"></script>
<script>
	$('audio,video').mediaelementplayer({
		success: function(player, node) {
			$('#' + node.id + '-mode').html('mode: ' + player.pluginType);
		}
	});
</script>
<?php } ?>
<script>
	function decrypttxt(){
		var fromtxt = document.getElementById("txt");
		$("#txt").html(mcrypt.Decrypt(hexToString(fromtxt.innerHTML), '', a858072b9, 'rijndael-256', 'ecb', '86E96EF8E1D71AF61E98B161C6281FFF'));
	};
	decrypttxt();
</script>	
<link rel="stylesheet" type="text/css" href="/lib/player/build/mediaelementplayer.min.css">

</body>
</html>
<!-- Thanks ;) ~C -->
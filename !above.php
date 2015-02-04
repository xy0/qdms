<?php // Query Display Management System (!above.php)
     // Designed and Maintained by cyroxos
    // 
   // 
  // Programmed by Clayton Shannon
 // All Rights Reserved. 
// Created/Modified 2014.07.28
#///////////////// 
 #             // 
  #           // PHP 5
   #   \_|\  // jQuery 2
    #       // MySQL 5.5
     #     // Windows IIS 7 and Ubuntu Apache 2.2.2.2
      #   // 
       # // 
        // for questions please contact cyroxos@cylab.info
       // 
      //
     // 
    //
   // 
  //   \_|\  ~C
 //
//
# (below) here are the default page titals when not speficically defined
$pageTitle=(isset($pageTitle)?$pageTitle:'');
$pageContent=(isset($pageContent)?$pageContent:'');
$pageKeyWords=(isset($pageKeyWords)?$pageKeyWords:'');
session_start();include($_SERVER['DOCUMENT_ROOT']."/_i1.php"); // This begins our custom CMS
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)</li>!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<title><?php echo $pageTitle ?></title>
	<meta name="description" content="<?php echo $pageContent ?>">
	<meta name="keywords" content="<?php echo $pageKeyWords ?>">
	<meta name="author" content="AnalyticsComputers.com">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="/css/base.css?<?php echo microtime(true); // This prevents file caching?>">
	<link rel="stylesheet" href="/css/skeleton.css?<?php echo microtime(true); // This prevents file caching?>">
	<link rel="stylesheet" href="/css/layout.css?<?php echo microtime(true); // This prevents file caching?>">
	<link rel="stylesheet" type="text/css" href="http://<?php echo $DOMAIN.$css_main_style_name; ?>?<?php echo microtime(true); // This prevents file caching?>">
	<link rel="stylesheet" type="text/css" href="<?php echo $css_extra_style_name; ?>?<?php echo microtime(true); // This prevents file caching?>">
	<link rel="shortcut icon" href="/img/favicon.ico">
	<link rel="apple-touch-icon" href="img/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="img/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="img/apple-touch-icon-114x114.png">
	<script src="/js/classie.js"></script>
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/lib/tinymce/tinymce.min.js"></script>
	<script src="/js/spiderWebs.js"></script>
	<script>function init(){window.addEventListener("scroll",function(e){var t=window.pageYOffset||document.documentElement.scrollTop,n=60,r=document.querySelector("header");if(t>n){classie.add(r,"smaller")}else{if(classie.has(r,"smaller")){classie.remove(r,"smaller")}}})}window.onload=init()</script> <!-- Resize header on scroll -->
</head>
<body>
<header>

	<img src="/img/banner1.gif">
	<!--<img class="headImg" style="right:0px;width:400px;" src="img/headerR.jpg">
	 <img class="headImg" style="left:0px;width:200px;" src="img/headerL.jpg"> -->

	<nav id="nav" role="navigation">
		<a href="#nav" title="Show navigation">Show navigation</a>
		<a href="#" title="Hide navigation">Hide navigation</a>
		<ul class="clearfix">
			<li><a href="/">root</a></li>
			<li>
				<a href="" aria-haspopup="true"><span>Community</span></a>
				<ul>
					<li><a href="/b">Random</a></li>
					<li><a href="/h">Wat?</a></li>
				</ul>
			</li>
			<li><a href="/dev">Projects</a></li>
			<li>
				<a href="" aria-haspopup="true"><span>Media</span></a>
				<ul>
					<li><a href="/m/apps">Applications</a></li>
					<li><a href="/m/audio">Audio</a></li>
					<li><a href="/m/educational">Educational</a></li>
					<li><a href="/m/imagery">Imagery</a></li>
					<li><a href="/m/text">Text</a></li>
					<li><a href="/m/video">Video</a></li>
				</ul>
			</li>
			<li><a href="/h/source">Source</a></li>
		</ul>
	</nav>

</header>
<?php if(false){ // basename($_SERVER['PHP_SELF']) == 'index.php' // this is the custom banner ?>
<div style="background-color:#011F37;width:100%;height:300px;"
		data-0="top:0%;" 
		data-200="top:10%;" 
		data-1200="display:block;" 
		data-1300="display:absolute;top:0px;" 
		data-1500="display:absolute;top:-180px;">		
</div>
<?php } ?>
<canvas height="605" width="1920" id="spiders" class="hidden-xs"></canvas>

<div class="container">
	<?php include($_SERVER['DOCUMENT_ROOT']."/_login_index.php"); ?>
	<!-- End of top -->
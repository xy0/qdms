<?php // config.php, sets up configurations for the QDMS
     // cylab.info 2015.02.04 
    // https://github.com/cyroxos/QDMS
   //
  //
 //
//
#CONFIG
$DB_PARAMETERS = array(
	'host' => 				'localhost',
	'user' => 				'qdmsUser',
	'pass' => 				'qdmsPassword',
	'name' => 				'qdmsDatabase',
	'table'=>				'qd-feed'
);
$DELETE_LOCATION=			"/a/deleted/";	// Location to move files to once they are deleted reletive to site root TRAILING FORWARD SLASH (eg: "_d/" will put the deleted files in www_d/ which will be NEXT TO your public html directory
$DISPLAY_MODE 	= 			"flat";	 // What post effect to use. (useful for testing new styles)
$DOMAIN 		=			"helpme.vodka";	// site domain
$CRYPTO_SALT	= 		md5('d41d8cd98f01b204e9800998ecf8427e');	// Change this but only once before you run the script. This defines how the DataBase is encrypted
$CRYPTO_KEY 	= 		md5($CRYPTO_SALT);	// creates a second key.
$CURRENT_DIR 	= 		str_replace("\\", '/',getcwd().'/'); // probably the most important line of code in this package... it gets the directory of where the user is relative to the site root (C:/ or var/www)
$SITE_ROOT		=		str_replace("\\", '/',dirname(dirname(__FILE__)));	// Location where the apache root directory is located. (usually public_html or www NO TRAILING FORWARD SLASH)
date_default_timezone_set("America/Denver");	// sets the default time zone location, needed to prevent error messages

$COMMANDS = 		array(
		'hello' 	=>	array('anon'),
		'hi'		=>	array('anon'),
		'register'	=>	array('anon'),
		'signup'	=>	array('anon'),
		'add_domain'=>	array('admin'),
		'login' 	=>	array('anon'),
		'l'			=>	array('anon'),
		'whoami'	=>	array('anon'),
		'post'		=>	array('registered'),
		'wysiwyg'	=>	array('registered'),
		'disp_post'	=>	array('anon',),
		'disp_text'	=>	array('anon',),	
		'verify'	=>	array('admin'),
		'addgroup' 	=>	array('admin'), // currently requires user's password hash
		'mkdir'		=>	array('admin'),
		'rm'		=>	array('admin'),
		'bcadd'		=>	array('admin'),
		'update'	=>	array('anon'),
		'sync'		=>	array('admin'),
		'email'		=>	array('registered'),
		'pdelete'	=>	array('registered'),
		'pedit'		=>	array('registered'),
		'logout'	=>	array('anon'),
		'bye'		=>	array('anon')
);
$page_title 			= 	'~QDMS ALPHA';	// default page title (probably only for root)
$css_main_style_name 	= 	"/css/_main_style.css";	// CSS Main File - contains the needed uplaoder and list styles
$css_extra_style_name 	=	"style.css";	// CSS extra file that can be overwritten on the CMS
$default_file_text 		=	"title{([  New!  ])} <br> content{([  <br><br> ])} tags{([    ])}
							";	// This is the default article _CONTENT.
								$sr_perm =	4;	// Site Root and the permission needed to administer it
$RESTRICTED_AREAS = array( array( 'location' => 'dev/',	// Other permission levels according to directory
								  'edit' => 	3,
								  'upload' => 2,
								  'create' => 2 
								),
						   array( 'location' => 'a/', 
								  'edit' => 	3,
								  'upload' => 2,
								  'create' => 2
								),
						   array( 'location' => 'u/', 
								  'edit' => 	4,
								  'upload' => 4,
								  'create' => 4 
								),
						   array( 'location' => 'b/', 
								  'edit' =>	3,
								  'upload' => 1,
								  'create' => 1 
								),
						   array( 'location' => 'h/', 
								  'edit' => 	2,
								  'upload' => 2,
								  'create' => 2 
								)
						 );
setlocale(LC_ALL, 'en_US.UTF8');
define( 'LINK_LIMIT', 45 );
define( 'LINK_FORMAT', '<a href="%s" rel="ext">%s</a>' );
$article_number=0;
$text_file_number=0;
$view_output_buffer = "";

#PLUGINS // configuring 3rd party librarys. 

// HTML Purifier config, cleans html POSTS to make sure they won't ruin the page, or violate securety
require_once $_SERVER['DOCUMENT_ROOT'] . "/lib/pureifier/HTMLPurifier.standalone.php";
$pure_config = HTMLPurifier_Config::createDefault();
$pure_config->set('HTML.TidyLevel', 'light');
$pure_config->set('URI.SafeIframeRegexp','%%');
$pure_config->set('HTML.SafeIframe', true);
$pure_config->set('HTML.SafeObject', 'true');
$pure_config->set('HTML.FlashAllowFullScreen', 'true');
$pure_config->set('Output.FlashCompat',true);
$pure_config->set('CSS.Trusted', 'true');
$pure_config->set('CSS.ForbiddenProperties', array(""));
$pure_config->set('CSS.AllowTricky', 'true');
$pure_config->set('CSS.Proprietary', 'true');
?>
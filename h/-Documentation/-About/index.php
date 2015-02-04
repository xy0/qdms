<?php // QDMS Default Index
     // 
    // 
   // by Clayton Shannon
  // Updated on 2014.12.19
 // All Rights Reserved. 
//
#
$pageTitle = 'QDMS on FYN';
$pageContent = 'Comprehensive Comprehension';
$pageKeyWords = 'FYN,712971818';

include ($_SERVER['DOCUMENT_ROOT'].'/!above.php'); //ob_flush(); // ob_flush sends the output buffer to trip the headers_sent function ?>

		<div class="sixteen columns" style="overflow:hidden;">
			<?php 

				include ($_SERVER['DOCUMENT_ROOT'].'/_blog_index.php'); // this includes the article displayer 1

				// include second post displayer here...
				$DB->disp_post(10,$CURRENT_DIR);	// Direct call to display all posts
				
				include($_SERVER['DOCUMENT_ROOT'].'/a/p.php'); // this includes the Query Display box

				$_file_index_hide = true; // this flag will hide the file list unless logged in
				include ($_SERVER['DOCUMENT_ROOT']."/_file_index.php");
			?>
		</div>

<?php include ($_SERVER['DOCUMENT_ROOT'].'/!below.php'); // includes the page footer?>
<?php include($_SERVER['DOCUMENT_ROOT']."/_i2.php"); // ESSENCIAL include of the CMS, do not add any lines after this one?>
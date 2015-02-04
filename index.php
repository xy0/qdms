<?php // QDMS ( index.php )
     // Designed for ForgetYourName
    // 
   //  
  // Programmed by Clayton Shannon
 // All Rights Reserved. 
// Created/Modified 2014.12.18
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
#
$pageTitle = 'QDMS Demo at HelpMe.Vodka';
$pageContent = 'Comprehensive Comprehension';
$pageKeyWords = 'helpmevodka,help,me,vodka,712971818';

include ($_SERVER['DOCUMENT_ROOT'].'/!above.php');//ob_flush(); // ob_flush sends the output buffer to trip the headers_sent function ?>

    <div class="sixteen columns" style="overflow:hidden;">
      <?php 

        include ($_SERVER['DOCUMENT_ROOT'].'/_blog_index.php'); // this includes the article displayer 1

        // include second post displayer here...
        $DB->disp_post(10,$CURRENT_DIR);  // Direct call to display all posts
        
        include($_SERVER['DOCUMENT_ROOT'].'/a/p.php'); // this includes the Query Display box

        $_file_index_hide = true; // this flag will hide the file list unless logged in
        include ($_SERVER['DOCUMENT_ROOT']."/_file_index.php");
        
      ?>
    </div>

<?php include ($_SERVER['DOCUMENT_ROOT'].'/!below.php'); // includes the page footer?>
<?php include($_SERVER['DOCUMENT_ROOT']."/_i2.php"); // ESSENCIAL include of the CMS, do not add any lines after this one?>
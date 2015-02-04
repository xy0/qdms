<?php
/* display an array of images, ~CCMS */
if ($article_number == 0 && isset($articles[1]) && file_exists($articles[1].'/_CONTENT.txt')){ 
	$blog_location = $CURRENT_DIR.'/_CONTENT.txt';
	$blog = fopen($blog_location, 'r');
	$image_allow_content = fread($blog, filesize($blog_location));
	fclose($blog);
}
if ($article_number == 1 && file_exists($articles[1].'/_CONTENT.txt') ){
	$blog_location = $articles[1].'/_CONTENT.txt';
	$blog = fopen($blog_location, 'r');
	$image_allow_content = fread($blog, filesize($articles[1].'/_CONTENT.txt'));
	fclose($blog);
}
if(isset($image_allow_content) && (strstr($image_allow_content,'adaptive{([0])}'))) {
// nothing happens because the user requested no image formatting	
}else{	


if (isset($_GET['imgpp']) && is_int($_GET['imgpp']) ) {
	$itemsPerPage = $_GET['imgpp'] ; // if ovveridden 
} else {
	$itemsPerPage = '40';         // number of images per page default	
}
// gallery settings
$thumb_width  = '240';        // width of thumbnails
$thumb_height = '170';         // height of thumbnails
$src_folder   = '.';             // current folder
$src_files    = scandir($src_folder); // files in current folder
$extensions   = array(".jpg",".jpeg",".JPEG",".png",".gif",".JPG",".PNG",".GIF"); // allowed extensions in photo gallery

// create thumbnails from images
error_reporting(0);
function make_thumb($folder,$src,$dest,$thumb_width) {

	$jpgarray =array('jpg','jpeg','Jpg','JPG','JPEG','JPE','jpe');
	$pngarray =array('png','PNG','Png');
	$gifarray =array('gif','GIF','Gif');

	if (in_array(substr(strrchr($folder.'/'.$src, '.'), 1),$jpgarray)){
		$source_image = imagecreatefromjpeg($folder.'/'.$src);
	}else if(in_array(substr(strrchr($folder.'/'.$src, '.'), 1),$pngarray)){
		$source_image = imagecreatefrompng($folder.'/'.$src);
	}else if(in_array(substr(strrchr($folder.'/'.$src, '.'), 1),$gifarray)){
		$source_image =imagecreatefromgif($folder.'/'.$src);
	}
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	$thumb_height = floor($height*($thumb_width/$width));
	$virtual_image = imagecreatetruecolor($thumb_width,$thumb_height);
	imagecopyresampled($virtual_image,$source_image,0,0,0,0,$thumb_width,$thumb_height,$width,$height);
	imagejpeg($virtual_image,$dest,100);	
}
// display pagination
function print_pagination($numPages,$currentPage) {
   if ($numPages > 1){ 
   		echo 'Page '. $currentPage .' of '. $numPages;
   }
   if ($numPages > 1) {
	   echo '&nbsp;&nbsp;';
       if ($currentPage > 1) {
	       $prevPage = $currentPage - 1;
	       echo '<a class="image_page" href="'. $_SERVER['PHP_SELF'] .'?p='. $prevPage.'">&laquo;&laquo;</a>';
	   }	   
	   for( $e=0; $e < $numPages; $e++ ) {
           $p = $e + 1;
	       if ($p == $currentPage) {	    
		       $class = 'current-paginate';
	       } else {
	           $class = 'paginate';
	       } 
		       echo '<a class="'. $class .'" href="'. $_SERVER['PHP_SELF'] .'?p='. $p .'">'. $p .'</a>';  	  
	   }
	   if ($currentPage != $numPages) {
           $nextPage = $currentPage + 1;	
		   echo '<a href="'. $_SERVER['PHP_SELF'] .'?p='. $nextPage.'">&raquo;&raquo;</a>';
	   }	  	 
   }
}
?>
<div class="gallery">
<div class="p10"></div>
<?php 
$files = array();
foreach($src_files as $file) {
	$ext = strrchr($file, '.');
    if(in_array($ext, $extensions)) {
          
       array_push( $files, $file );   
       if (!is_dir($src_folder.'/_thumbs')) {
          mkdir($src_folder.'/_thumbs');
          chmod($src_folder.'/_thumbs', 0777);
          //chown($src_folder.'/thumbs', 'apache'); 
       }
	   $thumb = $src_folder.'/_thumbs/'.$file;
       if (!file_exists($thumb)) {
          make_thumb($src_folder,$file,$thumb,$thumb_width);   
	   }   
	}     
}
if ( count($files) == 0 ) {
} else {
    $numPages = ceil( count($files) / $itemsPerPage );
    if(isset($_GET['p'])) {
       $currentPage = $_GET['p'];
       if($currentPage > $numPages) {
           $currentPage = $numPages;
       }
    } else {
       $currentPage=1;
    } 
    $start = ( $currentPage * $itemsPerPage ) - $itemsPerPage;
    for( $i=$start; $i<$start + $itemsPerPage; $i++ ) {
	   if( is_file( $src_folder .'/'. $files[$i] ) ) { 
	      echo '<div class="thumb">
	            <a href="'. $src_folder .'/'. $files[$i] .'" class="albumpix" rel="albumpix">
			       <img src="'. $src_folder .'/_thumbs/'. $files[$i] .'" width="'.$thumb_width.'" height="'.$thumb_height.'" alt="" />
				</a>
				';
		  echo '</div>'; 
	    } else {
		  echo $files[$i]; 
		}
    }
     echo '<div class="clear"></div>';
     echo '<div class="p5-sides">
	         <div class="float-left">'.count($files).' images</div>
	         <div class="float-right paginate-wrapper">';
              print_pagination($numPages,$currentPage);
       echo '</div>
	         <div class="clearb10">
		   </div>';
}
?>
</div>
</div>
<?php 
}
?>

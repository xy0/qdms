<?php // QDMS
     //	QueryDisplay is an attempt at having a Command-line web Content Management System that will be 
    // both secure, and minimal. It includes encrypted message sending, database integrity checking, 
   // multiple ways to implement, and real time updating.
  /*//////
       // by Clayton Shannon
      // 
     // 
    // at  cylab.info/u/cy
   // 
  // 
 //        \_|\ ~c
*/ 
#INIT
/*
error_reporting(-1);	// -1: report all errors, 0: report no errors
ini_set('display_errors', 'On');	// also reports all errors
//*/
isset($_SESSION)? '': session_start();	// if no session, create one.
/*
session_regenerate_id(true);	// prevents session fixation attack, disabled currently
//*/
$START_TIME = (isset($START_TIME))?$START_TIME:microtime(true); // if start time hasnt been set, then set it now
$_SESSION['chatTime'] = microtime(true);	// start the page load time sensing and let the chat script known when the chat was viewed

require_once(dirname(__FILE__).'/config.php');	// this file should always be in the same directory as these includes
require_once(dirname(__FILE__)."/functions.php");

if(isset( $_GET['q']) ){  // Query Mode GET
	$QUERY = new Query($_GET['q'],isset($_GET['q2'])? $_GET['q2']:'');	// Passes GET vars along to the Query class
	$S->add('Query Mode, Using GET Method >>'.$QUERY->do_print());	// Server Status Error Buffer
	if($QUERY->run()){	// Runs the Query
		$S->add('Query process completed.');
	}else{
		$S->add('Query Process failed.');
	}
}else if(isset($_POST['q']) ){	// Query Mode POST
	$QUERY = new Query($_POST['q'], isset($_POST['q2'])? $_POST['q2']:'');	// Passes POST vars along to the Query class
	$S->add('Query Mode, Using POST Method >>'.$QUERY->do_print());	// Server Status Error Buffer
	if($QUERY->run()){	// Runs the Query
		$S->add('Query process completed.');
	}else{
		$S->add('Query Process failed.');
	}
}else if(isset($_POST['d'])){	// if requesting text to be displayed
	$DB->disp_text($_POST['d'], isset($_POST['l'])? $_POST['l']:'',0);	// direct call to display text(comments)
}else{
	$focus=isset($noFocus)?'':''; // autofocus should be in the second '', but we are disabling it temp
	$chatLimit=isset($chatLimit)?$chatLimit:5;
	echo '
	<!-- START QUERY WIDGET -->
	<form name="queryForm">
		<div class="expandingArea">
			<pre><span></span><br></pre>
			<textarea id="queryBox" onKeydown="Javascript: if ((event.keyCode==13) && (!event.shiftKey)) {qd();event.preventDefault();}" '.$focus.'></textarea>
			<input id="queryButton" type="button" value="post" onClick="qd();"/>
		</div>
	</form>
	<div id="queryResult" style="position:relative;z-index:1;min-height:22px;margin-left:20px;"></div>
	<div id="queryComments" style="position:relative;z-index:1;margin-left:20px;margin-right:20px;">
	';
	$DB->disp_text($chatLimit,$CURRENT_DIR,1);
	echo '</div>
	<!-- END OF QUERY WIDGET -->
	';
}

$S->add((string) microtime(true)-$START_TIME);	// this adds the script execution time to the end of the log
//$S->dump();	// Uncomment to Dump the Server Status Buffer Log, for troubleshooting.

?>
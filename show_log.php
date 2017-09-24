<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Show log</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }

function openURLpopup( PageURL ) {
	window.open( PageURL ,'popUpWindow','height=400,width=600,left=10,top=10,,scrollbars=yes,menubar=no');
}
  
</script>	
</head>
<body>
<h2>Show log</h2>
<?php
$basedir=dirname(__FILE__);
include_once("image2video.php");

#include_once( image2video::$queue4job_path."/queue4job.php");
#$queue=new queue4job( image2video::$queue4job_path );
#$db=$queue->db;

/*
echo "<pre>";
echo var_dump( $queue );
echo "</pre>";
*/

$statusColors=array( 'failed'=>'#ffe6e6', 'finished'=>'#e6fff2' );
$errors= array();
$messages= array();

$today = date("F j, Y, g:i a");  
$dt =  date("U");

if( image2video::get_param( 'file' ) ) {
	$file=base64_decode ( image2video::get_param( 'file'  ) );
}

if( !file_exists( $file )  ) {
	echo "<h3> File $file do not exist !!! </h3>
		</body>
	</html>			
	";
	exit(0);
}

$content=file_get_contents( $file );
echo "<h2>$file</h2><hr>
<pre>
$content
</pre>

		</body>
	</html>			
";

?>

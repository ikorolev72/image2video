<?php
include_once("image2video.php");
$head="<!doctype html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Edit video effects</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
	<script src='jscolor.js'></script>	
</head>
<body>
<a href='index.php'> [ Home ] </a>
<hr>

	";
if( !$_POST['project_id'] && !$_GET['project_id']  ) {
	echo $head;
	echo "<h3> Error !Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}

$project_id=$_POST['project_id'];
if( !$project_id ) $project_id=$_GET['project_id'];
$basedir=dirname(__FILE__);
$main_upload_dir="$basedir/uploads/";
$upload_dir="$basedir/uploads/$project_id";
$upload_url="./uploads/$project_id";
$bin_dir="$basedir/bin";

if( !file_exists( "$upload_dir/project.txt") ) {		
	echo $head;

	echo "<h3> Error ! Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}



$errors= array();
$messages= array();
$effects_info= array();


$string = file_get_contents("$upload_dir/project.txt");
$project = json_decode($string, true);

	echo $head;

	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo image2video::showMenu( $project_id );	
	
	if( file_exists( "$upload_dir/outfile.mp4") ) {		
		if( $_GET['upload'] && $_GET['project_id'] ) 
		{
			#YOUTUBE_ID=`$YOUTUBE_UPLOADER  --client-secrets=$YOUTUBE_CLIENT_SECRET 	--title="$title" --description="More info:$back_url\nComposer:$composer_name\nSong:$full_title\nPerformer:$performer_name\n"  --category=Music 	$VIDEO`
			$youtube_client_id=image2video::$youtube_client_id;
			$youtube_crentials=image2video::$youtube_crentials;
			$description=$project['project_name'];
			$title=$project['project_name'];
			
			$command="/usr/local/bin/youtube-upload  --credentials-file='$youtube_crentials' --client-secrets='$youtube_client_id' --title='$title' --description='$description'  --category=Education 	'$upload_dir/outfile.mp4'  2>> '$upload_dir/youtube_upload.log' | tee -a '$upload_dir/youtube_upload.log' ";
			#$command="/usr/local/bin/youtube-upload  --credentials-file='$youtube_crentials' --client-secrets='$youtube_client_id' --title='$title' --description='$description'  --category=Education 	'$upload_dir/outfile.mp4' >> '$upload_dir/youtube_upload.log' 2>&1   ";
			#echo $command;
			exec ( $command, $ops, $ret );
			if( $ret==0 ) {
				echo "<hr>Your video: <a href='https://www.youtube.com/watch?v=". $ops[0] ."'> ". $ops[0] ."</a>";
			} else {
				echo "Someting wrong:
					<pre>";
				echo var_dump( $ops );
				echo "</pre>";				
			}
				echo "<hr>Upload <a href='$upload_url/youtube_upload.log'>Log file</a>";
			#header("Location: $upload_url/make_slideshow_report.html"); 
			
			exit;		
		}	
		else {
			echo "<hr><a href='$upload_url/outfile.mp4'> This project have early generated video file </a> <hr> ";
			echo "Please, click here for <a href='?project_id=$project_id&upload=1'>upload this file to youtube </a> <hr> ";			
		}
	}	
	else {
		echo "<hr><h3>Don't have the existing video file for this project</h3><hr> ";		
	}	





?>
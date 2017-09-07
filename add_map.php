<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add google map screenshot</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
function getObj(objID)
{
    if (document.getElementById) {return document.getElementById(objID);}
    else if (document.all) {return document.all[objID];}
    else if (document.layers) {return document.layers[objID];}
}


function setFieldValue( field, value ) {
   var elem, size, text, ftext;
   
	elem = getObj( field );
	elem.innerHTML=value;
	return true;
}


	function getCoordinatesFromUrl( str ) {
		var re = '/\@(-*\d+\.\d+),(-*\d+\.\d+),(\d+)z/i';
		var found = str.match(re);
			if ( found != -1) {
				setFieldValue( 'map_lat', match[1] );
				setFieldValue( 'map_lng', match[2] );
				setFieldValue( 'map_zoom', match[3] );
				return true;
			} else {
				alert( "Cannot get the google map coordinates from this string" );
				return false;
			}
	}
		
		
</script>	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Add google map screenshot</h3>

<?php
include_once("image2video.php");

$project_id=$_POST['project_id'];
if( !$project_id ) $project_id=$_GET['project_id'];

$basedir=dirname(__FILE__);
$upload_dir="$basedir/uploads/$project_id";
$upload_url="./uploads/$project_id";
$bin_dir="$basedir/bin";

if( !file_exists( "$upload_dir/project.txt") ) {		
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

$string = file_get_contents("$upload_dir/audio.txt");
$audio = json_decode($string, true);

$string = file_get_contents("$upload_dir/images.txt");
$images = json_decode($string, true);

$string = file_get_contents("$upload_dir/effects.txt");
$effects = json_decode($string, true);

$string = file_get_contents("$upload_dir/project.txt");
$project = json_decode($string, true);

$string = file_get_contents("$upload_dir/map.txt");
$map = json_decode($string, true);

$files_info=array();

	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo image2video::showMenu( $project_id );

$output_width=image2video::$output_width;
$output_height=image2video::$output_height;	

if( $map[1]['url'] ) {
	$map_filename=$map[1]['name'];
	$map_url=$map[1]['url'];
	$map_lng=$map[1]['lng'];
	$map_lat=$map[1]['lat'];
	$map_zoom=$map[1]['zoom'];
}



if( $project['width'] ) $output_width=$project['width'];
if( $project['height'] ) $output_height=$project['height'];
	

$form="
    <form action='add_map.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
		";
		
        if( $map[1]['url'] ) {
			$form.="<tr><td> Existing map</td><td><a href='$map_url'> $map_filename </a></td></tr>";
		}
		
$form.="
		<tr><td>Google map url</td><td><input type='text'  name='google_map_url' value='$google_map_url' size=50 length=250 > </td></tr>
        <tr><td></td><td><input type='submit' name='save' value='Save'> </td></tr>	
	<input type='hidden' name='project_id' value='$project_id'>		
	</table>
	";
/*
		<tr><td>Coordinate lat</td><td><input type='text'  name='map_lat' value='$map_lat' size=20 length=20 > </td></tr>
		<tr><td>Coordinate lng</td><td><input type='text'  name='map_lng' value='$map_lng' size=20 length=20 > </td></tr>
		<tr><td>Map zoom</td><td><input type='text'  name='map_zoom' value='$map_zoom' size=20 length=20 > </td></tr>
*/


if( $_POST['save'] ) {	
		$k=1;
		if( $_POST['google_map_url'] ) {
			$google_map_url=$_POST['google_map_url'];
			#https://www.google.com/maps/@13.8273947,100.5628821,17z
			#https://www.google.ru/maps/dir/47.529086,39.6330779/%D1%83%D0%BB.+%D0%92%D0%BE%D0%BB%D0%BA%D0%BE%D0%B2%D0%B0,+%D0%98%D0%B2%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0,+%D0%A0%D0%BE%D1%81%D1%82%D0%BE%D0%B2%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB.,+346583/@47.5318098,39.6353525,15z/data=!4m8!4m7!1m0!1m5!1m1!1s0x40e22cee72f23ed7:0x4bac1599b1a039fd!2m2!1d39.6330354!2d47.5291493
			if( preg_match( '/\@(-*\d+\.\d+),(-*\d+\.\d+),(\d+)z/', $google_map_url, $match )) {
				$lat=$match[1];
				$lng=$match[2];
				$zoom=$match[3];
				$dt=date("U");
				$htmlFilename="$upload_dir/${dt}_getmap.html";
				
				$file_ext="png";
				$file_type='image/png';
				$file_sha1=sha1( $htmlFilename );
			
				$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
				$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
				$file_thumb_name = sprintf("$upload_dir/%s.%s",  "thumb_$file_sha1" , "jpg") ;
				$file_thumb_url  = sprintf("$upload_url/%s.%s",  "thumb_$file_sha1" , "jpg") ;

				makeMapHtmlFile ( $htmlFilename, $lat, $lng, $zoom ) ;
				
				$cmd="phantomjs '$bin_dir/get_screenshot.js'  $htmlFilename $file_name";

				exec ( $cmd, $ops, $ret );
				if( $ret==0 ) {
					$messages[]="Map saved to <a href='$file_url'>$file_name</a>";	
				} else {
					$errors[]="Someting wrong:	<pre>". var_dump( $ops )."</pre>" ;			
				}
			}	
			if( file_exists($file_name) ) {
				list($width, $height) = getimagesize($file_name );
					if( image2video::make_thumb( $file_name, $file_thumb_name, $file_type, $width, $height ) ) 
					{
						list($thumb_w, $thumb_h) = getimagesize( $file_thumb_name );
					}
					else
					{
						$file_thumb_name=$file_name;
						$file_thumb_url=$file_url;
						$errors[]="Error Creating thumbnail for $file_name";			
						
						$ratio = $width/$height;
						$thumb_h = 200;
						$thumb_w = $thumb_h * $ratio;
					}
					
				$files_info[$k]=array(
					'url'=>		$file_url,
					'name'=>	$file_name,
					'ext'=>		$file_ext,
					'size'=>	$file_size,
					'type'=>	$file_type,
					'width'=>	$width,
					'height'=>	$height,
					'thumb_name'=>	$file_thumb_name,
					'thumb_url'=>	$file_thumb_url,
					'thumb_w'=>	$thumb_w,
					'thumb_h'=>	$thumb_h,
				);
			} else {
					$errors[]="File $file_name do not exist.";					
			}		
				
				
		} else {
				$errors[]="Cannot get the coordinates from your url";					
		}
			

	$myfile = fopen("$upload_dir/map.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/map.txt";
		}
	fwrite($myfile, json_encode ( $files_info ) );
	fclose($myfile);

	foreach($messages as $value)
		{
		echo "<font color=green>$value</font><br>";
		}			 			 
	foreach($errors as $value)
		{
		echo "<font color=red>$value</font><br>";
		}
		
	if ( $k>0 ){
		$form="
    <form action='import_image.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
        <tr><td></td><td><input type='submit' value='Next step'> </td></tr>
	</table>
	<input type='hidden' name='project_id' value='$project_id'>	
	<input type='hidden' name='import_name' value='map'>	
	<input type='hidden' name='import_key' value='1'>	
    </form>
		";
	}
}
	
	


 echo $form;

function makeMapHtmlFile ( $filename, $lat, $lng, $zoom) {
	global $errors;
	$key=image2video::$google_map_api_key;
	$str="<!DOCTYPE html>
	<html>
	<body>
	<div id='map' style='width:100%;height:1080px;'></div>


		<script>
		  function initMap() {
			var LanLng = {lat: $lat, lng: $lng};
			var map = new google.maps.Map(document.getElementById('map'), {
			  zoom: $zoom,
			  center: LanLng
			});
			var marker = new google.maps.Marker({
			  position: LanLng,
			  map: map,
			  title: 'We are here'
			});
		  }
		</script>
		<script async defer
		src='https://maps.googleapis.com/maps/api/js?key=$key&callback&callback=initMap'>
		</script>


	</body>
	</html>
	";
	$myfile = fopen($filename, "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $filename";
			fclose($myfile);
			return 0;
		}
	fwrite($myfile, $str );
	fclose($myfile);
	return 1;
 }
 
 ?>





</body>
</html> 



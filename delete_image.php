<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit video effects</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }
</script>		
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Remove the image </h3>

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

$files_info=array();


	if( $_GET['del'] && $_GET['image_id'] && $_GET['project_id'] ) {
		$image_id=$_GET['image_id'];
		if( array_key_exists ( $image_id ,$images ) ) {
			$new_images=array();
			$new_effect=array();
			$i=1;
			foreach( $images as $k=>$val ):
				if( $k != $image_id  ) {
					$new_images[$i]=$images[$k] ;
					$new_effects[$i]=$effects[$k] ;					
				}
				$i++;
			endforeach;		
			$myfile = fopen("$upload_dir/images.txt", "w") ;
			if( !$myfile ) 
				{
					$errors[]="Unable to open file $upload_dir/images.txt";
				}
			fwrite($myfile, json_encode ( $new_images ) );
			fclose($myfile);	
			
			$myfile = fopen("$upload_dir/effects.txt", "w") ;
			if( !$myfile ) 
				{
					$errors[]="Unable to open file $upload_dir/effects.txt";
				}
			fwrite($myfile, json_encode ( $new_effects ) );
			fclose($myfile);				
		}
	}


$string = file_get_contents("$upload_dir/images.txt");
$images = json_decode($string, true);

$string = file_get_contents("$upload_dir/effects.txt");
$effects = json_decode($string, true);

	
if(!empty($images))
{
	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo "// <a href='add_logo.php?project_id=$project_id'>Logo</a> // <a href='upload_audio.php?project_id=$project_id'>Audio</a> // <a href='add_new_image.php?project_id=$project_id'>Add new image</a> // <a href='change_image_order.php?project_id=$project_id'>Change image order</a> // <a href='delete_image.php?project_id=$project_id'>Remove the image</a> // <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> //<hr><br>";	
	
	echo "<table>";
	foreach($images as $k=>$val):
		$keys = array_keys($val);
		$form_fied_alert=array();
		$record_count++;
//			<td><a href=".$val['url']." target=_blank> <img src="'.$val['thumb_url'].'" width='.$val['thumb_w'].' height='.$val['thumb_h'].'</a></td>

		echo "<tr valign=center align=right>
			<td><a href='".$val['url']."' target=_blank> <img src='".$val['thumb_url']."' width=".$val['thumb_w']." height=".$val['thumb_h']."</a></td>
			<td>[ <a href='' onclick=\"confirm_prompt( 'Are you sure to remove this image?','delete_image.php?del=1&image_id=$k&project_id=$project_id'); return false;\">Remove this image</a> ]</td>
		";
	endforeach;	
	echo "</table>";	
}
else
{
	$errors[]="Cannot read file 'images.txt' with images info";			
}

	
	foreach($messages as $value)
		{
		echo "<font color=green>$value</font><br>";
		}			 			 
	foreach($errors as $value)
		{
		echo "<font color=red>$value</font><br>";
		}	
?>

<a name='bottom'></a>
</body>
</html>	
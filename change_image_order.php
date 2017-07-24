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

<h3> Change the images order </h3>

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


	if( $_POST['move_image'] && $_POST['image_id'] && $_POST['new_image_id'] ) {
		$image_id=$_POST['image_id'];
		$new_image_id=$_POST['new_image_id'];
		/*
			$i=1;
			foreach( $images as $k=>$val ):
				if( $k != $image_id  ) {
					$new_images[$i]=$images[$k] ;
					$new_effects[$i]=$effects[$k] ;					
					$i++;
				}

			endforeach;				
		*/
	$replaced=0;
			$i=1;
			$new_images=array();
			$new_effect=array();	
			
			echo( "<pre>");
			
			foreach( $images as $k=>$val ):
				if( $k==$new_image_id ) {
					$new_images[$i]=$images[$image_id] ;
					$new_effects[$i]=$effects[$image_id] ;		
					$replaced=1;
					$i++;
				} 
				if( $k!=$image_id ) {
					$new_images[$i]=$images[$k] ;
					$new_effects[$i]=$effects[$k] ;					
					$i++;
				}
			endforeach;		
			if( !$replaced ) {
				$new_images[$i]=$images[$image_id] ;
				$new_effects[$i]=$effects[$image_id] ;
			}	
			echo( "</pre>");
	

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


$string = file_get_contents("$upload_dir/images.txt");
$images = json_decode($string, true);

$string = file_get_contents("$upload_dir/effects.txt");
$effects = json_decode($string, true);

	
if(!empty($images))
{
	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo "// <a href='add_logo.php?project_id=$project_id'>Logo</a> // <a href='upload_audio.php?project_id=$project_id'>Audio</a> // <a href='add_new_image.php?project_id=$project_id'>Add new image</a> // <a href='change_image_order.php?project_id=$project_id'>Change image order</a> // <a href='delete_image.php?project_id=$project_id'>Remove the image</a> // <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> //<hr><br>";	
	
	echo "<form action='change_image_order.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
	";
	$i=1;
	foreach($images as $k=>$val):
		$keys = array_keys($val);
		$form_fied_alert=array();
		$record_count++;

		echo "<tr valign=center align=right>
			<td>  </td>
			<td> </td>
			<td> Move selected image here <input name='new_image_id' type='radio' value='$i'>  </td>
			<tr valign=center align=right>
			<td> Select image <input name='image_id' type='radio' value='$i'>  </td>
			<td> <a href='".$val['url']."' target=_blank> <img src='".$val['thumb_url']."' width=".$val['thumb_w']." height=".$val['thumb_h']."></a></td>
			<td> <img src='".$val['thumb_url']."' width=".$val['thumb_w']." height=".$val['thumb_h']."></td>

		";
		$i++;
	endforeach;	
		echo "<tr valign=center align=right>
			<td>  </td>
			<td> </td>
			<td> Move selected image here <input name='new_image_id' type='radio' value='$i'>  </td>
			<tr><td></td><td></td><td><input type='submit' value='Move' name='move_image' id='move_image'> </td></tr>
		</table>
		<input type='hidden' name='project_id' value='$project_id'>			
		</form>
		";	
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
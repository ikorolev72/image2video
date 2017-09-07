<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import image</title>
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

<h3> Import image </h3>

<?php
include_once("image2video.php");

$project_id=image2video::get_param( 'project_id' );

#$project_id=$_POST['project_id'];
#if( !$project_id ) $project_id=$_GET['project_id'];
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


$import_name=image2video::get_param( 'import_name' );
$import_key=image2video::get_param( 'import_key' );
#$import_name=$_POST['import_name'];
#if( !$import_name ) $import_name=$_GET['import_name'];

$possible_import_names=array();
$possible_import_names['map']='map.txt';
$possible_import_names['images']='images.txt';
$possible_import_names['crest']='crest.txt';
$possible_import_names['logo']='logo.txt';

if( $import_name  && $possible_import_names[$import_name] ) {
	$import_filename="$upload_dir/".$possible_import_names[$import_name];
	if( !file_exists( $import_filename ) ) {
		$errors[]="File '$import_filename' with image info do not exists";				
	} else {
		$string = file_get_contents( $import_filename );
		$import = json_decode($string, true);		
	}

	if( !file_exists( $import[$import_key]['name'] ) ) {
		#echo "<pre>";
		#echo var_dump($import);
		#echo var_dump($_POST);
		echo "<h3> Error !  File for import do have the image </h3>";
		echo "Try again <a href=index.php> here </a>
			</body>
			</html>
		";	
		exit(0);
	}
} else {
	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo image2video::showMenu( $project_id );
		echo "<table> ";
			foreach( $possible_import_names as $k=>$val ){
				echo "<tr><td>Import <a href='?project_id=$project_id&import_key=1&import_name=$k'> $k </a> ";
				echo "</td></tr>";				
			}
		echo "</table> 
		
			</body>
			</html>
		";	
		exit(0);	
}

$files_info=array();


	if( $_POST['move_image'] && $_POST['image_id'] && $_POST['new_image_id'] ) {
		$image_id=$_POST['image_id'];
		$new_image_id=$_POST['new_image_id'];

			$replaced=0;
			$i=1;
			$new_images=array();
			$new_effect=array();	
			
			echo( "<pre>");
			

			foreach( $images as $k=>$val ){
				if( $k==$new_image_id ) {
					$new_images[$i]=$import[$import_key] ;
					$new_effects[$i]=array() ;	
					$replaced=1;					
					$i++;
				} 
					$new_images[$i]=$images[$k] ;
					$new_effects[$i]=$effects[$k] ;	
					$i++;					
			}		
			if( !$replaced ) {
				$new_images[$i]=$import[$import_key] ;
				$new_effects[$i]=array() ;
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
	echo image2video::showMenu( $project_id );


	
	echo "<form action='import_image.php' method='post' multipart='' enctype='multipart/form-data'>
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
				<td> Import image here <input name='new_image_id' type='radio' value='$i'>  </td>
				<tr valign=center align=right>
				<td>   </td>
			";
		if( $i==1 ) {
			echo "<td> <input name='image_id' type='hidden' value='1'> <a href='".$import[$import_key]['url']."' target=_blank> <img src='".$import[$import_key]['thumb_url']."' width=".$import[$import_key]['thumb_w']." height=".$import[$import_key]['thumb_h']."></a></td>
				<td> <img src='".$val['thumb_url']."' width=".$val['thumb_w']." height=".$val['thumb_h']."></td>
			";
		} else {
			echo "<td> </td>
				<td> <img src='".$val['thumb_url']."' width=".$val['thumb_w']." height=".$val['thumb_h']."></td>
			";			
		}
		$i++;
	endforeach;		
		echo "<tr valign=center align=right>
			<td>  </td>
			<td> </td>
			<td> Import image here <input name='new_image_id' type='radio' value='$i'>  </td>
			<tr><td></td><td></td><td><input type='submit' value='Import' name='move_image' id='move_image'> </td></tr>
				<input type='hidden' name='project_id' value='$project_id'>	
				<input type='hidden' name='import_name' value='$import_name'>	
				<input type='hidden' name='import_key' value='$import_key'>	
			</form>
			<form action='edit_effect.php' method='post' multipart='' enctype='multipart/form-data'>	
				<input type='hidden' name='project_id' value='$project_id'>	
			<tr><td></td><td></td><td><input type='submit' value='Cancel'>  </td></tr>

			</form>
		</table>
		
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
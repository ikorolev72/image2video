<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit video effects</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Add new image </h3>

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


	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo "// <a href='add_logo.php?project_id=$project_id'>Logo</a> // <a href='upload_audio.php?project_id=$project_id'>Audio</a> // <a href='add_new_image.php?project_id=$project_id'>Add new image</a> // <a href='change_image_order.php?project_id=$project_id'>Change image order</a> // <a href='delete_image.php?project_id=$project_id'>Remove the image</a> // <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> //<hr><br>";	


$form="
    <form action='add_new_image.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
        <tr><td></td><td><input type='file' name='img[]' multiple> </td></tr>
        <tr><td></td><td><input type='submit'> </td></tr>		
	</table>
	<input type='hidden' name='project_id' value='$project_id'>		
    </form>
	";


$img = $_FILES['img'];
	
if(!empty($img))
{

$img_desc = image2video::reArrayFiles($img);
#echo '<pre>';
#    print_r($img_desc);
#echo '</pre>';
    $k=0;
    foreach($img_desc as $val)
    {
		if( empty($val['name'])) 
		{
			continue;
		}
		$file_size=$val['size'];
		$file_type=$val['type'];
		$file_ext=strtolower(end(explode('.',$val['name'])));

		$file_sha1=sha1_file( $val['tmp_name'] );
		$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
		$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
		$file_thumb_name = sprintf("$upload_dir/%s.%s",  "thumb_$file_sha1" , "jpg") ;
		$file_thumb_url  = sprintf("$upload_url/%s.%s",  "thumb_$file_sha1" , "jpg") ;
		$expensions= array( "png","gif","jpg","jpeg" );

		if( in_array($file_ext,$expensions)=== false)
		{
			$errors[]="extension for file ".$val['name']." not allowed, please choose another file.";
			continue;
		}
		if( !$file_size || $file_size> 2*1024*1024  )
		{
			$errors[]="File too big for uploading ( >2mb )";
			continue;
		}	
		
		if ( move_uploaded_file( $val['tmp_name'], $file_name ) ) 
		{
			$messages[]="File ".$val['name']." saved to $file_name";			
			
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
		}	
		else 
		{
			$errors[]="Cannot save the file ".$val['name']." to $file_name";			
		}
		$k++;
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

    }
	
	$image_new=array_pop( $files_info);
	array_push( $images, $image_new ) ;
	$files_info=$images;

	$myfile = fopen("$upload_dir/images.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/images.txt";
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
    <form action='edit_effect.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
        <tr><td></td><td><input type='submit' value='Next step'> </td></tr>
	</table>
	<input type='hidden' name='project_id' value='$project_id'>	
    </form>
		";
	}
		
}
	


 echo $form;
?>



</body>
</html> 



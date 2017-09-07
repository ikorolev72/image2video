<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add crest</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Add crest </h3>

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

$string = file_get_contents("$upload_dir/project.txt");
$project = json_decode($string, true);

$string = file_get_contents("$upload_dir/crest.txt");
$crest = json_decode($string, true);

$files_info=array();

	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo image2video::showMenu( $project_id );
	
	
$output_width=image2video::$output_width;
$output_height=image2video::$output_height;

if( $crest[1]['url'] ) {
	$crest_filename=$crest[1]['name'];
	$crest_url=$crest[1]['url'];
	$crest_x=$crest[1]['crest_x'];
	$crest_y=$crest[1]['crest_y'];
	$crest_enable_checked='no_checked';

	$crest_w=$crest[1]['crest_w'];
	$crest_h=$crest[1]['crest_h'];


	if( $crest[1]['crest_enable'] ) {
		$crest_enable_checked='checked';
	}
}
else {	
	$crest_x=20;
	$crest_y=0;	
	$crest_enable_checked='checked';
	$crest_w=200;
	$crest_h=200;	
}





if( $project['width'] ) $output_width=$project['width'];
if( $project['height'] ) $output_height=$project['height'];
	

$form="
    <form action='add_crest.php' method='post' multipart='' enctype='multipart/form-data'>	
	<table>		
		";
		
        if( $crest[1]['url'] ) {
			$form.="<tr><td> Existing crest</td><td><a href='$crest_url'> $crest_filename </a></td></tr>";
		}
		
$form.="
        <tr><td>Add/change crest</td><td><input type='file' name='img[]' multiple> </td></tr>
        <tr><td>X</td><td><input type='number'  name='crest_x' value='$crest_x' min='0' max='$output_width' > </td></tr>
        <tr><td>Y</td><td><input type='number'  name='crest_y' value='$crest_y' min='0' max='$output_height' > </td></tr>
        <tr><td>Resize to width</td><td><input type='number'  name='crest_w' value='$crest_w' min='0' max='$output_width' > </td></tr>
        <tr><td>Resize to height</td><td><input type='number'  name='crest_h' value='$crest_h' min='0' max='$output_height' > </td></tr>
        <tr><td>Enable crest</td><td><input type='checkbox'  name='crest_enable' value='1' $crest_enable_checked > </td></tr>
        <tr><td></td><td><input type='submit' name='save' value='Save'> </td></tr>	
	<input type='hidden' name='project_id' value='$project_id'>		
	</table>
	";

	



if( $_POST['save'] ) {	
$img = $_FILES['img'];

if( file_exists($img['tmp_name'][0])) # check if file uploaded
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
		$expensions= array( "png","jpg","jpeg" );

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
	if( $_POST['crest_x'] <0 ) 		{
			$errors[]="crest x and y coordinates must be zero or positive. X and Y set to 0";
			$_POST['crest_x']=0;
			$_POST['crest_y']=0;
		}
	if( ! $_POST['crest_w']  || $_POST['crest_h'] || $_POST['crest_w']>$output_width || $_POST['crest_h']>$output_height ) 	
		{
			$errors[]="crest width and height set to 200";
			$_POST['crest_w']=200;
			$_POST['crest_h']=200;
		}

	$files_info[$k]['crest_x']=intval( $_POST['crest_x'] );
	$files_info[$k]['crest_y']=intval( $_POST['crest_y'] );
	$files_info[$k]['crest_w']=intval( $_POST['crest_w'] );
	$files_info[$k]['crest_h']=intval( $_POST['crest_h'] );
	$files_info[$k]['crest_enable']=$_POST['crest_enable'];
	
} else {

	$files_info=&$crest;
	$k=1;
	if( $_POST['crest_x'] <0 ) 		{
			$errors[]="crest x and y coordinates must be zero or positive. X and Y set to 0";
			$_POST['crest_x']=0;
			$_POST['crest_y']=0;
		}
	if( !$_POST['crest_w']  || !$_POST['crest_h'] || $_POST['crest_w']>$output_width || $_POST['crest_h']>$output_height ) 	
		{
			$errors[]="crest width and height set to 200";
			$_POST['crest_w']=200;
			$_POST['crest_h']=200;
		}
	$files_info[$k]['crest_x']=intval( $_POST['crest_x'] );
	$files_info[$k]['crest_y']=intval( $_POST['crest_y'] );
	$files_info[$k]['crest_w']=intval( $_POST['crest_w'] );
	$files_info[$k]['crest_h']=intval( $_POST['crest_h'] );
	$files_info[$k]['crest_enable']=$_POST['crest_enable'];
}	


	$myfile = fopen("$upload_dir/crest.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/crest.txt";
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
    <form action='add_logo.php' method='post' multipart='' enctype='multipart/form-data'>	
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



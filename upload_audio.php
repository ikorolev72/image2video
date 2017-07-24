<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload audio file</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<h3> Upload audio</h3>

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

$string = file_get_contents("$upload_dir/logo.txt");
$logo = json_decode($string, true);

$files_info=array();

	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo "// <a href='add_logo.php?project_id=$project_id'>Logo</a> // <a href='upload_audio.php?project_id=$project_id'>Audio</a> // <a href='add_new_image.php?project_id=$project_id'>Add new image</a> // <a href='change_image_order.php?project_id=$project_id'>Change image order</a> // <a href='delete_image.php?project_id=$project_id'>Remove the image</a> // <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> //<hr><br>";	




if( $audio[1]['url'] ) {
	$audio_filename=$audio[1]['name'];
	$audio_url=$audio[1]['url'];
	$audio_enable_checked='';
	if( $audio[1]['audio_enable']==1 ) {
		$audio_enable_checked='checked';
	}
}
else {	
	$audio_enable_checked='checked';
}

$form="

	<table>		
		<form action='upload_audio.php' method='post' multipart='' enctype='multipart/form-data'>		
			<input type='hidden' name='project_id' value='$project_id'>	
		";
		
        if( $audio[1]['url'] ) {
			$form.="<tr><td> Existing audio file</td><td><a href='$audio_url'> $audio_filename </a></td></tr>";
		}
		
$form.="			
			<tr><td>Add/change audio file</td><td><input type='file' name='audioform[]' multiple> </td></tr>
			<tr><td>Enable audio</td><td><input type='checkbox'  name='audio_enable' value='1' $audio_enable_checked > </td></tr>
			<tr>
				<td><input type='submit' name='save' id='save' value='Save file'></form></td>

			</tr>
	</table>
	";

if( $_POST['save'] ) {	

$audioform = $_FILES['audioform'];
if( file_exists($audioform['tmp_name'][0])) # check if file uploaded
{

$audio_desc = image2video::reArrayFiles($audioform);
#echo '<pre>';
#    print_r($audio_desc);
#echo '</pre>';
    $k=0;
    foreach($audio_desc as $val)
    {
		if( empty($val['name'])) 
		{
			continue;
		}
		$file_size=$val['size'];
		$file_type=$val['type'];
		$file_ext=strtolower(end(explode('.',$val['name'])));

		$file_sha1=sha1( $val['tmp_name'] );
		$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
		$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
		

		$expensions= array( "mp3","aac","wav","ac3" );


		if( in_array($file_ext,$expensions)=== false)
		{
			$errors[]="extension for file ".$val['name']." not allowed, please choose another file ( mp3, aac, wav, ac3 ).";
			continue;
		}
		if( !$file_size || $file_size> 8*1024*1024  )
		{
			$errors[]="File too big for uploading ( >8mb )";
			continue;
		}
		
		
		if ( move_uploaded_file( $val['tmp_name'], $file_name ) ) 
		{
			$messages[]="File ".$val['name']." saved to $file_name";			
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
			'type'=>	$file_type
		);

    }
} else {
	$files_info=&$audio;
	$k=1;
	$files_info[$k]['audio_enable']=$_POST['audio_enable'];	
}	

	$myfile = fopen("$upload_dir/audio.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/audio.txt";
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
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clone project</title>
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

<h3> Clone project </h3>

<?php
include_once("image2video.php");



$basedir=dirname(__FILE__);
$main_upload_dir="$basedir/uploads/";
$main_upload_url="./uploads";
$bin_dir="$basedir/bin";


$today = date("F j, Y, g:i a");  
$dt =  date("U");


$errors= array();
$messages= array();
$effects_info= array();

$old_project_id=image2video::get_param( 'old_project_id' );
$project_name=image2video::get_param( 'project_name' );

/*
$upload_dir="$basedir/uploads/$old_project_id";
$upload_url="./uploads/$old_project_id";	
if( !file_exists( "$upload_dir/project.txt") ) {		
	echo "<h3> Error ! Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}
*/
if( $old_project_id ) {
	if( $project_name ) {
		$upload_dir="$basedir/uploads/$old_project_id";
		$upload_url="./uploads/$old_project_id";			
		$project=array();
		$project_id=$dt.sha1( $project_name.$today );
		$project['project_name']=$project_name;
		$project['project_id']=$project_id;
		$new_upload_dir="$main_upload_dir/$project_id";
		if( ! mkdir( $new_upload_dir, 0777, true) ) {
			echo "<h2><font color=red>Error! Cannot make the directory $new_upload_dir</font></h2>";
			exit (0);
		}
		
		$filename="$new_upload_dir/project.txt";
		if( !file_put_contents($filename, json_encode ( $project ) ) ) {
			echo "<h2><font color=red>Error! Unable to save file $filename</font></h2>";			
			exit(0);				
		}
		
		# clone files
		$expensions= array( "mp3","aac","wav","ac3", "png","gif","jpg","jpeg" );
		image2video::copy_files( $upload_dir, $new_upload_dir, $expensions );
		
		# clone info data
		$Targets=array( 'logo.txt', 'crest.txt', 'images.txt' , 'effects.txt',  'map.txt', 'audio.txt' );
		foreach( $Targets as $target ) {
			if( !file_exists( "$upload_dir/$target" ) ) continue;
			
			$string = file_get_contents("$upload_dir/$target");
			$json = json_decode($string, true);			
			$string= str_replace($old_project_id, $project_id, $string);
			
			$filename="$new_upload_dir/$target";
			if( ! file_put_contents( $filename, $string ) ) {
				echo "<h2><font color=red>Error! Unable to save file $filename</font></h2>";			
				exit(0);				
			}							
		}
		echo "<h3>Project '$project_name' created</h3>
				Edit effect for this project '<a href='edit_effect.php?project_id=$project_id'>$project_name</a>'
		";
		show_errors();
		echo "<a name='bottom'></a>	</body>	</html>	";
		exit( 0);		
	} else {
		$upload_dir="$basedir/uploads/$old_project_id";
		$upload_url="./uploads/$old_project_id";			
		$project=array();
		$string = file_get_contents("$upload_dir/project.txt");
		$project = json_decode($string, true);			
		$project_name=$project["project_name"];
		echo "<h3>Clone project '$project_name' to</h3>
				<form  method='post' multipart='' enctype='multipart/form-data'>
				<table>
				<tr>
					<td><input type='text' name='project_name' value='Clone of $project_name' size=50></td>
					<td><input type='submit'  name='save' id='save' value='Save'> </td>
					
				</tr>
				</table>
				<input type='hidden'  name='old_project_id' id='old_project_id' value='$old_project_id'>
				</form>		
		";
		show_errors();
		echo "<a name='bottom'></a>	</body>	</html>	";
		exit( 0);		
	}
} else {
		echo '<table>';	
		$Dirs=scandir( $main_upload_dir , SCANDIR_SORT_DESCENDING );
/*		echo '<pre>';
		echo var_dump($Dirs );
		echo '</pre>';
		echo '<pre>';
		echo var_dump($dir );
		echo '</pre>';
*/		
		foreach( $Dirs as $dir ) {
			if( file_exists( "$main_upload_dir/$dir/project.txt") ) {
				$string = file_get_contents("$main_upload_dir/$dir/project.txt");
				$project = json_decode($string, true);
				$project_name=$project['project_name'];
				$project_id=$project['project_id'];
				
				echo "	<tr>
							<td><a href='?old_project_id=$project_id'>$project_name</a></td>
							<td></td>
						</tr>\n";
			}
		}
		echo '</table>';	
		show_errors();
		echo "<a name='bottom'></a>	</body>	</html>	";
		exit( 0);		

}


function show_errors() {
	global $messages;
	global $errors;
	foreach($messages as $value)
		{
		echo "<font color=green>$value</font><br>";
		}			 			 
	foreach($errors as $value)
		{
		echo "<font color=red>$value</font><br>";
		}	
}

		
?>

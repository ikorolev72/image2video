<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project list</title>
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
<h2>Projects</h2>
<?php
include_once("image2video.php");


#$project_id=$_POST['project_id'];
$basedir=dirname(__FILE__);
$main_upload_dir="$basedir/uploads/";
$main_upload_url="./uploads";
$bin_dir="$basedir/bin";

$errors= array();
$messages= array();

$today = date("F j, Y, g:i a");  
$dt =  date("U");

if( $_GET['new'] ) {
	echo "<h3>Add new project</h3>
			<form action='index.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
			<tr>
				<td><input type='text' name='project_name' value='New project $today' size=50></td>
				<td><input type='submit'  name='save' id='save' value='Save'> </td>
				
			</tr>
			</table>
			<input type='hidden'  name='add' id='add' value='1'>
			</form>
		</body>
	</html>			
	";
	exit(0);
}

if( $_POST['add'] ) {
	$project=array();
	$project_id=$dt.sha1( $_POST['project_name'].$today );
	$project_name=$_POST['project_name'];
	$project['project_name']=$project_name;
	$project['project_id']=$project_id;
	
	if( ! mkdir( "$main_upload_dir/$project_id", 0777, true) ) 
	{
		echo "<h2><font color=red>Error! Cannot make the directory $main_upload_dir/$project_id</font></h2>";
		exit (0);
	}
	$myfile = fopen("$main_upload_dir/$project_id/project.txt", "w") ;
	if( !$myfile ) 
		{
			"<h2><font color=red>Error! Unable to save file $main_upload_dir/$project_id/project.txt</font></h2>";			
			exit(0);
		}
	fwrite($myfile, json_encode ( $project ) );
	fclose($myfile);	
	
	
	echo "<h3>Project '$project_name' created</h3>
			<form action='upload_images.php' method='post' multipart='' enctype='multipart/form-data'>
			<table>
			<tr>
				<td><input type='submit'  name='save' id='save' value='Add images to project'> </td>
			</tr>
			</table>
			<input type='hidden' name='project_id' value='$project_id'>				
			</form>			
		</body>
	</html>			
	";
	// renew the fonts list
		$command="find /usr/share/fonts/truetype/ |grep ttf\$ >$basedir/fonts.txt 2>/dev/null";
		#$command="convert -list font | awk '/Font:/ {print $2}' >$basedir/fonts.txt 2>/dev/null";
		exec ( $command );	
	exit(0);
}



if( $_GET['del'] && $_GET['project_id'] ) {
	$project_id=$_GET['project_id'];
	if( file_exists( "$main_upload_dir/$project_id/project.txt") ) {
		exec( "rm -rf $main_upload_dir/$project_id" );
		echo "<h2><font color=green>Project with id '$project_id' removed</font></h2>";					
	} else{
		echo "<h2><font color=red>Error! Cannot remove the project '$project_id'</font></h2>";					
	}
}	

echo '		
			<table>
			<tr>
				<td bgcolor=#FDF2FF><a href="index.php?new=1">Add new project</a></td>
				<td></td>
			</tr>

		';
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
							<td><a href='edit_effect.php?project_id=$project_id'>$project_name</a></td>
							<td>[ <a href='' onclick=\"confirm_prompt( 'Are you sure to remove this project?','index.php?del=1&project_id=$project_id'); return false;\">Remove this project</a> ]</td>
						</tr>\n";
			}
		}
echo '		
				</form>
			</table>

		</body>
	</html>			
';

?>

</body>
</html>
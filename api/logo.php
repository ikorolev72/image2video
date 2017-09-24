<?php
header('Content-Type: application/json');
$basedir=dirname(__FILE__);
$libFile="$basedir/../image2video.php";

include_once( $libFile );
include_once( "commonapi.php" );

$main_upload_dir=realpath( "$basedir/../uploads/" );
$bin_dir=realpath( "$basedir/../bin" );

$errors= array();
$messages= array();

$today = date("F j, Y, g:i a");  
$dt =  date("U");
$json_in=commonapi::get_param( 'in' );


if( !$json_in ) {
	$out['status']='error';
	$out['errorno']='4000';
	$out['error']="Did not receive input parameters";
	print_json_and_exit( $out );
}

$in=json_decode( $json_in , true) ;

if( !array_key_exists( 'apikey', $in ) ){
	$out['status']='error';
	$out['errorno']='4001';
	$out['error']="Did not recive 'apikey'";
	print_json_and_exit( $out );	
}

if( !array_key_exists( 'action', $in ) ){
	$out['status']='error';
	$out['errorno']='4002';
	$out['error']="Did not receive required input parameter 'action'";
	print_json_and_exit( $out );	
}

if( !commonapi::check_apikey( $in['apikey'] )) {
	$out['status']='error';
	$out['errorno']='4003';
	$out['error']="Incorrect 'apikey'";
	print_json_and_exit( $out );	
}


# action list
if( $in['action'] == 'list' ){
	# action list for record with id
	
	if( array_key_exists( 'id', $in ) ) {
		$dir=$in['id'];
		if( file_exists( "$main_upload_dir/$dir/project.txt") ) {
			$out['status']='ok';
			$out['rows']=array( );			
			$string = file_get_contents("$main_upload_dir/$dir/project.txt");
			$project = json_decode($string, true);
			array_push( $out['rows'], $project );
			print_json_and_exit( $out );
		} else {
			$out['status']='error';
			$out['errorno']='4100';
			$out['error']="Cannot get the project with id=".$in['id'];
			print_json_and_exit( $out );				
		}
	}
	# action list for records without id
	else {
		$out['status']='ok';
		$out['rows']=array( );
		$Dirs=scandir( $main_upload_dir , SCANDIR_SORT_DESCENDING );
		foreach( $Dirs as $dir ) {
			if( file_exists( "$main_upload_dir/$dir/project.txt") ) {
				$string = file_get_contents("$main_upload_dir/$dir/project.txt");
				$project = json_decode($string, true);
				array_push( $out['rows'], $project );
			}
		}
		print_json_and_exit( $out );
	}
}

# action 'add' new project
if( $in['action'] == 'add' ){
	# action clone for record with id	
	if( array_key_exists( 'name', $in ) ) 
	{
		$project_name=isset( $in['name'] )? $in['name'] : "New project $dt";
		$project=array();
		$project_id=$dt.sha1( $project_name.$today );
		$project['project_name']=$project_name;
		$project['project_id']=$project_id;
		$project['main_upload_dir']=$main_upload_dir;
		
		$new_upload_dir="$main_upload_dir/$project_id";
		if( !mkdir( $new_upload_dir, 0777, true) ) {
			$out['status']='error';
			$out['errorno']='4101';
			$out['error']="Cannot make the directory '$new_upload_dir'";
			print_json_and_exit( $out );				
		}
		
		$filename="$new_upload_dir/project.txt";
		if( !file_put_contents($filename, json_encode ( $project ) ) ) {
			$out['status']='error';
			$out['errorno']='4102';
			$out['error']="Unable to save file '$filename'";
			print_json_and_exit( $out );						
		}
		
		$out['status']='ok';
		$out['id']=$project_id;
		print_json_and_exit( $out );	
	}
	else 
	{
		$out['status']='error';
		$out['errorno']='4005';
		$out['error']="Did not receive required input parameter 'name'";
		print_json_and_exit( $out );	
	}
}


# action clone
if( $in['action'] == 'clone' ){
	# action clone for record with id	
	if( array_key_exists( 'id', $in ) ) 
	{
		$old_project_id=$in['id'];
		if( !file_exists( "$main_upload_dir/$old_project_id/project.txt")) {
			$out['status']='error';
			$out['errorno']='4100';
			$out['error']="Cannot get the project with id=".$in['id'];
			print_json_and_exit( $out );
		}
		$project_name=isset( $in['name'] )? $in['name'] : "Clone of project with id $old_project_id";
		$upload_dir="$main_upload_dir/$old_project_id";		
		$project=array();
		$project_id=$dt.sha1( $project_name.$today );
		$project['project_name']=$project_name;
		$project['project_id']=$project_id;
		$project['main_upload_dir']=$main_upload_dir;
		
		$new_upload_dir="$main_upload_dir/$project_id";
		if( !mkdir( $new_upload_dir, 0777, true) ) {
			$out['status']='error';
			$out['errorno']='4101';
			$out['error']="Cannot make the directory '$new_upload_dir'";
			print_json_and_exit( $out );				
		}
		
		$filename="$new_upload_dir/project.txt";
		if( !file_put_contents($filename, json_encode ( $project ) ) ) {
			$out['status']='error';
			$out['errorno']='4102';
			$out['error']="Unable to save file '$filename'";
			print_json_and_exit( $out );						
		}
		
		# clone files
		$expensions= array( "mp3","aac","wav","ac3", "png","gif","jpg","jpeg" );
		image2video::copy_files( $upload_dir, $new_upload_dir, $expensions );
		
		# clone info data
		$Targets=array( 'logo.txt', 'crest.txt', 'images.txt' , 'effects.txt',  'map.txt', 'audio.txt' );
		foreach( $Targets as $target ) {
			if( !file_exists( "$upload_dir/$target" ) ) continue;
			
			$string = file_get_contents("$upload_dir/$target");
			$string= str_replace($old_project_id, $project_id, $string);
			
			$filename="$new_upload_dir/$target";
			if( ! file_put_contents( $filename, $string ) ) {
				$out['status']='error';
				$out['errorno']='4102';
				$out['error']="Unable to save file '$filename'";
				print_json_and_exit( $out );			
			}							
		}
		$out['status']='ok';
		$out['id']=$project_id;
		print_json_and_exit( $out );	
	}
	else 
	{
		$out['status']='error';
		$out['errorno']='4003';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );	
	}
}


# action remove
if( $in['action'] == 'remove' ){
	# action remove for record with id	
	if( array_key_exists( 'id', $in ) ) 
	{
		$project_id=$in['id'];
		if( file_exists( "$main_upload_dir/$project_id/project.txt") ) {
			exec( "rm -rf $main_upload_dir/$project_id" );
			$out['status']='ok';		
			print_json_and_exit( $out );
		} else{
			$out['status']='error';
			$out['errorno']='4104';
			$out['error']="Project with id '$project_id' do not exists";
			print_json_and_exit( $out );		
		}
	}
	else
	{
		$out['status']='error';
		$out['errorno']='4003';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );	
	}
}


if( $in['action'] == 'count' ){	
		$out['status']='ok';
		$count=0;
		$Dirs=scandir( $main_upload_dir , SCANDIR_SORT_DESCENDING );
		foreach( $Dirs as $dir ) {
			if( file_exists( "$main_upload_dir/$dir/project.txt") ) {
				$count++;
			}
		}
		$out['count']=$count;
		print_json_and_exit( $out );
}



$out['status']='error';
$out['errorno']='4004';
$out['error']="Unknown 'action'";
print_json_and_exit( $out );			



function get_param( $val ) {
	global $_POST;
	global $_GET;
	$ret=isset( $_POST[ $val ] ) ? $_POST[ $val ] : 
				( isset( $_GET[ $val ] ) ? $_GET[ $val ] : null );
	return $ret;
}

function print_json_and_exit( $out ) {
	echo json_encode( $out, JSON_PRETTY_PRINT );
	exit(0);
}
?>

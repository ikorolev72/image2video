<?php
header('Content-Type: application/json');
$basedir=dirname(__FILE__);
$libFile="$basedir/../image2video.php";

include_once( $libFile );
include_once( "commonapi.php" );

$main_upload_dir=realpath( "$basedir/../uploads/" );
$main_upload_url="./uploads";
$bin_dir=realpath( "$basedir/../bin" );

$errors= array();
$messages= array();

$today = date("F j, Y, g:i a");  
$dt =  date("U");


/*
var_dump( $_GET );
var_dump( $_POST );
var_dump( $_FILES );
*/

$json_in=commonapi::get_param( 'in' );
$item_name='audio';

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

if( !array_key_exists( 'project_id', $in ) ){
	$out['status']='error';
	$out['errorno']='4006';
	$out['error']="Did not receive required input parameter 'project_id'";
	print_json_and_exit( $out );
}

$project_id=$in['project_id'];

if( !file_exists( "$main_upload_dir/$project_id/project.txt") ) {
	$out['status']='error';
	$out['errorno']='4104';
	$out['error']="Project with project_id '$project_id' do not exists";
	print_json_and_exit( $out );		
}


$item_file="$main_upload_dir/$project_id/$item_name.txt";
# action list
if( $in['action'] == 'list' ){
	if( file_exists( $item_file ) ) {
		$out['status']='ok';
		$string = file_get_contents( $item_file );
		$item = json_decode($string, true);
		$out['rows']=$item;			
		print_json_and_exit( $out );
	} else {
		$out['status']='error';
		$out['errorno']='4110';
		$out['error']="$item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
}


if( $in['action'] == 'set' ){
	if( !file_exists( $item_file ) ) {
		$out['status']='error';
		$out['errorno']='4110';
		$out['error']="$item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
	$string = file_get_contents( $item_file );
	$item = json_decode($string, true);
	if( array_key_exists( 'disable', $in ) || ( array_key_exists( 'enable', $in ) && !$in['enable'] ) ) {
		$item[1]["enable"]=0;
	}
	if( array_key_exists( 'rnd', $in ) && !$in['rnd']  ) {
		$item[1]["rnd"]=0;
	}	
	if( array_key_exists( 'item_selected', $in ) ) {
		$item[1]["item_selected"]=intval( $in['item_selected'] );
	}	

	$filename= $item_file;
	$string=json_encode( $item, JSON_PRETTY_PRINT );			
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}					
	$out['rows']=$item;	
	$out['status']='ok';		
	print_json_and_exit( $out );

}

# action 'add' new audio
if( $in['action'] == 'add' ){	
	$err=null;
	$upload_dir="$main_upload_dir/$project_id";
	$upload_url="$main_upload_url/$project_id";
	if( file_exists( $item_file ) ) {
		$string = file_get_contents( $item_file );
		$item = json_decode($string, true);		
	} else {
		$item = array();
		$item[1]["enable"]=1;
		$item[1]["rnd"]=1;
		$item[1]["item_selected"]=1;
	}
	if( array_key_exists( 'disable', $in ) || ( array_key_exists( 'enable', $in ) && !$in['enable'] ) ) {
		$item[1]["enable"]=0;
	}
	if( array_key_exists( 'rnd', $in ) && !$in['rnd']  ) {
		$item[1]["rnd"]=0;
	}
	if( array_key_exists( 'item_selected', $in ) ) {
		$item[1]["item_selected"]=intval( $in['item_selected'] );
	}
	
	if( array_key_exists( 'audio_base64', $in ) ) {
		$new_audio_item=image2video::save_audio_base64 ( $in['audio_base64'], $upload_dir, $upload_url, $err )	;
		if( ! $new_audio_item ) {
			$out['status']='error';
			$out['errorno']='4121';
			$out['error']="Error while try save audio: $err";
			print_json_and_exit( $out );				
		}
	}
	if( array_key_exists( 'audio_copy', $in ) ) {
		$new_audio_item=image2video::save_audio_copy ( $in['audio_copy'], $upload_dir, $upload_url, $err )	;
		if( ! $new_audio_item ) {
			$out['status']='error';
			$out['errorno']='4121';
			$out['error']="Error while try save audio: $err";
			print_json_and_exit( $out );				
		}
	}
	if( array_key_exists( 'audio_url', $in ) ) {
		$new_audio_item=image2video::save_audio_url ( $in['audio_url'], $upload_dir, $upload_url, $err )	;
		if( ! $new_audio_item ) {
			$out['status']='error';
			$out['errorno']='4121';
			$out['error']="Error while try save audio: $err";
			print_json_and_exit( $out );				
		}
	}		
	if( !$new_audio_item ) {
		$out['status']='error';
		$out['errorno']='4122';
		$out['error']="Do not have audio for $item_name ";
		print_json_and_exit( $out );				
	}		
	if( array_key_exists( 'id', $in ) ) { # replace
		$id=$in['id'];
		$item[$id]=array_replace($item[$id], $new_audio_item );		
	} else {
		array_push( $item, $new_audio_item );		
	}
	
	$filename= $item_file;
	$string=json_encode( $item, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}									
	$out['status']='ok';
	$out['rows']=$item;
	print_json_and_exit( $out );
}


if( $in['action'] == 'remove' ){
	if( !file_exists( $item_file ) ) {
		$out['status']='error';
		$out['errorno']='4110';
		$out['error']="$item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
	if( !array_key_exists( 'id', $in ) ) {
		$out['status']='error';
		$out['errorno']='4123';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );	
	}
	$string = file_get_contents( $item_file );
	$item = json_decode($string, true);
	
	$rnd=$item[1]['rnd'];
	$enable=$item[1]['enable'];
	$item_selected=$item[1]['item_selected'];
	unset( $item[ $in['id'] ] );
	$item[1]['rnd']=$rnd;
	$item[1]['enable']=$enable;
	$item[1]['item_selected']=$item_selected;
	# if don't exists the selected audio tracke, set randomize track
	if( !$item[$item_selected]['name'] ) {
		$item[1]['rnd']=1;
	}

	$filename= $item_file;
	$string=json_encode( $item, JSON_PRETTY_PRINT );			
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}					
	$out['rows']=$item;	
	$out['status']='ok';		
	print_json_and_exit( $out );

}




$out['status']='error';
$out['errorno']='4004';
$out['error']="Unknown 'action'";
print_json_and_exit( $out );			



function print_json_and_exit( $out ) {
	echo json_encode( $out, JSON_PRETTY_PRINT );
	exit(0);
}
?>

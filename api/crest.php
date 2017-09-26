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
$item_name='crest';

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
	if( array_key_exists( 'enable', $in )  ) {
		$item[1]["enable"]=$in['enable'];
	}
	if( array_key_exists( 'x', $in ) )  {
		$v=intval( $in['x'] );
		$item[1]["x"]=(  $v>0 && $v<image2video::$output_width ) ? $v : 0 ;
	}
	if( array_key_exists( 'y', $in ) )  {
		$v=intval( $in['y'] );
		$item[1]["y"]=(  $v>0 && $v<image2video::$output_height ) ? $v : 0 ;
	}
	if( array_key_exists( 'w', $in ) )  {
		$v=intval( $in['w'] );
		$item[1]["w"]=(  $v>0 && $v<image2video::$output_width ) ? $v : 200 ;
	}
	if( array_key_exists( 'h', $in ) )  {
		$v=intval( $in['h'] );
		$item[1]["h"]=(  $v>0 && $v<image2video::$output_height ) ? $v : 200 ;
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

# action 'add' new crest
if( $in['action'] == 'add' ){	
	$err=null;
	$upload_dir="$main_upload_dir/$project_id";
	$upload_url="$main_upload_url/$project_id";
	if( file_exists( $item_file ) ) {
		$string = file_get_contents( $item_file );
		$item = json_decode($string, true);		
	} else {
		$item = array();
		$item[1]["enable"]="1";
		$item[1]["x"]=20 ;
		$item[1]["y"]=0 ;
		$item[1]["w"]=200 ;
		$item[1]["h"]=200 ;
	}
	if( array_key_exists( 'disable', $in ) || ( array_key_exists( 'enable', $in ) && !$in['enable'] ) ) {
		$item[1]["enable"]="0";
	}
	if( array_key_exists( 'x', $in ) )  {
		$v=intval( $in['x'] );
		$item[1]["x"]=(  $v>0 && $v<image2video::$output_width ) ? $v : 20 ;
	}
	if( array_key_exists( 'y', $in ) )  {
		$v=intval( $in['y'] );
		$item[1]["y"]=(  $v>0 && $v<image2video::$output_height ) ? $v : 0 ;
	}
	if( array_key_exists( 'w', $in ) )  {
		$v=intval( $in['w'] );
		$item[1]["w"]=(  $v>0 && $v<image2video::$output_width ) ? $v : 200 ;
	}
	if( array_key_exists( 'h', $in ) )  {
		$v=intval( $in['h'] );
		$item[1]["h"]=(  $v>0 && $v<image2video::$output_height ) ? $v : 200 ;
	}
		
	if( array_key_exists( 'image_base64', $in ) ) {
		$new_image_item=image2video::save_image_base64 ( $in['image_base64'], $upload_dir, $upload_url, $err )	;
		if( ! $new_image_item ) {
			$out['status']='error';
			$out['errorno']='4111';
			$out['error']="Error while try save image: $err";
			print_json_and_exit( $out );				
		}
	}
	if( array_key_exists( 'image_copy', $in ) ) {
		$new_image_item=image2video::save_image_copy ( $in['image_copy'], $upload_dir, $upload_url, $err )	;
		if( ! $new_image_item ) {
			$out['status']='error';
			$out['errorno']='4111';
			$out['error']="Error while try save image: $err";
			print_json_and_exit( $out );				
		}
	}
	if( array_key_exists( 'image_url', $in ) ) {
		$new_image_item=image2video::save_image_url ( $in['image_url'], $upload_dir, $upload_url, $err )	;
		if( ! $new_image_item ) {
			$out['status']='error';
			$out['errorno']='4111';
			$out['error']="Error while try save image: $err";
			print_json_and_exit( $out );				
		}
	}		
	if( !$new_image_item ) {
		$out['status']='error';
		$out['errorno']='4112';
		$out['error']="Do not have image for $item_name ";
		print_json_and_exit( $out );				
	}		
	
	$item[1]=array_replace($item[1], $new_image_item );
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





$out['status']='error';
$out['errorno']='4004';
$out['error']="Unknown 'action'";
print_json_and_exit( $out );			



function print_json_and_exit( $out ) {
	echo json_encode( $out, JSON_PRETTY_PRINT );
	exit(0);
}
?>

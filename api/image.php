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
$item_name='images';

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
$effects_file="$main_upload_dir/$project_id/effects.txt";


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
		$out['error']="File $item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
}






# action 'add' new image
if( $in['action'] == 'add' ){	
	$err=null;
	$upload_dir="$main_upload_dir/$project_id";
	$upload_url="$main_upload_url/$project_id";
	if( file_exists( $item_file ) ) {
		$string = file_get_contents( $item_file );
		$item = json_decode($string, true);			
	} else {
		$item = array();
	}
	if( file_exists( $effects_file ) ) {
		$string = file_get_contents("$upload_dir/effects.txt");
		$effects = json_decode($string, true);		
	} else {
		$effects = array();
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
		$out['error']="Do not have image for $item_name ( please use any image_url, image_copy, image_base64 for new image)";
		print_json_and_exit( $out );				
	}		

	if( array_key_exists( 'id', $in ) ) { # replace
		$id=$in['id'];
		$item[$id]=array_replace($item[$id], $new_image_item );		
		#$effect[$id]=array_replace($item[$id], $new_image_item );		
	} else {
		$last_key = end(array_keys($item)); 
		$item[ $last_key+1 ]=$new_image_item;
		#array_push( $item, $new_image_item );		
		$effects[ $last_key+1 ]=image2video::default_effect();		
	}
	
	$filename= $item_file;
	$string=json_encode( $item, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}									
	$filename= $effects_file;
	$string=json_encode( $item, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $effects ) ) {
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
		$out['error']="File $item_name for project with project_id ".$in['project_id']." do not exist ";
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
	$string = file_get_contents("$upload_dir/effects.txt");
	$effects = json_decode($string, true);			

	$i=1;
	$new_item=array();
	$new_effects=array();
	
	# if we remove first image, we need save effects common values
	$saved_values=image2video::save_settings( $effects, image2video::$effects_common_settings ) ;

	## remove item with id 
	# unset( $item[ $in['id'] ] );	
	## remove effect with id 
	# unset( $effects[ $in['id'] ] );	
	foreach( $item as $k=>$val ){ # reorder
		if( $k==$in['id'] ) {
			continue;
		}
		$new_item[$i]=$item[$k] ;
		$new_effects[$i]=$effects[$k] ;		
		$i++;
	}	

	# restore saved values
	$new_effects[1]=array_replace($new_effects[1], $saved_values );


	$filename= $item_file;
	$string=json_encode( $new_item, JSON_PRETTY_PRINT );			
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}
	$filename= $effects_file;
	$string=json_encode( $new_effects, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}		
	$out['rows']=$new_item;	
	$out['status']='ok';		
	print_json_and_exit( $out );

}



# change the items place
if( $in['action'] == 'move' ){
	if( !file_exists( $item_file ) ) {
		$out['status']='error';
		$out['errorno']='4110';
		$out['error']="File $item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
	if( !array_key_exists( 'id', $in ) ) {
		$out['status']='error';
		$out['errorno']='4123';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );	
	}
	if( !array_key_exists( 'new_id', $in ) ) {
		$out['status']='error';
		$out['errorno']='4125';
		$out['error']="Did not receive required input parameter 'new_id'";
		print_json_and_exit( $out );	
	}
	$id=$in['id'];
	$new_id=$in['new_id'];
	if( $id<$new_id) $new_id++;
	
	$string = file_get_contents( $item_file );
	$item = json_decode($string, true);
	$string = file_get_contents("$upload_dir/effects.txt");
	$effects = json_decode($string, true);			

	if( $id==$new_id ) {
		$out['rows']=$item;	
		$out['status']='ok';		
		print_json_and_exit( $out );				
	}
	
	$replaced=0;
	$i=1;
	$new_item=array();
	$new_effects=array();	

	$saved_values=image2video::save_settings( $effects, image2video::$effects_common_settings ) ;
	
	foreach( $item as $k=>$val ){
		if( $k==$new_id ) {
			$new_item[$i]=$item[$id] ;
			$new_effects[$i]=$effects[$id] ;		
			$replaced=1;
			$i++;
		} 
		if( $k!=$id ) {
			$new_item[$i]=$item[$k] ;
			$new_effects[$i]=$effects[$k] ;					
			$i++;
		}
	}		
	if( !$replaced ) {
		$new_item[$i]=$item[$id] ;
		$new_effects[$i]=$effects[$id] ;
	}
	$new_effects[1]=array_replace($new_effects[1], $saved_values );



	
	$filename= $item_file;
	$string=json_encode( $new_item, JSON_PRETTY_PRINT );			
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}
	$filename= $effects_file;
	$string=json_encode( $new_effects, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}		
	$out['rows']=$new_item;	
	$out['status']='ok';		
	print_json_and_exit( $out );

}



# change the items place
if( $in['action'] == 'import' ){
	if( !file_exists( $item_file ) ) {
		$out['status']='error';
		$out['errorno']='4110';
		$out['error']="File $item_name for project with project_id ".$in['project_id']." do not exist ";
		print_json_and_exit( $out );				
	}
	if( !array_key_exists( 'id', $in ) ) {
		$out['status']='error';
		$out['errorno']='4123';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );	
	}
	if( !array_key_exists( 'source', $in ) ) {
		$out['status']='error';
		$out['errorno']='4126';
		$out['error']="Did not receive required input parameter 'source'";
		print_json_and_exit( $out );	
	}
	$source=$in['source'];
	$source_id=isset( $in['source_id'] ) ? $in['source_id'] : 1;	
	
	if( ! image2video::$possible_import_names[$source] ) {	
		$out['status']='error';
		$out['errorno']='4127';
		$out['error']="Unknown 'source' for import";
		print_json_and_exit( $out );	
	}
	
	$id=$in['id'];
	$string = file_get_contents( $item_file );
	$item = json_decode($string, true);
	$string = file_get_contents("$upload_dir/effects.txt");
	$effects = json_decode($string, true);		
	
	$import_file="$main_upload_dir/$project_id/".image2video::$possible_import_names[$source] ;	
	$string = file_get_contents( $import_file );
	$import=json_decode($string, true);
	
	$replaced=0;
	$i=1;
	$new_item=array();
	$new_effects=array();	

	$saved_values=image2video::save_settings( $effects, image2video::$effects_common_settings ) ;
	
	foreach( $item as $k=>$val ){
		if( $k==$id ) {
			$new_item[$i]=$import[$source_id] ;
			$new_effects[$i]=image2video::default_effect();		
			$replaced=1;					
			$i++;
		} 
			$new_item[$i]=$item[$k] ;
			$new_effects[$i]=$effects[$k] ;	
			$i++;					
	}		
	if( !$replaced ) {
		$new_item[$i]=$import[$source_id] ;
		$new_effects[$i]=image2video::default_effect();	
	}	
	
	$new_effects[1]=array_replace($new_effects[1], $saved_values );

	
	$filename= $item_file;
	$string=json_encode( $new_item, JSON_PRETTY_PRINT );			
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}
	$filename= $effects_file;
	$string=json_encode( $new_effects, JSON_PRETTY_PRINT );	
	if( ! file_put_contents( $filename, $string ) ) {
		$out['status']='error';
		$out['errorno']='4102';
		$out['error']="Unable to save file '$filename'";
		print_json_and_exit( $out );			
	}		
	$out['rows']=$new_item;	
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

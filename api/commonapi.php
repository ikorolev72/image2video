<?php
class commonapi {


function check_apikey( $key ){
	return (true);
}

function get_param( $val ) {
	global $_POST;
	global $_GET;
	global $_FILES;
	if( isset( $_POST[ $val ] ) )	return $_POST[ $val ];
	if( isset( $_GET[ $val ] ) )	return $_GET[ $val ];
	if( isset( $_FILES[ $val ] ) ) {
		$tmp_name=$_FILES[ $val ]["tmp_name"];	
		if( $string=file_get_contents( $tmp_name ) ) {
				unlink( $tmp_name );
				return( $string );
		}
	}
	return null;
}



function save_image ( $tmp_file, $upload_dir, $upload_url, &$err ) {
	# check the type of file
	$finfo = finfo_open(FILEINFO_MIME_TYPE); 
	$mime_type=finfo_file($finfo, $tmp_file);	
	finfo_close($finfo);	

	switch(strtolower( $mime_type ))
	{
		case IMAGETYPE_GIF:
			$file_ext="gif";			
			$file_type='image/gif';
			break;
		case IMAGETYPE_PNG:
			$file_ext="png";			
			$file_type='image/png';
			break;
		case IMAGETYPE_JPEG:
			$file_ext="jpg";			
			$file_type='image/jpeg';
			break;
		default:
		// unknown image type
			$err="Inknown image  type of file '$tmp_file'";
			unlink( $tmp_file );
			return(0);
	} 	
	$file_size=filesize ( $tmp_file );
	$file_sha1=sha1_file( $tmp_file );
	$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
	$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
	$file_thumb_name = sprintf("$upload_dir/%s.%s",  "thumb_$file_sha1" , "jpg") ;
	$file_thumb_url  = sprintf("$upload_url/%s.%s",  "thumb_$file_sha1" , "jpg") ;
		
	if ( rename( $tmp_file, $file_name ) ) 
	{
		list($width, $height) = getimagesize($file_name );
		if( image2video::make_thumb( $file_name, $file_thumb_name, $file_type, $width, $height ) ) 
			{
				list($thumb_w, $thumb_h) = getimagesize( $file_thumb_name );
			}
			else
			{
				$file_thumb_name=$file_name;
				$file_thumb_url=$file_url;
				#$errors[]="Error Creating thumbnail for $file_name";			
				
				$ratio = $width/$height;
				$thumb_h = 200;
				$thumb_w = $thumb_h * $ratio;
			}			
	} 
	else 
	{
		$err="Cannot rename file '$tmp_file' to '$file_name' :$!";
		unlink( $tmp_file );
		return(0);
	}
			
	$file_info=array(
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
	return( $file_info );
}

function save_image_base64 ( $content_base64, $upload_dir, $upload_url, &$err ) {	
	$content=base64_decode( $content_base64 );
	$filename="$upload_dir/".sha1( date() );
	if( !file_put_contents( $filename, $content ) ) {
		$err="Cannot save file '$filename' :$!";
		return(0);
	}
	return( save_image ( $filename, $upload_dir, $upload_url, $err ) );
}

function save_image_copy ( $filename, $upload_dir, $upload_url, &$err ) {	
	$filename_copy="$upload_dir/".sha1( date() );
	if( !copy( $filename, $filename_copy )) {
		$err="Cannot copy file '$filename' to '$filename_copy' :$!";	
		return(0);
	};
	return( save_image ( $filename_copy, $upload_dir, $upload_url, $err ) );
}

function save_image_url ( $url, $upload_dir, $upload_url, &$err ) {	
	$ctx = stream_context_create(array( 
		'http' => array( 
			'timeout' => 120 
			) 
		) 
	);
	try {
		$content = file_get_contents($url, 0, $ctx);
		if ($content === false) {
			throw new Exception("Cannot get file by url: '$url'");			
	}
	} catch (Exception $e) {
		$err=$e->getMessage();
		return(0);
		// Handle exception
	}

	$filename="$upload_dir/".sha1( date() );
	if( !file_put_contents( $filename, $content ) ) {
		$err="Cannot save file '$filename' :$!";
		return(0);
	}
	return( save_image ( $filename, $upload_dir, $upload_url, $err ) );
}


# save image in coded in base64
function save_image_base64_old ( $content_base64, $upload_dir, $upload_url, &$err ) {	
	$content=base64_decode( $content_base64 );
	$tmp_file="$upload_dir/".sha1( date() );
	if( !file_put_contents( $tmp_file, $content ) ) {
		$err="Cannot save file '$tmp_file' :$!";
		return(0);
	}
	# check the type of file
	$finfo = finfo_open(FILEINFO_MIME_TYPE); 
	$mime_type=finfo_file($finfo, $tmp_file);	
	finfo_close($finfo);	

	switch(strtolower( $mime_type ))
	{
		case IMAGETYPE_GIF:
			$file_ext="gif";			
			$file_type='image/gif';
			break;
		case IMAGETYPE_PNG:
			$file_ext="png";			
			$file_type='image/png';
			break;
		case IMAGETYPE_JPEG:
			$file_ext="jpg";			
			$file_type='image/jpeg';
			break;
		default:
		// unknown image type
			$err="Inknown image  type of file '$tmp_file'";
			unlink( $tmp_file );
			return(0);
	} 	
	$file_size=filesize ( $tmp_file );
	$file_sha1=sha1_file( $tmp_file );
	$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
	$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
	$file_thumb_name = sprintf("$upload_dir/%s.%s",  "thumb_$file_sha1" , "jpg") ;
	$file_thumb_url  = sprintf("$upload_url/%s.%s",  "thumb_$file_sha1" , "jpg") ;
		
	if ( rename( $tmp_file, $file_name ) ) 
	{
		list($width, $height) = getimagesize($file_name );
		if( image2video::make_thumb( $file_name, $file_thumb_name, $file_type, $width, $height ) ) 
			{
				list($thumb_w, $thumb_h) = getimagesize( $file_thumb_name );
			}
			else
			{
				$file_thumb_name=$file_name;
				$file_thumb_url=$file_url;
				#$errors[]="Error Creating thumbnail for $file_name";			
				
				$ratio = $width/$height;
				$thumb_h = 200;
				$thumb_w = $thumb_h * $ratio;
			}			
	} 
	else 
	{
		$err="Cannot rename file '$tmp_file' to '$file_name' :$!";
		unlink( $tmp_file );
		return(0);
	}
			
	$file_info=array(
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
	return( $file_info );
}



}


?>
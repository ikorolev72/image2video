<?php
class image2video {

public static $output_width=1280;
public static $output_height=720;
public static $font_size=60;
public static $font='/usr/share/fonts/truetype/roboto/hinted/Roboto-Bold.ttf';

public static $do_not_crop_images=0;
private static $do_not_wrap=0; 
private static $fixed_wrap_len=0;

# used for upload video to youtube
public static $youtube_client_id="/var/www/html/screenshot.unixpin.com/school_video/uploads/1502285316715b1958831c56b0fe1a714708.json";
public static $youtube_crentials="/var/www/html/screenshot.unixpin.com/school_video/uploads/15026718274b5c729e0ae2c8933bbd2b0fe1a71.json";
public static $google_map_api_key="AIzaSyCgO4NtJv7hqYId6_yohnSIFMbvKy8nLg4";

# set $queue4job_use=true; if you goin to use queue4job project for tasks queue processing
#public static $queue4job_use=false;
public static $queue4job_use=true;
public static $queue4job_path="/var/www/html/screenshot.unixpin.com/image2video/queue";


function reWrapText(  $text, $output_width, $font_size )
{
		if( ! self::$do_not_wrap ) {
			if( self::$fixed_wrap_len ) {
				$text= wordwrap( $text, self::$fixed_wrap_len, PHP_EOL, true )	;		
			} else {
				$text= wordwrap( $text, floor( 1.8*$output_width/$font_size ), PHP_EOL, true  )	;						
			}
			return $text;
		} 
		return $text;
}


function showMenu ( $project_id ) {
	$menu="	// <a href='upload_to_youtube.php?project_id=$project_id'>Upload to youtube</a> 
			// <a href='add_map.php?project_id=$project_id'>Google map image</a> 
			// <a href='add_crest.php?project_id=$project_id'>Crest</a> 
			// <a href='add_logo.php?project_id=$project_id'>Logo</a> 
			// <a href='upload_audio.php?project_id=$project_id'>Audio</a> 
			// <a href='add_new_image.php?project_id=$project_id'>Add new image</a> 
			// <a href='change_image_order.php?project_id=$project_id'>Change image order</a> 
			// <a href='delete_image.php?project_id=$project_id'>Remove the image</a> 
			// <a href='import_image.php?project_id=$project_id'>Import image</a> 
			// <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> 
			//<hr><br>";
	return $menu;	
}


function reArrayFiles($file)
{
    $file_ary = array();
    $file_count = count($file['name']);
    $file_key = array_keys($file);
    
    for($i=0;$i<$file_count;$i++)
    {
        foreach($file_key as $val)
        {
            $file_ary[$i][$val] = $file[$val][$i];
        }
    }
    return $file_ary;
}




function make_thumb($image, $target_file, $image_type, $width, $height ) {
  // $image is the uploaded image
  //list($width, $height) = getimagesize($image);

  //setup the new size of the image
  $ratio = $width/$height;
  $new_height = 100;
  $new_width = $new_height * $ratio;

  //move the file in the new location
  //move_uploaded_file($image, $target_file);
  
  // resample the image        
 
  $new_image = imagecreatetruecolor($new_width, $new_height);

		switch(strtolower($image_type))
		{
			case 'image/png':
				$old_image = imagecreatefrompng($image);								
				break;
			case 'image/gif':
				$old_image = imagecreatefromgif($image);					
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				if( !$old_image = imagecreatefromjpeg($image) ) return false;
				break;
			default:
				return false;
		}		
	 
  //$old_image = imagecreatefromjpeg($image);
  imagecopyresampled($new_image,$old_image,0,0,0,0,$new_width, $new_height, $width, $height);        

  //output
  imagejpeg($new_image, $target_file, 50 );
  return true;
}

function get_param( $val ) {
	global $_POST;
	global $_GET;
	$ret=isset( $_POST[ $val ] ) ? $_POST[ $val ] : 
				( isset( $_GET[ $val ] ) ? $_GET[ $val ] : null );
	return $ret;
}


function copy_files ( $src, $dst, $allowed ) { 
    $dir = opendir($src); 
    #@mkdir($dst); 
    while( false !== ( $file = readdir($dir)) ) { 
		$ext = pathinfo($file, PATHINFO_EXTENSION );
			if( in_array($ext, $allowed) ) {
				link( "$src/$file" , "$dst/$file" );
                # copy($src . '/' . $file,$dst . '/' . $file); 
            } 
    } 
    closedir($dir); 
	return true;
} 



function save_image ( $tmp_file, $upload_dir, $upload_url, &$err ) {
	# check the type of file
	$finfo = finfo_open(FILEINFO_MIME_TYPE); 
	$file_type=finfo_file($finfo, $tmp_file);	
	finfo_close($finfo);	
	switch( strtolower( $file_type ) )
	{
		case 'image/gif':
			$file_ext="gif";			
			break;
		case 'image/png':
			$file_ext="png";			
			break;
		case 'image/jpeg':
			$file_ext="jpg";			
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
	echo  $content_base64;
	$content=base64_decode( $content_base64 );
	$filename="$upload_dir/".sha1( date("F j, Y, g:i a") );
	if( !file_put_contents( $filename, $content ) ) {
		$err="Cannot save file '$filename' :$!";
		return(0);
	}
	return( self::save_image ( $filename, $upload_dir, $upload_url, $err ) );
}

function save_image_copy ( $filename, $upload_dir, $upload_url, &$err ) {	
	$filename_copy="$upload_dir/".sha1( date() );
	if( !file_exists( $filename ))  {
		$err="File $filename do not exists. Cannot copy file '$filename' to '$filename_copy' ";	
		return(0);
	}
	if( !copy( $filename, $filename_copy )) {
		$err="Cannot copy file '$filename' to '$filename_copy' ";	
		return(0);
	}
	return( self::save_image ( $filename_copy, $upload_dir, $upload_url, $err ) );
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
	return( self::save_image ( $filename, $upload_dir, $upload_url, $err ) );
}


function save_audio ( $tmp_file, $upload_dir, $upload_url, &$err ) {
	# check the type of file
	$finfo = finfo_open(FILEINFO_MIME_TYPE); 
	$file_type=finfo_file($finfo, $tmp_file);	
	finfo_close($finfo);	
	switch( strtolower( $file_type ) )
	{
		case 'audio/x-aac':
			$file_ext="aac";			
			break;
		case 'audio/x-wav':
			$file_ext="wav";			
			break;
		case 'audio/x-aiff':
			$file_ext="aif";			
			break;
		case 'audio/mpeg':
		case 'audio/mp3':
			$file_ext="mp3";			
			break;
		default:
		// unknown type
			$err="Inknown audio type of file '$tmp_file'";
			unlink( $tmp_file );
			return(0);
	} 	

	$file_size=filesize ( $tmp_file );
	$file_sha1=sha1( $tmp_file );
	$file_name = sprintf("$upload_dir/%s.%s",  $file_sha1 , $file_ext) ;
	$file_url  = sprintf("$upload_url/%s.%s",  $file_sha1 , $file_ext) ;
	
		
	if ( rename( $tmp_file, $file_name ) ) 
	{
		$file_info=array(
			'url'=>		$file_url,		
			'name'=>	$file_name,
			'ext'=>		$file_ext,
			'size'=>	$file_size,
			'type'=>	$file_type
		);
	} 
	else 
	{
		$err="Cannot rename file '$tmp_file' to '$file_name' :$!";
		unlink( $tmp_file );
		return(0);
	}

	return( $file_info );
}

function save_audio_base64 ( $content_base64, $upload_dir, $upload_url, &$err ) {	
	echo  $content_base64;
	$content=base64_decode( $content_base64 );
	$filename="$upload_dir/".sha1( date("F j, Y, g:i a") );
	if( !file_put_contents( $filename, $content ) ) {
		$err="Cannot save file '$filename' :$!";
		return(0);
	}
	return( self::save_audio ( $filename, $upload_dir, $upload_url, $err ) );
}

function save_audio_copy ( $filename, $upload_dir, $upload_url, &$err ) {	
	$filename_copy="$upload_dir/".sha1( date() );
	if( !file_exists( $filename ))  {
		$err="File $filename do not exists. Cannot copy file '$filename' to '$filename_copy' ";	
		return(0);
	}
	if( !copy( $filename, $filename_copy )) {
		$err="Cannot copy file '$filename' to '$filename_copy' ";	
		return(0);
	}
	return( self::save_audio ( $filename_copy, $upload_dir, $upload_url, $err ) );
}

function save_audio_url ( $url, $upload_dir, $upload_url, &$err ) {	
	$ctx = stream_context_create(array( 
		'http' => array( 
			'timeout' => 600 
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
	return( self::save_audio ( $filename, $upload_dir, $upload_url, $err ) );
}



}

?>
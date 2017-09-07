<?php
class image2video {

public static $output_width=1280;
public static $output_height=720;
public static $font_size=60;
public static $font='/usr/share/fonts/truetype/roboto/hinted/Roboto-Bold.ttf';


# used for upload video to youtube
public static $youtube_client_id="/var/www/html/screenshot.unixpin.com/school_video/uploads/1502285316715b1958831c56b0fe1a714708.json";
public static $youtube_crentials="/var/www/html/screenshot.unixpin.com/school_video/uploads/15026718274b5c729e0ae2c8933bbd2b0fe1a71.json";
public static $google_map_api_key="AIzaSyCgO4NtJv7hqYId6_yohnSIFMbvKy8nLg4";


public static $do_not_crop_images=0;
private static $do_not_wrap=0; 
private static $fixed_wrap_len=0;



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
				$old_image = imagecreatefromjpeg($image);			
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
	if( $_POST[ $val ]) return ($_POST[ $val ]);
	if( $_GET[ $val ]) 	return ($_GET[ $val ]);
	return null;
}




}

?>
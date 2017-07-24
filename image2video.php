<?php
class image2video {

public static $output_width=800;
public static $output_height=600;



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






}

?>
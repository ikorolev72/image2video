<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit video effects</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
	<script src="jscolor.js"></script>	
</head>
<body>
<a href="index.php"> [ Home ] </a>
<hr>

<?php

include_once("image2video.php");

if( !$_POST['project_id'] && !$_GET['project_id']  ) {
	echo "<h3> Error !Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}

$project_id=$_POST['project_id'];
if( !$project_id ) $project_id=$_GET['project_id'];
$basedir=dirname(__FILE__);
$upload_dir="$basedir/uploads/$project_id";
$upload_url="./uploads/$project_id";
$bin_dir="$basedir/bin";

if( !file_exists( "$upload_dir/project.txt") ) {		
	echo "<h3> Error ! Cannot found project name </h3>";
	echo "Try again <a href=index.php> here </a>
		</body>
		</html>
	";	
	exit(0);
}


$output_width=image2video::$output_width;
$output_height=image2video::$output_height;

$errors= array();
$messages= array();
$effects_info= array();

$string = file_get_contents("$upload_dir/audio.txt");
$audio = json_decode($string, true);

$string = file_get_contents("$upload_dir/images.txt");
$images = json_decode($string, true);

$string = file_get_contents("$upload_dir/effects.txt");
$effects = json_decode($string, true);

$string = file_get_contents("$upload_dir/project.txt");
$project = json_decode($string, true);

$string = file_get_contents("$upload_dir/logo.txt");
$logo = json_decode($string, true);

$fonts_array = file('./fonts.txt');
if( ! $fonts_array ) {
	$fonts_array=array();
	$fonts_array[]='/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
}

	if( $_POST['make_video'] ) 
	{
		$command="/bin/bash $upload_dir/make_slideshow.sh >> /$upload_dir/make_slideshow.log 2>&1 &";
		exec ( $command );
		sleep(3);
		header("Location: $upload_url/make_slideshow_report.html"); 
		exit;		
	}

if(!empty($images))
{
	echo '<h3> Project: '.$project['project_name'].'</h3>';	
	echo "// <a href='add_logo.php?project_id=$project_id'>Logo</a> // <a href='upload_audio.php?project_id=$project_id'>Audio</a> // <a href='add_new_image.php?project_id=$project_id'>Add new image</a> // <a href='change_image_order.php?project_id=$project_id'>Change image order</a> // <a href='delete_image.php?project_id=$project_id'>Remove the image</a> // <a href='edit_effect.php?project_id=$project_id'>Edit effect</a> //<hr><br>";	


	if( file_exists( "$upload_dir/outfile.mp4") ) {		
		echo "<hr><a href='$upload_url/outfile.mp4'> This project have early generated video file </a> <hr> ";
	}	
	
	echo '<form action="edit_effect.php#bottom" method="post" multipart="" enctype="multipart/form-data">
			<table>
			<tr><td>
			</td></tr>';
$first_record=1;
$record_count=0;
$last_record=0;
foreach($images as $k=>$val):
	$last_record=$k;
    $keys = array_keys($val);
	$form_fied_alert=array();
	$record_count++;
	if( $_POST['save'] ) {	
		# check and fix values

		$fade_in=$_POST['fade_in'][$k];
		$audio_fade_in=$_POST['audio_fade_in'][$k];
		$fade_out=$_POST['fade_out'][$k];
		$audio_fade_out=$_POST['audio_fade_out'][$k];
		
		$duration= $_POST['duration'][$k];
		if (empty($_POST['duration'][$k]) || !is_numeric( $duration) ) {
			$duration= 10;
		} else {
			$duration= $_POST['duration'][$k];			
		}
		
		$animation= $_POST['animation'][$k];
		#if ( $animation !='panorama' ) {
		#	$animation = 'none';
		#}
		
		$text= $_POST['text'][$k];
		
		$text_x= $_POST['text_x'][$k];		
		if( ! is_numeric( $text_x ) || $text_x <0 || $text_x > $output_width   ) {
			$text_x=0	;		
		}
		$text_y= $_POST['text_y'][$k];
		if( ! is_numeric( $text_y ) || $text_y <0 || $text_y > $output_height   ) {
			$text_y=0	;		
		}				

		$font= $_POST['font'][$k];
		
		$font_size= $_POST['font_size'][$k];
		if( ! is_numeric( $font_size ) || $font_size <12 || $font_size > 150   ) {
			$font_size=36	;		
		}	
		
		$text_color= $_POST['text_color'][$k];
		if( !preg_match('/^[a-f0-9]{6}$/i', $text_color) ) { 
				$form_fied_alert['text_color'][$k]=' bgcolor=#AFAFAF ';
				$errors[]="Field '$text_color[$k]' must be in web color format ( like '012ABC' )";				
		}		
		
		$text_boxborder_color= $_POST['text_boxborder_color'][$k];
		if( !preg_match('/^[a-f0-9]{6}$/i', $text_boxborder_color) ) { 
				$form_fied_alert['text_boxborder_color'][$k]=' bgcolor=#AFAFAF ';
				$errors[]="Field '$text_boxborder_color[$k]' must be in web color format ( like '012ABC' )";				
		}		
		
		$text_boxopacity= $_POST['text_boxopacity'][$k];
		if( ! is_numeric( $text_boxopacity ) || $text_boxopacity <0 || $text_boxopacity > 100  ) {
			$font_size=50	;		
		}
		$transition= $_POST['transition'][$k];	
		$transition_expensions= array( 'fade','crossfade','overlay','none','concat' );
		if( in_array($transition,$transition_expensions)=== false) {
			$transition='none';
		}		
		
	} 
	else {
		if( $effects[$k]['duration'] ) {
			$fade_in= $effects[$k]['fade_in'];
			$audio_fade_in= $effects[$k]['audio_fade_in'];
			$fade_out= $effects[$k]['fade_out'];
			$audio_fade_out= $effects[$k]['audio_fade_out'];
			$duration= $effects[$k]['duration'];
			$animation= $effects[$k]['animation'] ;
			$text= $effects[$k]['text'];
			$text_x= $effects[$k]['text_x'];
			$text_y= $effects[$k]['text_y'];
			$font= $effects[$k]['font'];
			$font_size= $effects[$k]['font_size'];
			$text_color= $effects[$k]['text_color'];
			$text_boxborder_color= $effects[$k]['text_boxborder_color'];
			$text_boxopacity= $effects[$k]['text_boxopacity'];
			$transition= $effects[$k]['transition'] ;
		}
		else { 
			# set default values
			$fade_in=  1 ; # only for first image
			$audio_fade_in=  1 ; # only for first image
			$fade_out=  1 ; # only for last image
			$audio_fade_out=  1 ; # only for last image
			$duration=  20 ;
			$animation=  'none' ;
			$text= '';
			$text_x=  0;
			$text_y=  0;
			$font= $fonts_array[0];
			$font_size= 36 ;
			$text_color= 'FFFFFF';
			$text_boxborder_color= 'CCCCCC';
			$text_boxopacity= 50;
			$transition= 'none' ;			
		}
	}
		$effects_info[$k]=array(
			'fade_in'=>	$fade_in ,
			'audio_fade_in'=>	$audio_fade_in ,
			'fade_out'=>	$fade_out ,
			'audio_fade_out'=>	$audio_fade_out ,
			'duration'=>	$duration ,
			'animation'=>	$animation,
			'text'=>	$text,
			'text_x'=>	$text_x,
			'text_y'=>	$text_y,
			'font'=>	$font,
			'font_size'=>	$font_size,
			'text_color'=>	$text_color,
			'text_boxborder_color'=>	$text_boxborder_color,
			'text_boxopacity'=>	$text_boxopacity,
			'transition'=>	$transition,
		);	
	if( ! $first_record ) {
		echo "</td>
		</tr>
		<tr valign=top bgcolor='#B0C4DE'>
		<td>Transition</td>
		<td>
		<select name='transition[$k]'>
			<option value='$transition' selected> $transition </option>
			<option value='none'> none </option>
			<option value='fade'> fade </option>
			<option value='crossfade'> crossfade </option>
			<option value='overlay'> overlay </option>
		</select>
		</td>
		</tr>
		";	
	} else {
		if( $fade_in ) {
			$fade_in_checked='checked' ;			
		}
		if( $audio_fade_in ) {
			$audio_fade_in_checked='checked' ;			
		}
		echo "</td>
		</tr>
		<tr valign=top bgcolor='#DCF3DA'>
		<td>Fade in</td>
		<td>
		<input type='checkbox' name='fade_in[$k]' $fade_in_checked> 
		</td>
		</tr>
		<tr valign=top bgcolor='#DCF3DA'>
		<td>Audio fade in</td>
		<td>
		<input type='checkbox' name='audio_fade_in[$k]' $audio_fade_in_checked> 
		</td>
		</tr>
		";	
		$first_record=0;		
	}
    echo '<tr valign=top>
		<td><a href="'.$val['url'].'" target=_blank> <img src="'.$val['thumb_url'].'" width='.$val['thumb_w'].' height='.$val['thumb_h'].'</a></td>
		<td>
		';
		
    echo "<table>
		<tr><td>Duration:</td><td><input type='number' name='duration[$k]' value='$duration' min='4' max='300' size=4> </td></tr>
		<tr><td>Animation:</td><td>
			<select name='animation[$k]'>
				<option value='$animation' selected> $animation </option>
				<option value='none'> none </option>
				<option value='panorame'> panorame </option>
				<option value='rotate'> rotate </option>
				<option value='rotate_ccw'> rotate_ccw </option>
				<option value='zoompan'> zoompan </option>
				
			</select>	
		</td></tr>
		<tr><td ".$form_fied_alert['text'][$k]." >Text:</td><td><textarea name='text[$k]' cols=30 rows=3>$text</textarea> </td></tr>
		<tr><td ".$form_fied_alert['font'][$k]." >Font:</td><td>
			<select name='font[$k]'>";
//		<tr><td ".$form_fied_alert['text'][$k]." >Text:</td><td><input type='text' name='text[$k]' value='$text' size=32> </td></tr>
//		<tr><td ".$form_fied_alert['text_x'][$k]." >X:</td><td><input type='number' name='text_x[$k]' value='$text_x' min='0' max='1920' size=4 > </td></tr>
//		<tr><td ".$form_fied_alert['text_y'][$k]." >Y:</td><td><input type='number' name='text_y[$k]' value='$text_y' min='0' max='1080' size=4 > </td></tr>
			echo "<option value='$font' selected> $font </option>\n";
			foreach ($fonts_array as $font_num=>$font_line) {
				$rtrim_font_line=rtrim($font_line);
				echo "<option value='$rtrim_font_line'> $rtrim_font_line </option>\n";
			}				
			echo "
			</select>
		</td></tr>
		<tr><td ".$form_fied_alert['font_size'][$k]." >Font size:</td><td><input type='number' name='font_size[$k]' value='$font_size' min='12' max='150' size=4> </td></tr>
		<tr><td ".$form_fied_alert['text_color'][$k]." >Text color:</td><td><input name='text_color[$k]'  class='jscolor' value='$text_color' size=6> </td></tr>
		<tr><td ".$form_fied_alert['text_boxborder_color'][$k]." >Box color:</td><td><input name='text_boxborder_color[$k]' class='jscolor' value='$text_boxborder_color' size=6></td></tr>
		<tr><td ".$form_fied_alert['text_boxopacity'][$k]." >Box opacity (%):</td><td><input type='number' name='text_boxopacity[$k]' value='$text_boxopacity' min='0' max='100' size=3> </td></tr>
		</table>
		";

endforeach;	

		if( $fade_out ) {
			$fade_out_checked='checked' ;			
		}
		if( $audio_fade_out ) {
			$audio_fade_out_checked='checked' ;			
		}
		echo "</td>
		</tr>
		<tr valign=top bgcolor='#E1E7F3'>
		<td>Fade out</td>
		<td>
		<input type=checkbox name='fade_out[$last_record]' $fade_out_checked> 
		</td>
		</tr>
		<tr valign=top bgcolor='#E1E7F3'>
		<td>Audio fade out</td>
		<td>
		<input type=checkbox name='audio_fade_out[$last_record]' $audio_fade_out_checked> 
		</td>
		</tr>
		";	
		
    echo "<!--
        <tr>
			<td>Audio effects:</td>
			<td>
				<table>
					<tr><td>Enable audio:</td><td> </td></tr>			
					<tr><td>Fade in:</td><td> </td></tr>			
					<tr><td>Fade out:</td><td> </td></tr>			
				</table>
			</td>
		</tr>
		-->
				<tr><td>
				</td></tr>
			<tr><td></td><td><input type='submit'  name='save' id='save' value='Save'> </td></tr>
        <tr><td></td><td></td></tr>
		";
	if( $_POST['save'] && ! $errors ) 
	{		
		echo '<tr><td></td><td><input type="submit" value="Make video" name="make_video" id="make_video"> </td></tr>	';
	}
    echo "
		</table>
		<input type='hidden' name='project_id' value='$project_id'>
		</form>
	";
	#echo "<pre>";
	#echo var_dump($_POST) ;
	#echo "</pre>";
}
else
{
	$errors[]="Cannot read file 'images.txt' with images info";			
}


	$myfile = fopen("$upload_dir/effects.txt", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/effects.txt";
		}
	fwrite($myfile, json_encode ( $effects_info) );
	fclose($myfile);		

	generate_command ( $images, $audio, $effects_info, $logo );

	foreach($messages as $value)
		{
		echo "<font color=green>$value</font><br>";
		}			 			 
	foreach($errors as $value)
		{
		echo "<font color=red>$value</font><br>";
		}

		


function generate_command ( $images, $audio, $effects, $logo ) {

global $upload_dir;
global $upload_url;
global $bin_dir;
global $output_width;
global $output_height;
$audio_file=$audio[1][name];
$audio_enable=$audio[1]['audio_enable'];
$logo_file=$logo[1][name];
$logo_x=$logo[1][logo_x];
$logo_y=$logo[1][logo_y];
$logo_enable=$logo[1][logo_enable];

$shell="#!/bin/bash
#	this script generated by \$0
FFMPEG=ffmpeg
FFPROBE=ffprobe
#FFMPEG=/usr/bin/ffmpeg
#FFPROBE=/usr/bin/ffprobe
#CONVERT=/usr/bin/convert
LOG=$upload_dir/log.txt

OUTFILE=$upload_dir/outfile.mp4
OUTFILE_URL=./outfile.mp4
OUTFILE_WIDTH=$output_width
OUTFILE_HEIGHT=$output_height
TIMEOUT=300 # timeout for ffmpeg processing

HTML_REPORT=$upload_dir/make_slideshow_report.html
# clean the report file
echo >\$HTML_REPORT

";

$shell.='
w2log() {
	DATE=`date +%Y-%m-%d_%H:%M:%S`
		echo "$DATE $@" 1>&2
		echo "$DATE $@" >> $LOG
		echo "$DATE $@" >> $HTML_REPORT
	return 0
}

echo "<html><head><meta http-equiv=refresh content=10 ></head><body><a href=../../index.php> [ Home ] </a><hr><pre>" >  $HTML_REPORT
w2log "Start processing"

';

$first_image=1;
$last_image=0;
foreach($images as $k=>$val):
	    #$keys = array_keys($val);
		
		$image=$val['name'];
		$image_extension=$val['ext'];
		$image_w=$val['width'];
		$image_h=$val['height'];
		$image_type=$val['type'];

		$fade_in= $effects[$k]['fade_in'];
		$audio_fade_in= $effects[$k]['audio_fade_in'];
		$fade_out= $effects[$k]['fade_out'];
		$audio_fade_out= $effects[$k]['audio_fade_out'];
		
		$duration= $effects[$k]['duration'];
		$animation= $effects[$k]['animation'];
		$text= $effects[$k]['text'];
		$text_x= $effects[$k]['text_x'];
		$text_y= $effects[$k]['text_y'];
		$font= $effects[$k]['font'];
		$font_size= $effects[$k]['font_size'];
		$text_color= $effects[$k]['text_color'];
		$text_boxborder= $effects[$k]['text_boxborder'];
		$text_boxborder_color= $effects[$k]['text_boxborder_color'];
		$text_boxopacity= $effects[$k]['text_boxopacity'];
		$transition= $effects[$k]['transition'];
		
		if( ! $duration ) $duration=10;

		if( $first_image ) {
				if( $fade_in ) {
					$show_fade_in="fade=in:d=1 ,";				
				}
				else { 
					$show_fade_in='null ,';
				}				
				if( $audio_fade_in ) {
					$audio_fade_in="afade=in:d=1 ,";				
				}
				else { 
					$audio_fade_in='anull ,';
				}				
				$first_image =0;
		}
		if( $fade_out ) {
			$show_fade_out=' , fade=out:st=$FADE_OUT_START:d=1 ';		
		} else { 	
			$show_fade_out=', null';
		}
		if( $audio_fade_out ) {
			$audio_fade_out=' , afade=out:st=$FADE_OUT_START:d=1 ';				
		} else { 	
			$audio_fade_out=', anull';
		}
		
		# crop image

		
		$ratio_output=$output_width/$output_height;
		$ratio_image=$image_w/$image_h;
		
		# 
		$new_width=$output_width;
		$new_height = $new_width / $ratio_image;			
###

		if ( $ratio_image > $ratio_output ) {			
			$new_height=$output_height;
			$new_width = $new_height * $ratio_image;

			if( $animation == 'none' ) {
				$filters=" scale=w=$new_width*1.05:h=-2, crop=w=$output_width:h=$output_height, setsar=1 ";								
			}
			
			if( $animation == 'rotate' ) {
				$filters=" scale=w=-2:$new_height*1.4, rotate=a='if(lt(t,20), PI*t/360 , PI*20/360)':c=black:ow=$output_width:oh=$output_height, setsar=1 "; 
			}			

			if( $animation == 'rotate_ccw' ) {
				$filters=" scale=w=-2:$new_height*1.4, rotate=a='if(lt(t,20), -PI*t/360 , -PI*20/360)':c=black:ow=$output_width:oh=$output_height, setsar=1 "; 
			}			
			if( $animation == 'zoompan' ) {
				$filters=" scale=w=2*iw:h=2*ih, zoompan=z=pzoom*1.0003:d=1:x=0:0:s=${output_width}x${output_height}, setsar=1 "; 
				#$filters=" scale=w=2*iw:h=2*ih, zoompan=z=pzoom*1.001:d=1:x='if(gte(zoom,1.5),x,x+1/a)':y='if(gte(zoom,1.5),y,y+1)':s=${output_width}x${output_height}, setsar=1 "; 
				#scale=w=2*iw:h=2*ih, zoompan=z=pzoom*1.002:d=1:x='if(gte(zoom,1.5),x,x+1/a)':y='if(gte(zoom,1.5),y,y+1)':s=${output_width}x${output_height}
			}			
			

			if( $animation == 'panorame' ) 
			{
				if ( $ratio_image > 1.2*$ratio_output ) { 
					$filters=" scale=w=-2:$new_height, crop=$output_width:$output_height:y=0:x=$output_width/3+$output_width/3*sin(n/(4*$duration)+4.71), setsar=1 "; 
				}
				else 
				{
					$filters=" scale=w=$new_width*2:h=-2, crop=$output_width:$output_height:y=cos( 'clip(n\,8.33*$duration,16.66*$duration)' /(8.33*$duration)*PI )*$output_height/3+($output_height/3):x=$output_width/2+$output_width/2*sin(n/(4*$duration)+4.71), setsar=1 ";
				}
			}
		}
		else 
		{
			if( $animation == 'none' ) {
				$filters=" scale=w=-2:h=$new_height, crop=w=$output_width:h=$output_height, setsar=1 ";				
			}
			
			if( $animation == 'rotate' ) {
				$filters=" scale=w=-2:$new_height*1.4, rotate=a='if(lt(t,20), PI*t/360 , PI*20/360)':c=black:ow=$output_width:oh=$output_height, setsar=1 "; 
			}			

			if( $animation == 'rotate_ccw' ) {
				$filters=" scale=w=-2:$new_height*1.4, rotate=a='if(lt(t,20), -PI*t/360 , -PI*20/360)':c=black:ow=$output_width:oh=$output_height, setsar=1 "; 
			}			

			if( $animation == 'zoompan' ) {
				$filters=" scale=w=2*iw:h=2*ih, zoompan=z=pzoom*1.0003:d=1:x=0:0:s=${output_width}x${output_height}, setsar=1 "; 
				#scale=w=2*iw:h=2*ih, zoompan=z=pzoom*1.002:d=1:x='if(gte(zoom,1.5),x,x+1/a)':y='if(gte(zoom,1.5),y,y+1)':s=${output_width}x${output_height}
			}			
			
			if( $animation == 'panorame' ) 
			{
				$filters=" scale=w=-2:h=$new_height, crop=$output_width:$output_height:y=cos( 'clip(n\,8.33*$duration,16.66*$duration)' /(8.33*$duration)*PI )*$output_height/3+($output_height/3):x=$output_width/2+$output_width/2*sin(n/(4*$duration)+4.71), setsar=1 ";				
			}
		}


		
		
		
		
		
		# prepare images with text 
		/*
		if( ! ctype_space( $text ) && $text!="" ) 
		{
			$text_boxopacity_fixed=$text_boxopacity/100;
			$text_fixed= preg_replace( '/[^[:cntrl:]]/', '',$text);
			
			
			$shell.="
			w2log 'Make image with text box $upload_dir/${k}_textbox.png'
				
				$bin_dir/make_textbox.pl --text '$text' --output '$upload_dir/${k}_textbox.png' --textcolor=$text_color --boxcolor $text_boxborder_color --fontpath '$font' --fontsize $font_size --transparent $text_boxopacity
			if [ \$? -ne 0 ]; then
				w2log 'Last command processing error'
			fi
			";			
		}
		*/
		
		$loop=" -loop 1 -i $image -ss 0 -t $duration ";
		
		if( strtolower($image_type)=='image/gif') {
			$loop="  -ignore_loop 0 -f gif -i $image -ss 0 -t $duration ";
			# we cannot use any additional animation for gif
		}
		#color=color=red@.5:size=800x50, fade=t=in:st=0:d=5:alpha=1 [a] ;[0:v][a]  overlay=x=10:y=400 
		$text= preg_replace( '/[\(\)\:\$\@]+/', '',$text);
		$text= preg_replace( "/[\'\"]/", '\"',$text);
		
		
		if ( $text_boxopacity > 100 || $text_boxopacity<0 ) {
			$text_boxopacity=50;
		}
		$text_boxopacity_fixed=$text_boxopacity/100;
		$box_w=$output_width-20;
		#$text= wordwrap( $text, $output_weidth/22 )	;
		$box_h=intval ( $font_size * 1.05 *  count( explode( PHP_EOL, $text ) ) ) ;
		$box_x=10;
		$box_y=$output_height-$box_h-10;
		$box_start_transition=2;
		$text_start_transition=3;		
		$text_y=$box_y+intval( $font_size*0.2 ) ;		
		$text_x=$box_x+intval( $font_size*0.2 );	
		
	
		if( $text ) {			
			$box_for_text=" [0v] ;  color=color=0x${text_boxborder_color}@ ${text_boxopacity_fixed}:size=${box_w}x${box_h} , fade=t=in:st=$box_start_transition:d=1:alpha=1 [a] ;[0v][a]  overlay=x=$box_x:y=$box_y "; 
			$text_in_box=", drawtext=fontfile=$font:text='$text': fontcolor=0x$text_color: fontsize=$font_size: alpha='if( lte(t, $text_start_transition+1 ), if( lte( t, $text_start_transition ),0 , t-$text_start_transition  )  ,1 )':x=$text_x:y=$text_y ";
		}
		$shell.="
		w2log 'Create video $upload_dir/$k.mp4'
			timeout \$TIMEOUT \$FFMPEG -loglevel warning -y $loop -filter_complex \" $filters $box_for_text $text_in_box [v] \" -map '[v]' -c:v libx264  -pix_fmt yuv420p -r 25 -threads 4  -strict -2  '$upload_dir/$k.mp4'
			if [ \$? -ne 0 ]; then
				w2log 'Last command processing error'
			fi
		";			

			
endforeach;		
		$shell.="
		w2log 'Start transition videos'
		";

$first_video='';
$tmp1_video="$upload_dir/tmp1.mp4";
$tmp2_video="$upload_dir/tmp2.mp4";

if( count( $images ) > 1 ) {
foreach($images as $k=>$val):
	if( $first_video=='' ) {
		$first_video="$upload_dir/$k.mp4";
		continue;
	} 
		$transition= $effects[$k]['transition'];
		switch(strtolower($transition))
		{
			case 'overlay':
				$do_transition='overlay'; 
				break;
			case 'fade':
				$do_transition='fade'; 
				break;			
			case 'crossfade':
				$do_transition='crossfade'; 
				break;			
			default:
				$do_transition='concat'; 
		}  		
		$shell.="
			w2log 'Start transition $first_video with $upload_dir/$k.mp4'
		if [ -f '$first_video' ]; then
			timeout \$TIMEOUT  $bin_dir/fftransition.pl --v1=$first_video --v2=$upload_dir/$k.mp4 --out=$tmp1_video --effect=$do_transition --duration=2
			#w2log \"$bin_dir/fftransition.pl --v1=$first_video --v2=$upload_dir/$k.mp4 --out=$tmp1_video --effect=$do_transition --duration=2\"
			if [ \$? -eq 0 ]; then
				mv -f $tmp1_video $tmp2_video
			else
				w2log 'Someting wrong while transition $first_video with $upload_dir/$k.mp4'
				mv -f $upload_dir/$k.mp4 $tmp2_video
			fi								
		else
			w2log 'file $first_video do not exist'
				mv -f $upload_dir/$k.mp4 $tmp2_video
		fi
		";
		
		$first_video=$tmp2_video;
		
endforeach;		
} else {
		$shell.="
					mv -f $upload_dir/1.mp4 $tmp2_video
		";
}
		$shell.="
		DURATION=`\$FFPROBE -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '$tmp2_video'`
		FADE_OUT_START=` bc -l <<< \"scale=2; \$DURATION - 1 \"` 
		";		
	if ( $audio_enable ) {
		$shell.="		
		if [ -f '$audio_file' ]; then			
			w2log 'Add audio to $tmp2_video'
				timeout \$TIMEOUT \$FFMPEG   -loglevel warning -y -i  $tmp2_video -t \$DURATION -i $audio_file  -shortest -filter_complex \"[0:v] $show_fade_in null $show_fade_out [v];[1:a] $audio_fade_in aformat=sample_fmts=s16:channel_layouts=stereo,aresample=44100 $audio_fade_out [a]\" -map '[a]' -map '[v]'  -strict -2  \$OUTFILE
				if [ \$? -ne 0 ]; then
					w2log 'Last command processing error. Cannot add audio track to video'
					mv -f $tmp2_video \$OUTFILE
				fi			
		else
			w2log 'Audio file $audio_file do not exist'
			w2log 'Add begining and ending fade'
			timeout \$TIMEOUT \$FFMPEG   -loglevel warning -y -i  $tmp2_video -t \$DURATION -shortest -filter_complex \"[0:v] $show_fade_in null $show_fade_out [v]\" -map '0:a?' -map '[v]'  -strict -2  \$OUTFILE
		fi
		";			
	} else {
		$shell.="			
			w2log 'Add begining and ending fade'
			timeout \$TIMEOUT \$FFMPEG   -loglevel warning -y -i  $tmp2_video -t \$DURATION -shortest -filter_complex \"[0:v] $show_fade_in null $show_fade_out [v]\" -map '0:a?' -map '[v]'  -strict -2  \$OUTFILE
		";			
	}
	if ( $logo_enable ) {
		$shell.="		

		if [ -f '$logo_file'  ]; then			
				w2log 'Add logo to $tmp2_video'
				mv -f \$OUTFILE $tmp2_video
				timeout \$TIMEOUT \$FFMPEG  -loglevel warning -y -i  $tmp2_video -t \$DURATION -loop 1 -i $logo_file  -shortest -filter_complex '[0:v][1:v]overlay=x=$logo_x:y=$logo_y [v]' -map '0:a?' -map '[v]'  -strict -2  \$OUTFILE
				if [ \$? -ne 0 ]; then
					w2log 'Last command processing error. Cannot add logo to video'
					mv -f $tmp2_video \$OUTFILE
				fi		
		else
			w2log 'Logo file $logo_file do not exist'				
		fi
		";			
	}		
		$shell.="		
		w2log \"File  \$OUTFILE is ready. <a href='\$OUTFILE_URL'>\$OUTFILE_URL</a>\"
		w2log \"Processing finished\"
		";

	$myfile = fopen("$upload_dir/make_slideshow.sh", "w") ;
	if( !$myfile ) 
		{
			$errors[]="Unable to open file $upload_dir/make_slideshow.sh";
		}
	fwrite($myfile, $shell);
	fclose($myfile);		
	return true;
}


?>

<a name='bottom'></a>
</body>
</html>
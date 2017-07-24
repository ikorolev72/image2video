#!/usr/bin/perl
# korolev-ia [at] yandex.ru
# version 1.0 2017.03.27
##############################
# this script generate the image with text ( use '\n' for several lines )
# require imagemagick
# list of ImageMagick fonts : convert -list font |grep ttf
#  ffmpeg -y -i zoomout.mp4 -i cap.png -filter_complex 'overlay=100:100' encoded/output.mp4

#use Data::Dumper;
use Getopt::Long;

my $CONVERT='/usr/bin/convert';
my $DEBUG=0; # set in 1 for debug





GetOptions (
        'text=s' => \$text,
        'output=s' => \$output,
        'fontpath=s' => \$fontpath,
        'fontsize=i' => \$fontsize,
        'textcolor=s' => \$textcolor,
        'boxcolor=s' => \$boxcolor,
        'transparent=i' => \$transparent,
        'show|s' => \$show,
        "help|h|?"  => \$help ) or show_help();


		
show_help() if($help);
show_help( "Please check 'text' option" ) unless( $text  );
show_help( "Please check 'output' option") unless( $output );

# defaults :
$fontsize=36 unless( $fontsize );
$fontpath='/usr/share/fonts/truetype/freefont/FreeSans.ttf' unless( $fontpath );
$textcolor='FFFFFF'  unless( $textcolor );
$boxcolor='CCCCCC'  unless( $boxcolor );
$transparent=30  unless( $transparent );



# fix values
#$text=~s/^'//g;
#$text=~s/^[']*(.+)[']*$/$1/g;
$text=~s/'/"/g;
$text=~s/\n/\\n/g;
$text_fixed=$text;

$font_fixed=''; 
if($fontpath) {
	$font_fixed=" -font '$fontpath' "  ; 
} else {
	print "Font file do not exist. Use default font\n";
}

$transparent_fixed=0.3;
if( $transparent=~/^\d+$/ && $transparent < 101) {
	$transparent_fixed=$transparent/100 
} else {
	print "Option transparent must be beetween 0..100. Use default value 30\n";
}

$textcolor_fixed='#FFFFFF'; 
if( $textcolor=~/^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i ) {
	$textcolor_fixed='#'.$textcolor ; 
} else {
	print "Incorrect textcolor option '$textcolor'. Use default value 'FFFFFF'\n";
}

$boxcolor_fixed="rgba( 204%, 204%, 204%, $transparent_fixed ) ";
if( $boxcolor=~/^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i) {
	$boxcolor_fixed="rgba( ".hex( $1 )."%, ".hex( $2 )."%, ".hex( $3 )."%, $transparent_fixed ) ";
} else {
	print "Incorrect boxcolor option '$boxcolor'. Use default value 'CCCCCC'\n";
}


my $cmd="$CONVERT -background '$boxcolor_fixed' -fill '$textcolor_fixed' -gravity center $font_fixed -pointsize '$fontsize' label:'$text_fixed' '$output' ";
if( $DEBUG || $show ) {
	print "$cmd\n";
} else {
	$ret=system( $cmd );
}


exit($ret);

					
sub show_help {
my $msg=shift;
print STDERR "
$msg

This script make the png image with text in tranparent box. Color must be in the the web view (like 'ABC123'), tranparent must be in the percent.
Sample: $0 \\
 --text 'Hello World!' \\
 --textcolor 'ABC999' \\
 --output title.png \\
 --fontpath /home/alberto/font.ttf \\
 --fontsize 48 \\
 --boxcolor 'ABC999' \\
 --transparent 50
";
	exit (1);
}					
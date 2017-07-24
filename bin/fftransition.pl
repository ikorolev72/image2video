#!/usr/bin/perl
# korolev-ia [at] yandex.ru
# version 1.0 2016.09.03
##############################

use Data::Dumper;
use Getopt::Long;

GetOptions (
        'v1=s' => \$v1,
        'v2=s' => \$v2,
        'out|o=s' => \$out,
        'effect|e=s' => \$effect,
        'duration|d=f' => \$duration,
        'show|s' => \$show,
        "help|h|?"  => \$help ) or show_help();

show_help() if($help);
show_help() unless( $v1 && $v2 && $out );


my $FFPROBE='/usr/bin/ffprobe';
my $FFMPEG='/usr/bin/ffmpeg';

$duration=1 unless( $duration );
unless ( -r $v1 ) {
	        print STDERR "Cannot read file $v1\n"; 
			exit 1;
}
unless ( -r $v2 ) {
	        print STDERR "Cannot read file $v2\n"; 
			exit 2;
}

my $cmd;

$cmd="$FFPROBE -v error -show_streams -of default=noprint_wrappers=1 $v1";
my $streams_info_1=`$cmd`;

$streams_info_1=~/\s+duration=(\d*\.\d+)/;
my $d1=$1;
$streams_info_1=~/\s+width=(\d+)/;
my $width=$1 || 1920;

$streams_info_1=~/\s+height=(\d+)/;
my $height=$1 || 1080;

$cmd="$FFPROBE -v error -show_streams -of default=noprint_wrappers=1 $v2";
my $streams_info_2=`$cmd`;
$streams_info_2=~/\s+duration=(\d*\.\d+)/;
my $d2=$1;


unless ( $d1 ) {
	        print STDERR "Cannot get the video duration of file $v1\n"; 
			exit 3;
}
unless ( $d2 ) {
	        print STDERR "Cannot get the video duration of file $v2\n"; 
			exit 4;
}

if( $duration > $d1 ) {
	$duration=$d1;
	print STDERR "Set the effect of duraion to $d1\n"; 
}

if( $duration > $d2 ) {
	$duration=$d2;
	print STDERR "Set the effect of duraion to $d2\n"; 
}



if( $effect=~/^crossfade$/i ){
	my $tmp_duration1=$d1+$d2-$duration;
	my $tmp_duration2=$d1-$duration;

	$cmd="$FFMPEG -loglevel warning -y -i $v1 -i $v2 -filter_complex 'color=black:${width}x${height}:d=$tmp_duration1 [base]; [0:v]setpts=PTS-STARTPTS[v0]; [1:v]format=yuva420p,fade=in:st=0:d=$duration:alpha=1, setpts=PTS-STARTPTS+($tmp_duration2/TB)[v1]; [base][v0]overlay[tmp]; [tmp][v1]overlay,format=yuv420p[fv]' -map [fv]  -threads 4 $out";
}

if( $effect=~/^fade$/i ){
	my $tmp_duration1=$d1-$duration;

	$cmd="$FFMPEG -loglevel warning -y -i $v1 -i $v2 -filter_complex '[0:v] scale=${width}x${height},fade=t=out:st=$tmp_duration1:d=$duration [v0]; [1:v] fade=t=in:st=0:d=$duration [v1]; [v0][v1] concat=n=2:v=1[v]' -map '[v]' -threads 4 $out";
}


if( $effect=~/^overlay$/i ){
	my $tmp_duration1=$d1-$duration;

	$cmd="$FFMPEG -loglevel warning -y -i $v1 -i $v2 -filter_complex '[0:v] trim=$tmp_duration1:$d1, setpts=PTS-STARTPTS [a]; [1:v] trim=duration=$duration, setpts=PTS-STARTPTS[b]; [a][b] overlay=x='\\''if(lte(-w+t*w,0),(1-t)*w,0)'\\'':y=0[c]; [0:v] trim=0:$tmp_duration1, setpts=PTS-STARTPTS [a2]; [1:v] trim=$duration:$d2, setpts=PTS-STARTPTS [b2]; [a2][c][b2] concat=n=3:v=1[v]' -map '[v]' -threads 4 $out";
}

if( $effect=~/^none$/i || $effect=~/^concat$/i ){
	# simple concat
	$cmd="$FFMPEG -loglevel warning -y -i $v1 -i $v2 -filter_complex '[0:v][1:v] concat=n=2:v=1 [v]' -map '[v]' -c:v libx264  -pix_fmt yuv420p -threads 4 $out";
	#$cmd="$FFMPEG -loglevel warning -y -i $v1 -i $v2 -filter_complex '[0:v:0][1:v:0] concat [v]' -map '[v]' -c:v libx264  -pix_fmt yuv420p -threads 4 $out";
}



if( $show ) {
	print "$cmd\n" ;
} else {
	my $result=system( $cmd );
	exit( $result );
}
exit(0);


					
sub show_help {
print STDERR "
Concat two video file with transition effects:
fade (default), crossfade, overlay
Usage: $0 --v1=video1.mp4 --v2=video2.avi --out=video_outfile.mp4 [ --effect={fade|crossfade|overlay|none|concat} ] [ --duration=duration ] [--show] [--help]
where:
	effect - used effect( fade default )
	duration - the time of effect in secounds ( 1 sec default )
	show - show only prepared command, do not execute
Sample:
$0 --v1=1.mp4 --v2=2.avi --out=3.mp4 --effect=overlay --duration=3.25
";
	exit (1);
}					
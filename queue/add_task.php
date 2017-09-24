<?php
$basedir=dirname(__FILE__);
include_once("$basedir/queue4job.php");

$queue=new queue4job( $basedir );
$options = getopt("c:d::t::p::");

$cmd=isset($options['c']) ? $options['c'] : '';
$description=isset($options['d']) ? $options['d'] : 'One more task';
$tasktype=isset($options['t']) ? $options['t'] : 'default' ;
$taskprice=isset($options['p']) ? $options['p'] : intval( $queue->maxTasksSlots * 0.55 ) ;

if( !$cmd ) help("Need command defined with 'c' option");

$row=array();
$row['cmd']=$cmd;
$row['description']=$description;
$row['tasktype']=$tasktype;
$row['taskprice']=$taskprice;

$id=$queue->add_task_row( $row );
if( $id ) {
	echo $id;
	exit(0);
}
exit(1);

function help($msg) {
	fwrite(STDERR, 
	"$msg
	Usage:$0 -c command [-d description] [-t tasktype] [-p taskprice]
	where:
	command - this command will put to queue
	description - description of task
	tasktype - type of task ( eg ffmpeg_processing, audio_transcoding, upload_file )
	taskprice - define how many slots used for this task ( by default we have 100 free slots. For one processing in once time use 55 )
	\n");	
	exit(-1);
}

?>
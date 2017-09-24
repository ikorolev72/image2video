<?php

$basedir=dirname(__FILE__);
include_once("$basedir/queue4job.php");

$queue=new queue4job( $basedir );
$options = getopt("i:s:");

$id=isset($options['i']) ? $options['i'] : '';
$status=isset($options['s']) ? $options['s'] : '';

if( !$status ) help("Need 'status' option");
if( !$id ) help("Need 'id' option");

$basedir=dirname(__FILE__);
$queue=new queue4job( $basedir );

exit( $queue->set_status( $id, $options['s'] ) );


function help($msg) {
	fwrite(STDERR,
	"$msg
	Usage:$0 -s status -i id
	where:
	status - new status ( sheduled, running, failed, finished )
	id - task id
	\n");	
	exit(-1);
}

?>
<?php

include_once("queue4job.php");

$basedir=dirname(__FILE__);
$queue=new queue4job( $basedir );

$db =  $queue->db ;

$results = $db->query(
"
CREATE TABLE 'tasks' (
'id' TEXT,
'tasktype' TEXT,
'taskprice' INTEGER,
'description' TEXT,
'json' TEXT,
'cmd' TEXT,
'status' TEXT,
'outfile' TEXT,
'errfile' TEXT,
'pid' TEXT,
'pdt' TEXT,
'dt' TEXT
);


"
);

if( $results ) {
	exit(0);
} else{
	echo "someting wrong: $!";
	exit(1);
}


?>
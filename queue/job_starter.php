<?php
include_once("queue4job.php");
$basedir=dirname(__FILE__);
$queue=new queue4job( $basedir );

# check the this ( job starter process )
$pidFile=$queue->pidDir."/jobstarter.php.pid";
if( $queue->checkPidProcess($pidFile)  ) {
	# another process is running
	exit(0);
}

#save my pid
file_put_contents( $pidFile, getmypid()); 

$db =  $queue->db ;
# check the failed processes
$sql='select pid,id from tasks where status="running"';
$results = $db->query($sql);
while ($row = $results->fetchArray()) {	
	if( $queue->checkPidProcess( $row['pid'] ) ) {	
		continue;
	}
	$queue->set_status( $row['id'] , 'failed' );
}



# start new process if have free slots
# we have $maxTasksSlots slots
$sql="select sum( taskprice ) as sumprice from tasks where status='running'";
$results = $db->query( $sql );
$row = $results->fetchArray();
if( $row['sumprice'] >= $queue->maxTasksSlots ) {
	unlink($pidFile);
	exit(0);
}
$freeSlots=$queue->maxTasksSlots-$row['sumprice'];

$sql="select id,taskprice from tasks where taskprice<$freeSlots and status='sheduled' order by taskprice desc ;";
$results = $db->query( $sql );
while ( $row = $results->fetchArray() ) {
	if( $freeSlots-$row['taskprice'] < 0  ) {
		continue;
	}
	$freeSlots=-$row['taskprice'];
	startJob( $queue, $row['id'] );
}	

unlink($pidFile);
exit(0);


function startJob($queue, $id) {
	#echo "\n! $id\n";
	$row=$queue->get_record( $id );
	$cmd=$row['cmd'];
	$pidFile=$row['pid'];
	$outFile=$row['outfile'];
	$errFile=$row['errfile'];
	$basedir=$queue->binDir;
	$queue->set_status( $id, 'running' );
	
	$command="/bin/bash -c 'echo $$ > $pidFile; \
					$cmd >$outFile 2>$errFile ; \
					if [ $? -ne 0 ]; then php $basedir/set_status.php -s failed -i $id; \
						else php $basedir/set_status.php -s finished -i $id;\
					fi;\
					rm  $pidFile' & \n";
					#echo $command;
					system( $command );
}




?>
<?php
class queue4job {

private $mainDir;
private $logDir;
private $logDirUrl;

public	$pidDir;
public	$db;
public	$maxTasksSlots;
public	$binDir;
public	$err;
public	$statuses;

	function __construct( $mainDir ) {
		$databaseFile="$mainDir/data/6715b195933bbd2b0fe18831c56b0fe.sqlite";
		$this->mainDir=$mainDir;

		$today = date("Ymd"); 
		$this->binDir="$mainDir";
		$this->pidDir="$mainDir/data";
		$this->logDir="$mainDir/data/$today/log";
		$this->logDirUrl="/queue/data/$today/log";

		$this->maxTasksSlots=100;
		$this->statuses=array( 'sheduled', 'running', 'failed', 'finished', 'paused', 'canceled' );

		@mkdir ( "$mainDir/data", 0777, true );
		@mkdir ( $this->pidDir, 0777, true );
		@mkdir ( $this->logDir, 0777, true );				
		#make database
		$this->db = new SQLite3( $databaseFile );
		$this->db->busyTimeout(5000);
		$this->db->exec('PRAGMA journal_mode=WAL;');		
		#var_dump( $this );
	}


function get_count( $status  ) {
	$db=$this->db;
	if( $status ) {
		$where_status="where status='$status'";		
	}
	$sql="select count(*) as rowscount from tasks $where_status " ;
	$results = $db->query( $sql );
	$row = $results->fetchArray(SQLITE3_ASSOC);
	return ( $row['rowscount'] );			
}	
	

function set_status( $id , $status ) {
	$db=$this->db;
	$dt=date("U");
	$sql="update tasks set status='$status', dt='$dt' where id=$id" ;
	if( $db->exec($sql) ) return( 1 );
	return( 0 );			
}

function get_status( $id  ) {
	$db=$this->db;
	$sql="select status from tasks where id=$id" ;
	$results = $db->query( $sql );
	$row = $results->fetchArray(SQLITE3_ASSOC);
	return ( $row['status'] );			
}

function get_record( $id  ) {
	$db=$this->db;
	$sql="select * from tasks where id=$id" ;
	try {
		$results = $db->query( $sql );
		$row = $results->fetchArray(SQLITE3_ASSOC);
	} catch(PDOException $e) {
		$this->err=$e;
	}
	return ( $row );			
}


function add_task_row( $row ) {
	$db=$this->db;
	$dt =  date("U");
	#$id="$dt".getmypid().random_int(100,999);	 
	#$id=intval( "$dt".getmypid() ) ;
	$id=sprintf( "%s%05d", $dt, getmypid() )  ;
	$outfile=$this->logDir."/$id.out";
	$errfile=$this->logDir."/$id.err";
	$pid=$this->pidDir."/$id.pid";
	
	$row['id']=$id;
	$row['outfile']=$outfile;
	$row['errfile']=$errfile;
	$row['pid']=$pid;

	$description=isset($row['description']) ? $row['description'] : 'one more task' ;
	$tasktype=isset($row['tasktype']) ? $row['tasktype'] : 'default' ;
	$taskprice=isset($row['taskprice']) ? $row['taskprice'] : intval( $this->maxTasksSlots*0.55 ) ;  # by default can start only one task of this type
	$cmd=isset($row['cmd']) ? $row['cmd'] : '/bin/true' ;
	$json=json_encode ( $row, JSON_PRETTY_PRINT );

	$sql="insert into tasks ( id, description, tasktype, taskprice, cmd, status, outfile, errfile, pid, pdt, dt, json ) 
		values
		( ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )" ;
		
	$sth = $db->prepare( $sql );
	$values=array( $id, $description, $tasktype, $taskprice, $cmd, 'sheduled' , $outfile, $errfile, $pid, $dt, $dt, $json ) ;
	$i=1;
	foreach( $values as $v ) {
		$sth ->bindValue( $i++, $v );		
	}
	if( $sth->execute() ) return( $id );
	return( 0 );
}




function checkPidProcess ($pidFileName) {
	if( !file_exists ($pidFileName)) return 0;
    $pid=intval( file_get_contents($pidFileName)) ; 
	return file_exists("/proc/{$pid}");
}
		
function processExists($pid) {
    return file_exists("/proc/{$pid}");
}
	
function catPidFile ($fileName) {
	if( !file_exists ($fileName)) return 0;
    $pid=intval( file_get_contents($fileName)) ; 
	return $pid;
}		
		
}

?>

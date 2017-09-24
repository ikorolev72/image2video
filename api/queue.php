<?php
header('Content-Type: application/json');
$basedir=dirname(__FILE__);
$libFile="$basedir/../queue/queue4job.php";

include_once( "$basedir/../queue/queue4job.php" );
include_once( "commonapi.php" );
$queue=new queue4job( "$basedir/../queue/" );
$db=$queue->db;

$today = date("F j, Y, g:i a");  
$dt =  date("U");
$out=array();

$json_in=commonapi::get_param( 'in' );


if( !$json_in ) {
	$out['status']='error';
	$out['errorno']='4000';
	$out['error']="Did not receive input parameters";
	print_json_and_exit( $out );
}

$in=json_decode( $json_in , true) ;

if( !array_key_exists( 'apikey', $in ) ){
	$out['status']='error';
	$out['errorno']='4001';
	$out['error']="Did not recive 'apikey'";
	print_json_and_exit( $out );	
}

if( !array_key_exists( 'action', $in ) ){
	$out['status']='error';
	$out['errorno']='4002';
	$out['error']="Did not receive required input parameter 'action'";
	print_json_and_exit( $out );	
}

if( !commonapi::check_apikey( $in['apikey'] )) {
	$out['status']='error';
	$out['errorno']='4003';
	$out['error']="Incorrect 'apikey'";
	print_json_and_exit( $out );	
}


# action list
if( $in['action'] == 'list' ){
	# action list for record with id
	if( array_key_exists( 'id', $in ) ) {
		$row=$queue->get_record( $in['id']  );
		if( $row ) {
			$out['status']='ok';
			$out['rows']= array( $row );
			print_json_and_exit( $out );	
		} else {
			$out['status']='error';
			$out['errorno']='4050';
			$out['error']="Cannot get the task with id=".$in['id'];
			print_json_and_exit( $out );				
		}
	}
	# action list for records without id
	else {
		$out['status']='ok';
		$out['rows']=array( );
		$offset=isset( $in['offset']) ? $in['offset'] : 0;
		$limit=isset( $in['limit']) ? $in['limit'] : 50;
		$where_status=isset( $in['status']) ? " where status='".$in['status']."' " : "";
		$sql="select * from tasks $where_status order by id desc LIMIT $limit OFFSET $offset";
		$results = $db->query( $sql );
		/*
		if( !$result ){
			$out['status']='error';
			$out['errorno']='4002';
			$out['error']="Database query error: $e";
			print_json_and_exit( $out );				
		}		
		*/
		while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
			array_push( $out['rows'], $row );
		}
		print_json_and_exit( $out );		
	}
}

if( $in['action'] == 'count' ){
	$status=isset( $in['status']) ? $in['status'] : "";		
	$count=$queue->get_count( $status  );
	$out['status']='ok';
	$out['count']= $count;
	print_json_and_exit( $out );	
}


if( $in['action'] == 'set_status' ){
	# action list for record with id
	if( array_key_exists( 'id', $in ) ) {
		if( array_key_exists( 'status', $in ) ) {
			if( $queue->set_status( $in['id'] , $in['status'] ) ) {
				$out['status']='ok';
				print_json_and_exit( $out );								
			} else {
				$out['status']='error';
				$out['errorno']='4051';
				$out['error']="Cannot set new status for task with id=".$in['id'];
				print_json_and_exit( $out );								
			}
		} else {
				$out['status']='error';
				$out['errorno']='4052';
				$out['error']="Did not receive required input parameter 'status'";
				print_json_and_exit( $out );											
		}
	} else {
		$out['status']='error';
		$out['errorno']='4050';
		$out['error']="Did not receive required input parameter 'id'";
		print_json_and_exit( $out );			
	}
}


if( $in['action'] == 'add_task' ){
	# action list for record with id
	$cmd=isset($in['cmd']) ? $in['cmd'] : '/bin/true';
	$description=isset($in['d']) ? $in['d'] : 'One more task';
	$tasktype=isset($in['t']) ? $in['t'] : 'default' ;
	$taskprice=isset($in['p']) ? $in['p'] : intval( $queue->maxTasksSlots * 0.55 ) ;	
	$row=array();
	$row['cmd']=$cmd;
	$row['description']=$description;
	$row['tasktype']=$tasktype;
	$row['taskprice']=$taskprice;	
	
	if( array_key_exists( 'cmd', $in ) ) {
		$id=$queue->add_task_row( $row );
		if( $id ) {
			$out['status']='ok';
			$out['id']=$id;
			print_json_and_exit( $out );			
		} else {
			$out['status']='error';
			$out['errorno']='4054';
			$out['error']="Cannot aad new task in the queue for command '$cmd'";
			print_json_and_exit( $out );						
		}
	} else {
		$out['status']='error';
		$out['errorno']='4055';
		$out['error']="Did not receive required input parameter 'cmd'";
		print_json_and_exit( $out );			
	}
}



$out['status']='error';
$out['errorno']='4004';
$out['error']="Unknown 'action'";
print_json_and_exit( $out );			





function print_json_and_exit( $out ) {
	echo json_encode( $out, JSON_PRETTY_PRINT );
	exit(0);
}
?>

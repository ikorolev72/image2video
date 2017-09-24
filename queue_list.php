<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs queue list</title>
	<LINK href='main.css' type=text/css rel=stylesheet>
<script>
  function confirm_prompt( text,url ) {
     if (confirm( text )) {
      window.location = url ;
    }
  }

function openURLpopup( PageURL ) {
	window.open( PageURL ,'popUpWindow','height=400,width=600,left=10,top=10,,scrollbars=yes,menubar=no');
}
  
</script>	
</head>
<body>
<h2>Jobs queue list</h2>
<?php
$basedir=dirname(__FILE__);
#$db = open( queue4job::$db );

include_once("image2video.php");
include_once( image2video::$queue4job_path."/queue4job.php");
$queue=new queue4job( image2video::$queue4job_path );
$db=$queue->db;

/*
echo "<pre>";
echo var_dump( $queue );
echo "</pre>";
*/

$statusColors=array( 'failed'=>'#ffe6e6', 'finished'=>'#e6fff2' );
$errors= array();
$messages= array();

$today = date("F j, Y, g:i a");  
$dt =  date("U");


if( !$db ) {
	echo "<h3> Cannot open database!!! </h3>
		</body>
	</html>			
	";
	exit(0);
}


$offset=0;
$limit=50;

if( image2video::get_param( 'offset' ) ) {
	$offset=intval( image2video::get_param( 'offset' ) );
	if( !$offset ) $offset=0;
}
if( image2video::get_param( 'limit' ) ) {
	$limit=intval( image2video::get_param( 'limit' ));
	if( !$limit  ) $limit=50;
}

if( image2video::get_param( 'id' ) ) {
	$id=image2video::get_param( 'id' );
	$row=$queue->get_record( $id  )	;
	echo "<table border=1>\n";
	echo "<tr><td> id</td><td>$id</td></tr>\n";
	echo "<tr><td> description </td><td>". $row['description'] ."</td></tr>\n";
	if (array_key_exists( $row['status'] , $statusColors)) {
		echo "<tr><td> status</td><td bgcolor='".$statusColors[ $row['status'] ]."'>". $row['status'] ."</td></tr>\n";
	} else {
		echo "<tr><td> status</td><td>". $row['status'] ."</td></tr>\n";		
	}	
	#echo "<tr><td> status</td><td>". $row['status'] ."</td></tr>\n";
	echo "<tr><td> added</td><td>". $row['pdt'] ."</td></tr>\n";
	echo "<tr><td> changed</td><td>". $row['dt'] ."</td></tr>\n";
	echo "<tr><td> shedulled command</td><td>". $row['cmd'] ."</td></tr>\n";
	if( file_exists($row['outfile']) && filesize( $row['outfile'] )>0  ) {
		# we need encode to someting, like + salt or someting
		$file=base64_encode( $row['outfile'] ); 
		echo "<tr><td> stdout log </td><td><a href='show_log.php?file=$file&id=$id&log=outfile' target=_new>stdout log </a> </td></tr>\n";		
	} else {
		echo "<tr><td> stdout log </td><td> empty </td></tr>\n";				
	}
	if( file_exists($row['errfile']) && filesize( $row['errfile'] )>0  ) {
		# we need encode to someting, like + salt or someting
		$file=base64_encode( $row['errfile'] ); 
		echo "<tr><td> stderr log </td><td><a href='show_log.php?file=$file&id=$id&log=errfile' target=_new>stderr log </a> </td></tr>\n";		
	} else {
		echo "<tr><td> stderr log </td><td> empty </td></tr>\n";				
	}
	echo "</table>";
	echo '</body></html>';
	exit(0);
}




$sql="select * from tasks order by pdt LIMIT $limit OFFSET $offset";
$results = $db->query($sql);

echo "<table border=1><tr>
<td> id</td>
<td> description </td>
<td> status</td>
<td> added</td>
<td> changed</td>
</tr>
";

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    #var_dump($row);
	$id= $row['id'];
	
	echo "<td><a href='' onclick=\"openURLpopup('?id=$id');\">$id</a></td>";
	echo "<td>". $row['description'] ."</td>\n";
	if (array_key_exists( $row['status'] , $statusColors)) {
		echo "<td bgcolor='".$statusColors[ $row['status'] ]."'>". $row['status'] ."</td>\n";
	} else {
		echo "<td>". $row['status'] ."</td>\n";		
	}
	echo "<td>". $row['pdt'] ."</td>\n";
	echo "<td>". $row['dt'] ."</td>\n";
	echo "</tr>\n";
}
echo '</table>

		</body>
	</html>			
';

?>

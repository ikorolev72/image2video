<?php
class commonapi {


function check_apikey( $key ){
	return (true);
}

function get_param( $val ) {
	global $_POST;
	global $_GET;
	$ret=isset( $_POST[ $val ] ) ? $_POST[ $val ] : 
				( isset( $_GET[ $val ] ) ? $_GET[ $val ] : null );
	return $ret;
}

}


?>
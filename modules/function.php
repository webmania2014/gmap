<?php
	//check admin login
	function checkLogin()
	{
		if ( !isset( $_SESSION['MYCANVAS_SESSION'] ) )
		{
			echo '
			<script>
				document.location.href = "index.php?menu=login";
			</script>
			';
		}
	}

	function getData ( $tableName, $where, $fields ) 
	{
		global $link;

		$arr = array();

		if ( $fields == "*"  )
		{
			$sql		= "select * from ".$tableName." ".$where;
			$query		= db_query( $sql, $link );
			$columns	= mysql_num_fields( $query );
			$row		= mysql_fetch_assoc( $query );

			for($i = 0; $i < $columns; $i++) 
			{
				$field				= mysql_field_name( $query, $i );
				$arr[trim($field)]	= $row[trim($field)];
			}
		}
		else
		{
			$sql	= "select ".$fields." from ".$tableName." ".$where;
			$query	= db_query ( $sql, $link );
			$row	= mysql_fetch_assoc( $query );

			$split	= explode( ",", $fields );
			foreach ( $split as $k => $v )
			{
				$arr[trim($v)] = $row[trim($v)];
			}
		}

		return $arr;
	}
	
	function getDataByMySQLFunc ( $tableName, $where, $fieldName, $mysqlFunc )
	{
		global $link;

		$sql	= "SELECT ".$mysqlFunc."( ".$fieldName." ) as sp FROM ". $tableName ." ".$where;
		$query	= db_query( $sql, $link );
		$sp		= array_shift ( mysql_fetch_assoc ( $query ) );

		return $sp;
	}

	function fill_options_no_space ( $var, $opt= "", $flag_single = false )
	{
		while (list ($key, $val) = each ($var)) {
			if( $flag_single ) $key = $val;
			if ( $opt != '' && trim($key) == trim($opt) ) {
				echo "<option value='".$key."' selected>".$val."</option>\r\n";
			} else {
				echo "<option value='".$key."' >".$val."</option>\r\n";
			}
		}
	}

	function fill_options ( $var, $opt= "", $default_str="", $flag_single = false )
	{
		echo "<option value='' style='color:silver'>" . $default_str . "</option>\r\n";
		fill_options_no_space ( $var, $opt, $flag_single );
	}

	function string_cut ( $str, $length ) 
	{
		$str = strip_tags ( $str );

		if ( mb_strlen ( $str, "utf-8" ) > $length ) 
		{
			$str = iconv( "utf-8", "utf-8", mb_substr( $str, 0, $length, "utf-8") ).'..';
		}
		return $str;
	}

	function get_client_ip()
	{
		$ipaddress = '';
		if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) )
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			$ipaddress = $_SERVER['HTTPX_FORWARDED_FOR'];
		else if ( !empty( $_SERVER['HTTP_X_FORWARDED'] ) )
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if ( !empty( $_SERVER['HTTP_FORWARDED_FOR'] ) )
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if ( !empty( $_SERVER['HTTP_FORWARDED'] ) )
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if ( !empty( $_SERVER['REMOTE_ADDR'] ) )
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = '';

		return $ipaddress;
	}

	function get_ip_service()
	{
		$location = file_get_contents( 'http://api.hostip.info/get_json.php' );
		if ( $location == false )
			return get_client_ip();

		$arr = json_decode( $location );
		return $arr->ip;
	}

	function get_geo_info()
	{
/*
		$location = file_get_contents( 'http://api.hostip.info/get_json.php?position=true' );
		echo $location;
		if ( $location == false )
			return false;

		return json_decode( $location );
*/
		$location = @file_get_contents( 'http://ipinfo.io/' . get_client_ip() . '/json' );
		if ( $location === FALSE )
			return FALSE;

		return json_decode( $location );
	}

	function makeLinks( $str ) 
	{
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		$urls = array();
		$urlsToReplace = array();
		if ( preg_match_all( $reg_exUrl, $str, $urls ) ) 
		{
			$numOfMatches = count($urls[0]);
			$numOfUrlsToReplace = 0;
			for( $i = 0; $i < $numOfMatches; $i++ ) {
				$alreadyAdded = false;
				$numOfUrlsToReplace = count($urlsToReplace);
				for ( $j = 0; $j < $numOfUrlsToReplace; $j++ ) 
				{
					if($urlsToReplace[$j] == $urls[0][$i]) 
						$alreadyAdded = true;
				}
				if(!$alreadyAdded)
					array_push($urlsToReplace, $urls[0][$i]);
			}

			$numOfUrlsToReplace = count($urlsToReplace);

			for($i=0; $i<$numOfUrlsToReplace; $i++) 
				$str = str_replace($urlsToReplace[$i], "<a href=\"".$urlsToReplace[$i]."\" target=\"_blank\">".$urlsToReplace[$i]."</a> ", $str);
	
			return $str;
		} 
		else 
		{
			return $str;
		}
	}
?>
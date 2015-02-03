<?php
	$modulePath = './modules';
	require_once( $modulePath . '/module_index.php' );
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>My Canvas</title>

		<!--jQuery js link-->
		<script type='text/javascript' src='./assets/js/jquery-1.10.2.js'></script>
		<script type='text/javascript' src='./assets/js/jquery.form.js'></script>

		<!--Bootstrap-->
		<link href="./assets/css/bootstrap.css" rel="stylesheet" type="text/css">
		<script type='text/javascript' src='./assets/js/bootstrap.js'></script>
		<link href="./assets/css/style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<div id="sLoading">
			<img src="./assets/images/loading1.gif">
		</div>
		<script>
			function show_loading( bShow )
			{
				jQuery("#sLoading").css( "width", jQuery(document).width() );
				jQuery("#sLoading").css( "height", jQuery(document).height() );
				
				$img = jQuery("#sLoading img");

				$w = $h = 100;
				$img.css( { "width": $w + "px" }, { "height": $h + "px" } );

				jQuery("#sLoading img").css( "left", jQuery(window).width() / 2 - $w / 2 );
				jQuery("#sLoading img").css( "top", jQuery(window).height() / 2 - $h / 2/* + window.scrollY */);

				if ( bShow )
					jQuery("#sLoading").css( "display", "inline" );
				else
					jQuery("#sLoading").css( "display", "none" );
			}
		</script>
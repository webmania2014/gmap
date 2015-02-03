<?php
	require_once( './page_inc/header.php' );
	
	$getMenu = isset( $_GET['menu'] ) ? trim( $_GET['menu'] ) : '';

	$bLogIn = isset( $_SESSION['MYCANVAS_SESSION'] ) ? true : false;

	if ( $getMenu == '' )
	{
		if ( !$bLogIn )
			require_once( './src/login.php' );
		else
			require_once( './src/main.php' );
	}
	else
	{
		if ( !$bLogIn )
		{
			if ( $getMenu != 'login' && $getMenu != 'signup' )
			{
				require_once( './src/login.php' );
			}
			else
			{
				//check already exist file
				$src = './src/' . $getMenu . '.php';
				if ( !file_exists( $src ) )
				{
					echo '404 Error!';
				}
				else
				{
					require_once( $src );
				}
			}
		}
		else
		{
			//check already exist file
			$src = './src/' . $getMenu . '.php';
			if ( !file_exists( $src ) )
			{
				echo '404 Error!';
			}
			else
			{
				require_once( $src );
			}
		}
	}

	require_once( './page_inc/footer.php' );
?>
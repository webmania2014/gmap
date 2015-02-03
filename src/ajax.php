<?php
	$modulePath = '../modules';
	require_once( $modulePath . '/module_index.php' );

	$action = isset( $_POST['action'] ) ? $_POST['action'] : '';
	switch ( $action )
	{
		case 'LOGIN':
			$username = $_POST['username'];
			$password = $_POST['password'];

			//check match username and password.
			$check = db_get_value('SELECT COUNT(no) FROM user WHERE username = \'' . db_sql( $username ) . '\' AND passwd = \'' . db_sql( $password ) . '\'', $link);
			if ( !$check )
			{
				echo 'Invalid user name or password. Please try again.';
				exit;
			}

			$_SESSION['MYCANVAS_SESSION'] = getData('user', ' WHERE username = \'' . db_sql( $username ) . '\'', '*');

			echo 'SUCCESS';
			break;
		case 'LOGOUT':
			if ( isset( $_SESSION['MYCANVAS_SESSION'] ) )
				unset( $_SESSION['MYCANVAS_SESSION'] );

			echo 'SUCCESS';
			break;
		case 'SIGNUP':
			$username = $_POST['username'];
			$password = $_POST['password'];

			//check if already exist same user name
			$bExist = db_get_value('SELECT COUNT(no) FROM user WHERE username = \'' . db_sql( $username ) . '\'', $link);
			if ( $bExist )
			{
				echo 'Already exist same user name. Please input another user name.';
				exit;
			}

			$sql  = 'INSERT INTO user SET';
			$sql .= '  username = \'' . db_sql( $username ) . '\'';
			$sql .= ', passwd = \'' . db_sql( $password ) . '\'';
			$query = db_query( $sql, $link );

			echo 'SUCCESS';
			break;
		case 'ADD_PIN':
			$user_no		= $_POST['user-no'];
			$pin_type		= $_POST['pin-type-select'];
			$lat			= $_POST['lat'];
			$lng			= $_POST['lng'];
			$address		= $_POST['address'];
			$title			= $_POST['title'];
			$memo			= $_POST['memo'];
			$company_type	= $_POST['company-type'];
			$pin_date		= $_POST['pin-date'];

			$arrCustomField = array();
			if ( isset( $_POST['field-name'] ) )
			{
				for ( $i = 0; $i < count( $_POST['field-name'] ); $i ++ )
				{
					if ( trim( $_POST['field-name'][$i] ) == '' ) continue;

					$fieldName = trim( $_POST['field-name'][$i] );
					$fieldContent = isset( $_POST['field-content'][$i] ) ? trim( $_POST['field-content'][$i] ): '';
					$arrCustomField[] = array( 'name' => $fieldName, 'content' => $fieldContent );
				}
			}

			$jsonCustomField = json_encode( $arrCustomField );

			$sql  = 'INSERT INTO pin SET';
			$sql .= '  user_no = \'' . db_sql( $user_no ) . '\'';
			$sql .= ', pin_type = \'' . db_sql( $pin_type ) . '\'';
			$sql .= ', lat = \'' . db_sql( $lat ) . '\'';
			$sql .= ', lng = \'' . db_sql( $lng ) . '\'';
			$sql .= ', address = \'' . db_sql( $address ) . '\'';
			$sql .= ', title = \'' . db_sql( $title ) . '\'';
			$sql .= ', memo = \'' . db_sql( $memo ) . '\'';
			$sql .= ', custom_field = \'' . db_sql( $jsonCustomField ) . '\'';
			$sql .= ', pin_date = \'' . db_sql( $pin_date ) . '\'';
			$query = db_query( $sql, $link );
			$pin_no = mysql_insert_id();

			//upload files
			//create directory
			$arrImgList = array();
			if ( isset( $_FILES['upload-file'] ) )
			{
				$uploadDir = '../upload/' . $user_no . '/' . mysql_insert_id();

				if ( !file_exists( $uploadDir ) )
				{
					if ( !mkdir( $uploadDir, 0777, true ) )				
					{
						echo json_encode(array('code' => 'ERROR', 'no' => 'Create upload directory failed'));
						exit;
					}
				}

				$fdata = $_FILES['upload-file'];
				$files = array();

				if ( is_array( $fdata['name'] ) )
				{
					for ( $i = 0; $i < count( $fdata['name'] ); ++$i )
					{
						$files[] = array(
							'name'		=> $fdata['name'][$i],
							'tmp_name'	=> $fdata['tmp_name'][$i],
						);
					}
				}
				else
				{
					$files[] = $fdata;
				}

				foreach ( $files as $file )
				{
					if ( !move_uploaded_file( $file['tmp_name'], $uploadDir . '/' . $file['name'] ) )
					{
						echo json_encode(array('code' => 'ERROR', 'no' => 'Upload failed!'));
						exit;
					}
					
					$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $arrImgExt ) ) $arrImgList[] = './upload/' . $user_no . '/' . $pin_no . '/' . $file['name'];
				}
			}

			$pin_no = mysql_insert_id();
			$company_no = 0;
			$company_logo = '';
			$company_name = '';

			if ( $company_type == SELECT_COMPANY )
			{
				$company_no = isset( $_POST['set-company'] ) ? $_POST['set-company'] : 0;
				$companyInfo = getData('company', ' WHERE no = \'' . db_sql( $company_no ) . '\'', '*');
				$company_logo = './upload/' . $user_no . '/logo/' . $companyInfo['company_logo'];
				$company_name = $companyInfo['company_name'];
			}
			else if ( $company_type == ADD_COMPANY )
			{
				$company_name = isset( $_POST['company-name'] ) ? trim( $_POST['company-name'] ) : '';

				if ( isset( $_FILES['company-logo-img'] ) )
				{
					$uploadDir = '../upload/' . $user_no . '/logo';

					if ( !file_exists( $uploadDir ) )
					{
						if ( !mkdir( $uploadDir, 0777, true ) )				
						{
							echo json_encode(array('code' => 'ERROR', 'no' => 'Create upload directory failed'));
							exit;
						}
					}

					$fdata = $_FILES['company-logo-img'];
					$ext = strtolower( pathinfo( $fdata['name'], PATHINFO_EXTENSION ) );
					$new_name = uniqid() . '.' . $ext;
					$company_logo = './upload/' . $user_no . '/logo/' . $new_name;

					if ( !move_uploaded_file( $fdata['tmp_name'], $uploadDir . '/' . $new_name ) )
					{
						echo json_encode(array('code' => 'ERROR', 'no' => 'Company Logo upload failed!'));
						exit;
					}
				}

				$sql  = 'INSERT INTO company SET';
				$sql .= '  user_no = \'' . db_sql( $user_no ) . '\'';
				$sql .= ', company_logo = \'' . db_sql( $new_name ) . '\'';
				$sql .= ', company_name = \'' . db_sql( $company_name ) . '\'';
				$sql .= ', reg_date = \'' . db_sql( date('Y-m-d') ) . '\'';
				$query = db_query( $sql, $link );

				$company_no = mysql_insert_id();
			}

			$sql  = 'UPDATE pin SET';
			$sql .= ' company_no = \'' . db_sql( $company_no ) . '\'';
			$sql .= ' WHERE no = \'' . db_sql( $pin_no ) . '\'';
			$query = db_query( $sql, $link );

			$markerInfo = array( 
				'no'			=> $pin_no,
				'pin_type'		=> $pin_type,
				'lat'			=> $lat,
				'lng'			=> $lng,
				'address'		=> $address,
				'title'			=> $title,
				'memo'			=> nl2br( makeLinks( $memo ) ),
				'favorite'		=> 0,
				'company_no'	=> $company_no,
				'company_logo'	=> $company_logo,
				'company_name'	=> $company_name,
				'custom_field'	=> $arrCustomField,
				'pin_date'		=> $pin_date,
				'img_cnt'		=> count( $arrImgList ),
				'img_list'		=> $arrImgList
			);

			echo json_encode( array('code' => 'SUCCESS', 'content' => $markerInfo) );
			break;
		case 'EDIT_PIN':
			$pin_no			= $_POST['pin-no'];
			$pin_type		= $_POST['pin-type-select'];
			$user_no		= $_POST['user-no'];
			$address		= $_POST['address'];
			$title			= $_POST['title'];
			$memo			= $_POST['memo'];
			$company_type	= $_POST['company-type'];
			$pin_date		= $_POST['pin-date'];
			
			//upload files
			//create directory
			if ( isset( $_FILES['upload-file'] ) )
			{
				$uploadDir = '../upload/' . $user_no . '/' . $pin_no;

				if ( !file_exists( $uploadDir ) )
				{
					if ( !mkdir( $uploadDir, 0777, true ) )				
					{
						echo json_encode(array('code' => 'SUCCESS', 'no' => 'Create upload directory failed'));
						exit;
					}
				}

				$fdata = $_FILES['upload-file'];
				$files = array();

				if ( is_array( $fdata['name'] ) )
				{
					for ( $i = 0; $i < count( $fdata['name'] ); ++$i )
					{
						$files[] = array(
							'name'		=> $fdata['name'][$i],
							'tmp_name'	=> $fdata['tmp_name'][$i],
						);
					}
				}
				else
				{
					$files[] = $fdata;
				}

				foreach ( $files as $file )
				{
					if ( !move_uploaded_file( $file['tmp_name'], $uploadDir . '/' . $file['name'] ) )
					{
						echo json_encode(array('code' => 'SUCCESS', 'no' => 'Upload failed!'));
						exit;
					}
				}
			}

			$dir = '../upload/' . $user_no . '/' . $pin_no;			
			$arrImgList = array();
			if ( file_exists( $dir ) )
			{
				$arrFile = scandir( $dir );
				foreach ( $arrFile as $file )
				{
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $arrImgExt ) )
					{
						$arrImgList[] = './upload/' . $user_no . '/' . $pin_no . '/' . $file;
					}
				}
			}

			$company_no = 0;
			$company_logo = '';
			$company_name = '';

			if ( $company_type == SELECT_COMPANY )
			{
				$company_no = isset( $_POST['set-company'] ) ? $_POST['set-company'] : 0;
				$companyInfo = getData('company', ' WHERE no = \'' . db_sql( $company_no ) . '\'', '*');
				$company_logo = './upload/' . $user_no . '/logo/' . $companyInfo['company_logo'];
				$company_name = $companyInfo['company_name'];
			}
			else if ( $company_type == ADD_COMPANY )
			{
				$company_name = isset( $_POST['company-name'] ) ? trim( $_POST['company-name'] ) : '';

				if ( isset( $_FILES['company-logo-img'] ) )
				{
					$uploadDir = '../upload/' . $user_no . '/logo';

					if ( !file_exists( $uploadDir ) )
					{
						if ( !mkdir( $uploadDir, 0777, true ) )				
						{
							echo json_encode(array('code' => 'ERROR', 'no' => 'Create upload directory failed'));
							exit;
						}
					}

					$fdata = $_FILES['company-logo-img'];
					$ext = strtolower( pathinfo( $fdata['name'], PATHINFO_EXTENSION ) );
					$new_name = uniqid() . '.' . $ext;
					$company_logo = './upload/' . $user_no . '/logo/' . $new_name;

					if ( !move_uploaded_file( $fdata['tmp_name'], $uploadDir . '/' . $new_name ) )
					{
						echo json_encode(array('code' => 'ERROR', 'no' => 'Company Logo upload failed!'));
						exit;
					}
				}

				$sql  = 'INSERT INTO company SET';
				$sql .= '  user_no = \'' . db_sql( $user_no ) . '\'';
				$sql .= ', company_logo = \'' . db_sql( $new_name ) . '\'';
				$sql .= ', company_name = \'' . db_sql( $company_name ) . '\'';
				$sql .= ', reg_date = \'' . db_sql( date('Y-m-d') ) . '\'';
				$query = db_query( $sql, $link );

				$company_no = mysql_insert_id();
			}

			$arrCustomField = array();
			if ( isset( $_POST['field-name'] ) )
			{
				for ( $i = 0; $i < count( $_POST['field-name'] ); $i ++ )
				{
					if ( trim( $_POST['field-name'][$i] ) == '' ) continue;

					$fieldName = trim( $_POST['field-name'][$i] );
					$fieldContent = isset( $_POST['field-content'][$i] ) ? trim( $_POST['field-content'][$i] ): '';
					$arrCustomField[] = array( 'name' => $fieldName, 'content' => $fieldContent );
				}
			}

			$jsonCustomField = json_encode( $arrCustomField );
			$sql  = 'UPDATE pin SET';
			$sql .= '  pin_type = \'' . db_sql( $pin_type ) . '\'';
			$sql .= ', address = \'' . db_sql( $address ) . '\'';
			$sql .= ', title = \'' . db_sql( $title ) . '\'';
			$sql .= ', memo = \'' . db_sql( $memo ) . '\'';
			$sql .= ', company_no = \'' . db_sql( $company_no ) . '\'';
			$sql .= ', custom_field = \'' . db_sql( $jsonCustomField ) . '\'';
			$sql .= ', pin_date = \'' . db_sql( $pin_date ) . '\'';
			$sql .= ' WHERE no = \'' . db_sql( $pin_no ) . '\'';
			$query = db_query( $sql, $link );

			$pinInfo = getData('pin', ' WHERE no = \'' . db_sql( $pin_no ) . '\'', '*');
			$markerInfo = array( 
				'no'			=> $pin_no,
				'pin_type'		=> db_get_value('SELECT pin_type FROM pin WHERE no = \'' . db_sql( $pin_no ) . '\'', $link),
				'lat'			=> $pinInfo['lat'],
				'lng'			=> $pinInfo['lng'],
				'address'		=> $address,
				'title'			=> $title,
				'memo'			=> nl2br( makeLinks( $memo ) ),
				'favorite'		=> $pinInfo['favorite'],
				'company_no'	=> $company_no,
				'company_logo'	=> $company_logo,
				'company_name'	=> $company_name,
				'custom_field'	=> $arrCustomField,
				'pin_date'		=> ( $pin_date == '0000-00-00' || $pin_date == '' ) ? '' : $pin_date,
				'img_cnt'		=> count( $arrImgList ),
				'img_list'		=> $arrImgList
			);

			echo json_encode(array('code' => 'SUCCESS', 'content' => $markerInfo));
			break;
		case 'RE_PIN':
			$no = $_POST['no'];
			$lat = $_POST['lat'];
			$lng = $_POST['lng'];

			$sql  = 'UPDATE pin SET ';
			$sql .= '  lat = \'' . db_sql( $lat ) . '\'';
			$sql .= ', lng = \'' . db_sql( $lng ) . '\'';
			$sql .= '  WHERE no = \'' . db_sql( $no ) . '\'';
			$query = db_query( $sql, $link );
			echo 'SUCCESS';
			break;
		case 'REMOVE_PIN':
			$pin_no = $_POST['pin-no'];
			$user_no = $_SESSION['MYCANVAS_SESSION']['no'];

			$dir = '../upload/' . $_SESSION['MYCANVAS_SESSION']['no'] . '/' . $pin_no;
			if ( file_exists( $dir ) )
			{
				$arrFile = scandir( $dir );
				foreach ( $arrFile as $file )
				{
					if ( $file == '.' || $file == '..' ) continue;
					
					$file_path = $dir . '/' . $file;
					if ( file_exists( $file_path ) )
						unlink( $file_path );
				}
			}

			if ( file_exists( $dir ) ) rmdir( $dir );

			$sql  = 'DELETE FROM pin WHERE no = \'' . db_sql( $pin_no ) . '\'';
			$query = db_query( $sql, $link );
			echo 'SUCCESS';
			break;
		case 'GET_DOWNLOAD_FILES':
			$user_no = $_POST['user-no'];
			$pin_no = $_POST['pin-no'];

			$files = array();
			
			$dir = '../upload/' . $user_no . '/' . $pin_no;
			if ( !file_exists( $dir ) )
			{
				echo json_encode( $files );
				exit;
			}

			$arrFile = scandir( '../upload/' . $user_no . '/' . $pin_no );
			foreach ( $arrFile as $file )
			{
				if ( $file == '.' || $file == '..' ) continue;
				$files[] = $file;
			}

			echo json_encode( $files );
			break;
		case 'GET_MULTI_VIEW':
			$user_no			= $_POST['user-no'];
			$visible_markers	= $_POST['visible-markers'];
			$favorite			= $_POST['favorite'];

			$where  = ' WHERE user_no = \'' . db_sql( $user_no ) . '\'';
			if ( $favorite ) $where .= ' AND favorite = \'' . db_sql( $favorite ) . '\'';
			$sub_where  = '';
			if ( trim( $visible_markers ) != '' )
			{
				$arrMarkers = explode(',', $visible_markers);
				$sub_where .= ' AND (';		
				foreach ( $arrMarkers as $marker_no )
				{
					if ( trim( $marker_no ) == '' ) continue;
					$sub_where .= ' no = \'' . db_sql( $marker_no ) . '\' OR';
				}
				$sub_where  = rtrim( $sub_where, ' OR' );
				$sub_where .= ')';
			}
			else
			{
				echo json_encode( array() );
				exit;
			}
			$where .= $sub_where;

			$arrRet = array();
			$sql  = 'SELECT * FROM pin';
			$sql .= $where;
			$query = db_query( $sql, $link );
			while ( $row = mysql_fetch_assoc( $query ) )
			{
				$dir = '../upload/' . $user_no . '/' . $row['no'];

				$img_counter = 0;
				$symbolImg = '';
				if ( file_exists( $dir ) )
				{
					$arrFile = scandir( $dir );
					$bFirst = 1;
					foreach ( $arrFile as $file )
					{
						$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
						if ( in_array( $ext, $arrImgExt ) )
						{
							if ( $bFirst ) $symbolImg = './upload/' . $user_no . '/' . $row['no'] . '/' . $file;
							$img_counter ++;
							$bFirst = 0;
						}
					}
				}

				$arrRet[] = array(
					'no'		=> $row['no'],
					'pin_type'	=> $row['pin_type'],
					'lat'		=> $row['lat'],
					'lng'		=> $row['lng'],
					'address'	=> string_cut( $row['address'], 40 ),
					'title'		=> $row['title'],
					'memo'		=> nl2br( makeLinks( $row['memo'] ) ),
					'favorite'	=> $row['favorite'],
					'img_cnt'	=> $img_counter,
					'img_path'	=> $symbolImg
				);
			}

			echo json_encode( $arrRet );
			break;
		case 'GET_PIN_INFO':
			$user_no	= $_SESSION['MYCANVAS_SESSION']['no'];
			$pin_no		= $_POST['pin_no'];

			$dir = '../upload/' . $user_no . '/' . $pin_no;			
			$arrImage = array();
			if ( file_exists( $dir ) )
			{
				$arrFile = scandir( $dir );
				foreach ( $arrFile as $file )
				{
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $arrImgExt ) )
					{
						$arrImage[] = './upload/' . $user_no . '/' . $pin_no . '/' . $file;
					}
				}
			}

			$pinInfo = getData('pin', ' WHERE no = \'' . db_sql( $pin_no ) . '\'', '*');
			$retArr	 = array(
				'pin_no'	=> $pin_no,
				'pin_type'	=> $pinInfo['pin_type'],
				'lat'		=> $pinInfo['lat'],
				'lng'		=> $pinInfo['lng'],
				'address'	=> $pinInfo['address'],
				'title'		=> $pinInfo['title'],
				'memo'		=> nl2br( makeLinks( $pinInfo['memo'] ) ),
				'favorite'	=> $pinInfo['favorite'],
				'pin_date'	=> ( $pinInfo['pin_date'] == '0000-00-00' || $pinInfo['pin_date'] == '' ) ? '' : $pinInfo['pin_date'],
				'img_cnt'	=> count( $arrImage ),
				'img_list'	=> $arrImage,
			);

			echo json_encode( $retArr );
			break;
		case 'DELETE_FILE':
			$user_no	= $_SESSION['MYCANVAS_SESSION']['no'];
			$pin_no		= $_POST['pin-no'];
			$file_name	= $_POST['file-name'];

			$file = '../upload/' . $user_no . '/' . $pin_no . '/' . $file_name;
			if ( file_exists( $file ) )
				unlink( $file );
			
			$dir = '../upload/' . $user_no . '/' . $pin_no;			
			$arrImage = array();
			if ( file_exists( $dir ) )
			{
				$arrFile = scandir( $dir );
				foreach ( $arrFile as $file )
				{
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $arrImgExt ) )
					{
						$arrImage[] = './upload/' . $user_no . '/' . $pin_no . '/' . $file;
					}
				}
			}
			echo json_encode( $arrImage );
			break;
		case 'DROPDOWN_UPLOAD':
			$user_no	= $_POST['user-no'];
			$pin_no		= $_POST['pin-no'];
			
			if ( isset( $_FILES['upload-file'] ) )
			{
				$uploadDir = '../upload/' . $user_no . '/' . $pin_no;

				if ( !file_exists( $uploadDir ) )
				{
					if ( !mkdir( $uploadDir, 0777, true ) )				
					{
						echo json_encode(array('code' => 'SUCCESS', 'no' => 'Create upload directory failed'));
						exit;
					}
				}

				$fdata = $_FILES['upload-file'];
				$files = array();

				if ( is_array( $fdata['name'] ) )
				{
					for ( $i = 0; $i < count( $fdata['name'] ); ++$i )
					{
						$files[] = array(
							'name'		=> $fdata['name'][$i],
							'tmp_name'	=> $fdata['tmp_name'][$i],
						);
					}
				}
				else
				{
					$files[] = $fdata;
				}

				foreach ( $files as $file )
				{
					if ( !move_uploaded_file( $file['tmp_name'], $uploadDir . '/' . $file['name'] ) )
					{
						echo json_encode(array('code' => 'FAILED', 'no' => 'Upload failed!'));
						exit;
					}
				}
			}

			$dir = '../upload/' . $user_no . '/' . $pin_no;			
			$arrImage = array();
			if ( file_exists( $dir ) )
			{
				$arrFile = scandir( $dir );
				foreach ( $arrFile as $file )
				{
					$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
					if ( in_array( $ext, $arrImgExt ) )
					{
						$arrImage[] = './upload/' . $user_no . '/' . $pin_no . '/' . $file;
					}
				}
			}

			echo json_encode( $arrImage );
			break;
		case 'FAVORITE_PIN':
			$pin_no = $_POST['pin-no'];
			
			$favorite = db_get_value('SELECT favorite FROM pin WHERE no = \'' . db_sql( $pin_no ) . '\'');
			$sql  = 'UPDATE pin SET';
			$sql .= ' favorite = \'' . db_sql( !$favorite ) . '\'';
			$sql .= ' WHERE no = \'' . db_sql( $pin_no ) . '\'';
			$query = db_query( $sql, $link );

			echo 'SUCCESS';
			break;
		default:
			echo 'Undefined action!';
			break;
	}
?>
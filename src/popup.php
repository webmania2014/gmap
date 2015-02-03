<?php
	require_once('popup_header.php');
	$user_no = $_SESSION['MYCANVAS_SESSION']['no'];
	$lat	= $_GET['lat'];
	$lng	= $_GET['lng'];
	$icon	= isset( $_GET['icon'] ) ? $_GET['icon'] : '';
	$bView	= isset( $_GET['edit'] ) ? 1 : 0;

	$no			= isset( $_GET['no'] ) ? $_GET['no'] : '';
	$address	= '';
	$title		= '';
	$memo		= '';
	$pin_date	= date('Y-m-d');

	$companyList = array();
	$sql  = 'SELECT * FROM company WHERE user_no = \'' . db_sql( $user_no ) . '\'';
	$query = db_query( $sql, $link );
	while ( $row = mysql_fetch_assoc( $query ) )
		$companyList[] = array( 'no' => $row['no'], 'logo' => '../upload/' . $user_no . '/logo/' . $row['company_logo'], 'name' => $row['company_name'] );

	$jsonPinInfo = '[]';

	$markerInfo = array();
	if ( $bView )
	{
		$where  = ' WHERE 1';
		$where .= ' AND no = \'' . db_sql( $no ) . '\'';
		$pinInfo = getData('pin', $where, '*');

		$no			= $pinInfo['no'];
		$icon		= $pinInfo['pin_type'];
		$address	= $pinInfo['address'];
		$title		= $pinInfo['title'];
		$memo		= $pinInfo['memo'];
		$pin_date	= $pinInfo['pin_date'];

		$dir = '../upload/' . $user_no . '/' . $no;			
		$arrImgList = array();
		if ( file_exists( $dir ) )
		{
			$arrFile = scandir( $dir );
			foreach ( $arrFile as $file )
			{
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( in_array( $ext, $arrImgExt ) )
				{
					$arrImgList[] = './upload/' . $user_no . '/' . $no . '/' . $file;
				}
			}
		}

		$companyInfo = getData( 'company', ' WHERE no = \'' . db_sql( $pinInfo['company_no'] ) . '\'', '*' );

		$markerInfo['no']			= $pinInfo['no'];
		$markerInfo['pin_type']		= $pinInfo['pin_type'];
		$markerInfo['lat']			= $pinInfo['lat'];
		$markerInfo['lng']			= $pinInfo['lng'];
		$markerInfo['address']		= $address;
		$markerInfo['title']		= $title;
		$markerInfo['memo']			= nl2br( $memo );
		$markerInfo['favorite']		= $pinInfo['favorite'];
		$markerInfo['company_no']	= $pinInfo['company_no'];
		$markerInfo['company_logo'] = ( $companyInfo['company_logo'] != '' ) ? './upload/' . $user_no . '/logo/' . $companyInfo['company_logo'] : '';
		$markerInfo['company_name'] = ( $companyInfo['company_name'] != '' ) ? $companyInfo['company_name'] : 'No Company Information';
		$markerInfo['custom_field'] = ( $pinInfo['custom_field'] != '' ) ? json_decode( $pinInfo['custom_field'], true ) : array();
		$markerInfo['pin_date']		= ( $pin_date == '0000-00-00' || $pin_date == '' ) ? '' : $pin_date;
		$markerInfo['img_cnt']		= count( $arrImgList );
		$markerInfo['img_list']		= $arrImgList;

		$jsonPinInfo = json_encode( $markerInfo );
	}
	else
	{
		$geo = @file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng);
		$address = '';
		if ( $geo )
		{
			$arrGeo = json_decode( $geo, true );
			$address = $arrGeo['results'][0]['formatted_address'];
		}
	}

	$class = ( $no == '' ) ? '' : 'col-xs-6 col-sm-6 col-md-6 col-lg-6';
?>
<script>
	$(document).ready(function(){
		$("#pin-date").datepicker({changeMonth:true, changeYear:true, dateFormat:'yy-mm-dd', showAnim:'slideDown'});
	});
</script>
<form id='pinForm' role='form' name='pinForm' method=post action='../src/ajax.php' enctype='multipart/form-data'>
<input type='hidden' id='action' name='action' value='<?php echo ($bView) ? 'EDIT_PIN' : 'ADD_PIN'; ?>'>
<input type='hidden' id='user-no' name='user-no' value='<?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>'>
<input type='hidden' id='pin-no' name='pin-no' value='<?php echo $no ?>'>
<input type='hidden' id='pin-type' name='pin-type' value='<?php echo $icon ?>'>
<input type='hidden' id='lat' name='lat' value='<?php echo $lat ?>'>
<input type='hidden' id='lng' name='lng' value='<?php echo $lng ?>'>
<div class='popup-container'>
	<div class='<?php echo $class ?>' id='info-div'>
		<section class='lAlign'>
			<div class='col-md-5 col-xs-5 col-sm-5 col-lg-5 popup-label rAlign'>
				<select id="pin-type-select" name="pin-type-select">
				<?php
					for ( $i = 0; $i < 9; $i ++ )
					{
						$select = ( $icon == 'mark-0-' . $i ) ? 'selected = "selected"' : '';
						echo '<option value="mark-0-' . $i . '" data-image="../assets/images/mark-0-' . $i . '.png" ' . $select . '>' . $arrPinName[ $i ] . '</option>';
					}
				?>
				</select>
				<!--img src = '../assets/images/<?php echo $icon ?>.png'-->
			</div>
			<div class='col-md-7 col-xs-7 col-sm-7 col-lg-7'>
				Latitude : <?php echo $lat ?><br>
				Longitude : <?php echo $lng ?>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Address:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<input type='text' id='address' name='address' value='<?php echo $address; ?>'>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Title:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<input type='text' id='title' name='title' value='<?php echo $title; ?>'>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Memo:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<textarea id='memo' name='memo'><?php echo $memo; ?></textarea>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Company type:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<select id='company-type' name='company-type'>
				<?php 
					if ( isset( $markerInfo['company_no'] ) ) 
					{
						if ( $markerInfo['company_no'] == 0 )
							fill_options_no_space( $arrCompanyType );
						else
							fill_options_no_space( $arrCompanyType, 1 );
					}
					else
						fill_options_no_space( $arrCompanyType );
				?>
				</select>
			</div>
			<div class='clear'></div>
		</section>
		<?php
			$style = '';
			if ( isset( $markerInfo['company_no'] ) )
			{
				if ( $markerInfo['company_no'] != 0 ) $style = "style='display:block;'";
			}
		?>
		<section id='set-company-section' <?php echo $style; ?>>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Select Company:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<select id='set-company' name='set-company'>
				<?php
					foreach ( $companyList as $company )
					{
						$select = '';
						if ( isset( $markerInfo['company_no'] ) )
							$select = ( $company['no'] == $markerInfo['company_no'] ) ? 'selected = "selected"' : '';
						echo '<option value="' . $company['no'] . '" data-image="' . $company['logo'] . '" ' . $select . '>' . $company['name'] . '</option>';
					}
				?>
				</select>
			</div>
			<div class='clear'></div>
		</section>
		<section id='create-company-section'>
			<section>
				<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Company Logo:</div>
				<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
					<input type='file' id='company-logo-img' name='company-logo-img'>
				</div>
				<div class='clear'></div>
			</section>
			<section>
				<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Company Name:</div>
				<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
					<input type='text' id='company-name' name='company-name'>
				</div>
				<div class='clear'></div>
			</section>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>
				Add field 
				<button id='add-field' class='btn' type='button' onclick='javascript:addField();'>
					<span class='glyphicon glyphicon-plus'></span>
				</button>
			</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9' id='add-field-wrapper'>
			<?php
				if ( isset( $markerInfo['custom_field'] ) )
				{
					foreach ( $markerInfo['custom_field'] as $customField )
					{
						echo "
						<section class='add-field-section'>
							<div class='col-md-4 col-xs-4 col-sm-4 col-lg-4 rAlign popup-label'>
								<input type='text' placeholder='Field name' name='field-name[]' value=" . $customField['name'] . "></input>
							</div>
							<div class='col-md-7 col-xs-7 col-sm-7 col-lg-7'>
								<input type='text' name='field-content[]' value=" . $customField['content'] . ">
							</div>
							<div class='col-md-1 col-xs-1 col-sm-1 col-lg-1'>
								<button type='button' class='btn del-field-btn'><span class='glyphicon glyphicon-remove'></span></button>
							</div>
							<div class='clear'></div>
						</section>
						";
					}
				}
			?>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>Date:</div>
			<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<input type='text' id='pin-date' name='pin-date' value='<?php echo $pin_date; ?>'>
			</div>
			<div class='clear'></div>
		</section>
		<section>
			<input type='file' id='fileselect' name='fileselect[]' multiple style='display:none;'>
			<div id="filedrag">Drop files here for uploading</div>
			<div class='progress'>
				<div class='progress-bar progress-bar-success' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width:0%;'>
				<span class='sr-only'>90% Completed (Success)</span>
				</div>
			</div>
			<!--div class = 'col-md-3 col-xs-3 col-sm-3 col-lg-3 rAlign popup-label'>File(s):</div>
			<div class = 'col-md-9 col-xs-9 col-sm-9 col-lg-9'>
				<input type = 'file' id = 'upload-file' name = 'upload-file[]' multiple>
			</div>
			<div class = 'clear'></div-->
		</section>
	</div>
	<div class='<?php echo $class ?>' id='upload-file-list' style='<?php echo ( $no == '' ) ? 'display:none' : '' ?>'>
		<div id = 'file-list-wrapper'>
			<table class='table table-hover' width='100%'>
				<col width='20%'></col>
				<col width='60%'></col>
				<col width='20%'></col>
				<thead>
					<tr>
						<th class='rAlign'>No</th>
						<th>File Name</th>
						<th class='cAlign'>**</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$arrFileList = array();
					if ( $no != '' )
					{
						$uploadDir = '../upload/' . $_SESSION['MYCANVAS_SESSION']['no'] . '/' . $no;

						if ( file_exists( $uploadDir ) )
						{
							$arrFile = scandir( $uploadDir );
							foreach ( $arrFile as $file )
							{
								if ( $file == '.' || $file == '..' ) continue;
								$arrFileList[] = $file;
							}
						}
					}

					$i = 1;
					foreach ( $arrFileList as $file )
					{
						echo "
						<tr id='tr-" . $file . "'>
							<td class='rAlign'>" . $i . "</td>
							<td class='lAlign'>" . $file . "</td>
							<td class='cAlign'>
								<button type='button' class='btn remove-upload-file' onclick='javascript:removeFile(\"" . $file . "\")'>
									<span class='glyphicon glyphicon-remove'></span>
								</button>
							</td>
						</tr>
						";

						$i ++;
					}

					if ( !count( $arrFileList ) )
					{
						echo "
						<tr><td colspan='3' class = 'cAlign'>There are no uploaded files.</td></tr>
						";
					}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<div class='clear'></div>
	<section>
		<input type='button' class='btn btn-primary' value='Set' onclick='javascript:setInfo();'>
		<input type='button' class='btn btn-primary' value='Download' onclick='javascript:download();' <?php echo ( !$bView || !count($arrFileList) ) ? 'disabled' : ''; ?>>
		<input type='button' class='btn btn-danger' value='Remove Pin' onclick='javascript:removePin();' <?php echo ( !$bView ) ? 'disabled' : ''; ?>>
	</section>
</div>
</form>
<form id='downloadForm' name='downloadForm' method=post action='../src/ajax.php'>
	<input type='hidden' id='action' name='action' value='GET_DOWNLOAD_FILES'>
	<input type='hidden' id='user-no' name='user-no' value='<?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>'>
	<input type='hidden' id='pin-no' name='pin-no' value='<?php echo $no; ?>'>
</form>
<form id='removeForm' name='removeForm' method=post action='../src/ajax.php'>
	<input type='hidden' id='action' name='action' value='REMOVE_PIN'>
	<input type='hidden' id='pin-no' name='pin-no' value='<?php echo $no ?>'>
</form>
<form id='deleteFileForm' name='deleteFileForm' method=post action='../src/ajax.php'>
	<input type='hidden' id='action' name='action' value='DELETE_FILE'>
	<input type='hidden' id='pin-no' name='pin-no' value='<?php echo $no; ?>'>
	<input type='hidden' id='file-name' name='file-name' value=''>
</form>
<script>
	var user_no = <?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>;
	var markerInfo = <?php echo $jsonPinInfo ?>;

	$(document).ready(function(){
		$('#pinForm').ajaxForm({
			success:function(ret)
			{
				var arrRet = $.parseJSON(ret);
				if ( arrRet.code == 'SUCCESS' )
				{
					markerInfo = arrRet.content;
					parent.show_loading( false );
					if ( $('#pinForm').find('[id*=action]').val() == 'ADD_PIN' )
					{
						UploadFile( user_no, markerInfo, 'ADD_PIN' );
//						parent.makeMarker( arrRet.content );
					}
					else
					{
//						UploadFile( <?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>, arrRet.content, 'EDIT_PIN' );
						parent.changeMarker( arrRet.content );
						parent.JqueryDialog.Close();
					}
				}
				else
				{
					alert( arrRet.no );
					parent.show_loading( false );
				}
			}
		});
		
		$('#downloadForm').ajaxForm({
			success:function(ret)
			{
				var arrFiles = $.parseJSON( ret );

				for ( var i = 0; i < arrFiles.length; i++ )
				{
					var a = $("<a>").attr("href", "../upload/" + <?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?> + '/' + <?php echo $no ?> + '/' + arrFiles[i]).attr("download", arrFiles[i]).appendTo("body");
					a[0].click();
					a.remove();
				}
			}
		});

		$('#removeForm').ajaxForm({
			success:function(ret)
			{
				if ( $.trim(ret) == 'SUCCESS' )
				{
					parent.show_loading( false );
					parent.removePin( markerInfo );
					parent.JqueryDialog.Close();
				}
				else
				{
					alert( ret );
					parent.show_loading( false );
				}
			}
		});

		$('#deleteFileForm').ajaxForm({
			success:function(ret)
			{
				var arrRet = $.parseJSON( ret );
				markerInfo.img_cnt = arrRet.length;
				markerInfo.img_list = arrRet;
				parent.changeMarker( markerInfo );
				document.location.href = document.location.href;
				parent.show_loading(false);
			}
		});

		$("select").msDropdown({roundedBorder:false, enableAutoFilter:false});

		$('#company-type').change(function(){
			var selected = $(this).val();
			if ( selected == '0' )
			{
				$('#set-company-section, #create-company-section').css('display', 'none');
			}
			else if ( selected == '1' )
			{
				$('#set-company-section').css('display', 'block');
				$('#create-company-section').css('display', 'none');
			}
			else if ( selected == '2' )
			{
				$('#set-company-section').css('display', 'none');
				$('#create-company-section').css('display', 'block');
			}

			$("select").msDropdown({roundedBorder:false, enableAutoFilter:false});
		});

		$(document).on('click', '.del-field-btn', function(e){
			var currentElement = e.target;
			$(currentElement).parent().parent().remove();
		});

		//==================for fixing img select=================//
		$('#set-company-section').css('display', 'none');
		$('#create-company-section').css('display', 'none');

		if ( $('#company-type').val() == '0' )
		{
			$('#set-company-section, #create-company-section').css('display', 'none');
		}
		else if ( $('#company-type').val() == '1' )
		{
			$('#set-company-section').css('display', 'block');
			$('#create-company-section').css('display', 'none');
		}
		else if ( $('#company-type').val() == '2' )
		{
			$('#set-company-section').css('display', 'none');
			$('#create-company-section').css('display', 'block');
		}
		///////////////////////////////////////////////////////////
	});

	function download()
	{
		$('#downloadForm').submit();
	}

	function setInfo()
	{
		if ( $('#company-type').val() == 1 )
		{
			if ( $('#set-company').val() == null )
			{
				alert('Please select company or add new company.');
				return;
			}
		}

		if ( $('#company-type').val() == 2 )
		{
			if ( $('#company-logo-img').val() == '' )
			{
				alert('Please select company logo.');
				return;
			}
			
			if ( $.trim( $('#company-name').val() ) == '' )
			{
				alert('Please input company name.');
				return;
			}
		}

		parent.show_loading( true );
		$('#pinForm').submit();
	}

	function removePin()
	{
		parent.show_loading(true);
		$('#removeForm').submit();
	}

	function removeFile( filename )
	{
		if ( confirm('Are you sure to remove this file?') )
		{
			parent.show_loading(true);
			$('#deleteFileForm').find('[id*=file-name]').val( filename );
			$('#deleteFileForm').submit();
		}
	}

	function showValue()
	{
		alert( $('#pin-type-select').val() );
	}

	function addField()
	{
		var html = "";
		html += "<section class='add-field-section'>";
		html += "	<div class='col-md-4 col-xs-4 col-sm-4 col-lg-4 rAlign popup-label'>";
		html += "		<input type='text' placeholder='Field name' name='field-name[]'></input>";
		html += "	</div>";
		html += "	<div class='col-md-7 col-xs-7 col-sm-7 col-lg-7'>";
		html += "		<input type='text' name='field-content[]'>";
		html += "	</div>";
		html += "	<div class='col-md-1 col-xs-1 col-sm-1 col-lg-1'>";
		html += "		<button type='button' class='btn del-field-btn'><span class='glyphicon glyphicon-remove'></span></button>";
		html += "	</div>";
		html += "	<div class='clear'></div>";
		html += "</section>";

		$('#add-field-wrapper').append( html );
	}

	var pin_no = '';
</script>

<?php if ( $no != '' ) echo '<script>pin_no = ' . $no . ';</script>'; ?>

<!--drag and drop library-->
<link rel='stylesheet' href='../assets/js/filedrag/styles.css'>
<script src='../assets/js/filedrag/filedrag.js'></script>
<?php
	require_once('popup_footer.php');
?>
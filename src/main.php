<?php
	define( "DEFAULT_GMAP_ZOOM", "12" );
	$arrGeo = array( 'lat' => 40.784042, 'lng' => -73.967555 );
	$user_no = $_SESSION['MYCANVAS_SESSION']['no'];

	$retGeo = get_geo_info(); 
	if ( $retGeo )
	{
		if ( isset( $retGeo->loc ) )
		{
			$location = $retGeo->loc;
			$arrLatLng = explode( ',', $location );
			$arrGeo['lat'] = $arrLatLng[0];
			$arrGeo['lng'] = $arrLatLng[1];
		}
	}

	$pin_type			= isset( $_POST['pin-type'] ) ? $_POST['pin-type'] : '';
	$search_pin_input	= isset( $_POST['search-pin-input'] ) ? trim( $_POST['search-pin-input'] ) : '';

	//get my markers.
	$arrMarkers = array();
	$where  = ' WHERE 1';
	$where .= ' AND user_no = \'' . db_sql( $_SESSION['MYCANVAS_SESSION']['no'] ) . '\'';
	if ( $pin_type != '' )
		$where .= ' AND pin_type = \'' . db_sql( $pin_type ) . '\'';
	if ( $search_pin_input )
	{
		$where .= ' AND (';
		$sub_where = '';
		$arrKeyword = explode(' ', $search_pin_input);
		foreach ( $arrKeyword as $keyword )
		{
			if ( trim( $keyword ) == '' ) continue;

			$sub_where .= ' address LIKE \'%' . db_sql( $keyword ) . '%\'';
			$sub_where .= ' OR title LIKE \'%' . db_sql( $keyword ) . '%\'';
			$sub_where .= ' OR memo LIKE \'%' . db_sql( $keyword ) . '%\'';
			$sub_where .= ' OR';
		}
		$sub_where = rtrim( $sub_where, ' OR' );
		$where .= $sub_where;
		$where .= ')';
	}

	$sql  = 'SELECT * FROM pin';
	$sql .= $where;
	$query = db_query( $sql, $link );
	while ( $row = mysql_fetch_assoc( $query ) )
	{
		$dir = './upload/' . $user_no . '/' . $row['no'];

		$arrImage = array();
		if ( file_exists( $dir ) )
		{
			$arrFile = scandir( $dir );
			foreach ( $arrFile as $file )
			{
				$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
				if ( in_array( $ext, $arrImgExt ) )
				{
					$arrImage[] = $dir . '/' . $file;
				}
			}
		}

		$companyInfo = getData('company', ' WHERE no = \'' . db_sql( $row['company_no'] ) . '\'', '*');

		$arrMarkers[] = array( 
			'no'			=> $row['no'],
			'pin_type'		=> $row['pin_type'],
			'lat'			=> $row['lat'],
			'lng'			=> $row['lng'],
			'address'		=> $row['address'],
			'title'			=> $row['title'],
			'memo'			=> nl2br( makeLinks( $row['memo'] ) ),
			'favorite'		=> $row['favorite'],
			'company_no'	=> $row['company_no'],
			'company_logo'	=> ( $companyInfo['company_logo'] != '' ) ? './upload/' . $user_no . '/logo/' . $companyInfo['company_logo'] : '',
			'company_name'	=> ( $companyInfo['company_name'] != '' ) ? $companyInfo['company_name'] : '',
			'custom_field'	=> ( $row['custom_field'] != '' ) ? json_decode( $row['custom_field'], true ) : array(),
			'pin_date'		=> ( $row['pin_date'] == '0000-00-00' ) ? '' : $row['pin_date'],
			'img_cnt'		=> count( $arrImage ),
			'img_list'		=> $arrImage
		);
	}

	$jsMarkers = json_encode( $arrMarkers );
	$jsPinName = json_encode( $arrPinName );
?>

<!----------------------------------Include external js files------------------------------->
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true&libraries=places"></script>
<script type="text/javascript" src="./assets/js/gmap/gmaps.js"></script>
<script type="text/javascript" src="./assets/js/gmap/prettify.js"></script>
<script type="text/javascript" src="./assets/js/gmap/markercluster.js"></script>
<script src="./assets/js/jui/ui/jquery.ui.core.js"></script>
<script src="./assets/js/jui/ui/jquery.ui.widget.js"></script>
<script src="./assets/js/jui/ui/jquery.ui.mouse.js"></script>
<script src="./assets/js/jui/ui/jquery.ui.draggable.js"></script>
<script src="./assets/js/jui/ui/jquery.ui.droppable.js"></script>
<link href="./assets/js/jqueryDialog/jquery.dialog.css" rel="stylesheet" type="text/css">
<script type = 'text/javascript' src = './assets/js/jqueryDialog/jquery.dialog.js'></script>
<!--for infoBubble-->
<!--
<script type="text/javascript">
	var script = '<script type="text/javascript" src="http://google-maps-' +
		'utility-library-v3.googlecode.com/svn/trunk/infobubble/src/infobubble';
		if (document.location.search.indexOf('compiled') !== -1) {
			script += '-compiled';
		}
	script += '.js"><' + '/script>';
	document.write(script);
</script>
-->
<script type='text/javascript' src = './assets/js/gmap/infobubble.js'></script>
<script src="./assets/js/bxslider/jquery.bxslider.min.js"></script>
<link href="./assets/js/bxslider/jquery.bxslider.css" rel="stylesheet" />
<!------------------------------------------------------------------------------------------>

<div id='header'>
	<section id='header-title'>
		<div class='col-xs-6 col-md-6 col-sm-6 col-lg-6'>
			<button type='button' class='btn dropdown-toggle' id='dropdownMenu' data-toggle='dropdown'>
				Pin Options
				<span class='caret'></span>
			</button>
			<div id='search-address-box'>
				<input type="text" id='search-address-input' class="form-control" placeholder='Search address'>
				<button type='button' class='btn' id='search-address-btn' onclick='javascript:centerMapByAddress();'><span class="glyphicon glyphicon-search"></span></button>
			</div>
		</div>
		<div class='col-xs-6 col-md-6 col-sm-6 col-lg-6 rAlign'>
			<button type='button' id='logout-btn' title='Logout' onclick='javascript:logout();'></button>
			<button type='button' id='right-menu'></button>
		</div>
		<div class='clear'></div>
	</section>
	<section id='sub-menu'>
		<ul>
			<li role="presentation">
				<a href="#">Add Pins<span class='caret'></span></a>
				<div class='popup' id='add-pins-div'>
					<?php
						for ( $i = 0; $i < 9; $i ++ )
						{
							echo "<section class='cAlign pin-section'>";
							echo "	<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3'>";
							if ( $i == 2 || $i == 4 || $i == 5 || $i == 6 || $i == 8 )
							echo "		<img class='pin-btn' id='mark-0-" . $i . "' src='./assets/images/mark-0-" . $i . ".png' style='width:20px;'>";
							else
							echo "		<img class='pin-btn' id='mark-0-" . $i . "' src='./assets/images/mark-0-" . $i . ".png'>";
							echo "	</div>";
							echo "	<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9 pin-title'>" . $arrPinName[ $i ] . "</div>";
							echo "	<div class='clear'></div>";
							echo "</section>";
						}
					?>
				</div>
			</li>
			<li role="presentation">
				<a href="#">Filter Pins<span class='caret'></span></a>
				<div class='popup' id='filter-pins-div'>
					<?php
						for ( $i = 0; $i < 9; $i ++ )
						{
							echo "<section class='cAlign pin-section' onclick='javascript:filterPins(\"mark-0-" . $i . "\")' style='cursor:pointer;'>";
							echo "	<div class='col-md-3 col-xs-3 col-sm-3 col-lg-3'>";
							if ( $i == 2 || $i == 4 || $i == 5 || $i == 6 || $i == 8 )
							echo "		<img class='pin-btn' id='mark-0-" . $i . "' src='./assets/images/mark-0-" . $i . ".png' style='width:20px;'>";
							else
							echo "		<img class='pin-btn' id = 'mark-0-" . $i . "' src='./assets/images/mark-0-" . $i . ".png'>";
							echo "	</div>";
							echo "	<div class='col-md-9 col-xs-9 col-sm-9 col-lg-9 pin-title'>" . $arrPinName[ $i ] . "</div>";
							echo "	<div class='clear'></div>";
							echo "</section>";
						}
					?>
				</div>
			</li>
			<li role="presentation">
				<a href="#">Search Pins<span class='caret'></span></a>
				<form id='searchForm' name='searchForm' method=post action=''>
				<div class = 'popup' id = 'search-pins-div'>
					<!--section class = 'rAlign'><img class = 'close-btn' src = './assets/images/close-btn.png' width='20px' height='20px' onclick = 'javascript:closePopup();'></section-->
					<section style='position:relative'>
						<input type='text' class='form-control' id='search-pin-input' name='search-pin-input' placeholder='Please input search keywords' value='<?php echo $search_pin_input; ?>'>
						<button type='button' class='btn' id='search-pin-btn' onclick='javascript:searchPins();'><span class="glyphicon glyphicon-search"></span></button>
					</section>
					<!--section class = 'rAlign'><br><input type = 'submit' class = 'btn btn-primary' value = 'Search'></section-->
				</div>
				</form>
			</li>
			<li role="presentation"><a href="index.php">View All Pins</a></li>
			<div class = 'clear'></div>
		</ul>
	</section>
</div>

<!-----Google Map Canvas and Right Panel----->
<div id='main-wrapper'>
	<div id='map_canvas'></div>
	<div id='right-panel'>
		<div id='right-panel-wrapper'>
			<div id='multi-loading'>
				<!--img src = './assets/images/loading3.gif'-->
			</div>
			<div id='no-result-div'>
				<section><img src='./assets/images/search-icon.png'></section>
				<section>
					<section id='no-result-title'>0 Results</section>
					<section>We can't find any pins in this location.</section>
				</section>
			</div>
			<div id='favorite-div'>
				<ul class="nav nav-pills nav-justified">
					<li class="active"><a href="javascript:getFavoriteItems(0);">All Pins</a></li> 
					<li><a href="javascript:getFavoriteItems(1);">Favorite Pins</a></li>
					<div class = 'clear'></div>
				</ul>
			</div>
			<div id='right-panel-body'></div>
		</div>
	</div>
</div>
<!------------------------------------------->

<!--------------------------------------Form area--------------------------------------->
<form id='filterForm' name='filterForm' method=post action=''>
	<input type='hidden' id='pin-type' name='pin-type'>
</form>
<form id='logoutForm' name='logoutForm' method=post action='./src/ajax.php'>
	<input type='hidden' id='action' name='action' value='LOGOUT'>
</form>
<form id='repinForm' name='repinForm' method=post action='./src/ajax.php'>
	<input type='hidden' id='action' name='action' value='RE_PIN'>
	<input type='hidden' id='no' name='no'>
	<input type='hidden' id='lat' name='lat'>
	<input type='hidden' id='lng' name='lng'>
</form>
<form id='multiviewForm' name='multiviewForm' method=post action='./src/ajax.php'>
	<input type='hidden' id='action' name='action' value='GET_MULTI_VIEW'>
	<input type='hidden' id='user-no' name='user-no' value='<?php echo $_SESSION['MYCANVAS_SESSION']['no']; ?>'>
	<input type='hidden' id='visible-markers' name='visible-markers' value=''>
	<input type='hidden' id='favorite' name='favorite' value='0'>
</form>
<form id='favoriteForm' name='favoriteForm' method=post action='./src/ajax.php'>
	<input type='hidden' id='action' name='action' value='FAVORITE_PIN'>
	<input type='hidden' id='pin-no' name='pin-no' value=''>
</form>
<form id='downloadForm' name='downloadForm' method=post action='./src/ajax.php'>
	<input type='hidden' id='action' name='action' value='GET_DOWNLOAD_FILES'>
	<input type='hidden' id='user-no' name='user-no' value='<?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>'>
	<input type='hidden' id='pin-no' name='pin-no' value=''>
</form>
<!-------------------------------------------------------------------------------------->

<script>
	// ********** Google Map ********** //
	var map;
	var marker;
	var markers = [];
	var markerCluster = null;
	var infoBubble = null;
	var visibleMarkers = [];
	var places, autocomplete;
	var dragStartLat = 0;
	var dragStartLng = 0;
	var gmap_lat = 0;
	var gmap_lng = 0;
	var move_lat = 0;
	var move_lng = 0;
	var user_no = <?php echo $_SESSION['MYCANVAS_SESSION']['no'] ?>;

	var arrMarker = <?php echo $jsMarkers ?>;
	var arrPinName = <?php echo $jsPinName ?>;

	gmap_lat = <?php echo $arrGeo['lat'] ?>;
	gmap_lng = <?php echo $arrGeo['lng'] ?>;

	$(document).ready(function(){
		$('#header #dropdownMenu').click(function(){
			if ( $('#header ul').css('display') == 'none' )
				$('#header ul').slideDown();
			else
				$('#header ul').slideUp();
		});

		$(document).click(function(e){
			var target = e.target;
			
			if ( !$(target).is('#header ul') && !$(target).is('#header ul *') )
			{
				$('#header ul').slideUp();
			}
		});

		$('#header ul li a').hover(function(){
			$(this).css('color', '#1B7BBB');
			$(this).parent().find('[class=popup]').slideDown();
		});

		$('#header ul li a').parent().mouseleave(function(){
			$(this).find('a').css('color', '#666');
			$(this).find('[class=popup]').slideUp();
		});

		$('#right-menu').click(function(){
			$('#right-panel').animate({width:'toggle'}, 500, function(){
				if ( $('#right-panel').css('display') == 'none' )
				{
					$('#map_canvas').css('width', '100%');
				}	
				else
				{
					$('#right-panel-body').css('height', $(window).height() - $('#header').outerHeight() - $('#favorite-div').outerHeight());

					$('#map_canvas').css('width', $('#map_canvas').width() - $('#right-panel').width());
					getMultiViewItems();
				}
			});
		});

		$('#map_canvas, #multi-loading').css('height', $(window).height() - $('#header').outerHeight());

		$( "#add-pins-div .pin-btn" ).draggable({
			appendTo: "body",
			helper: "clone"
		});

		$('#favorite-div a').click(function(){
			$('#favorite-div li').removeClass();
			$(this).parent().attr('class', 'active');
		})
		
		initialize();

		$('#search-address-input').keypress(function(e){
			var key = e.which || e.keyCode;
			if ( key == 13 )
			{
				centerMapByAddress();
			}
		});

		$('#map_canvas').droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ":not(.ui-sortable-helper)",
			drop: function( event, ui ) {
				var icon = ui.draggable.context.id;
				JqueryDialog.Open('Add', './src/popup.php?lat=' + move_lat + '&lng=' + move_lng + '&icon=' + icon, 500, 550);
			}
		});

		$('#logoutForm').ajaxForm({
			success:function(ret)
			{
				if ( $.trim( ret ) == 'SUCCESS' )
				{
					document.location.href = 'index.php?menu=login';
				}
			}
		});

		$('#repinForm').ajaxForm({
			success:function(ret)
			{
				if ( $.trim( ret ) == 'SUCCESS' )
				{
					show_loading(false);
				}
				else
				{
					alert( ret );
					show_loading(false);
				}
			}
		});

		$('#multiviewForm').ajaxForm({
			success:function(ret)
			{
				$('#right-panel-body').html("");

				var arrRet = $.parseJSON(ret);
				for (var i = 0; i < arrRet.length; i++)
				{
					var img = './assets/images/noImage.png';
					if ( arrRet[i].img_path != '' )
					{
						img = arrRet[i].img_path;
					}
					
					var html = "";
					html	 = "<section class='multi-unit'>";
					html	+= "	<div class='col-md-4'>";
					html	+= "		<img class='represent-img' src='" + img + "'>";
					html	+= "		<div class='img-cnt'>" + arrRet[i].img_cnt + " Image(s)</div>";
					html	+= "	</div>";
					html	+= "	<div class='col-md-8 multi-description'>";
					html	+= "		<section class='multi-unit-address'>";
					html	+= "			<img class='multi-marker-img' src='./assets/images/" + arrRet[i].pin_type + ".png'>";
					html	+= "			<a href='javascript:showDetailBox(" + arrRet[i].no + ", " + arrRet[i].lat + ", " + arrRet[i].lng + ")'>" + arrRet[i].address + "</a>";
					html	+= "		</section>";
					html	+= "		<section class='multi-unit-title'>" + arrRet[i].title + "</section>";
					html	+= "		<section class='multi-unit-memo'>" + arrRet[i].memo + "</section>";
					html	+= "	</div>";
					html	+= "	<div class='clear'></div>";
					html	+= "</section>";
					$('#right-panel-body').append( html );
					$('#right-panel-body').scrollTop(0);
				}

				showMultiViewLoading( false );

				if ( arrRet.length <= 0 )
				{
					$('#no-result-div').css('display', 'block');
				}
			}
		});

		$('#downloadForm').ajaxForm({
			success:function(ret)
			{
				var arrFiles = $.parseJSON( ret );
				var pin_no = $('#downloadForm').find('[id*=pin-no]').val();

				if ( arrFiles.length == 0 )
				{
					alert('No attached files.');
				}

				for ( var i = 0; i < arrFiles.length; i++ )
				{
					var a = $("<a>").attr("href", "./upload/" + user_no + '/' + pin_no + '/' + arrFiles[i]).attr("download", arrFiles[i]).appendTo("body");
					a[0].click();
					a.remove();
				}
			}
		});
	});

	$('#favoriteForm').ajaxForm({
		success: function(ret)
		{
			if ( $.trim(ret) == 'SUCCESS' )
			{
				var pin_no = $('#favoriteForm').find('[id*=pin-no]').val();
				var btn_class = $('#favorite-btn-' + pin_no).attr('class');

				if ( btn_class == 'btn favorite-btn' )
				{
					$('#favorite-btn-' + pin_no).attr('class', 'btn favorite-btn-focus');
				}				
				else
				{
					$('#favorite-btn-' + pin_no).attr('class', 'btn favorite-btn');
				}

				getMultiViewItems();
			}
		}
	});

	function initialize() {
		var myLatlng = new google.maps.LatLng(gmap_lat,gmap_lng);

		var myOptions = {
			zoom: <?PHP echo DEFAULT_GMAP_ZOOM; ?>,
			maxZoom: 21,
			center: myLatlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		map.setTilt(0);
	    places = new google.maps.places.PlacesService( map );
	    autocomplete = new google.maps.places.Autocomplete(document.getElementById('search-address-input'));

		infoBubble = new InfoBubble({
			map: map,
			minWidth:700,
			maxWidth:700,
			minHeight:430,
			maxHeight:430,
			Padding:10,
		});

		for ( var i = 0; i < arrMarker.length; i++ )
		{
			makeMarker( arrMarker[i] );
		}

		var mcOptions = {styles: [
			{ height: 53, url: "./assets/images/m1.png", width: 53, textSize: 16, textColor: 'white' },
			{ height: 56, url: "./assets/images/m2.png", width: 56, textSize: 16, textColor: 'white' },
			{ height: 66, url: "./assets/images/m3.png", width: 66, textSize: 16, textColor: 'white' },
			{ height: 78, url: "./assets/images/m4.png", width: 78, textSize: 16, textColor: 'white' },
			{ height: 90, url: "./assets/images/m5.png", width: 90, textSize: 16, textColor: 'white' }
		], ignoreHidden: true };

		markerCluster = new MarkerClusterer(map, markers, mcOptions);

		google.maps.event.addListener(map, 'mousemove', function (event) {
			move_lat = event.latLng.k;
			move_lng = event.latLng.D;
//			displayCoordinates(event.latLng);
		});

		google.maps.event.addListener(map, 'idle', function() {
			var bounds = map.getBounds();
			var bTrue = 0, bFalse = 0;
			
			visibleMarkers = [];
			for (var i = 0; i < markers.length; i ++)
			{
				var latlng = new google.maps.LatLng( markers[i].lat, markers[i].lng );
				if ( bounds.containsLatLng( latlng ) )
				{
					visibleMarkers.push( markers[i].no );
				}
			}

			$('#visible-markers').val( visibleMarkers );

			if ( $('#right-panel-body').css('display') != 'none' )
				getMultiViewItems();
		});

		google.maps.event.addListener(map, 'zoom_changed', function() {
			console.log('max-zoom: ' + map.maxZoom);
			console.log('current-zoom: ' + map.zoom);

			if ( map.zoom >= map.maxZoom )
			{
				showMarkerCluster(false);
			}
			else
			{
				if ( markerCluster == null )
					showMarkerCluster(true);
			}
		});

		function displayCoordinates(pnt) {
			var lat = pnt.lat();
//			lat = lat.toFixed(4);
			var lng = pnt.lng();
//			lng = lng.toFixed(4);
//			console.log("Latitude: " + lat + "  Longitude: " + lng);
			move_lat = lat;
			move_lng = lng;
		}
	}

	function clearResults() {
		var results = document.getElementById("results");
		while (results.childNodes[0]) {
			results.removeChild(results.childNodes[0]);
		}
	}

	function addMarker( unit_maker ) {
		markers.push( unit_maker );
	}

	function makeMarker( markerInfo )
	{
		//store marker coordiate
		var marker_location = new google.maps.LatLng( markerInfo.lat, markerInfo.lng );

		//set marker
		var marker = new google.maps.Marker({
			no: markerInfo.no,
			lat: markerInfo.lat,
			lng: markerInfo.lng,
			icon: './assets/images/' + markerInfo.pin_type + '.png',
			position: marker_location, 
			map: map,
			draggable: true
		});

		marker.setMap( map );

		// store marker array
		addMarker( marker );

		// marker event
		google.maps.event.addListener(marker, 'click', function(event) {
			var info = null;
			for (var i = 0; i < arrMarker.length; i ++)
			{
				if ( arrMarker[i].no == markerInfo.no )
				{
					info = arrMarker[i];
					break;
				}
			}

			var html = makeContentForInfoBubble( info );
			infoBubble.setContent( html );
			infoBubble.setPosition( new google.maps.LatLng( markerInfo.lat, markerInfo.lng ) );

			if ( typeof $("#img-slider-" + marker.no).attr('style') == 'undefined' )
			{
				$("#img-slider-" + marker.no).bxSlider({mode:'fade', preloadImages:'all', pager:false});
				$('.bxslider li').css('width', '100%' );
			}
			$('.bxslider li').css('width', '100%' );
			$('.summary-right').css('overflow-y', 'hidden');
			$('.summary-more').click(function(){
				$('.summary-right').css('overflow-y', 'auto');
			});

			infoBubble.close();
			infoBubble.open(map, marker);
		});

		google.maps.event.addListener(marker, 'dragstart', function() {
			dragStartLat = marker.position.k;
			dragStartLng = marker.position.D;
		});

		google.maps.event.addListener(marker, 'dragend', function() {
			if ( confirm( 'Would you like to move this pin?' ) )
			{
				repin( marker.no, marker.position.k, marker.position.D );
			}
			else
			{
				var latlng = new google.maps.LatLng(dragStartLat, dragStartLng);
				marker.setPosition(latlng);
			}
		});
	}

	function makeContentForInfoBubble( markerInfo )
	{
		var html = "";
		html += "<div class='summary'>";
		html += "	<section>";
		html += "		<div class='col-md-6 summary-left'>";
		html += "			<section class='lAlign'>";
		html += "				<span class='summary-pin-type'><img src='./assets/images/" + markerInfo.pin_type + ".png'></span>";
		html += "				<span class='summary-date'>" + markerInfo.pin_date + "<br>" + arrPinName[ markerInfo.pin_type ] + "</span>";
		html += "				<div class='clear'></div>";
		html += "			</section>";
		html += "			<section class='summary-address'>" + markerInfo.address + "</section>";
		html += "			<section>";
		html += "				<div class='img-slider'>";
		html +=	"					<ul class='bxslider' id='img-slider-" + markerInfo.no + "'>";
		if ( markerInfo.img_cnt == 0 )
		{
			html += "					<li><img class='summary-img' src='./assets/images/noImage.png'></li>";
		}
		else
		{
			for ( var i = 0; i < markerInfo.img_list.length; i++ )
				html += "				<li><img class='summary-img' src='" + markerInfo.img_list[i] + "'></li>";
		}
		html += "					</ul>";
		html += "					<div class='img-cnt'>" + markerInfo.img_cnt + " Image(s)</div>";
		html += "				</div>";
		html +=	"			</section>";
		html += "		</div>";
		html += "		<div class='col-md-6'>";
		html += "			<div class='summary-right'>";
		html += "				<section class='summary-title'>" + markerInfo.title + "</section>";
		html += "				<section class='summary-memo'>" + markerInfo.memo;
		if ( markerInfo.custom_field.length > 0 )
			html += '<br>';
		for ( var i = 0; i < markerInfo.custom_field.length; i++ )
			html += markerInfo.custom_field[i].name + ": " + markerInfo.custom_field[i].content + "<br>";
		html += "				</section>";
		html += "			</div>";
		html += "			<section class='summary-more-div'>";
		html += "				<div class='col-md-6 summary-doc lAlign' onclick='javascript:download(" + markerInfo.no + ")'>DOCUMENTS ATTACHED</div>";
		html += "				<div class='col-md-6 summary-more rAlign'>READ MORE</div>";
		html += "				<div class='clear'></div>";
		html += "			</section>";
		html += "		</div>";
		html += "		<div class='clear'></div>";
		html += "	</section>";
		var favorite_class = ( markerInfo.favorite == 0 ) ? 'btn favorite-btn' : 'btn favorite-btn-focus';
		html += "	<section class='summary-action'>";
		html += "		<div class='col-md-6 summary-date lAlign company-info'>";
		if ( markerInfo.company_logo == '' )
			html +=		"	<span style='color:#666666;'>" + markerInfo.company_name + "</span>";
		else
			html += "		<img src='" + markerInfo.company_logo + "'>&nbsp;&nbsp" + markerInfo.company_name;
		html += "		</div>";
		html += "		<div class='col-md-6 rAlign'>";
		html += "			<input type='button' id='favorite-btn-" + markerInfo.no + "' class='" + favorite_class + "' value='Favorite' style='outline:none;' onclick='javascript:favoritePin(" + markerInfo.no + ")'>";
		html += "			<input type='button' class='btn view-edit-btn' value='View/Edit' style='outline:none;' onclick='javascript:showViewEditDialog(" + markerInfo.no + ", " + markerInfo.lat + ", " + markerInfo.lng + ")'>";
		html += "		</div>";
		html += "		<div class='clear'></div>";
		html += "	</section>";
		html += "</div>";
		return html;
	}

	function changeMarkerInfo( markerInfo )
	{
		for (var i = 0; i < arrMarker.length; i++)
		{
			if ( arrMarker[i].no == markerInfo.no )
			{
				arrMarker[i] = markerInfo;
				break;
			}
		}
	}

	function changeMarker( markerInfo )
	{
		for (var i = 0; i < markers.length; i++)
		{
			if ( markers[i].no == markerInfo.no )
			{
				markers[i].no		= markerInfo.no;
				markers[i].pin_type = markerInfo.pin_type;
				markers[i].lat		= markerInfo.lat;
				markers[i].lng		= markerInfo.lng;
				markers[i].address	= markerInfo.address;
				markers[i].title	= markerInfo.title;
				markers[i].memo		= markerInfo.memo;
				markers[i].img_cnt	= markerInfo.img_cnt;
				markers[i].img_list = markerInfo.img_list;
				markers[i].setIcon( './assets/images/' + markerInfo.pin_type + '.png' );
				break;
			}
		}

		changeMarkerInfo( markerInfo );
	}

	function removePinInfo( markerInfo )
	{
		for (var i = 0; i < arrMarker.length; i++)
		{
			if ( arrMarker[i].no == markerInfo.no )
			{
				delete arrMarker[i];
				break;
			}
		}
	}

	function removePin( markerInfo )
	{
		for (var i = 0; i < markers.length; i++)
		{
			if ( markers[i].no == markerInfo.no )
			{
				markers[i].setMap(null);
				break;
			}
		}

		removePinInfo( markerInfo );
	}

	function clearMarkers()
	{
		for (var i = 0; i < markers.length; i++) 
			markers[i].setMap(null);

		if ( markerCluster )
		{
			markerCluster.clearMarkers();
			markerCluster = null;
		}

		markers = [];
	}

	function showMarkerCluster( bShow )
	{
		clearMarkers();

		for ( var i = 0; i < arrMarker.length; i++ )
		{
			makeMarker( arrMarker[i] );
		}

		if ( bShow )
		{
			var mcOptions = {styles: [
				{ height: 53, url: "./assets/images/m1.png", width: 53, textSize: 16, textColor: 'white' },
				{ height: 56, url: "./assets/images/m2.png", width: 56, textSize: 16, textColor: 'white' },
				{ height: 66, url: "./assets/images/m3.png", width: 66, textSize: 16, textColor: 'white' },
				{ height: 78, url: "./assets/images/m4.png", width: 78, textSize: 16, textColor: 'white' },
				{ height: 90, url: "./assets/images/m5.png", width: 90, textSize: 16, textColor: 'white' }
			], ignoreHidden: true };
			markerCluster = new MarkerClusterer(map, markers, mcOptions);
		}		
	}

	function centerMapByAddress()
	{
		var address = $.trim( $('#search-address-input').val() );
		if ( address != '' )
		{
			GMaps.geocode({
				address: address,
				callback: function(results, status) {
					if (status == 'OK') {
						var latLng = results[0].geometry.location;
						if (map.getZoom() <= 18) map.setZoom(18); 
						map.setCenter( latLng );
						var marker = new google.maps.Marker({
							position: latLng, 
							map: map
						});
						marker.setMap( map );
					}
				}
			});
		}		
	}

	function closePopup()
	{
		$('.popup').slideUp();
	}

	function showMultiViewLoading( b )
	{
		if ( b ) $('#right-panel #multi-loading').css('display', 'block');
		else $('#right-panel #multi-loading').css('display', 'none');
	}

	function showAddPinPopup()
	{
		$('.popup').css('display', 'none');
		$('#add-pins-div').css('display', 'block');
	}

	function showFilterPinPopup()
	{
		$('.popup').css('display', 'none');
		$('#filter-pins-div').css('display', 'block');
	}

	function showSearchPinPopup()
	{
		$('.popup').css('display', 'none');
		$('#search-pins-div').css('display', 'block');
	}

	function showViewEditDialog( no, lat, lng )
	{
		infoBubble.close();
		JqueryDialog.Open('View/Edit', './src/popup.php?edit=1&no=' + no + '&lat=' + lat + '&lng=' + lng, 950, 550);
	}

	function filterPins( pin_type )
	{
		$('#filterForm').find('[id*=pin-type]').val( pin_type );
		$('#filterForm').submit();
	}

	function searchPins()
	{
		if ( $.trim( $('#search-pin-input').val() ) != '' )
		{
			$('#searchForm').submit();
		}
	}

	function getMultiViewItems()
	{
		$('#no-result-div').css('display', 'none');
		showMultiViewLoading( true );
		$('#multiviewForm').submit();
	}

	function getFavoriteItems( favorite )
	{
		$('#multiviewForm').find('[id*=favorite]').val( favorite );
		showMultiViewLoading( true );
		$('#multiviewForm').submit();
	}

	function multiLoadingPosition()
	{
		$('#multi-loading').css('height', $(document).height() - $('#header').outerHeight());
	}

	function showDetailBox( no, lat, lng )
	{
		var center_location = new google.maps.LatLng( lat, lng );
		map.setCenter( center_location );
		for (var i = 0; i < markers.length; i++)
		{
			if ( no == markers[i].no )
			{
				//GEvent.trigger(markers[i], 'click');
				google.maps.event.trigger(markers[i], 'click');
				break;
			}
		}
	}

	function repin( no, lat, lng )
	{
		$('#repinForm').find('[id*=no]').val( no );
		$('#repinForm').find('[id*=lat]').val( lat );
		$('#repinForm').find('[id*=lng]').val( lng );
		show_loading( true );
		$('#repinForm').submit();
	}

	function favoritePin( pin_no )
	{
		$('#favoriteForm').find('[id*=pin-no]').val( pin_no );
		$('#favoriteForm').submit();
	}

	function download( pin_no )
	{
		$('#downloadForm').find('[id*=pin-no]').val( pin_no );
		$('#downloadForm').submit();
	}

	function logout()
	{
		$('#logoutForm').submit();
	}
</script>
<!DOCTYPE>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>MarkerClusterer v3 Info On Click Example</title>
<style type="text/css">
body {
margin: 0;
padding: 10px 20px 20px;
font-family: Arial;
font-size: 16px;
}
#map-container {
padding: 6px;
border-width: 1px;
border-style: solid;
border-color: #ccc #ccc #999 #ccc;
-webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
-moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
box-shadow: rgba(64, 64, 64, 0.1) 0 2px 5px;
width: 600px;
}
#map {
width: 600px;
height: 400px;
}
</style>
<script src="http://www.google.com/jsapi"></script>
<script type="text/javascript" src="../src/data.json"></script>
<script type="text/javascript">
var script = '<script type="text/javascript" src="../src/markerclusterer';
if (document.location.search.indexOf('packed') !== -1) {
script += '_packed';
}
if (document.location.search.indexOf('compiled') !== -1) {
script += '_compiled';
}
script += '.js"><' + '/script>';
document.write(script);
</script>
<script type="text/javascript">
google.load('maps', '3', {
other_params: 'sensor=false'
});
google.setOnLoadCallback(initialize);
function initialize() {
var center = new google.maps.LatLng(37.4419, -122.1419);
var map = new google.maps.Map(document.getElementById('map'), {
zoom: 3,
center: center,
mapTypeId: google.maps.MapTypeId.ROADMAP
});
var markers = [];
for (var i = 0, dataPhoto; dataPhoto = data.photos[i]; i++) {
var latLng = new google.maps.LatLng(dataPhoto.latitude,
dataPhoto.longitude);
var marker = new google.maps.Marker({
position: latLng,
//This will be the fallback if content
title: "This is a title for the marker.",
content: "<b>This is HTML Content we use instead of the Marker Title for the mass infowindow.</b><br>"
});
markers.push(marker);
}
var mcOptions = {
infoOnClick: true,
infoOnClickZoom: 7
};
var markerCluster = new MarkerClusterer(map, markers, mcOptions);
}
</script>
</head>
<body>
<h3>A simple example of MarkerClusterer (100 markers)</h3>
<p>
<a href="?compiled">Compiled</a> |
<a href="?packed">Packed</a> |
<a href="?">Standard</a> version of the script.
</p>
<div id="map-container">
<div id="map"></div>
</div>
</body>
</html>
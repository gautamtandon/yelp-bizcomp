<?php

include "YelpClient.php";

$yc = new YelpClient();

if (isset($_REQUEST['q'])) {
	if ($_REQUEST['q'] == 'business') {
		echo $yc->request("business/".$_REQUEST['biz_id'], null);
	} elseif ($_REQUEST['q'] == 'search') {
		$url_params = array();
		$url_params['bounds'] = $_REQUEST['bounds'];
		$url_params['category_filter'] = $_REQUEST['category_filter'];
		echo $yc->request("search", http_build_query($url_params));
	}
	exit;
}

?>
<html>

<head>
    <script src="/js/jquery-1.6.2.min.js" ></script>
    <script src="/js/jquery-ui-1.8.16.custom.min.js" ></script>
    <style>
    body {font-family: arial}
    </style>
</head>

<body>
<div align="center">
Your Yelp Business ID: <input id="biz_id" /> <input id="button" type="button" value="Find Competition" onclick="fill_map(true)" /><br/>
<br/>
<div id="map" style="width: 70%; height: 80%; border: 1px solid gray">Loading...</div>
</div>

<script>
var map = null;
var markers = [];

function add_marker(biz_data, show_label) {
	var marker = new google.maps.Marker({
		position: {lat: biz_data['location']['coordinate']['latitude'], lng: biz_data['location']['coordinate']['longitude']},
		map: map,
		animation: google.maps.Animation.DROP,
	});
	if (show_label) {
		marker.setLabel(""+Math.round(biz_data['rating']));
	}

	var display_address = "";
	$.each(biz_data['location']['display_address'], function(idx) {
		display_address += biz_data['location']['display_address'][idx] + "<br/>";
	});
	var infoWindowContent=
		"<table>"+
		"<tr valign='top'>"+
		"<td>"+
		"<h3>"+biz_data['name']+"</h3>"+
		"<p>"+display_address+"</p>"+
		"<p style='font-size: 80%'><img align='middle' src='"+biz_data['rating_img_url']+"' /> "+biz_data['review_count']+" reviews</p>"+
		"<p>"+(biz_data['deals'] != null ? "["+biz_data['deals'].length+" deals]" : "")+(biz_data['gift_certificates'] != null ? "["+biz_data['gift_certificates'].length+" gift certificates]" : "")+"</p>"+
		"</td>"+
		"<td>"+
		"<img src='"+biz_data['image_url']+"' />"+
		"</td>"+
		"</tr>"+
		"</table>";

	var infoWindow = new google.maps.InfoWindow();
	marker.addListener("mouseover", function() {
		infoWindow.setContent(infoWindowContent);
		infoWindow.open(map, marker);
	});
	marker.addListener("mouseout", function() {
		infoWindow.close();
	});
	marker.addListener("click", function() {
		infoWindow.close();
		$("#biz_id").val(biz_data['id']);
		fill_map(true);
	});
	markers.push(marker);
}

function initMap() {
	var mapDiv = $("#map")[0];
	var currLocation = navigator.geolocation.getCurrentPosition(function(position) {
		map = new google.maps.Map(mapDiv, {
			center: {lat: position.coords.latitude, lng: position.coords.longitude}, 
			zoom: 13
		});
		map.addListener("dragend", function() {fill_map(false)});
		map.addListener("resize", function() {fill_map(false)});
		map.addListener("zoom_changed", function() {fill_map(false)});

		var marker = new google.maps.Marker({
			position: {lat: position.coords.latitude, lng: position.coords.longitude},
			map: map,
			animation: google.maps.Animation.DROP,
			title: "You Are Here!"
		});
		markers.push(marker);
	});
	$("#button").prop("disabled", false);
}

function fill_map(set_center) {
	$.each(markers, function(idx){
		markers[idx].setMap(null);
	});
	markers = [];

	$("#button").prop("disabled", true);
	$("#button").val("Finding This Business...");
	var biz_data = null;
	$.ajax({
		type: "GET",
		async: false,
		url: "/index.php?q=business&biz_id="+$("#biz_id").val()
	}).done(function(data) {
		biz_data = $.parseJSON(data);
		add_marker(biz_data, false);
		if (set_center) {
			map.setCenter({lat: biz_data['location']['coordinate']['latitude'], lng: biz_data['location']['coordinate']['longitude']});
		}
	});

	var categories = "";
	$.each(biz_data['categories'], function(idx) {
		if (categories == "") {
			categories = biz_data['categories'][idx][1];
		} else {
			categories += "," + biz_data['categories'][idx][1];
		}
	});

	$("#button").prop("disabled", true);
	$("#button").val("Finding Competition...");
	$.ajax({
                type: "GET",
                async: false, 
                url: "/index.php?q=search&bounds="+map.getBounds().getSouthWest().lat()+","+map.getBounds().getSouthWest().lng()+"|"+map.getBounds().getNorthEast().lat()+","+map.getBounds().getNorthEast().lng()+"&category_filter="+encodeURI(categories)
        }).done(function(data) {
                var search_data = $.parseJSON(data);
		$.each(search_data['businesses'], function(idx){
			if (search_data['businesses'][idx]['id'] != biz_data['id']) {
				add_marker(search_data['businesses'][idx], true);
			}
		});
        });
	$("#button").prop("disabled", false);
	$("#button").val("Find Competition");

}
</script>
<script src="https://maps.googleapis.com/maps/api/js?callback=initMap" async defer></script>
</body>

</html>

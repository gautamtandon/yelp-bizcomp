<html>

<head>
    <script src="/js/jquery-1.6.2.min.js" ></script>
    <script src="/js/jquery-ui-1.8.16.custom.min.js" ></script>
    <style>
    body {font-family: arial}
    </style>
</head>

<body>
<div id="map" style="width: 100%; height: 98%; border: 1px solid gray"></div>
<div id="status_bar" style="text-align: right; font-size: 80%; font-family: monospace; color: gray">Initializing Google Maps...</div>

<!--
<table align="center" width="100%" height="100%">
<tr><td style="width: 100%; height: 100%"></td></tr>
<tr><td style="font-size: 80%; font-family: monospace; color: gray" id="status_bar">Initializing Google Maps...</td></tr>
</table>
-->

<script>
var map = null;
var markers = [];
var rating_colors = ["", "#FF0000", "#FFA500", "#FFFF00", "#00FF00", "#008000"];

function add_marker(biz_data, is_competitor) {
	var marker = new google.maps.Marker({
		position: {lat: biz_data['location']['coordinate']['latitude'], lng: biz_data['location']['coordinate']['longitude']},
		map: map,
		animation: google.maps.Animation.DROP,
	});
	if (is_competitor) {
		marker.setIcon({path: google.maps.SymbolPath.CIRCLE, scale: 12, strokeWeight: 0, fillColor: rating_colors[Math.round(biz_data['rating'])], fillOpacity: 1});
		if (biz_data['deals'] != null || biz_data['gift_certificates'] != null) {
			marker.setLabel({fontWeight: "bold", fontSize: "24px", color: "#FFFFFF", text: "*"});
		}
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
		window.location.href="/?id="+biz_data['id'];
	});
	marker.addListener("rightclick", function() {
		infoWindow.close();
		window.open("https://www.yelp.com/biz/"+biz_data['id']);
	});
	markers.push(marker);
}

function initMap() {
	map = new google.maps.Map($("#map")[0], {
		zoom: 18
	});
	map.addListener("dragend", function() {fill_map(false)});
	map.addListener("resize", function() {fill_map(false)});
	map.addListener("zoom_changed", function() {fill_map(false)});

	fill_map(true);
}

function fill_map(set_center) {
	$.each(markers, function(idx){
		markers[idx].setMap(null);
	});
	markers = [];

	var biz_data = null;
	$("#status_bar").html("Getting Yelp Business Info for id: "+"<?php echo $_REQUEST['id'] ?>...");
	$.ajax({
		type: "GET",
		async: false,
		url: "/yelpclient.php?q=business&biz_id="+"<?php echo $_REQUEST['id'] ?>"
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

	$("#status_bar").html("Getting Yelp Business Info for competitors in the visible map...");
	$.ajax({
                type: "GET",
                async: false, 
                url: "/yelpclient.php?q=search&bounds="+map.getBounds().getSouthWest().lat()+","+map.getBounds().getSouthWest().lng()+"|"+map.getBounds().getNorthEast().lat()+","+map.getBounds().getNorthEast().lng()+"&category_filter="+encodeURI(categories)
        }).done(function(data) {
                var search_data = $.parseJSON(data);
		$.each(search_data['businesses'], function(idx){
			if (search_data['businesses'][idx]['id'] != biz_data['id']) {
				add_marker(search_data['businesses'][idx], true);
			}
		});
        });

	$("#status_bar").html("Done loading.");
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?callback=initMap" async defer></script>
</body>

</html>

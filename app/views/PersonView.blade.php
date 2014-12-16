<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>EBA</title>
	<!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->

	{{HTML::style('css/bootstrap.min.css');}}
	{{HTML::style('css/personview.css');}}
	<!-- {{HTML::script('js/bootstrap.min.js');}} -->
 <!-- {{HTML::script('js/jquery-1.11.1.min.js');}}
 {{HTML::script('js/jquery.js');}} -->
 
 <script type="text/javascript"
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDn7BL_KVNfQo3SlE7QCvRZ3xz84CB2T3U">
 </script>
 <script type="text/javascript">
 var tokyo = new google.maps.LatLng(35.66919, 139.7413805);

 var neighborhoods = [
 new google.maps.LatLng(52.511467, 13.447179),
 new google.maps.LatLng(52.549061, 13.422975),
 new google.maps.LatLng(52.497622, 13.396110),
 new google.maps.LatLng(52.517683, 13.394393)
 ];

 var contents = new Array();
 var predictedLocationClient = new Array();
 var locationLogClient = new Array();



 var markers = [];
// var bounceMarker = null;
var iterator = 0;
var infowindow = null;
var map;
var showPredictedLocation = false;

function initialize() {
	prepareData();
	contents = predictedLocationClient;
	var mapOptions = {
		zoom: 12,
		center: tokyo,
		mapTypeControl: true,
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
			position: google.maps.ControlPosition.BOTTOM_CENTER
		},
		panControl: true,
		panControlOptions: {
			position: google.maps.ControlPosition.TOP_RIGHT
		},
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.LARGE,
			position: google.maps.ControlPosition.RIGHT_CENTER
		},
		scaleControl: true,
		streetViewControl: true,
		streetViewControlOptions: {
			position: google.maps.ControlPosition.RIGHT_TOP
		}
	}

	
	map = new google.maps.Map(document.getElementById('map-canvas'),
		mapOptions);
	swap();
	document.getElementById('leftPanel').hidden=false; 
	document.getElementById('addPanel').hidden=true; 
	drop();
}

function prepareData(){
	@foreach($predictedLocation as $location)
	var temp = { 'date':'{{$location->dateTime}}', 'latitude':{{$location->latitude}}, 'longitude':{{$location->longitude}} };
	predictedLocationClient.push(temp);
	@endforeach

	@foreach($locationLog as $location)
	var temp = { 'date':'{{$location->dateTime}}', 'latitude':{{$location->latitude}}, 'longitude':{{$location->longitude}} };
	locationLogClient.push(temp);
	@endforeach
}

function drop(){
	for (var i = 0; i < neighborhoods.length; i++) {
		addMarker();
	}
}


function addMarker() {
	markers.push(new google.maps.Marker({
		position: neighborhoods[iterator],
		map: map,
		draggable: false,
		animation: google.maps.Animation.DROP
	}));
	var content = contents[iterator];
	var marker = markers[iterator];
	google.maps.event.addListener(marker, 'click', function() {
		setInfoWindow(content);
		infowindow.open(map,marker);
	});
	iterator++;
}
function setInfoWindow(content){
	if (infowindow) {
		infowindow.close();
	}
	infowindow = new google.maps.InfoWindow({
		content: '<div>'+
		'<h4 id="heading">'+content+'</h4>'+
		'</div>'
	});   
}

function panToMarker(i) {
	// if(bounceMarker!=null){
	//   bounceMarker.setAnimation(null);  
	// }
	// markers[i].setAnimation(google.maps.Animation.BOUNCE);
	// bounceMarker = markers[i];
	setInfoWindow(contents[i]);
	infowindow.open(map,markers[i]);
	map.panToBounds(markers[i].getPosition());
}
function clearOverlays() {
	for (var i = 0; i < markers.length; i++ ) {
		markers[i].setMap(null);
	}
	markers.length = 0;
}

google.maps.event.addDomListener(window, 'load', initialize);

function prepareContentHTML(){
	var text = '<tbody>';
	for(var i=0 ; i<contents.length ; i++) {
		text += '<tr>';
		text += '<td onclick="panToMarker('+i+')">';
		text += ''+contents[i].date+'</br>'+contents[i].latitude+', '+contents[i].longitude+'</td>';
		text += '</tr>';
	}
	text += '</tbody>';
	return text;
}

function swap(){

	if(showPredictedLocation){
		showPredictedLocation = false;

		document.getElementById('panelTitle').innerHTML = "Location Log";
		contents = locationLogClient;
	}else{
		showPredictedLocation = true;
		contents = predictedLocationClient;
		document.getElementById('panelTitle').innerHTML = "Predicted Location";

	}
	var resultHTML = prepareContentHTML();
	document.getElementById('locationContents').innerHTML = resultHTML;
}

function addData(){
	document.getElementById('leftPanel').hidden=true;  
	document.getElementById('addPanel').hidden=false;
}

function delUser(){

}
// $(function () {
//   $('[data-toggle="tooltip"]').tooltip()
// })
</script>
</head>
<body>

	<div id="searchPanel" class="row">
		<div class="col-sm-2">
			<a href="{{ URL::to('/') }}" class="btn btn-default" type="button" id="drop"><span class="glyphicon glyphicon-user"></span></a>
		</div>
		<div class="input-group col-sm-10">
			<input type="text" class="form-control" placeholder="Enter a username">
			<span class="input-group-btn">
				<button class="btn btn-primary" type="button" id="drop"><span class="glyphicon glyphicon-search"></span></button>
			</span>
		</div><!-- /input-group -->
	</div>

	<div id="leftPanel" class="col-sm-4 col-md-2" hidden="true">
		<div class="row">
			<div class="col-sm-6">
				<h3>{{$person->name}}</h3>
			</div>
			<div class="col-sm-6">

				<h3>
					<button type="button" class="icon" onclick="swap()" data-toggle="tooltip" data-placement="right" title="Switch to Location Log"><span class="glyphicon glyphicon-retweet"></span></button>
					<button type="button" class="icon" onclick="addData()" data-toggle="tooltip" data-placement="top" title="Add more location information"><span class="glyphicon glyphicon-plus"></span></button>
					<button type="button" class="icon" data-toggle="modal" data-target="#confirm-delete" data-placement="top" title="Delete this user's location information"><span class="glyphicon glyphicon-trash"></span></button>
				</h3>
			</div>
		</div><!-- /.row -->
		<h4 id="panelTitle">Predicted Location</h4>
		<table class="table table-hover" id="locationContents">
		</table>
	</div>



	<div id="addPanel" class="col-sm-4 col-md-2" hidden="true">
		<div class="row">
			<div class="col-sm-6">
				<h3>{{$person->name}}</h3>
			</div>
		</div>
		<h4 id="panelTitle">Add Location Log</h4>
		<!-- <form role="form"> -->
		<div class="form-group">
			<label for="exampleInputFile">File input</label>
			<input type="file" id="exampleInputFile">
		</div>
		<button type="submit" class="btn btn-primary">Add</button>
		<button class="btn btn-default" onclick="search()">Cancel</button>
		<!-- </form> -->
	</div><!--/addPanel -->

	<div id="map-canvas"></div>



	<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Confirm Delete</h4>
				</div>

				<div class="modal-body">
					<p>Are you sure you want to delete this user?</p>
					<p class="debug-url"></p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-danger danger" onclick="delUser()">Yes</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- {{HTML::script('js/bootstrap.min.js');}} -->
 <!-- {{HTML::script('js/jquery-1.11.1.min.js');}}
 {{HTML::script('js/jquery.js');}} -->
 <script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
 <script type="text/javascript" src="js/bootstrap.min.js"></script>
</body>
</html>
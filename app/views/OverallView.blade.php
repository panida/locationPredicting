<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>EBA</title>
 <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->

 {{HTML::style('css/bootstrap.min.css');}}
 {{HTML::style('css/overallview.css');}}
 <!-- {{HTML::script('js/bootstrap.min.js');}} -->
 <!-- {{HTML::script('js/jquery-1.11.1.min.js');}}
 {{HTML::script('js/jquery.js');}} -->
 
 <script type="text/javascript"
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDn7BL_KVNfQo3SlE7QCvRZ3xz84CB2T3U">
 </script>
 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
 <script type="text/javascript">
 var berlin = new google.maps.LatLng(52.520816, 13.410186);

 var neighborhoods = [
 new google.maps.LatLng(52.511467, 13.447179),
 new google.maps.LatLng(52.549061, 13.422975),
 new google.maps.LatLng(52.497622, 13.396110),
 new google.maps.LatLng(52.517683, 13.394393)
 ];

 var users=[];
 var user=0;
 var dateTime = [];

//  for (i = 0; i < 24; i++) {
//   dateTime[i] = "2014/11/01."+i+":24:50";
// }

var markers = [];
var specificMarkers =[];
// var bounceMarker = null;
var iterator = 0;
var infowindow = null;
var map;
var showPredictedLocation = true;
var sendNotification = false;
var dateTime=[];
var userNumber=2;
function initialize() {
  for(i=0;i<userNumber;i++){
    users.push({
      name: "user"+(i+1),
      locations: [] 
    });
    markers.push([]);
  }
  var lng =13.44;
  for (i=0;i<24;i++) {
    users[0].locations.push(new google.maps.LatLng(52.511467, lng));
    users[1].locations.push(new google.maps.LatLng(52.471467, lng));
    lng+=0.01;
    dateTime.push("2014/11/01."+i+":24:50");
  }
  var mapOptions = {
    zoom: 12,
    center: berlin,
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
  var input = /** @type {HTMLInputElement} */(
    document.getElementById('search'));
  var autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.bindTo('bounds', map);
  autocomplete.setTypes([]);
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(12); 
    }
    
    var address = '';
    if (place.address_components) {
      address = [
      (place.address_components[0] && place.address_components[0].short_name || ''),
      (place.address_components[1] && place.address_components[1].short_name || ''),
      (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }
  });

  drop();
}
google.maps.event.addDomListener(window, 'load', initialize);

function showNewRect(event) {
  var ne = rectangle.getBounds().getNorthEast();
  var sw = rectangle.getBounds().getSouthWest();
  var member="";
  for(var i=0;i<specificMarkers.length;i++){

    var isInRect = specificMarkers[i].getPosition().lat()<=ne.lat()&&
    specificMarkers[i].getPosition().lat()>=sw.lat()&&
    specificMarkers[i].getPosition().lng()<=ne.lng()&&
    specificMarkers[i].getPosition().lng()>=sw.lng();
    console.log(member);

    if(isInRect){
      if(member!=""){
        member+=", ";
      }
      member+=users[i].name;
    }
  }
  document.getElementById('selectedUsers').innerHTML = member; 

}
function drop(){
  for(var i=0;i<users.length;i++){
    user=i;
    for (var j = 0; j < users[0].locations.length; j++) {
      addMarker();
    }
    iterator=0;
  }
}
function search() {
  document.getElementById('predictPanel').hidden=false; 
  document.getElementById('addPanel').hidden=true; 
  document.getElementById('btnSendNoti').hidden=true; 
  document.getElementById('sendNotiPanel').hidden=true; 
  rectangle.setMap(null); 
  sendNotification=false;
  clearMarkers();
  markers=[];
  for(var i=0;i<userNumber;i++){
    markers.push([]);
  }
  drop();
}

function addMarker() {
  markers[user].push(new google.maps.Marker({
    position: users[user].locations[iterator],
    map: map,
    draggable: false,
    animation: google.maps.Animation.DROP
  }));
  var content = dateTime[iterator];
  var marker = markers[user][iterator];
  var username = users[user].name;
  google.maps.event.addListener(marker, 'click', function() {
    setInfoWindow(content,username);
    infowindow.open(map,marker);
  });  
  iterator++;
}

function showSpecificMarkers(row){
  document.getElementById('btnSendNoti').hidden=false; 
  clearMarkers();
  for(var i=0;i<users.length;i++){
    user=i;
    addSpecificMarkers(row);
  }
  map.panTo(specificMarkers[0].getPosition());
}

function addSpecificMarkers(row){
  specificMarkers.push(new google.maps.Marker({
    position: users[user].locations[row],
    map: map,
    draggable: false,
    animation: google.maps.Animation.DROP
  }));
  var content = dateTime[row];
  var marker = specificMarkers[user];
  var username = users[user].name;
  google.maps.event.addListener(marker, 'click', function() {
    setInfoWindow(content,username);
    infowindow.open(map,marker);
  });  
}

function setInfoWindow(content,username){
  if (infowindow) {
    infowindow.close();
  }
  infowindow = new google.maps.InfoWindow({
    content: '<div>'+
    '<h4 id="heading">'+username+'</h4>'+
    '<h5>'+content+'</h5>'+
    '</div>'
  });   
}

function setAllMap(map) {
  for(var i=0;i<markers.length;i++){
    for (var j = 0; j < markers[i].length; j++) {
      markers[i][j].setMap(map);
    }  
  }
}
function clearMarkers() {
  setAllMap(null);
  for(var i=0;i<specificMarkers.length;i++){
    specificMarkers[i].setMap(null);
  }
  specificMarkers =[];
}
function showMarkers() {
  setAllMap(map);
}

function swap(){

  if(showPredictedLocation){
    showPredictedLocation = false;
    document.getElementById('panelTitle').innerHTML = "Location Log";
  }else{
    showPredictedLocation = true;
    document.getElementById('panelTitle').innerHTML = "Predicted Location";
  }
}

function addUser(){
  document.getElementById('predictPanel').hidden=true;  
  document.getElementById('addPanel').hidden=false;
}

function delUser(){

}

function sendNoti(){
  if(sendNotification){
    rectangle.setMap(null); 
    document.getElementById('sendNotiPanel').hidden=true; 
    sendNotification=false;
    
  }else {
    document.getElementById('sendNotiPanel').hidden=false; 
    var bounds = new google.maps.LatLngBounds(
      new google.maps.LatLng(52.510816, 13.390186),
      new google.maps.LatLng(52.530816, 13.430186)
      );

  // Define the rectangle and set its editable property to true.
  rectangle = new google.maps.Rectangle({
    bounds: bounds,
    editable: true,
    draggable: true
  });

  rectangle.setMap(map);

  // Add an event listener on the rectangle.
  google.maps.event.addListener(rectangle, 'bounds_changed', showNewRect);

  // Define an info window on the map.
  infoWindow = new google.maps.InfoWindow();
  sendNotification = true;
}
}
// $(function () {
//   $('[data-toggle="tooltip"]').tooltip()
// })
</script>
</head>
<body>
  <div id="searchPanel" class="row">
    <div class="col-sm-2">
      <a href="{{ URL::to('/person') }}" class="btn btn-default" type="button" id="drop"><span class="glyphicon glyphicon-user"></span></a>
    </div>
    <div class="input-group col-sm-10">
      <input id="search" type="text" class="form-control" placeholder="Enter a location">
      <span class="input-group-btn">
        <button class="btn btn-primary" type="button" id="drop" onclick="search()"><span class="glyphicon glyphicon-search"></span></button>
      </span>
    </div><!-- /input-group -->
    
  </div>

  <div id="predictPanel" class="col-sm-4 col-md-2">
    <!--    <h3>2 Users  <button type="button" class="icon" onclick="addUser()" data-toggle="tooltip" data-placement="top" title="Add new user"><span class="glyphicon glyphicon-plus-sign"></span></button></h3> -->
    <div class="row">
      <div class="col-sm-8">
       <h3>2 Users</h3>
     </div>
     <div class="col-sm-4">
      <h3>
       <button type="button" class="icon" onclick="addUser()" data-toggle="tooltip" data-placement="top" title="Add new user"><span class="glyphicon glyphicon-plus-sign"></span></button>
       <button type="button" class="icon" onclick="sendNoti()" data-toggle="tooltip" data-placement="top" title="Send notification" hidden="true" id="btnSendNoti"><span class="glyphicon glyphicon-phone"></span></button>
     </h3>
   </div>
 </div><!-- /.row -->
 <h4 id="panelTitle">Predicted Location</h4>
 <table class="table table-hover">
  <tbody>
    <tr>
      <td onclick="search()">All</td>
    </tr>
    @for ($i = 0; $i < 24; $i++)
    <tr>
      <td onclick="showSpecificMarkers({{$i}})">2014/11/01.{{ $i }}:24:50 </td>
    </tr>
    @endfor
  </tbody>
</table>
</div><!--/predictPanel -->

<div id="addPanel" class="col-sm-4 col-md-2" hidden="true">
 <h3>Add New User</h3>
 <hr>
 <!-- <form role="form"> -->
 <div class="form-group">
  <label for="inputUsername">Username</label>
  <input type="text" id="inputUsername" class="form-control" placeholder="Username">
</div>
<div class="form-group">
  <label for="exampleInputFile">Location log</label>
  <input type="file" id="exampleInputFile">
</div>
<hr>
<button type="submit" class="btn btn-primary">Add</button>
<button class="btn btn-default" onclick="search()">Cancel</button>
<!-- </form> -->
</div><!--/addPanel -->

<div id="sendNotiPanel" class="col-sm-4 col-md-2" hidden="true">
 <h4>Send Notification</h4>
 <hr>
 <!-- <form role="form"> -->
 <label for="selectedUsers">Selected Users</label>
 <div id="selectedUsersPanel">
  <p id="selectedUsers">
  </div>
  <div class="form-group">
    <label for="exampleInputFile">Message</label>
    <textarea type="text" id="exampleInputFile" class="form-control" placeholder="Message"></textarea>
  </div>
  <hr>
  <button type="submit" class="btn btn-primary">Send</button>
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
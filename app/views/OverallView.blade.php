<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>EBA</title>
 {{HTML::style('css/bootstrap.min.css');}}
 {{HTML::style('css/overallview.css');}}
 <link rel="stylesheet" href="css/jquery-ui.min.css">
 <script type="text/javascript"
 src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDn7BL_KVNfQo3SlE7QCvRZ3xz84CB2T3U">
 </script>
 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
 <script type="text/javascript">
 var tokyo = new google.maps.LatLng(35.66919, 139.7413805);

 var specificUsers=new Array();
 var markers = new Array();
 var markersUsers=new Array();
 var specificMarkers =new Array();
 var infowindow = null;
 var map;
 var showPredictedLocation = true;
 var sendNotification = false;
 var searchLocation=true;
 var rectangle;
 var timeGroups = new Array();

 function initialize() {
  prepareData();
  // contents = predictedLocationClient;
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
  
  var input = /** @type {HTMLInputElement} */(
    document.getElementById('inputSearchLocation'));
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
  var resultHTML = prepareContentHTML();
  document.getElementById('locationContents').innerHTML = resultHTML;
  showMarkers();

  var usernames=[];
  @foreach($users as $user){
    usernames.push('{{$user->name}}');
  }
  @endforeach
  $("#inputUsername").autocomplete({
    source: usernames
  });  
}

function prepareData(){
  @foreach($timeGroups as $time){
    timeGroups.push({{json_encode($time,JSON_PRETTY_PRINT)}});
  }
  @endforeach
}

function showNewRect(event) {
  var ne = rectangle.getBounds().getNorthEast();
  var sw = rectangle.getBounds().getSouthWest();
  var member="";
  for(var i=0;i<specificMarkers.length;i++){
    var isInRect = specificMarkers[i].getPosition().lat()<=ne.lat()&&
    specificMarkers[i].getPosition().lat()>=sw.lat()&&
    specificMarkers[i].getPosition().lng()<=ne.lng()&&
    specificMarkers[i].getPosition().lng()>=sw.lng();
    if(isInRect){
      if(member!=""){
        member+=",";
      }
      member+=specificUsers[i];
    }
  }
  for(var i=0;i<markers.length;i++){
    var isInRect = markers[i].getPosition().lat()<=ne.lat()&&
    markers[i].getPosition().lat()>=sw.lat()&&
    markers[i].getPosition().lng()<=ne.lng()&&
    markers[i].getPosition().lng()>=sw.lng();
    if(isInRect){
      if(member!=""){
        member+=",";
      }
      member+=markersUsers[i];
    }
  }
  arr =  $.unique(member.split(','));
  member = arr.join(",");
  document.getElementById('selectedUsers').innerHTML = member; 
}

function showMarkers(){
  document.getElementById('predictPanel').hidden=false; 
  document.getElementById('addPanel').hidden=true; 
  document.getElementById('sendNotiPanel').hidden=true;
  var resultHTML = prepareContentHTML();
  document.getElementById('locationContents').innerHTML = resultHTML;
  if(rectangle){
    rectangle.setMap(null);   
  }
  sendNotification=false;
  clearAllMarkers();
  for(var i=0;i<timeGroups.length;i++){
    for(var j=0;j<timeGroups[i].users.length;j++){
      addMarker(timeGroups[i].users[j]);
      markersUsers.push(timeGroups[i].users[j].username);
    }
  }
  if(markers.length!=0){
   map.panTo(markers[0].getPosition()); 
 }
}

function addMarker(userInfo) {
 var location = new google.maps.LatLng(userInfo.latitude, userInfo.longitude);
 var content = userInfo.dateTime;
 var marker = new google.maps.Marker({
  position: location,
  map: map,
  draggable: false
});
 markers.push(marker);
 var username = userInfo.username;
 google.maps.event.addListener(marker, 'click', function() {
  setInfoWindow(content,username);
  infowindow.open(map,marker);
});
}

function showSpecificMarkers(row){
  clearAllMarkers();
  for(var i=0;i<timeGroups[row].users.length;i++){
    addSpecificMarkers(timeGroups[row].users[i]);
    specificUsers.push(timeGroups[row].users[i].username);
  }
  map.panTo(specificMarkers[0].getPosition());
}

function addSpecificMarkers(userInfo){
  var location = new google.maps.LatLng(userInfo.latitude, userInfo.longitude);
  var content = userInfo.dateTime;
  var marker = new google.maps.Marker({
    position: location,
    map: map,
    draggable: false
  });
  specificMarkers.push(marker);
  var username = userInfo.username;
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


function clearAllMarkers() {
  for(var i=0;i<markers.length;i++){
    markers[i].setMap(null);
  }
  markers=[];
  markersUsers=[];
  for(var i=0;i<specificMarkers.length;i++){
    specificMarkers[i].setMap(null);
  }
  specificMarkers =[];
  specificUsers=[];
}

function prepareContentHTML(){
  var text='<tbody>';
  if(timeGroups.length==0){
   text ='<tr>'+
   '<td>No predicted location</td>'+
   '</tr>'+
   '<tbody>';
   return text;
 }  
 text += 
 '<tr>'+
 '<td onclick="showMarkers();">'+
 'All'+
 '</td>'+
 '</tr>';
 for(var i=0;i<timeGroups.length;i++){
   text += 
   '<tr>'+
   '<td onclick="showSpecificMarkers('+i+')">'+
        // '<div class="checkbox">'+
          // '<label>'+
            // '<input type="checkbox">'+
            timeGroups[i].dateTime+
          // '</label>'+
        // '</div>'+
        '</td>'+
        '</tr>';
      }
      text += '</tbody>';
      return text;
    }

    function addUser(){
      document.getElementById('predictPanel').hidden=true;  
      document.getElementById('addPanel').hidden=false;
    }

    function cancel(){
      document.getElementById('predictPanel').hidden=false;  
      document.getElementById('addPanel').hidden=true;
    }

    function switchSearch(){
      if(searchLocation){
        searchLocation=false;
        document.getElementById('searchIcon').setAttribute("class","glyphicon glyphicon-user");
        document.getElementById('searchLocation').hidden=true;
        document.getElementById('searchUser').hidden=false;

      }else{
        searchLocation=true;
        document.getElementById('searchIcon').setAttribute("class","glyphicon glyphicon-globe");
        document.getElementById('searchLocation').hidden=false;
        document.getElementById('searchUser').hidden=true;
      }
    }

    function sendNoti(){
      if(sendNotification){
        rectangle.setMap(null); 
        document.getElementById('sendNotiPanel').hidden=true; 
        sendNotification=false;

      }else {
        document.getElementById('sendNotiPanel').hidden=false; 
        var bounds = new google.maps.LatLngBounds(
          new google.maps.LatLng(map.getCenter().lat()-0.01, map.getCenter().lng()-0.02),
          new google.maps.LatLng(map.getCenter().lat()+0.01, map.getCenter().lng()+0.02)
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


google.maps.event.addDomListener(window, 'load', initialize);

</script>
</head>
<body>
  <div id="searchPanel" class="row">
    <div class="col-sm-2">
      <button class="btn btn-default" type="button" onclick="switchSearch()"><span id="searchIcon" class="glyphicon glyphicon-globe"></span></button>
    </div>
    <div class="col-sm-10" id="searchUser" hidden="true">
      {{ Form::open(array('url' => 'searchUser')) }}
      <div class="input-group">
        {{Form::text('username', '', array('class' => 'form-control','id'=>'inputUsername', 'placeholder' => 'Enter a username'))}}
        <span class="input-group-btn">
          {{Form::button( '<span class="glyphicon glyphicon-search"></span>', array('class' => 'btn btn-primary', 'type'=>'submit'))}}
        </span>
      </div><!-- /input-group -->
      {{Form::close()}}
    </div>
    <div class="col-sm-10" id="searchLocation">
      <div class="input-group">
        <input id="inputSearchLocation" type="text" class="form-control" placeholder="Enter a location">
        <span class="input-group-btn">
          <button class="btn btn-primary" type="button" id="btnSearchLocation"><span class="glyphicon glyphicon-search"></span></button>
        </span>
      </div><!-- /input-group -->
    </div>
  </div>

  <div id="predictPanel" class="col-sm-4 col-md-2">
    <div class="row">
      <div class="col-sm-8">
        @if (count($users) <= 1)
        <h3>{{count($users)}} User</h3>
        @else
        <h3>{{count($users)}} Users</h3>
        @endif
      </div>
      <div class="col-sm-4">
        <h3>
         <button type="button" class="icon" onclick="addUser()" data-toggle="tooltip" data-placement="top" title="Add new user"><span class="glyphicon glyphicon-plus-sign"></span></button>
         <button type="button" class="icon" onclick="sendNoti()" data-toggle="tooltip" data-placement="top" title="Push notification" id="btnSendNoti"><span class="glyphicon glyphicon-phone"></span></button>
       </h3>
     </div>
   </div><!-- /.row -->
   <h4 id="panelTitle">Predicted Location</h4>
   <table class="table table-hover" id="locationContents">
   </table>
 </div><!--/predictPanel -->

 <div id="addPanel" class="col-sm-4 col-md-2" hidden="true">
   <h3>Add New User</h3>
   <!-- <form role="form"> -->
   {{ Form::open(array('url' => 'addUser','files'=>true)) }}
   <div class="form-group">
    {{Form::label('username','Username')}}
    {{Form::text('username', '', array('class' => 'form-control', 'placeholder' => 'Username'))}}
  </div>

  <div class="form-group">
   {{Form::label('file','Location log')}}
   {{Form::file('file')}}
 </div>
 {{Form::submit('Add', array('class' => 'btn btn-primary'))}}
 <a class="btn btn-default" onclick="cancel()">Cancel</a>
 {{Form::close()}}
</div><!--/addPanel -->

<div id="sendNotiPanel" class="col-sm-4 col-md-2" hidden="true">
 <h4>Push Notification</h4>
 <label for="selectedUsers">Selected Users</label>
 <div id="selectedUsersPanel">
  <p id="selectedUsers">
  </div>
  <div class="form-group">
    <label for="exampleInputFile">Message</label>
    <textarea type="text" id="exampleInputFile" class="form-control" placeholder="Message"></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Send</button>
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
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
</body>
</html>
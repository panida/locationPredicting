<?php

class OverallController extends Controller {

	public function addUser(){
		// if (Input::hasFile('file'))
		// {
		// 	$file = Input::file('file');
		// 	$filename = $file->getClientOriginalName();
		// 	Input::file('file')->move('upload/', $filename);
		// 	$username = Input::get('username');
		// 	$id=DB::table('person')->insertGetId(array('name' => $username,'personId' => $username));	
		// 	$locationList = LocationLog::extractLocationListFromFile("upload/".$filename,$id);
		// 	return Redirect::to('/'.$id);		
		// }
		$username = Input::get('username');
		$id=DB::table('person')->insertGetId(array('name' => $username,'personId' => $username));
		return Redirect::to('/'.$id);	
	}
	public function showAllPredictedLocation(){
		$predictedLocations = PredictedLocation::getAllPredictedLocation();
		$rows = array();
		$tdateTime = $predictedLocations[0]->dateTime;
		$init = (object) ['dateTime' => $tdateTime,'users' => array()];
		array_push($rows,$init);
		// var_dump($tdateTime);
		// var_dump(date('H', $tdateTime));
		foreach ($predictedLocations as $predictedLocation) {
			$user = Person::find($predictedLocation->personId);
			$userInfo = (object)[
			'username'=>$user->name, 
			'latitude'=>$predictedLocation->latitude,
			'longitude'=>$predictedLocation->longitude,
			'dateTime'=>$predictedLocation->dateTime];
			//var_dump($tdateTime);
			
			//var_dump($tdateTime->diff(strtotime($predictedLocation->dateTime)));

			$interval = strtotime($predictedLocation->dateTime) - strtotime($tdateTime);
			
			if($interval < 3600){
				end($rows);
			 	array_push($rows[key($rows)]->users,$userInfo);
			}
			else{
				$tdateTime = $predictedLocation->dateTime;
				$temp = (object) ['dateTime' => $tdateTime,'users' => array()];
				array_push($temp->users,$userInfo);
				array_push($rows,$temp);	
			 }
		}
		// var_dump($rows[4]);
		 $users = DB::table('person')->get();
		 $usersCount =count($users); 
		// $predictedLocations = array();
		// for($i=0;$i<count($users);$i++){
		// 	$predictedLocation = array();
		// 	$tpredictedLocation = PredictedLocation::getPredictedLocationByPerson2($users[$i]->id);
		// 	for($j=0;$j<count($tpredictedLocation);$j++){
		// 		array_push($predictedLocation, $tpredictedLocation[$j]);
		// 	}
		// 	array_push($predictedLocations, $predictedLocation);
		// }
		// return View::make('OverallView',array('users'=>$users,'predictedLocations'=>$predictedLocations));
		return View::make('OverallView',array('timeGroups'=>$rows,'usersCount'=>$usersCount));
	}

	public function searchUser(){
		$username = Input::get('username');
		$person = DB::table('person')->where('name', $username)->first();
		if ($person){
			return Redirect::to('/'.$person->id);
		}
		else{
			return Redirect::to('/');
		}
	}

}
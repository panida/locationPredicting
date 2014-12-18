<?php

class OverallController extends Controller {

	public function addUser(){
		if (Input::hasFile('file'))
		{
			$file = Input::file('file');
			$filename = $file->getClientOriginalName();
			Input::file('file')->move('upload/', $filename);
			$username = Input::get('username');
			$id=DB::table('person')->insertGetId(array('name' => $username,'personId' => $username));	
			$locationList = LocationLog::extractLocationListFromFile("upload/".$filename,$id);
			$locationLog = LocationLog::getLocationLogByPerson($id);
			$predictedLocationList = PredictionAlgorithm::predict($locationLog);
			$lastestDate = $locationLog[0]->dateTime;
			PredictedLocation::storePredictedData($predictedLocationList,$id,$lastestDate);
			return Redirect::to('/'.$id);		
		}
		// $username = Input::get('username');
		// $id=DB::table('person')->insertGetId(array('name' => $username,'personId' => $username));
		return Redirect::to('/');	
	}
	public function showAllPredictedLocation(){
		$predictedLocations = PredictedLocation::getAllPredictedLocation();
		$rows = array();
		$users = Person::getAllUsers();
		$usersCount =count($users); 
		if(count($predictedLocations)>0){
			$tdateTime = $predictedLocations[0]->dateTime;
			$init = (object) ['dateTime' => $tdateTime,'users' => array()];
			array_push($rows,$init);
			foreach ($predictedLocations as $predictedLocation) {
				$user = Person::find($predictedLocation->personId);
				$userInfo = (object)[
				'username'=>$user->name, 
				'latitude'=>$predictedLocation->latitude,
				'longitude'=>$predictedLocation->longitude,
				'dateTime'=>$predictedLocation->dateTime];
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
		}
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
<?php

class PersonController extends Controller {

	public function importLocationLog($personId){
		//$file_max = ini_get('upload_max_filesize');
		//try{
			// $file = Input::file('locationList');
			// $filename = $file->getClientOriginalName();
			// Input::file('locationList')->move('img/upload/', $filename);
			// $locationList = LocationLog::extractLocationListFromFile("upload/".$filename);
			$person = new Person;
			$locationList = LocationLog::extractLocationListFromFile("upload/location1.txt", $personId);
			return Redirect::to('/');		
		// }
		// catch(Exception $e){
		// 	return Redirect::to('blankpage');
		// }
	}

	public function showInfo($personId){
		$person = Person::find($personId);
		$locationLog = LocationLog::getLocationLogByPerson($personId);
		$predictedLocation = PredictedLocation::getPredictedLocationByPerson($personId);
		return View::make('PersonView',array('person'=>$person,'locationLog'=>$locationLog, 'predictedLocation'=>$predictedLocation));
	}

}

<?php

class PersonController extends Controller {

	public function importLocationLog($personId){
		$file_max = ini_get('upload_max_filesize');
		
			$file = Input::file('inputFile');
			$filename = $file->getClientOriginalName();
			Input::file('inputFile')->move('upload/', $personId.'_'.$filename);
			DB::table('locationLogs')->delete();
			
			$locationList = LocationLog::extractLocationListFromFile("upload/".$personId.'_'.$filename,$personId);
			$locationLog = LocationLog::getLocationLogByPerson($personId);
			$predictedLocationList = PredictionAlgorithm::predict($locationLog);
			//PredictedLocation::storePredictedData($predictedLocationList);
			var_dump($predictedLocationList);
			//return View::make('blank_page');
			return Redirect::to('/'.$personId);		
		
	}

	public function showInfo($personId){
		$person = DB::table('person')->where('personId', $personId)->first();
		$locationLog = LocationLog::getLocationLogByPerson($person->id);
		$lastestDate = $locationLog[count($locationLog)-1]->dateTime;
		$predictedLocation = PredictedLocation::getPredictedLocationByPerson($person->id, $lastestDate);
		return View::make('PersonView',array('person'=>$person,'locationLog'=>$locationLog, 'predictedLocation'=>$predictedLocation));
	}
	public function deleteUser(){
		$id = Input::get('id');
		LocationLog::where('personId', $id)->delete();
		PredictedLocation::where('personId', $id)->delete();
		$person = Person::find($id);
		$person->delete();
		return Redirect::to('/');
	}
}

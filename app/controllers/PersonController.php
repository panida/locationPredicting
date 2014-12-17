<?php

class PersonController extends Controller {

	public function importLocationLog($personId){
		$file_max = ini_get('upload_max_filesize');
		try{
			$file = Input::file('inputFile');
			$filename = $file->getClientOriginalName();
			Input::file('inputFile')->move('upload/', $personId.'_'.$filename);
			$locationList = LocationLog::extractLocationListFromFile("upload/".$personId.'_'.$filename,$personId);
			return Redirect::to('/'.$personId);		
		}
		catch(Exception $e){
			return Redirect::to('/'.$personId);
		}
	}

	public function showInfo($personId){
		$person = DB::table('person')->where('personId', $personId)->first();
		$locationLog = LocationLog::getLocationLogByPerson($personId);
		$predictedLocation = PredictedLocation::getPredictedLocationByPerson($personId);
		return View::make('PersonView',array('person'=>$person,'locationLog'=>$locationLog, 'predictedLocation'=>$predictedLocation));
	}

}

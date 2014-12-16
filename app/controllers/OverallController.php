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
			return Redirect::to('/');		
		 }
	}

	public function showInfo($personId){
		$person = Person::find($personId);
		$locationLog = LocationLog::getLocationLogByPerson($personId);
		$predictedLocation = PredictedLocation::getPredictedLocationByPerson($personId);
		return View::make('PersonView',array('person'=>$person,'locationLog'=>$locationLog, 'predictedLocation'=>$predictedLocation));
	}

	public function showAllUsers(){
		
	}
}
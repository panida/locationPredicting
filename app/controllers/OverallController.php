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
	public function showAllUsers(){
		$users = DB::table('person')->get();
		$predictedLocations = array();
		foreach ($users as $user)
		{
			// need to change to real predictedLocation
			$predictedLocation = array();
			$tpredictedLocation = LocationLog::getLocationLogByPerson($user->id);
			for($i=count($tpredictedLocation)-24;$i<count($tpredictedLocation);$i++){
				array_push($predictedLocation, $tpredictedLocation);	
			}
			array_push($predictedLocations, $predictedLocation);
		}
		return View::make('OverallView',array('users'=>$users,'predictedLocations'=>$predictedLocation));
	}

	public function searchUser(){
		$username = Input::get('username');
		$person = DB::table('person')->where('name', $username)->first();
		if ($person){
			return Redirect::to('/'.$person->personId);
		}
		else{
			return Redirect::to('/');
		}
	}

}
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
	public function showAllUsers(){
		$users = DB::table('person')->get();
		$predictedLocations = array();
		for($i=0;$i<count($users);$i++){
			// need to change to real predictedLocation
			$predictedLocation = array();
			$tpredictedLocation = PredictedLocation::getPredictedLocationByPerson2($users[$i]->id);
			for($j=0;$j<count($tpredictedLocation);$j++){
				array_push($predictedLocation, $tpredictedLocation[$j]);
			}
			array_push($predictedLocations, $predictedLocation);
		}
		return View::make('OverallView',array('users'=>$users,'predictedLocations'=>$predictedLocations));
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
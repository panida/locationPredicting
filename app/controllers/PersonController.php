<?php

class PersonController extends Controller {

	public function importLocationLog($personId){
		//$file_max = ini_get('upload_max_filesize');
		//try{
			// $file = Input::file('locationList');
			// $filename = $file->getClientOriginalName();
			// Input::file('locationList')->move('img/upload/', $filename);
			// $locationList = LocationLog::extractLocationListFromFile("upload/".$filename);
			$locationList = LocationLog::extractLocationListFromFile("upload/location1.txt", $personId);
			var_dump($locationList);
			return Redirect::to('/');		
		// }
		// catch(Exception $e){
		// 	return Redirect::to('blankpage');
		// }
	}

}

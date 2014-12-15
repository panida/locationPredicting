<?php
class LocationLog extends Eloquent{
	protected $table = 'LocationLog';
	public $timestamps = flase;
	public $errors;

	public function person(){
		return $this->belongsTo('Person','personId','id');
	}

	public static function extractLocationListFromFile($path, $personId){
		$locations = array();
		$file = fopen($path, 'r');
		while (!feof($file)) {
			$line = fgets($file);
			$elements = explode(",", $line);
			if(count($elements)>=3){
				$logs = array(
					'date' => $elements[0],
					'latitude' => $elements[1],
					'longitude' => $elements[2]
					);
				array_push($locations, $logs);
				storeLocation($logs);
			}
			
		}
		fclose($file);
		return $locations;
	}

	public static storeLocation($logs){
		$location = new LocationLog;
		$location->latitude = $logs['latitude'];
		$location->longitude = $logs['longitude'];
		$location->dateTime = new 
	}


}
<?php
class LocationLog extends Eloquent{
	protected $table = 'locationLogs';
	public $timestamps = false;
	

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
				LocationLog::storeLocation($logs, $personId);
			}
		}
		fclose($file);
		return $locations;
	}

	public static function storeLocation($logs, $personId){
		$location = new LocationLog;
		$location->latitude = $logs['latitude'];
		$location->longitude = $logs['longitude'];
		$location->dateTime = Carbon::createFromFormat('Y/m/d.H:i:s', $logs['date']);
		$location->personId = $personId;
		$location->save();
		return $location->id;
	}

	public static function getLocationLogByPerson($personId){
		return DB::table('locationLogs')->where('personId', $personId)->orderBy('dateTime','desc')->get();
	}
}
?>
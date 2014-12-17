<?php

class PredictedLocation extends Eloquent{
	protected $table = 'predictedLocation';

	public $timestamps = false;
	

	public function person(){
		return $this->belongsTo('Person','personId','id');
	}

	public static function getPredictedLocationByPerson($personId,$currentDate){
		return DB::table('predictedLocation')->where('personId', '=', $personId)->where('dateTime','>=', new DateTime($currentDate))->orderBy('dateTime')->get();
	}

	public static function storePredictedData($predictedLocationList, $personId, $lastestDate){
		var_dump($lastestDate);
		$tempDateTime = Carbon::createFromFormat('Y-m-d H:m:i', $lastestDate);
		foreach ($predictedLocationList as $predictedLocation) {
			$location = new PredictedLocation;
			$location->latitude = $predictedLocation->lat;
			$location->longitude = $predictedLocation->lng;
			$newDateTime = $tempDateTime->addHours(intval($predictedLocation->time));
			$location->dateTime = new DateTime($newDateTime->toDateTimeString());
			$location->personId = $personId;
			$location->save();
		}
	}
}
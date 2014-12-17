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

	public static function storePredictedData($predictedLocationList,$personId){
		foreach ($predictedLocationList as $predictedLocation) {
			$location = new PredictedLocation;
			$location->latitude = $predictedLocation->lat;
			$location->longitude = $predictedLocation->lng;

		}
	}
}
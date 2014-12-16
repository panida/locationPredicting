<?php

class PredictedLocation extends Eloquent{
	protected $table = 'predictedLocation';

	public $timestamps = false;
	

	public function person(){
		return $this->belongsTo('Person','personId','id');
	}

	public static function getPredictedLocationByPerson($personId){
		return DB::table('predictedLocation')->where('personId', '=', $personId)->where('dateTime','>=', new DateTime('today'))->orderBy('dateTime')->get();
	}
}
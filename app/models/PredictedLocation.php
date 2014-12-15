<?php
class PredictedLocation extends Eloquent{
	protected $table = 'predictedlocation';
	public $timestamps = flase;
	public $errors;

	public function person(){
		return $this->belongsTo('Person','personId','id');
	}


}
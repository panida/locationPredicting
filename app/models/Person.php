<?php
class Person extends Eloquent{
	protected $table = 'person';
	public $timestamps = false;
	protected $fillable = array('personId','name');


}
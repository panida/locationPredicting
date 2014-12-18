<?php
class Person extends Eloquent{
	protected $table = 'person';
	public $timestamps = false;
	protected $fillable = array('personId','name');

	public static function getAllUsers(){
		return $users = DB::table('person')->get();
	}
}
<?php
Class Location{

	private static $NO_DESTINATION = -1;
	public $lat;
	public $lng;
	public $time;
	public $id;
	public $person_id;
	public $destination_cluster_id = -1;
	public $parent_cluster_id = -1;
	public $dateTime;
}
?>
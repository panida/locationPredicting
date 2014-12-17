<?php
Class Cluster{
	private static $UNKNOWN = 0;
	private static $ROUTE = -1;
	private static $PLACE = -2;
	
	public $location_list=array();
	public $type = 0;
	public $cluster_id = -1;
	public $lat;
	public $lng;


}
?>
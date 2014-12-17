<?php
Class PredictionAlgorithm{

	//constant from paper
	public static $VARIANCE_LAT = 0.00002017;
	public static $VARIANCE_LNG = 0.00003082;
	public static $VARIANCE_TIME = 4.0;
	public static $K_TIME = 0.0004;
	public static $DIST_THRESHOLD = 0.0025;

	//developer constant
	public static $TIME_DESTINATION_THRESHOLD = 24;

	//for predicting 24h
	public static $MAX_PREDICTING_TIME = 24;
	public static $PREDICTING_TIME_INTERVAL = 1.0;
	public static $TIME_24H_DESTINATION_THRESHOLD = 0.5;

	public static $location_id_counter = 0;
	public static $cluster_id_counter = 0;

	public static function preProcessLocationInput($locationLogsInput){
		global $location_id_counter, $cluster_id_counter;
		$output = array();
		foreach ($locationLogsInput as $locationLog){
			$temp = new Location;
			$temp->lat = $locationLog->latitude;
			$temp->lng = $locationLog->longitude;
			$temp->time = intval(date_format(new DateTime($locationLog->dateTime), 'H'))+(intval(date_format(new DateTime($locationLog->dateTime), 'i'))/60.0);
			$temp->id = $location_id_counter;
			$temp->dateTime = new DateTime($locationLog->dateTime);
			array_push($output, $temp);
			$location_id_counter = $location_id_counter+1;
		}
		return $output;

	}

	///-------------------- clustering and define destination part
	public static function isClosedLocation($sLocation, $location2) {
		$temp1 = null;
		$temp2 = null;
		if ( $sLocation->time > $location2->time ) {
			$temp1 = $sLocation;
			$temp2 = $location2;
		}
		else {
			$temp1 = $location2;
			$temp2 = $sLocation;
		}
		$dLat = $temp1->lat - $temp2->lat;
		$dLng = $temp1->lng - $temp2->lng;
		$dTime = min($temp1->time - $temp2->time, 24 - $temp1->time + $temp2->time);
		$dist = $dLat*$dLat + $dLng*$dLng + PredictionAlgorithm::$K_TIME*PredictionAlgorithm::$K_TIME*$dTime*$dTime;
		if ( $dist < PredictionAlgorithm::$DIST_THRESHOLD ) {
			return true;
		}
		else {
			return false;
		}
	}
	public static function generateDestinationRef(&$location_list) {
		for ( $i = 0; $i < count($location_list) - 1; $i+=1 ) {
			if ( abs($location_list[$i]->time - $location_list[$i + 1]->time) <= PredictionAlgorithm::$TIME_DESTINATION_THRESHOLD )
				$location_list[$i]->destination_cluster_id = $location_list[$i + 1]->parent_cluster_id;
		}
	}
	public static function calClusterLocation(&$cluster_list) {
		for ( $i = 0; $i < count($cluster_list); $i=$i+1 ) {
			$lat = 0;
			$lng = 0;
			for ( $j = 0; $j < count($cluster_list[$i]->location_list); $j++ ) {
				$lat += $cluster_list[$i]->location_list[$j]->lat;
				$lng += $cluster_list[$i]->location_list[$j]->lng;
			}
			$lat /= count($cluster_list[$i]->location_list);
			$lng /= count($cluster_list[$i]->location_list);
			$cluster_list[$i]->lat = $lat;
			$cluster_list[$i]->lng = $lng;
		}
	}


	public static function calClusterDistribution(&$cluster_list, &$average, &$variance, &$place_threshold) {
		$average = 0;
		for ( $i = 0; $i < count($cluster_list); $i=$i+1 ) {
			$average += count($cluster_list[$i]->location_list);
		}
		$average /= count($cluster_list);

		$variance = 0;
		for ( $i = 0; $i < count($cluster_list); $i=$i+1 ) {
			$diff = count($cluster_list[$i]->location_list) - $average;
			$variance += $diff*$diff;
		}
		$variance /= count($cluster_list);
		$variance /= count($cluster_list);
		//out << "average: " << average << "\tvariance: " << variance << "\n";
		//placeThreshold = average + sqrt(variance)/2;
		//place_threshold = PLACE_THRESHOLD_SIZE;
	}

	public static function clustering(&$location_list,&$cluster_list) {
		global $cluster_id_counter;
		if (count($location_list)==0) return;
		$location_status=array();
		for ($i = 0; $i < count($location_list); $i=$i+1)
		{
	    	array_push($location_status, 0);
		}
		$new_cluster_list=array();
		$cluster_id_counter = 0;
		$location_id_counter = 0;
		for($i = 0; $i < count($location_list); $i=$i+1){
			if ( $location_status[$i] != 0 ) {
				continue;
			}
			$temp = $location_list[$i];
			$location_status[$i] = 1;
			$location_list[$i]->parent_cluster_id = $cluster_id_counter;
			$cluster = new Cluster;
			array_push($cluster->location_list, $location_list[$i]);
			
			$QLocation = new SplQueue();
			$QLocation->enqueue($temp);
			while ( !$QLocation->isEmpty()) {
				$sLocation = $QLocation->dequeue();
				for ( $j = 0; $j < count($location_list); $j=$j+1 ) {
					if ( $location_status[$j] != 0 ) {
						continue;
					}
					if ( PredictionAlgorithm::isClosedLocation($sLocation, $location_list[$j]) ) {
						$QLocation->enqueue($location_list[$j]);
						$location_status[$j] = 1;
						$location_list[$j]->parent_cluster_id = $cluster_id_counter;
						array_push($cluster->location_list,$location_list[$j]);
					}
				}
			}
			$cluster->cluster_id = $cluster_id_counter;
			$cluster_id_counter = $cluster_id_counter+1;
			array_push($new_cluster_list, $cluster);
		}
		$cluster_list = $new_cluster_list;
	}

	///-------------------- predicting part
	public static function calDestinationScore($current_location, $location) {
		$score = 0;
		$diff_lat = $current_location->lat - $location->lat;
		$diff_lng = $current_location->lng - $location->lng;
		$diff_time = $current_location->time - $location->time;
		if ( abs($current_location->time - $location->time) > 12.0 && $current_location->time > $location->time ) {
			$diff_time -= 12.0;
		}
		else if ( abs($current_location->time - $location->time) > 12.0 && $current_location->time <= $location->time ) {
			$diff_time += 12.0;
		}
		$score = 1.0 / ((2 * pi())* sqrt(2 * pi()))/ sqrt(PredictionAlgorithm::$VARIANCE_LAT + PredictionAlgorithm::$VARIANCE_LNG + PredictionAlgorithm::$VARIANCE_TIME) * exp(-1.0 / 2.0 * (($diff_lat*$diff_lat*PredictionAlgorithm::$VARIANCE_LAT) + ($diff_lng*$diff_lng*PredictionAlgorithm::$VARIANCE_LNG) + ($diff_time*$diff_time*PredictionAlgorithm::$VARIANCE_TIME)));
		return $score;
	}

	public static function predictNextLocation($current_location, $cluster_list, $location_list) {
		//echo '+++++'.$current_location->parent_cluster_id;
		$current_cluster = $cluster_list[$current_location->parent_cluster_id];
		$score_of_place = array();
		for ($i = 0; $i < count($cluster_list); $i=$i+1)
		{
	    	array_push($score_of_place, 0);
		}
		for ( $i = 0; $i < count($current_cluster->location_list); $i=$i+1 ) {
			$dest_cluster_id = $current_cluster->location_list[$i]->destination_cluster_id;
			if ( $dest_cluster_id < 0 ) {
				continue;
			}
			$score = PredictionAlgorithm::calDestinationScore($current_location, $current_cluster->location_list[$i]);
			$score_of_place[$dest_cluster_id] += $score;
		}
		$maxScore = 0;
		$clusterID = -1;
		for ( $i = 0; $i < count($score_of_place); $i=$i+1 ) {
			
			if ( $score_of_place[$i] > $maxScore ) {
				$maxScore = $score_of_place[$i];
				$clusterID = $i;
			}
		}
		return $clusterID;
	}

	//-------------------- add new location and evaluating the accuracy part
	public static function reExecute(&$location_list, &$cluster_list, &$average, &$variance, &$place_threshold) {
		PredictionAlgorithm::clustering($location_list, $cluster_list);
		
		PredictionAlgorithm::calClusterDistribution($cluster_list, $average, $variance, $place_threshold);
		
		PredictionAlgorithm::calClusterLocation($cluster_list);
		
		PredictionAlgorithm::generateDestinationRef($location_list);
		
	}

	public static function isClosedTime($time1, $time2, $time_threshold) {
		$diff = abs($time1 - $time2);
		if ( $diff >= 12 )
			$diff = 24 - $diff;
		if($diff <= $time_threshold){
			return true;
		}
		else return false;
	}

	/// my stupid algorithm (=_=)
	public static function predictNext24hLocation($current_location, $cluster_list, $location_list, &$predicted_location_list) {
		global $location_id_counter, $cluster_id_counter;
		$newList=array();
		$predicted_location_list = $newList;
		//echo "-----------";
		$cluster_num = PredictionAlgorithm::predictNextLocation($current_location, $cluster_list, $location_list);
		$time_count = PredictionAlgorithm::$PREDICTING_TIME_INTERVAL;
		$time_temp = PredictionAlgorithm::$PREDICTING_TIME_INTERVAL;
		//echo $cluster_num." ".$time_count.' '.$time_temp;
		while ( $time_count <= 24 ) {
			if ( $cluster_num == -1 ) {
				break;
			}
			$is_possible_time = false;
			$predicted_time = $current_location->time + $time_temp;
			for ( $i = 0; $i < count($cluster_list[$cluster_num]->location_list); $i=$i+1 ) {
				if ( PredictionAlgorithm::isClosedTime($predicted_time, $cluster_list[$cluster_num]->location_list[$i]->time, PredictionAlgorithm::$TIME_24H_DESTINATION_THRESHOLD) ) {
					$is_possible_time = true;
					break;
				}
			}
			if ($is_possible_time ) {
				/// create new predicted_location and use it as current_location for next predicting
				$current_location = new Location;
				$current_location->lat = $cluster_list[$cluster_num]->lat;
				$current_location->lng = $cluster_list[$cluster_num]->lng;
				$current_location->time = $predicted_time;
				$current_location->id = $location_id_counter;
				$location_id_counter=$location_id_counter+1;
				$current_location->parent_cluster_id = $cluster_num;
				//echo $current_location;
				array_push($predicted_location_list,$current_location);
				/// calculate next predicted location
				$cluster_num = PredictionAlgorithm::predictNextLocation($current_location, $cluster_list, $location_list);
				$time_count = $time_count + 2;
				/// reset time_temp for next prediction
				$time_temp = 2;
			}
			else {
				$time_count = $time_count + 2;
				$time_temp = $time_temp + 2;
			}
		}
		// echo '<pre>';
		// var_dump($predicted_location_list);
		// echo '</pre>';
	}

	public static function Predict($locationLogsInput){
		global $location_id_counter, $cluster_id_counter;
		$location_id_counter = 0;
		$cluster_id_counter = 0;
		
		$cluster_list = array();
		$predicted_location = new Location;

		$average = 0;
		$variance = 0;
		$place_threshold = 0;

		$location_list = PredictionAlgorithm::preProcessLocationInput($locationLogsInput);
		PredictionAlgorithm::reExecute($location_list, $cluster_list, $average, $variance, $place_threshold);
		// echo '<pre>';
		// var_dump($cluster_list);
		// echo '</pre>';
		PredictionAlgorithm::predictNext24hLocation($location_list[0], $cluster_list, $location_list, $predicted_location_list);
		//var_dump($predicted_location_list);
		return $predicted_location_list;
		/// predict next location
		// $predicted_next_cluster_id = predictNextLocation($location_list[count($location_list) - 1], $cluster_list, $location_list);
		// $predicted_location = new Location;
		// $predicted_location->lat = $cluster_list[$predicted_next_cluster_id]->lat;
		// $predicted_location->lng = $cluster_list[$predicted_next_cluster_id]->lng;
		// $predicted_location->time =$location_list[count($location_list) - 1]->time + 1
		// $predicted_location->id = $location_id_counter;
		// $location_id_counter=$location_id_counter+1;
		// $predicted_location->parent_cluster_id = $predicted_next_cluster_id;
		// echo $predicted_location->lat.", ".$predicted_location->lat;
		// return $predicted_location;
		/// test by increasing
		// file_test.open(FILE_TEST);
		// vector<Location> test_location_list;
		// ReadFileLocation(file_test, test_location_list);
		// addTestList(location_list, cluster_list, average, variance, place_threshold, predicted_location, test_location_list);

		// out.close();
	}
}
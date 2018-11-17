<?php
	include_once $_SERVER["DOCUMENT_ROOT"] . 'functions.php';
	include_once $_SERVER["DOCUMENT_ROOT"] . 'config/config.php';

	//############## GET TIME SERIES #############################################

	function getTimeSeriesData($mysqli, $bID, $mStart = NULL, $mEnd = NULL)
	{
		// Error Check Start and End
		if(!is_null($mStart) && !is_null($mEnd) && $mStart>=$mEnd) {
			//could just swap them around if not equal?
			echo "Start date cannot be after or the same as end date<br>";
			return 0;
		}

		// Get Crime Types from table.
		$CTR = mysqli_query($mysqli, "SELECT `crime_type` FROM `data_crimes`");
		$crime_types = array();
		while($row = mysqli_fetch_assoc($CTR)) {
			$crime_types[] = $row['crime_type'];
		}

		// Add 1 to Requests
		$addQ = "UPDATE `box` SET `requests` = `requests` + 1 WHERE `id` = $bID";
		$addR = mysqli_query($mysqli, $addQ);

		// Build a Smart Query
		$TSQ = "SELECT * FROM `box_month` WHERE `bm_boxid` = $bID";
		if(!is_null($mStart)) {
			$TSQ = $TSQ." AND `bm_month` > $mStart";
		}
		if(!is_null($mEnd)) {
			$TSQ = $TSQ." AND `bm_month` <= $mEnd";
		}
		$TSQ = $TSQ." ORDER BY `bm_month` ASC";

		// Return Time Series Query
		$TSR = mysqli_query($mysqli, $TSQ);

		// Fetch results into ChartData class
		$out = new ChartData();
		$out->type = 'line';
		$out->legend = true;
		$out->toolTips = true;
		$out->autoSkipX = true;

		$xLabels = array();
		$sets = array();
		while($row = mysqli_fetch_assoc($TSR)) {
			$xLabels[] = $row['bm_month'];
			foreach(array_keys($row) as $type) {
				$sets[$type][] = $row[$type];
			}
		}
		$out->labels = $xLabels;

		// Get counts
		foreach($sets as $type => $counts) {
			if(in_array($type, $crime_types)) { // This filters out crime_types not in data_crimes
				$counts = convertToPC($counts); //convert to percent (delta-percent over time)
				$out->addDataset($counts, $type, getChartColours($type));
			}
		}

		return $out->getData();
	}

	// Converts a number array into a percent change from index to index
	function convertToPC($counts) {
		$out = array();
		foreach($counts as $key=>$count) {
			$pc = NULL;
			if(!is_null($counts[$key]) && !is_null($counts[$key-1])) {
				$pc = (($counts[$key]-$counts[$key-1])/$counts[$key-1])*100;
			}
			$out[$key] = $pc;
		}
		return $out;
	}


	// ################# MAIN ############################################
	// A request from a device to get timeseries information
	function timeSeriesRequest($lat, $long) {
		global $mysqli;

		$nearestBox = getBoxByLoc($lat, $long);

		// Get the time series data
		$data = getTimeSeriesData($mysqli, $nearestBox);

		// Return data
		return $data;
	}
?>

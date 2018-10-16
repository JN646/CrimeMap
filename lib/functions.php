<?php
include_once("classes.php");
include_once '../config/config.php';

//############## FUNCTION FILE #################################################
//############## Version Number ################################################
class ApplicationVersion
{
    // Define version numbering
    const MAJOR = 0;
    const MINOR = 0;
    const PATCH = 0;

    public static function get()
    {
        // Prepare git information to form version number.
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        // Get date and time information.
        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));

        // Format all information into a version identifier.
        return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('Y-m-d H:m:s'));
    }

    // Usage: echo 'MyApplication ' . ApplicationVersion::get();
}

//############## INIT VALUE ####################################################
// Debug
$radVal1 = $radVal2 = $n = $mode = 0;
$JSONEnable = "TRUE";

//############## CALL STATS #########################################################

function callStat($mysqli, $stat) {
	// SELECT All
	$query = "SELECT count FROM stats WHERE stat = $stat";
	$result = mysqli_query($mysqli, $query);
	$rows = mysqli_fetch_row($result);

	// If Error
	if (!$rows) {
		//no stat?
		// it could be that the stat hasn't been defined, or the name is wrong, or the cron job hasn't been run yet
		//die('<p class="SQLError">Could not run query: ' . mysqli_error($mysqli) . '</p>');
		return "Stat does not exist:<br>Contact your system administrator";
	}

	// Free Query
	mysqli_free_result($result);

	// Return Value.
	return $rows[0];
}



//############## Get Colours for Charts ###############################################
   
function getChartColours($crimes) {
   	// Fetch Global Array
   	global $CRIME_COLOURS;
   	
   	// If not array, make it so
	if(!is_array($crimes)) {
		$crimes = [$crimes];
	}
   	
   	$orderedColours = array();
   	foreach($crimes as $key => $crime) {
   		// Should search to see if key exists first?
   		$orderedColours[] = $CRIME_COLOURS[$crime];
   	}
   	
   	return $orderedColours;
}

//############## GET NEAREST BOX #############################################
function getBoxByLoc($mysqli, $lat, $long) {
	// Find Some Nearby Boxes
	$t = 0.2; //threshold in radians
	$boxesQ = "SELECT * FROM `box`
		WHERE `longitude` > ($long-$t)
       	AND `longitude` < ($long+$t)
       	AND `latitude` > ($lat-$t)
       	AND `latitude` < ($lat+$t)";
	$boxesR = mysqli_query($mysqli, $boxesQ);
	
	if(!mysqli_fetch_assoc($boxesR)) {
		echo "Error: No nearby regions (boxes) found. Please make your way towards the UK (barr Soctland).<br>";
		return NULL;
	}
	
	// Calculate Nearest From Nearby Boxes
	$distance = [];
	while($row = mysqli_fetch_assoc($boxesR)) {
		$distance[$row['id']] = computeArcDistance($lat, $long, $row['latitude'], $row['longitude']);
	}
	$nearestBox = array_keys($distance, min($distance))[0];
	return $nearestBox;
}


//############## SPHERICAL GEOMETRY #############################################

function computeOffset($from, $distance, $heading) {
	global $EARTH_RADIUS;
	$distance /= 6371000; //MathUtil::EARTH_RADIUS; //calculates fraction of unit circle. Can we call this constant from somewhere?
	$heading = deg2rad($heading);
	// http://williams.best.vwh.net/avform.htm#LL
	$fromLat = deg2rad($from['lat']);
	$fromLng = deg2rad($from['lng']);
	$cosDistance = cos($distance);
	$sinDistance = sin($distance);
	$sinFromLat = sin($fromLat);
	$cosFromLat = cos($fromLat);
	$sinLat = $cosDistance * $sinFromLat + $sinDistance * $cosFromLat * cos($heading);
	$dLng = atan2($sinDistance * $cosFromLat * sin($heading),
		$cosDistance - $sinFromLat * $sinLat);
	return ['lat' => rad2deg(asin($sinLat)), 'lng' =>rad2deg($fromLng + $dLng)];
}

function computeArcDistance($latitude1, $longitude1, $latitude2, $longitude2) {
	global $EARTH_RADIUS;
	$earth_radius = 6371000;

	$dLat = deg2rad($latitude2 - $latitude1);
	$dLon = deg2rad($longitude2 - $longitude1);

	$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
	$c = 2 * asin(sqrt($a));
	$d = $earth_radius * $c;

	return $d;
}


//############## CONVERT MONTHS ################################################

function dateAsInt($date) {
	$ym = explode("-", $date); //year|month array
	$epoch = 2015; //year 0
	return (($ym[0]-$epoch)*12) + $ym[1];
}

function intAsDate($int) {
	$epoch = 2015; //year 0
	$month = ($int % 12) + 1;
	if($month<=9) {
		$month = "0".$month;
	}
	$year = floor($int/12)+$epoch;
	return $year."-".$month;
}


//############## REPORT HEADER #################################################
function reportHeader($latVal, $longVal) {
  $link = "https://www.google.com/maps/@".$latVal.",".$longVal.",15z";
  ?>
  <!-- Table -->
  <table class='table col-md-6'>
    <tbody>
      <tr>
        <td><b>Location:</b></td>
        <td><a href="<?php echo $link ?>" target="_blank"><?php echo round($latVal, 4) ?>, <?php echo round($longVal, 4) ?></a></td>
      </tr>
      <tr>
        <td><b>Generated:</b></td>
        <td><?php echo date("Y-m-d H:i:s") ?></td>
      </tr>
    </tbody>
  </table>
  <?php
}

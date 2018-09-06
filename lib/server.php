<?php
//############## SERVER FILE ###################################################

// Get Database Config
include_once '../config/config.php';
include_once '../lib/functions.php';
?>

<!-- Stylesheet -->
<link rel="stylesheet" href="../css/basic.css">

<?php
// Flags
$failFlag = 0;

// Check Empty.
if (!empty($_POST["long"])) {
  $longVal    = trim($_POST["long"]);;
} else {
  echo "<p>Long is missing.</p>";
  $longVal = 0;
  $failFlag = 1;
}

if (!empty($_POST["lat"])) {
  $latVal     = trim($_POST["lat"]);
} else {
  echo "<p>Lat is missing.</p>";
  $latVal = 0;
  $failFlag = 1;
}

if (!empty($_POST["rad1"])) {
  $radVal1    = trim($_POST["rad1"]);
} else {
  echo "<p>Rad1 is missing.</p>";
  $radVal1 = 0;
  $failFlag = 1;
}

if (!empty($_POST["rad2"])) {
  $radVal2    = trim($_POST["rad2"]);
} else {
  echo "<p>Rad2 is missing.</p>";
  $radVal2 = 0;
  $failFlag = 1;
}

if (!empty($_POST["month"])) {
  $monthVal   = $_POST["month"];

  // $monthList = implode(", ",$monthVal);
  // $monthSQL = implode(" and ",$monthVal);
} else {
  echo "<p>month is missing.</p>";
  $monthVal = 0;
  $failFlag = 1;
}

if (!empty($_POST["year"])) {
  $yearVal    = trim($_POST["year"]);
} else {
  echo "<p>Year is missing.</p>";
  $yearVal = 0;
  $failFlag = 1;
}

// Store in array
$crimeValues = array($longVal,$latVal,$radVal1,$radVal2,$monthVal,$yearVal);

// Precalculation of ranges
if ($failFlag != 1) {
  // Immediate
  $latLow1    = $latVal - $radVal1;
  $latHigh1   = $latVal + $radVal1;
  $longLow1   = $longVal - $radVal1;
  $longHigh1  = $longVal + $radVal1;

  // Map to array
  $immediateCal = array($latLow1,$latHigh1,$longLow1,$longHigh1);

  // Local
  $latLow2    = $latVal - $radVal2;
  $latHigh2   = $latVal + $radVal2;
  $longLow2   = $longVal - $radVal2;
  $longHigh2  = $longVal + $radVal2;

  // Map to array
  $localCal = array($latLow2,$latHigh2,$longLow2,$longHigh2);
}

// Output Array
if ($failFlag != 1) {
  // Immediate Array
  echo "<h3>Immediate Values</h3>";
  // Calculated Values JSON
  $crimeValObj = new \stdClass();
  $crimeValObj->LowLatitude = $latLow1;
  $crimeValObj->HighLatitude = $latHigh1;
  $crimeValObj->LowLongitude = $longLow1;
  $crimeValObj->HighLongitude = $longHigh1;
  $crimeValObj->Radius1 = $radVal1;

  $crimeImmediate = json_encode($crimeValObj);

  echo $crimeImmediate;

  // Local Array
  echo "<h3>Local Values</h3>";
  // Calculated Values JSON
  $crimeValObj2 = new \stdClass();
  $crimeValObj2->LowLatitude = $latLow2;
  $crimeValObj2->HighLatitude = $latHigh2;
  $crimeValObj2->LowLongitude = $longLow2;
  $crimeValObj2->HighLongitude = $longHigh2;
  $crimeValObj2->Radius2 = $radVal2;

  $crimeLocal = json_encode($crimeValObj2);

  echo $crimeLocal;

  // Run Queries
  $resultCount_Immediate  = sqlCrimeArea($mysqli, $longLow1, $longHigh1, $latLow1, $latHigh1, $latVal, $longVal, $radVal1, $monthVal, $yearVal);
  $resultCount_Local      = sqlCrimeArea($mysqli, $longLow2, $longHigh2, $latLow2, $latHigh2, $latVal, $longVal, $radVal2, $monthVal, $yearVal);

  // Generate Table
  $table = preCalcTable($resultCount_Immediate, $resultCount_Local, $radVal1, $radVal2);
  renderTable($table);
}
//############## MAKE TABLE ####################################################

function preCalcTable($resultCount_Immediate, $resultCount_Local, $radVal1, $radVal2)
{
    $nRows = mysqli_num_rows($resultCount_Local);
    $table = array(array(),array(),array(),array());
    // Fetch Results
    if ($nRows) {
      $j = 0; //table index
      while ($row = mysqli_fetch_assoc($resultCount_Local)) {
          // Set Variables
          $table[$j][0] = $row["Crime_Type"]; //crime type
          $table[$j][1] = 0; //immediate count
          $table[$j][2] = $row["COUNT(id)"]; //local count
          $table[$j][3] = "n/a"; //risk

          // Get Immediate Count
          $row1 = mysqli_fetch_assoc($resultCount_Immediate);
          for ($i=0; $i < count($resultCount_Immediate); $i++) {
              if ($row1["Crime_Type"] == $table[$j][0]) {
                  $table[$j][1] = $row1["COUNT(id)"];
              }
          }
          //calculate risk here...?
          $table[$j][3] = calcRisk($table[$j][1], $table[$j][2], $radVal1, $radVal2);
        $j++;
      }
    } else {
        // No Results
        echo "<p id='noResults'>Something Bad Happened.</p>";
    }

    return $table;
}

function calcRisk($n1, $n2, $r1, $r2) {
  $a1 = PI()*$r1*$r1;
  $a2 = PI()*$r2*$r2;
  $ra1 = $n1/$a1;
  $ra2 = $n2/$a2;
  if($n1 == 0) {
    $c = "n/a";
  } else {
    $c = round(log($ra1/$ra2, 2), 2);
  }
  return $c;
}

function renderTable($table) {
  ?>
    <h2>Crimes Around You</h2>
    <table class='table-border' width=500px>
      <tr>
        <th class='text-center text-bold'>Crime</th>
        <th class='text-center text-bold'>Immediate</th>
        <th class='text-center text-bold'>Local</th>
        <th class='text-center text-bold'>Risk</th>
      </tr>
    <?php
    for ($i=0; $i < count($table); $i++) {
      ?>
      <tr>
        <td><?php echo $table[$i][0] ?></td>
        <td><?php echo $table[$i][1] ?></td>
        <td><?php echo $table[$i][2] ?></td>
        <td><?php echo $table[$i][3] ?></td>
      </tr>
      <?php
      }
    ?>
    </table>
    <?php
}


//############## RUN SQL #######################################################
// SQL Immediate
function sqlCrimeArea($mysqli, $longLow, $longHigh, $latLow, $latHigh, $latVal, $longVal, $radVal, $monthList, $yearVal)
{
    //immediate area
    $sql_immediate = "SELECT COUNT(id), Longitude, Latitude, Crime_Type, Month, Year FROM data
  WHERE Longitude > $longLow AND Longitude < $longHigh AND Latitude > $latLow AND Latitude < $latHigh AND SQRT(POW(Latitude-'$latVal', 2)+POW(Longitude-'$longVal', 2))<'$radVal'
  GROUP BY Crime_Type
  ORDER BY COUNT(id) DESC";

    // Run Query
    $resultCount_Immediate = mysqli_query($mysqli, $sql_immediate);

    // If Error
    if (!$resultCount_Immediate) {
        die('Could not run query: ' . mysqli_error($mysqli));
    }

    // Return
    return $resultCount_Immediate;
}

// SQL Local
// function sqlLocal($mysqli, $longLow2, $longHigh2, $latLow2, $latHigh2, $latVal, $longVal, $radVal2, $monthVal, $yearVal)
// {
//     //local area
//     $sq2_local = "SELECT COUNT(id), Longitude, Latitude, Crime_Type, Month, Year FROM data
//   WHERE Longitude > $longLow2 AND Longitude < $longHigh2 AND Latitude > $latLow2 AND Latitude < $latHigh2 AND SQRT(POW(Latitude-'$latVal', 2)+POW(Longitude-'$longVal', 2))<'$radVal2'
//   -- AND Month='$monthVal'
//   -- AND Year='$yearVal'
//   GROUP BY Crime_Type
//   ORDER BY COUNT(id) DESC";
//
//     // Run Query
//     $resultCount_Local = mysqli_query($mysqli, $sq2_local);
//
//     // If Error
//     if (!$resultCount_Local) {
//         die('Could not run query: ' . mysqli_error($mysqli));
//     }
//
//     // Return
//     return $resultCount_Local;
// }
 ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Crime App</title>
    <link rel="stylesheet" href="css/master.css">
    <?php include 'functions.php' ?>
  </head>
  <body>
    <?php
    // Debug
    $safety = "safe";
     ?>
    <!-- Container -->
    <div class="container">
      <h1>The Crimes</h1>
      <p>Are you at risk of attack?</p>

      <!-- Search Form -->
      <form class="" action="" method="post">
        <label for="">Longitude</label>
        <input type="text" name="long" value="">
        <label for="">Latitude</label>
        <input type="text" name="lat" value="">
        <button type="submit" name="btnSearch">Search</button>
      </form>

      <!-- Risk Notification -->
      <?php if ($safety == "safe") { ?>
        <p class="safe">You might be safe!</p>
      <?php } elseif ($safety == "danger") { ?>
        <p class="danger">You are at risk!</p>
      <?php } ?>

      <?php
      if (isset($_POST["btnSearch"])) {
          $longVal = $_POST["long"];
          $latVal = $_POST["lat"];

          // Get SQL
          $sql = "SELECT id, Longitude, Latitude, Crime_Type FROM data WHERE Longitude='$longVal' OR Latitude='$latVal'  ORDER BY Crime_Type ASC";
          $result = mysqli_query($mysqli, $sql);

          // Fetch Results
          if (mysqli_num_rows($result) > 0) {
              // output data of each row
              while ($row = mysqli_fetch_assoc($result)) {
                  // Set Variables
                  $id = $row["id"];
                  $long = $row["Longitude"];
                  $lat = $row["Latitude"];
                  $crime_type = $row["Crime_Type"];

                  echo "<p class='outputText'><b>Long:</b> " . $long . " <b>Lat:</b> " . $lat . " <b>Crime Type:</b> " . $crime_type . "</p>";
              }
          } else {
              // No Results
              echo "0 results";
          }
      }

       ?>
    </div>
  </body>
</html>

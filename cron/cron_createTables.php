<?php
// Add Database Connection
require_once '../config/config.php';

//############## MAIN ##########################################################
cronCreateStatTable($mysqli);
cronCreateBox($mysqli);

//############## Create Stat Table #############################################
function cronCreateStatTable($mysqli) {
  // SELECT All
  $query = "SELECT id FROM stats";
  $result = mysqli_query($mysqli, $query);

  if(empty($result)) {
    // Create table if doesn't exist.
    $query = "CREATE TABLE IF NOT EXISTS `stats` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Statistic ID',
      `stat` varchar(100) DEFAULT NULL COMMENT 'Statistic Name',
      `count` int(11) DEFAULT '0' COMMENT 'Statistic Count',
      `last_run` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated',
      PRIMARY KEY (`id`)) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1";
    $result = mysqli_query($mysqli, $query);

    // Insert Data.
    $query = "INSERT INTO `stats` (`id`, `stat`, `count`, `last_run`) VALUES
    (1, 'Crime Count', 0, '2018-09-13 22:44:57'),
    (2, 'All Crime Types', 0, '2018-09-13 22:48:15'),
    (3, 'Months worth of data', 0, '2018-09-13 23:01:54'),
    (4, 'Crimes with no location', 0, '2018-09-14 15:52:26'),
    (5, 'Falls Within', 0, '2018-09-14 15:52:27'),
    (6, 'Reported By', 0, '2018-09-14 15:45:42')";
    $result = mysqli_query($mysqli, $query);
  }

  mysqli_free_result($result); // Free Query
}

//############## Create Box ####################################################
function cronCreateBox($mysqli) {
  // SELECT All
  $query = "SELECT id FROM 'box'";
  $result = mysqli_query($mysqli, $query);

  if(empty($result)) {
    // Create table if doesn't exist.
    $query = "CREATE TABLE IF NOT EXISTS `box` (
      `box_id` int(11) NOT NULL AUTO_INCREMENT,
      `box_lat` decimal(9,3) DEFAULT NULL,
      `box_long` decimal(9,3) DEFAULT NULL,
      PRIMARY KEY (`box_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
    $result = mysqli_query($mysqli, $query);
  }

  mysqli_free_result($result); // Free Query
}

//############## Create Box Month ##############################################
function cronCreateBoxMonth($mysqli) {
  // SELECT All
  $query = "SELECT id FROM 'box_month'";
  $result = mysqli_query($mysqli, $query);

  if(empty($result)) {
    // Create table if doesn't exist.
    $query = "CREATE TABLE IF NOT EXISTS `box_month` (
      `bm_id` int(11) NOT NULL AUTO_INCREMENT,
      `bm_month` varchar(20) DEFAULT NULL,
      `bm_boxid` int(11) DEFAULT NULL,
      `bm_antisocial` int(11) DEFAULT NULL,
      `bm_burglary` int(11) DEFAULT NULL,
      `bm_othertheft` int(11) DEFAULT NULL,
      `bm_publicorder` int(11) DEFAULT NULL,
      `bm_violencesex` int(11) DEFAULT NULL,
      `bm_vehiclecrime` int(11) DEFAULT NULL,
      `bm_criminaldamage` int(11) DEFAULT NULL,
      `bm_othercrime` int(11) DEFAULT NULL,
      `bm_robbery` int(11) DEFAULT NULL,
      `bm_bicycletheft` int(11) DEFAULT NULL,
      `bm_drugs` int(11) DEFAULT NULL,
      `bm_shoplifting` int(11) DEFAULT NULL,
      `bm_theftperson` int(11) DEFAULT NULL,
      `bm_weapons` int(11) DEFAULT NULL,
      PRIMARY KEY (`bm_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
    $result = mysqli_query($mysqli, $query);
  }

  mysqli_free_result($result); // Free Query
}
?>

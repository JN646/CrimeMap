<?php
genBoxes();

function genBoxes()
{
	/*
	** This function is intended to only ever be used once.
	** It will generate the centrepoints of boxes with a defined
	** spacing; and write these points to the db.
	** This spacing is designed to be a constant (along with
	** box size) over the lifetime of the product.
	** If these constants were to be changed, an entire recalculation
	** of boxes and the data they relate to must be performed.
	**
	** However, this list of boxes does not need to be static;
	** New boxes can be created or detoryed procedurally or manually.
	*/

	$ukLatMin = -10.8544921875; //truncate these numbers? just to beautify?
	$ukLatMax = 2.021484375;
	$ukLongMin = 49.82380908513249;
	$ukLongMax = 59.478568831926395;

	$hop = 0.5; //in radians - size to be confirmed

	$x = $ukLatMin;
	$y = $ukLongMin;
	while($x < $ukLatMax) {
		while($y < $ukLongMax) {
			//SQL INSERT
			//can there be a way to avoid creating boxes that are probably out at sea? maybe try find if there's a crime within 10 miles or similar?
			if(True) {
				$sql = "INSERT INTO box (latitude, longitude) VALUES ($x, $y)";
			}
			$y += $hop;
		}
		$x += $hop;
	}

}
?>
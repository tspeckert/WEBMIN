<?php
	# loads our library
	require_once("lib_mysql.php");
	# start the session
	session_start();
	$email = $_SESSION['email'];
	# Get city of user's favourite restaurant
	$fav_city_id = $_GET['fav_city_id'];
	
	unset($_SESSION['rec_res_array']);
?>

<html>
	<head><title>Group 6 Recommendation System</title></head>
	<body>
		<div>
			Hello <?php echo htmlspecialchars($email); ?>, please select now your favourite restaurant in <?php echo htmlspecialchars($fav_city_id)?>: <br><br>
		</div>
		<div>
			<form action="sim.php" method="get">
				<select name="fav_rest_id">
				<?php
					# Get all restaurants in the user's favourite city
					$query = sprintf(
						"SELECT a.res_id, a.res_name 
						FROM restaurant a 
						JOIN city b 
						WHERE a.cit_id = b.cit_id 
						AND a.cit_id = %s
						ORDER BY res_name ASC",
						$fav_city_id);

					$restaurants_rows = execute_rows($query);
					
					# write all the cities in the form
					foreach($restaurants_rows as $row)
						echo sprintf("<option value=%s>%s</option>", $row[0], $row[1]);
				?>
				</select>
				<br>
				<br>
				And please let us know in which city you would like to eat now.
				<br>
				<br>
				<select name="fav_city_id">
				<?php
					# get the available cities
					$query = "SELECT * FROM city ORDER BY cit_name ASC";
					$rows = execute_rows($query);
					
					# write all the cities in the form
					foreach($rows as $row)
						echo sprintf("<option value=%s>%s</option>", $row['cit_id'], $row['cit_name']);
				?>
				</select>
				<input type="submit" value="Send">
			</form>
		</div>
	</body>
</html>
<?php
	# loads our library
	require_once("lib_mysql.php");
	
	# function to save the features of a restaurant in the user profile
	function save_res_to_profile($res_id, $user_id) {
		# get the new like number
		$query = "SELECT MAX(lik_no) FROM likes WHERE user_id = ".$user_id;
		$lik_no = execute_scalar($query) + 1;
		
		# get the features id of the favourite user restaurant
		$query = sprintf(
			"SELECT fea_id
			FROM res_fea
			WHERE res_id = %s",
			$res_id);
		$features_id = execute_rows($query);
		
		# saves the features of the favorite restaurant in the user profile
		foreach($features_id as $row) {
			$query = sprintf(
				"INSERT INTO likes(res_id, user_id, fea_id, lik_no, lik_value)
				VALUES (%s, %s, %s, %s, 1)",
				$res_id,
				$user_id,
				$row[0],
				$lik_no);
			execute_query($query);
		}
	}
	
	# start the session
	session_start();
	$email = $_SESSION['email'];
	$user_id = $_SESSION['user_id'];
	
	if(array_key_exists('rec_res_array', $_SESSION))
		$rec_res_array = $_SESSION['rec_res_array'];
	
	# get the disliked features ids from mod_restaurant.php
	if(array_key_exists('dislike_fea_ids', $_GET))
		$dislike_fea_ids = $_GET['dislike_fea_ids'];
	
	# get favorite city id from like_restaurant.php
	if(array_key_exists('fav_city_id', $_GET))
		$fav_city_id = $_SESSION['fav_city_id'] = $_GET['fav_city_id'];
	else
		$fav_city_id = $_SESSION['fav_city_id'];
	# get favorite restaurant id from like_restaurant.php
	if(array_key_exists('fav_rest_id', $_GET))
		$fav_rest_id = $_SESSION['fav_rest_id'] = $_GET['fav_rest_id'];
	else
		$fav_rest_id = $_SESSION['fav_rest_id'];
?>

<html>
	<head><title>Group 6 Recommendation System</title></head>
	<body>
	Already recommanded restaurants:</br>
<?php
	$i = 0;
	if(isset($rec_res_array)) {
		foreach($rec_res_array as $res_name) 
			echo sprintf("%s. %s</br>", ++$i, $res_name);
	}
	else
		echo "None.";
	echo "</br>";
	
	# we come from mod_restaurant.php
	if(isset($dislike_fea_ids)) {
		# get the last like number
		$query = "SELECT MAX(lik_no) FROM likes WHERE user_id = ".$user_id;
		$lik_no = execute_scalar($query);
		
		# loop through all disliked features and set the value -1 in user profile
		foreach($dislike_fea_ids as $fea_id) {
			$query = sprintf(
				"UPDATE likes 
				SET lik_value = -1
				WHERE user_id = %s
				AND lik_no = %s
				AND fea_id = %s",
				$user_id,
				$lik_no,
				$fea_id);
			execute_query($query);
		}
	}
	# we come from like_restaurant.php
	else {
		# saves the favorite user restaurant to his profile
		save_res_to_profile($fav_rest_id, $user_id);
	}
	
	# get the average values of the user profile
	$query = sprintf(
		"SELECT l.fea_id, SUM(l.lik_value)/(
			SELECT max(lik_no)
			FROM likes
			WHERE user_id = %s) as average_value, f.fea_name
		FROM likes l
		JOIN feature f ON l.fea_id = f.fea_id
		WHERE l.user_id = %s
		GROUP BY l.fea_id",
		$user_id,
		$user_id);
		
		$profile_fea_val = execute_rows($query);
	?>
	<div style="float:left; padding-right: 30px">
	<table border="1">
		<caption> User profile </caption>
		<tr>
		<th>fea_id</th>
		<th>fea_name</th>
		<th>average</th>
		</tr>
		<?php
		foreach($profile_fea_val as $row)
			echo sprintf("<tr>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>",
				$row[0],
				$row[2],
				$row[1]);
		?>
	</table> 
	</div>
	<div style="float:left">
	<?php
	$profile_fea_val = execute_indexed_values($query);
	
	$min_distance = 0;
	$closest_res_id = -1;
	
	# get all restaurants and their features in the city in which the user might like to eat in
	$query = sprintf(
		"SELECT r.res_id, rf.fea_id
		FROM restaurant r
		JOIN res_fea rf ON r.res_id = rf.res_id
		WHERE r.cit_id = %s
		ORDER BY r.res_id, rf.fea_id",
		$fav_city_id);
	$resfea_rows = execute_rows($query);
	
	# stores the results in a new array
	foreach($resfea_rows as $row)
		$res_features[$row["res_id"]][] = $row["fea_id"];
	
	# loop through all the restaurants in the city that the user wants a recommandation in
	foreach($res_features as $res_id => $res_fea_ids){
		$distance = 0;
		# merge profile features with restaurant features		
		$possible_features = array_unique(array_merge(array_keys($profile_fea_val), $res_fea_ids));
		# loop through all the possible features
		foreach($possible_features as $fea_id) {
		#for($fea_id = 0; $fea_id < 256; $fea_id++) {
			# get profile value for this feature, or 0 if doens't exist
			$profile_value = array_key_exists($fea_id, $profile_fea_val) ? $profile_fea_val[$fea_id] : 0;
			# get restaurant value for this feature or -1 if it doesn't have it
			$res_value = in_array($fea_id, $res_fea_ids) ? 1 : -1;
			# adds the distance
			$distance += pow(($profile_value - $res_value), 2);
		}
		
		# save the restaurant if it is the closest
		if(!array_key_exists($res_id, $rec_res_array) && ($closest_res_id == -1 || $distance < $min_distance)) {
			$closest_res_id = $res_id;
			$min_distance = $distance;
			$closest_res_fea_ids = $res_fea_ids;
		}
	}
	
	# saves the recommanded restaurant to the user profile
	save_res_to_profile($closest_res_id, $user_id);
	
	# get restaurant name
	$query = sprintf(
		"SELECT res_name
		FROM restaurant
		WHERE res_id = %s",
		$closest_res_id);
	$res_name = execute_scalar($query);
	
	# remember the recommended restaurants
	$_SESSION['rec_res_array'][$closest_res_id] = $res_name;
	
	$query = sprintf(
		"SELECT r.fea_id, f.fea_name
		FROM res_fea r
		JOIN feature f ON r.fea_id = f.fea_id
		WHERE r.res_id = %s",
		$closest_res_id);
	$res_features = execute_rows($query);

?>
	<br>
	Great <b><?php echo $email;?></b> we found an alternative to your favourite restaurant.
	<br><br>
	Restaurant
	<b><?php echo $res_name;?></b> has the highest similarity with a <b>distance of <?php echo $min_distance;?></b>.
	<br><br>
	<table border="1">
		<caption>Features recommended restaurant</caption>
		<tr>
			<th>fea_id</th>
			<th>fea_name</th>
		</tr>
		<?php
		foreach($res_features as $row)
			echo sprintf("<tr>
				<td>%s</td>
				<td>%s</td>
				</tr>",
				$row[0],
				$row[1]);
		?>
	</table> 
	<a href="mod_restaurant.php?res_id=<?php echo $closest_res_id?>"> I don't like this restaurant</a>
	</div>
	</body>
</html>
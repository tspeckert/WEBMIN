<?php
	# loads our library
	require_once("lib_mysql.php");
	# start the session
	session_start();
	# retrieve the email
	$email = $_GET['email'];

	if(isset($email)) $_SESSION['email'] = $email;
	else echo "no email has been set";
	
	$query = sprintf(
		"SELECT user_id
		FROM user
		WHERE user_email='%s'",
		htmlspecialchars($email));
	
	# check if the account already exists and gets the user id
	if (!$user_id = execute_scalar($query, false)) {
		# otherwise no account exists, so lets create one
		$query = sprintf(
			"INSERT INTO user(user_email)
			VALUES('%s')",
			htmlspecialchars($email));
		
		$user_id = execute_query($query);
	}

	#add the user_id to the session variable array
	$_SESSION['user_id'] = $user_id;
?>

<html>
	<head><title>Group 6 Recommendation System</title></head>
	<body>
		<div>
			Hello <?php echo htmlspecialchars($email); ?>, please select the city of your favourite restaurant: <br><br>
		</div>
		<div>
			<form action="like_restaurant.php" method="get">
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
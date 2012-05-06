<?php
	# loads our library
	require_once("lib_mysql.php");
	# start the session and get the initial login data
	session_start();
	$email = $_SESSION['email'];
	$user_id = $_SESSION['user_id'];
?>

<html>
	<head><title>Group 6 Recommendation System</title></head>
	<body>
		Please <b>select</b> the attributes of the restaurant that made you reject this recommendation.<br><br>
		<form action="sim.php" method="get">
		<input type="hidden" name ="mod_rest_id" value="<?php echo $rec_rest_id;?>">
		<?php	
		# Get id from recommended restaurant
		$rec_rest_id = $_GET['res_id'];
		
		# Get attributes for recommended restaurant
		$query = sprintf(
			"SELECT b.fea_id, b.fea_name
			FROM res_fea a, feature b 
			WHERE a.fea_id = b.fea_id 
			AND a.res_id = %s 
			ORDER BY b.fea_name ASC", 
			$rec_rest_id);
		$rows = execute_rows($query);
		
		foreach($rows as $row)
			echo "<input type=\"checkbox\" name = \"dislike_fea_ids[]\" value=\"".$row['fea_id']."\">".$row['fea_name']."<br>";
		?>
		<input type="submit" value ="Create new recommendation">
		</form>
	</body>
</html>
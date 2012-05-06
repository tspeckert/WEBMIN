<?php
	require_once("lib_mysql.php");
	
	# start the session and get the initial login data
	session_start();
	$email = $_SESSION['email'];
	$userid = $_SESSION['userid'];

?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>

<?php
	
	# Get attribute array from recommended restaurant as well as restaurant id
	$mod_res_attr_ids = $_GET['pref_attribute'];
	$rec_restaurant = $_GET['restaurant_id'];
	
	echo $rec_restaurant;
	
	#deserialize original attribute array
	#$res_attr_ids = unserialize($_GET['restaurant_attrs']);
	#echo $res_attr_ids;
	
	# Establish DB connection
	$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
	mysql_select_db("entree_db") or die ("no database connection possible");
	
	# Add the restaurant and features to the user's profile
	
	# Get the highest choice id
	$get_choice_id_query = "SELECT MAX(choice_id) AS choice_id FROM likes";
	$get_choice_id_result = mysql_query($get_choice_id_query);
	$choice_temp = mysql_fetch_object($get_choice_id_result);
	$choice_id = $choice_temp->choice_id;
	
	# First add?
	if($choice_id ==FALSE)
	{
	$choice_id =1;
	}
	else
	{
	$choice_id++;
	}
	
	
	for($i=0;$i<count($mod_res_attr_ids);$i++)
	{
			//add to user profile
			$addToProfile_query = "INSERT INTO `likes`(`res_id`, `userid`,`fea_id`,`choice_id`,`like_code`) VALUES (".$rec_restaurant.",".$userid.",".$mod_res_attr_ids[$i].",".$choice_id.",1);";
			$addToProfile_result = mysql_query($addToProfile_query);
	}
	
	
?>

</body>
</html>
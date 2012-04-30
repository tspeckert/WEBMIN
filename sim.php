<?php
	# Start the session
	session_start();
	$email = $_SESSION['email'];
	$userid = $_SESSION['userid'];
?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>

<?php
	
	# Favourite restaurant of the user (restaurant_id)
	$fav_restaurant = $_GET['fav_restaurant'];

	# City in which the user wants to eat in (city_id)
	$fav_res_city = $_GET['fav_res_city'];
	
	# Establish DB connection
	$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
	mysql_select_db("entree_db") or die ("no database connection possible");

	# Add the restaurant to the user's profile
	
		//check if already a part of the user profile
		$checkProfile_query = "SELECT * FROM `likes` WHERE res_id = \"" . $fav_restaurant . "\" AND userid = \"" . $userid . "\";";
		//echo $checkProfile_query;
		$checkProfile_result = mysql_query($checkProfile_query);
		//echo $checkProfile_result;
		if (mysql_num_rows($checkProfile_result) == 0) {
			//add to user profile
			$addToProfile_query = "INSERT INTO `likes`(`res_id`, `userid`) VALUES (" . $fav_restaurant . "," . $userid . ");";
			$addToProfile_result = mysql_query($addToProfile_query);
		}
	# Get all restaurants in the city in which the user would like to eat in
	$restaurants_query = "SELECT res_id FROM restaurant a JOIN city b WHERE a.cit_id = b.cit_id AND a.cit_id =".$fav_res_city." ORDER BY res_name ASC";
	$restaurants_result = mysql_query($restaurants_query);
	
	#Get the attributes of the favourite user restaurant
	$fav_rest_attrs_query = "SELECT * FROM res_fea WHERE res_id =".$fav_restaurant." ORDER BY fea_id ASC";
	$fav_rest_attrs_result = mysql_query($fav_rest_attrs_query);
	
	
	# this array only contains the ids of the user's favourite restaurants
	$fav_rest_ids = array();
	
	$temp_array_index = -1;
	$temp_fav_rest_id = -1;
	
	
		
		while($row = mysql_fetch_object($fav_rest_attrs_result))
		{		
			# Has an array entry for the current restaurant already been created?
			if($temp_fav_rest_id != $row->res_id)
			{	
				#Increment array index for saving new restaurant in a new Array-entry
				$temp_array_index++;
				
				$fav_rest_ids[] = $row->res_id;
				
				# first restaurant?
				if($temp_fav_rest_id == -1)
				{	
					# Create new entry for the restaurant
					$fav_rest_attr_vector = array($row->res_id => array($row->fea_id));
					# Remember current restaurant
					$temp_fav_rest_id = $row->res_id;
				}
				# if not the first restaurant, attach to array
				else
				{	
					$temp_fav_rest_id = $row->res_id;
					$fav_rest_attr_vector[$temp_fav_rest_id] = array($row->fea_id);
				}
				# Remember current restaurant
				$temp_fav_rest_id = $row->res_id;
			}
			else
			{
				# Add new attribute to existing restaurant in Array
				$fav_rest_attr_vector[$temp_fav_rest_id][] = $row->fea_id;
			}
		}
	
	#Get the attributes of the restaurants in the city in which the user would like to eat 
	$rest_attrs_query = "SELECT * FROM res_fea a, restaurant b WHERE a.res_id = b.res_id AND b.cit_id =".$fav_res_city." ORDER BY a.res_id ASC";
	$rest_attrs_result = mysql_query($rest_attrs_query);
	
	# this array only contains the ids of the restaurants in the selected city
	$rest_ids = array();
	$temp_array_index = -1;
	$temp_rest_id = -1;
	
	while($row = mysql_fetch_object($rest_attrs_result))
		{		
			# Has an array entry for the current restaurant already been created?
			if($temp_rest_id != $row->res_id)
			{	
				#Increment array index for saving new restaurant in a new Array-entry
				$temp_array_index++;
				
				$rest_ids[] = $row->res_id;
				# Create new entry for the restaurant
				
				# first restaurant?
				if($temp_rest_id == -1)
				{
					$rest_attr_vector = array($row->res_id => array($row->fea_id));
					# Remember current restaurant
					$temp_rest_id = $row->res_id;
				}
				# if not the first restaurant, attach to array
				else
				{	
					$temp_rest_id = $row->res_id;
					$rest_attr_vector[$temp_rest_id] = array($row->fea_id);
				}
				# Remember current restaurant
				$temp_rest_id = $row->res_id;
			}
			else
			{
				# Add new attribute to existing restaurant in Array
				$rest_attr_vector[$temp_rest_id][] = $row->fea_id;
			}
		}

	# count the similarity with each restaurant
	
	$sim_count = 0;
	
	#rest_id with the highest similarity
	$sim_max_id = 0;
	
	#highest similarity value
	$sim_max=0;
	
	
	foreach($rest_attr_vector as $rest => $attrs){
			
			for($i=0;$i<count($fav_rest_attr_vector[$fav_restaurant]);$i++)
			{	
				if(in_array($fav_rest_attr_vector[$fav_restaurant][$i],$attrs))
				{	
					$sim_count++;
				}
			}
			$sim_restaurants[] = array($rest, $sim_count);
			
			#upadte the max_sim value
			if($rest != $fav_restaurant && $sim_count>$sim_max)
			{
				$sim_max = $sim_count;
				$sim_max_id = $rest;
				$sim_max_attrs = $attrs;
			}
			$sim_count = 0;
		
	}
	
	if($sim_max>0)
	{	echo "<br>Great <b>".$email.", </b>we found an alternative to your favourite restaurant.";
		
		# Get restaurant name
		$res_query = "SELECT res_name FROM restaurant WHERE res_id =".$sim_max_id;
		$res_max_result = mysql_query($res_query);
		$res_max = mysql_fetch_object($res_max_result);
		
		echo "<br><br>Restaurant <b>".$res_max->res_name."</b> has the highest similarity with <b>".$sim_max." similar attributes</b>.<br><br>";
		
		echo "Attributes favourite restaurant:<br><br> ";
		print_r($fav_rest_attr_vector[$fav_restaurant]);
		
		echo "<br><br>Attributes recommended restaurant:<br><br>";
		print_r($sim_max_attrs);
	}
	else
	{
		echo "Sorry, unfortunately there is no similar restaurant here. :-(";
	}

?>


</body>
</html>
<?php
	# Start the session
	session_start();
	$email = $_SESSION['email'];
	$userid = $_SESSION['userid'];
	
	$THRESHOLD = 0.2;
?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>

<?php
	
	# City in which the user wants to eat in (city_id)
	$restaurant_id = $_GET['restaurant_id'];
	
	# Establish DB connection
	$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
	mysql_select_db("entree_db") or die ("no database connection possible");
	
	echo "Already recommanded restaurants : ";
	print_r($_SESSION['rec_rest_array']);
	echo "</br>";

	# Get the highest choice id
	$get_choice_id_query = "SELECT MAX(choice_id) AS choice_id FROM likes";
	$get_choice_id_result = mysql_query($get_choice_id_query);
	$choice_temp = mysql_fetch_object($get_choice_id_result);
	$choice_id = $choice_temp->choice_id;
		
	if($restaurant_id) {
		# we come from mod_restaurant.php
	
		# retrieve the favorite city from the session
		$fav_res_city = $_SESSION['fav_res_city'];
		# retrieve the favorite restaurant from the session
		$fav_restaurant = $_SESSION['fav_restaurant'];
	
		$mod_res_attr_ids = $_GET['pref_attribute'];
		
		# get the last choice id
		$lastChoiceID_query = "SELECT max(choice_id) AS choice_id FROM likes WHERE userid = ".$userid.";";
		$lastChoiceID_result = mysql_query($lastChoiceID_query);
		$last_choice_id = mysql_fetch_object($lastChoiceID_result);
		$last_choice_id = $last_choice_id->choice_id;
		
		
		
		# updates the data in the likes table (puts -1 for features that the user doesn't like)
		foreach($mod_res_attr_ids as $attr_id)
		{
			//add to user profile
			$updateProfile_query = "UPDATE `likes` SET `like_code`= -1 WHERE choice_id = ".$last_choice_id." AND fea_id = ".$attr_id.";";
			$updateProfile_result = mysql_query($updateProfile_query);
		}
	}
	else {
		# Favourite restaurant of the user (restaurant_id)
		$fav_restaurant = $_GET['fav_restaurant'];
		$_SESSION['fav_restaurant'] = $fav_restaurant;

		# City in which the user wants to eat in (city_id)
		$fav_res_city = $_GET['fav_res_city'];
		$_SESSION['fav_res_city'] = $fav_res_city;
		
		# Create array for remembering the recommended restaurants
		$_SESSION['rec_rest_array'] = array();
	
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
		
		# Add the restaurant and features to the user's profile
		
		# First add?
		if($choice_id ==FALSE)
		{
			$choice_id =1;
		}
		else
		{
			$choice_id++;
		}
		
		for($i=0;$i<count($fav_rest_attr_vector[$fav_restaurant]);$i++)
		{
			//add to user profile
			$addToProfile_query = "INSERT INTO `likes`(`res_id`, `userid`,`fea_id`,`choice_id`,`like_code`) VALUES (".$fav_restaurant.",".$userid.",".$fav_rest_attr_vector[$fav_restaurant][$i].",".$choice_id.",1);";
			$addToProfile_result = mysql_query($addToProfile_query);	
		}
	}
	
	# Get all restaurants in the city in which the user would like to eat in
	$restaurants_query = "SELECT res_id FROM restaurant a JOIN city b WHERE a.cit_id = b.cit_id AND a.cit_id =".$fav_res_city." ORDER BY res_name ASC";
	$restaurants_result = mysql_query($restaurants_query);
	
	#Get the attributes of the user profile
	
	$allFeaturesFromUser_query = "SELECT DISTINCT (fea_id) FROM `likes` WHERE userid = ".$userid.";";
	$allFeaturesFromUser_result = mysql_query($allFeaturesFromUser_query);
	
	$numberOfChoices_query = "SELECT COUNT( DISTINCT (choice_id) ) as number FROM `likes` WHERE userid = ".$userid.";";
	$numberOfChoices_result = mysql_query($numberOfChoices_query);
	$numberOfChoices = mysql_fetch_object($numberOfChoices_result);
	$numberOfChoices = $numberOfChoices->number;
	
	$profile_feature_ids = array();
	
	while($row = mysql_fetch_array( $allFeaturesFromUser_result )) {
		$feature_id = $row['fea_id'];
		
		$sumFeature_query = "SELECT SUM( like_code ) as sum FROM `likes` WHERE userid = ".$userid." AND fea_id = ".$feature_id;
		$sumFeature_result = mysql_query($sumFeature_query);
		$sumFeature = mysql_fetch_object($sumFeature_result);
		$sumFeature = $sumFeature->sum;
		
		$average = $sumFeature / $numberOfChoices;
		
		echo $feature_id." = ".$average." | ";
		
		if($average > $THRESHOLD)
			$profile_feature_ids[] = $feature_id;
	} 
	echo "</br>";
	
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
	
	# numer of attributes of the favourite restaurant that could not be found
	$dissim_count_fav_res = 0;
	
	# numer of attributes of the compared restaurant that could not be found
	$dissim_count_res = 0;
	
	#rest_id with the highest similarity
	$sim_jaccard_max_id = 0;
	
	#highest similarity value
	$sim_jaccard_max=0;
	
	
	foreach($rest_attr_vector as $rest => $attrs){
			
			foreach($profile_feature_ids as $profile_feature_id)
			{	
				if(in_array($profile_feature_id,$attrs))
				{	
					$sim_count++;
				}
			}
			
			# calculate the dissimilarity
			$dissim_count_res = count($attrs)-$sim_count;
			$dissim_count_fav_res = count($profile_feature_ids)-$sim_count;
			# jaccard similarity
			$jaccard_sim = $sim_count / ($sim_count+$dissim_count_res+$dissim_count_fav_res);
			
			$sim_restaurants[] = array($rest, $sim_count, $jaccard_sim);
			
			#upadte the max_jaccard_sim value
			$rec_rest_array = $_SESSION['rec_rest_array'];
			
			if($rest != $fav_restaurant && !in_array($rest, $rec_rest_array) && $jaccard_sim>$sim_jaccard_max)
			{
				$sim_max = $sim_count;
				$sim_jaccard_max = $jaccard_sim;
				$sim_jaccard_max_id = $rest;
				$sim_jaccard_max_attrs = $attrs;
			}
			$sim_count = 0;
			$dissim_count_res = 0;
			$dissim_count_fav_res = 0;
		
	}
	
	if($sim_jaccard_max>0)
	{	echo "<br>Great <b>".$email.", </b>we found an alternative to your favourite restaurant.";
		
		# Get restaurant name
		$res_query = "SELECT res_name FROM restaurant WHERE res_id =".$sim_jaccard_max_id;
		$res_max_result = mysql_query($res_query);
		$res_max = mysql_fetch_object($res_max_result);
		
		# Remember the already recommended restaurants
		$_SESSION['rec_rest_array'][] = $sim_jaccard_max_id;
		
		echo "<br><br>Restaurant <b>".$res_max->res_name."</b> has the highest similarity with <b>".$sim_max." similar attributes</b>.<br><br>";
		echo "This corresponds to a Jaccard similarity value of <b>".round($sim_jaccard_max,4)."</b>.<br><br>";
		
		echo "Attributes favourite restaurant:<br><br> ";
		print_r($profile_feature_ids);
		
		echo "<br><br>Attributes recommended restaurant:<br><br>";
		print_r($sim_jaccard_max_attrs);
		
		echo "<br><br><a href=\"mod_restaurant.php?res_id=".$sim_jaccard_max_id."\"> Modify recommendation </a>";
		
		# saves the recommended restaurant in the likes table
		$choice_id++;
		
		foreach($sim_jaccard_max_attrs as $attr_id)
		{
			//add to user profile
			$addToProfile_query = "INSERT INTO `likes`(`res_id`, `userid`,`fea_id`,`choice_id`,`like_code`) VALUES (".$sim_jaccard_max_id.",".$userid.",".$attr_id.",".$choice_id.",1);";
			$addToProfile_result = mysql_query($addToProfile_query);
		}
	}
	else
	{
		echo "Sorry, unfortunately there is no similar restaurant here. :-(";
	}

?>


</body>
</html>
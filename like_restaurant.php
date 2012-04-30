<?php

	# start the session and get the initial login data
	session_start();
	$email = $_SESSION['email'];
	$userid = $_SESSION['userid'];
?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>
<div>
 Hello <?php echo htmlspecialchars($email); ?>, please select now your favourite restaurant in <?php echo htmlspecialchars($_GET['fav_city'])?>: <br><br>
</div>
<div>
<?php 

# Get city of user's favourite restaurant
 
$fav_city = $_GET['fav_city'];

# Establish DB connection
$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
mysql_select_db("entree_db") or die ("no database connection possible");

# Get all restaurants in the user's favourite city

$restaurants_query = "SELECT * FROM restaurant a JOIN city b WHERE a.cit_id = b.cit_id AND a.cit_id =\"".$fav_city."\"ORDER BY res_name ASC";
$restaurants_result = mysql_query($restaurants_query);

# Get the cities from which the user selects one, i.e. the one in which he would like to eat

$city_query = $city_query = "SELECT * FROM city ORDER BY cit_name ASC";
$city_result = mysql_query($city_query);

# Build form for selecting favourite restaurant and city in which the user is looking for a restaurant

echo "<form action=\"sim.php\" method=\"get\">";

# Dropdown

echo "<select name = \"fav_restaurant\">";

# Restaurant options

while($row = mysql_fetch_object($restaurants_result))
	{
	echo "<option value =".$row->res_id.">";
	echo $row->res_name."<br>";
	echo "</option>";
	}
echo "</select>";

echo "<br><br> And please let us know in which city you would like to eat now. <br> <br>";

# City in which the user wants to eat in
echo "<select name = \"fav_res_city\">";

while($row = mysql_fetch_object($city_result))
	{
	echo "<option value =".$row->cit_id.">";
	echo $row->cit_name."<br>";
	echo "</option>";
	}
echo "</select>";

echo "<br><br><input type=\"submit\" value=\"Send\">";
echo "</form>";

	
mysql_close();

 ?>
</div>
</body>
</html>
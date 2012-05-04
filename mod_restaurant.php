<?php
	
	# start the session and get the initial login data
	session_start();
	$email = $_SESSION['email'];
	$userid = $_SESSION['userid'];

?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>

<?php
	
	# Get id from recommended restaurant
	$rec_rest_id = $_GET['res_id'];
	
	# Establish DB connection
	$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
	mysql_select_db("entree_db") or die ("no database connection possible");
	
	# Get attributes for recommended restaurant
	$rec_rest_attrs_query = "SELECT * FROM res_fea a, feature b WHERE a.fea_id = b.fea_id AND a.res_id =".$rec_rest_id." ORDER BY b.fea_name ASC";
	$rec_rest_attrs_result = mysql_query($rec_rest_attrs_query);
	
	# Attribute array from the recommended restaurant for serialization
	$rec_rest_attrs = array();
	
	echo "Please <b>select</b> the attributes of the restaurant that made you reject this recommendation.<br><br>"; 

	#Display all attributes for the recommended restaurant; Needed for comparison in the next step
	echo "<form action=\"sim.php\" method=\"get\">";
	
	# hidden parameter that holds the restaurant_id
	echo "<input type=\"hidden\" name =\"restaurant_id\" value =\"".$rec_rest_id."\">";
	
	while($row = mysql_fetch_object($rec_rest_attrs_result))
		{
		# attach attribute
		$rec_rest_attrs[]= $row->fea_id;
		
		# set checkbox with attribute value
		echo "<input type=\"checkbox\" name = \"pref_attribute[]\" value=\"".$row->fea_id."\">".$row->fea_name."<br>";
		}
	
	# serialize attribute array of recommended restaurant
	#$rec_rest_attrs_ser = serialize($rec_rest_attrs);
	#echo $rec_rest_attrs_ser;
	#$rec_rest_attrs_ser = addslashes($rec_rest_attrs_ser);
	#echo $rec_rest_attrs_ser;
	#echo "<input type=\"hidden\" name=\"restaurant_attrs\" value =\"".$rec_rest_attrs_ser."\">";

	
	echo "<input type=\"submit\" value =\"Create new recommendation\">";
	
	echo "</form>";
?>

</body>
</html>
<?php 

session_start();
$email = $_GET['email'];

if(isset($email))
	{
	 $_SESSION['email'] = $email;
	}
else echo "no email has been set";
?>

<html>
<head><title>Group 6 Recommendation System</title></head>
<body>
<div>
 Hello <?php echo htmlspecialchars($email); ?>, please select the city of your favourite restaurant: <br><br>
</div>
<div>
<?php 

$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
mysql_select_db("entree_db") or die ("no database connection possible");

$city_query = "SELECT * FROM city ORDER BY cit_name ASC";
$city_result = mysql_query($city_query);

echo "<form action=\"like_restaurant.php\" method=\"get\">";
echo "<select name = \"fav_city\">";

while($row = mysql_fetch_object($city_result))
	{
	echo "<option value=".$row->cit_id.">";
	echo $row->cit_name."<br>";
	echo "</option>";
	}
echo "</select>";
echo "<input type=\"submit\" value=\"Send\">";
echo "</form>";
	
mysql_close();

 ?>
</div>
</body>
</html>
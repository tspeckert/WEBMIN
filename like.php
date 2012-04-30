<html>
<head><title>Group 6 Recommendation System</title></head>
<body>
<div>
 Hello <?php echo htmlspecialchars($_GET["email"]); ?>, please select the restaurants you like below: <br><br>
</div>
<div>
<?php 

$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
mysql_select_db("entree_db") or die ("no database connection possible");

$restaurants_query = "SELECT * FROM restaurant a JOIN city b WHERE a.cit_id = b.cit_id ORDER BY res_name ASC";
$restaurants_result = mysql_query($restaurants_query);

echo "<form action=\"sim.php\" method=\"get\">";
echo "<select name = \"fav_restaurant\">";

while($row = mysql_fetch_object($restaurants_result))
	{
	echo "<option>";
	echo $row->cit_name."    -     ".$row->res_name."     -     ".$row->res_id."<br>";
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
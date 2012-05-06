<?php
# library for useful mysql commands

# establish DB connection
$connection = mysql_connect("localhost","root","") or die ("no server connection possible");
mysql_select_db("entree_db") or die ("no database connection possible");

# get first result from the first row
function execute_scalar($query,$default="") {
    $rs = mysql_query($query) or mysql_err($query);
    if (mysql_num_rows($rs)) {
        $r = mysql_fetch_row($rs);
        mysql_free_result($rs);
        return $r[0];
        mysql_free_result($rs);
    }
    return $default;
}

# get the first row
function execute_row($query) {
    $rs = mysql_query($query) or mysql_err($query);
    if (mysql_num_rows($rs)) {
        $r = mysql_fetch_array($rs);
        mysql_free_result($rs);
        return $r;
    }
    mysql_free_result($rs);
    return "";
}

# get array of rows
function execute_rows($query) {
    $rs = mysql_query($query) or mysql_err($query);
    if (mysql_num_rows($rs)) {
		while($row = mysql_fetch_array($rs))
			$r[] = $row;
        mysql_free_result($rs);
        return $r;
    }
    mysql_free_result($rs);
    return array();
}

# get array values with their index
function execute_indexed_values($query) {
    $rs = mysql_query($query) or mysql_err($query);
    if (mysql_num_rows($rs)) {
		while($row = mysql_fetch_array($rs))
			$r[$row[0]] = $row[1];
        mysql_free_result($rs);
        return $r;
    }
    mysql_free_result($rs);
    return array();
}

# get array of single values
function execute_values($query) {
    $rs = mysql_query($query) or mysql_err($query);
    if (mysql_num_rows($rs)) {
		while($row = mysql_fetch_array($rs))
			$r[] = $row[0];
        mysql_free_result($rs);
        return $r;
    }
    mysql_free_result($rs);
    return array();
}

# execute query without any return value
function execute_query($query) {
	mysql_query($query) or mysql_err($query);
	return mysql_insert_id();
}

# generate error message
function mysql_err($query) {
	$message  = 'Invalid query : ' . mysql_error() . "\n";
    $message .= 'Complete query : ' . $query;
    die($message);
}
?>
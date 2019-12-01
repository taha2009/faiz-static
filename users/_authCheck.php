<?php
include('connection.php');

// check if user didn't hit this page directly and is coming from login page
session_start();
if (!isset($_SESSION['fromLogin'])) {
 	header("Location: login.php");
}

// check if user has right to access the page
$rights = array(
	"musaid" => array("/users/musaid.php"),
	"admin" => array("/users/musaid.php",
		"/users/admin_scripts.php",
		"/users/stop_permanant.php",
		"/users/thalisearch.php"
	),
	"all" => array("/users/index.php","/users/hoobHistory.php","/users/events.php","/users/update_details.php")
);	
// fetch user role
$sql = mysqli_query($link,"SELECT role from users where email='".$_SESSION['email']."'");
$row = mysqli_fetch_row($sql);
if (!empty($row[0])) {
	$role= $row[0];
	if (!in_array($_SERVER['REQUEST_URI'], $rights[$role]) && !in_array($_SERVER['REQUEST_URI'], $rights['all'])) {
		echo "You are not an authorized to get this page";
		header("Location: index.php");
	}
} else {
	echo "You are not an authorized user.";
	header("Location: index.php");
}
?>
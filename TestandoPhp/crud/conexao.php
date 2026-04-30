<?php
$servername = "db";
$username = "sistema";
$password = "sistema";
$dbname = "dbkprev";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully" . "<br>". "<br>";
?>
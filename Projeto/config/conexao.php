<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "bancodedados";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Falha na conexÃ£o: " . mysqli_connect_error());
}


mysqli_set_charset($conn, "utf8");
?>
<?php
require_once "utils/setup.php";

// Database connection information
$conn = mysqli_connect('localhost', $ini['userName'], $ini['password'], $ini['dbName']);

<?php
$user_id = $_SESSION['user_id'] ?? 1;

$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);
<?php
$conn = new mysqli("127.0.0.1", "root", "", "parkir_dishub", 3306);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
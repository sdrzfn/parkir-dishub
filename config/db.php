<?php

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "parkir_dishub";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn -> connect_error) {
        die("koneksi gagal". $conn-> connect_error);
    }

?>
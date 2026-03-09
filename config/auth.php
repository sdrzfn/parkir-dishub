<?php

// require_once __DIR__ . "./db.php";

// function require_login() {
//     if (empty($_SESSION['user_id'])) {
//         header('Location: '); //nanti diisi
//         exit;
//     }
// }

// function current_user() {
//     global $conn;
//     if (empty($_SESSION['user_id']))
//         return null;

//     $id = (int) $_SESSION['user_id'];
//     $sql = ""; // nanti diganti
//     $res = $conn->query($sql);
//     if ($res && $res->num_rows) {
//         return $res->fetch_assoc();
//     }
//     return null;
// }

session_start();
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

?>
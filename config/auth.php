<?php
session_start();
require_once __DIR__ . "/db.php";

/**
 * Fetch user yang login
 */
function current_user()
{
    global $conn;
    if (empty($_SESSION['user_id']))
        return null;

    $id = $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'");

    if ($query && mysqli_num_rows($query) > 0) {
        return mysqli_fetch_assoc($query);
    }
    return null;
}

/**
 * Melakukan check user login
 */
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php");
        exit();
    }
}

/**
 * Pembatasan role
 */
function allowRole($allowed_roles = [])
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        echo "<script>
                alert('Akses Ditolak! Anda tidak memiliki izin untuk halaman ini.');
                window.location.href = '../auth/login.php';
              </script>";
        exit();
    }
}
?>
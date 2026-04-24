<?php
include '../config/db.php';
include '../config/auth.php';
allowRole(['super-admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];

    if ($action == 'add') {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nama_lengkap, username, email, password, role) 
                VALUES ('$nama', '$username', '$email', '$hashed_pass', '$role')";
    } elseif ($action == 'edit') {
        $sql = "UPDATE users SET nama_lengkap='$nama', username='$username', email='$email', role='$role'";
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password='$hashed_pass'";
        }
        $sql .= " WHERE id='$id'";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: ../super-admin/manage-users.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    header("Location: ../super-admin/manage-users.php?status=deleted");
}
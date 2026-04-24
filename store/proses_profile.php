<?php
include '../config/db.php';
include '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "UPDATE users SET nama_lengkap='$nama', username='$username', email='$email'";

    if (!empty($password)) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password='$hashed_pass'";
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "assets/img/users/";
        $file_ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $new_name = "USER_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $sql .= ", foto='$new_name'";
        }
    }

    $sql .= " WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: profile.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
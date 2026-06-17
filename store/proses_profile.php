<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    // $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "UPDATE users SET nama='$nama', username='$username'";

    if (!empty($password)) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password='$hashed_pass'";
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../assets/img/users/";
        $file_name = $_FILES["foto"]["name"];
        $file_size = $_FILES["foto"]["size"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi Ekstensi yang diizinkan
        $allowed_extensions = array("jpg", "jpeg", "png");

        // Validasi ukuran maksimal 2MB
        if ($file_size > 2 * 1024 * 1024) {
            redirectBack('profile.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
            // header("Location: ../profile.php?status=error&msg=" . urlencode("Ukuran file terlalu besar! Maksimal 2MB."));
            exit;
        }

        if (in_array($file_ext, $allowed_extensions)) {
            $new_name = "USER_" . $id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_name;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {

                $query_lama = mysqli_query($conn, "SELECT foto FROM users WHERE id='$id'");
                $data_lama = mysqli_fetch_assoc($query_lama);
                if (!empty($data_lama['foto']) && file_exists($target_dir . $data_lama['foto'])) {
                    unlink($target_dir . $data_lama['foto']);
                }

                $sql .= ", foto='$new_name'";
            }
        } else {
            redirectBack('profile.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
            exit;
        }
    }

    $sql .= " WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['nama'] = $nama;
        }
        redirectBack('profile.php', ['status' => 'success']);
        exit;
    } else {
        redirectBack('profile.php', ['status' => 'error', 'msg' => mysqli_error($conn)]);
        exit;
    }
}
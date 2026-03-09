<?php
include '../config/db.php';
// include '../config/auth.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $result = mysqli_query($conn, "SELECT foto FROM lokasi WHERE id = '$id'");
    if ($row = mysqli_fetch_assoc($result)) {
        $foto = $row['foto'];
        
        if (!empty($foto)) {
            $path_foto = "../assets/images/lokasi/" . $foto;
            if (file_exists($path_foto)) {
                unlink($path_foto);
            }
        }
    }
        
    $sql = "DELETE FROM lokasi WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Data berhasil dihapus";
        header("Location: ../lokasi.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
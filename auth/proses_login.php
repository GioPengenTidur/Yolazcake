<?php

session_start();
include "../config/koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users
        WHERE username='$username'
        AND password='$password'";

$query = mysqli_query($conn,$sql);

if(mysqli_num_rows($query) > 0){

    $_SESSION['username'] = $username;

    header("Location: ../index.php");
    exit();

}else{

    echo "Login Gagal";

}

?>
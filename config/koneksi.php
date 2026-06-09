<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "yolazcake_login"
);

if(!$conn){
    die("Koneksi gagal: ".mysqli_connect_error());
}

?>
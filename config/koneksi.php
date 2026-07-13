<?php

// Set timezone ke WIB (Asia/Jakarta) supaya semua fungsi date()/time() di
// seluruh project konsisten pakai waktu Indonesia, bukan default server
// (biasanya UTC, beda 7 jam -> bisa bikin tanggal "ketinggalan" sehari
// kalau diakses dini hari WIB).
date_default_timezone_set('Asia/Jakarta');

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
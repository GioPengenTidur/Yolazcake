<?php

session_start();

require_once "../config/koneksi.php";
require_once "../config/remember_me_helper.php";
remember_me_forget($conn);

session_destroy();

header("Location: ../index.php");
exit;

?>
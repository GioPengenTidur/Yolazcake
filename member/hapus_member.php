<?php
include '../config/koneksi.php';

$id = $_GET['id'];

mysqli_query(
    $conn,
    "DELETE FROM member WHERE id_member='$id'"
);

echo "
<script>
alert('Member berhasil dihapus');
window.location='data_member.php';
</script>
";
?>
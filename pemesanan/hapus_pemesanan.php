<?php
include '../config/koneksi.php';

$id = $_GET['id'];

mysqli_query(
$conn,
"DELETE FROM detail_pemesanan
WHERE id_pemesanan='$id'"
);

mysqli_query(
$conn,
"DELETE FROM pemesanan
WHERE id_pemesanan='$id'"
);

echo "
<script>
alert('Pemesanan berhasil dihapus');
window.location='data_pemesanan.php';
</script>
";
?>
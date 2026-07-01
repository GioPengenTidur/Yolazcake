<?php
include '../config/koneksi.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if(file_exists("../assets/img/produk/".$produk['foto'])){
    unlink("../assets/img/produk/".$produk['foto']);
}

mysqli_query(
    $conn,
    "DELETE FROM produk WHERE id_produk='$id'"
);

echo "
<script>
alert('Produk berhasil dihapus');
window.location='data_produk.php';
</script>
";
?>
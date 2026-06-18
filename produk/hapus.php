<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query(
    $conn,
    "SELECT * FROM produk WHERE id_produk='$id'"
);

$produk = mysqli_fetch_assoc($data);

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
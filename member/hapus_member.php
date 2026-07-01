<?php
include '../config/koneksi.php';

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM member WHERE id_member= ?");

$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

echo "
<script>
alert('Member berhasil dihapus');
window.location='data_member.php';
</script>
";
?>
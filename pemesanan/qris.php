<?php
session_start();

if(isset($_POST['nama_pemesan'])){
    $_SESSION['nama_pemesan'] = $_POST['nama_pemesan'];
}

if(isset($_POST['no_hp'])){
    $_SESSION['no_hp'] = $_POST['no_hp'];
}

$_SESSION['id_booking'] = $_POST['id_booking'] ?? ($_SESSION['id_booking'] ?? null);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pembayaran QRIS</title>
</head>

<style>
    body{
        font-family:'Segoe UI', system-ui, sans-serif;
        
        background-color: #FAF8F5;
        color: #6D4C41;
    }

    section{
        place-self: center;
        place-items: center;
    }
    h2{
        font-family: 'Playfair Display', Georgia, serif;
        font-size:2em;
        color: #6D4C41;
    }

    button{
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.25rem 2.5rem;
        border-radius: 9999px;
        border: none;
        background-color: #6d4c41;
        color: white;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-decoration: none;
        box-shadow: 0 12px 40px rgba(92, 64, 51, 0.2);
        transition: all var(--transition-normal);
        position: relative;
        overflow: hidden;
    }

</style>
<body>
    <section>
        <h2>Pembayaran QRIS</h2>

        <p>Silakan scan QRIS berikut</p>

<img src="../assets/img/image.png" width="300">

        <br><br>

        <form action="proses_pemesanan.php" method="POST">
            <button type="submit" name="bayar">
                Saya Sudah Bayar
            </button>
        </form>
    </section>
</body>
</html>
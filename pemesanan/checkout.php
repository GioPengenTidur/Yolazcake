<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>

<style>

    body{
        place-content: center;
        
    }

    h2{
        font-family: 'Playfair Display', Georgia, serif;
        font-size:2em;
        color: #6D4C41;
        place-self: center;
    }

    button{
        place-self: center;
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

    #formCheckout{
        place-self: center;
        place-content: center;
    }

    form{
        display: flex;
        justify-content: center;
        flex-direction: column;
        place-content: center;
        color: #6D4C41;
        gap: 1px;
    }

    input{
        padding: 50px;
        margin: 1px;
    }

</style>

<body>

<h2>Checkout</h2>

<div id="formCheckout">
    <form action="qris.php" method="POST">

        <label>Nama Pemesan</label><br>
        <input type="text" name="nama_pemesan" required><br><br>

        <label>No HP</label><br>
        <input type="text" name="no_hp" required><br><br>

        <button type="submit">
            Lanjut Pembayaran
        </button>

        <input type="hidden"
        name="id_booking"
        value="<?= $_SESSION['id_booking'] ?? '' ?>">

    </form>
</div>


</body>
</html>
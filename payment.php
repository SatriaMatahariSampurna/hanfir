<?php
session_start();
include 'db.php';

// Cek apakah user sudah login dan memiliki role 'user'
if ($_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

// Ambil user_id dan saldo dari session
$user_id = $_SESSION['user_id'];

// Query untuk mengambil saldo pengguna berdasarkan user_id
$stmt = $conn->prepare("SELECT saldo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($saldo);

if ($stmt->fetch()) {
    // Saldo berhasil diambil
} else {
    $saldo = 0;
}

$stmt->close();

// Proses top-up saldo
if (isset($_POST['topup_amount'])) {
    $topup_amount = $_POST['topup_amount'];

    if ($topup_amount > 0) {
        // Insert top-up request ke tabel top_up_requests
        $stmt = $conn->prepare("INSERT INTO top_up_requests (user_id, amount, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("id", $user_id, $topup_amount);
        $stmt->execute();
        $stmt->close();

        // Log transaksi top-up
        $stmt = $conn->prepare("INSERT INTO transaction_logs (user_id, type, amount) VALUES (?, 'topup', ?)");
        $stmt->bind_param("id", $user_id, $topup_amount);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Top-up request telah diajukan dan menunggu persetujuan admin.');</script>";
    } else {
        echo "<script>alert('Jumlah top-up tidak valid.');</script>";
    }
}

// Proses transfer saldo
if (isset($_POST['transfer_amount']) && isset($_POST['transfer_to'])) {
    $transfer_amount = $_POST['transfer_amount'];
    $transfer_to = $_POST['transfer_to'];

    if ($transfer_amount > 0 && $transfer_amount <= $saldo) {
        // Cek apakah user tujuan ada di database berdasarkan username
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $transfer_to);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($target_user_id);
            $stmt->fetch();

            // Proses pengurangan saldo pengguna
            $stmt = $conn->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
            $stmt->bind_param("ii", $transfer_amount, $user_id);
            $stmt->execute();

            // Proses penambahan saldo ke user tujuan
            $stmt = $conn->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
            $stmt->bind_param("ii", $transfer_amount, $target_user_id);
            $stmt->execute();

            // Log transaksi transfer
            $stmt = $conn->prepare("INSERT INTO transaction_logs (user_id, type, amount, target_user_id) VALUES (?, 'transfer', ?, ?)");
            $stmt->bind_param("idi", $user_id, $transfer_amount, $target_user_id);
            $stmt->execute();

            $stmt->close();
            echo "<script>alert('Transfer berhasil!');</script>";
        } else {
            echo "<script>alert('User tujuan tidak ditemukan.');</script>";
        }
    } else {
        echo "<script>alert('Saldo tidak mencukupi atau jumlah transfer tidak valid.');</script>";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Top Up & Transfer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 20%;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 80px;
            border-radius: 8px;
            box-shadow: 0 30px 40px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #333;
        }
        .header h1 {
            font-size: 28px;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .header i {
            font-size: 36px;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .balance-info {
            margin-bottom: 30px;
            text-align: center;
        }
        .balance-info span {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .button-container {
            text-align: center;
        }
        .submit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-button:hover {
            background-color: #45a049;
        }
        .bottom-nav {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding: 38px 0;
}

.bottom-nav a {
    color: white;
            font-size: 24px;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 30%;
            background-color: #4CAF50;
            transition: background-color 0.3s;
}

.bottom-nav a:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>

<div class="container">
    <div class="header">
    <i class="fas fa-users"></i>
    <h1>Hanfir Multi</h1>
        <h2>Payment - Top Up & Transfer</h2>
    </div>

    <div class="balance-info">
        <p>Saldo Anda: <span>Rp <?= number_format($saldo, 0, ',', '.'); ?></span></p>
    </div>

    <!-- Top Up Form -->
    <form method="POST">
        <div class="form-group">
            <label for="topup_amount">Jumlah Top Up</label>
            <input type="number" id="topup_amount" name="topup_amount" required min="1" placeholder="Masukkan jumlah top-up">
        </div>
        <div class="button-container">
            <button type="submit" class="submit-button">Ajukan Top Up</button>
        </div>
    </form>

    <hr>

    <!-- Transfer Form -->
 <!-- Transfer Form -->
<form method="POST">
    <div class="form-group">
        <label for="transfer_to">Username Tujuan</label>
        <input type="text" id="transfer_to" name="transfer_to" required placeholder="Masukkan username pengguna tujuan">
    </div>
    <div class="form-group">
        <label for="transfer_amount">Jumlah Transfer</label>
        <input type="number" id="transfer_amount" name="transfer_amount" required min="1" max="<?= $saldo ?>" placeholder="Masukkan jumlah transfer">
    </div>
    <div class="button-container">
        <button type="submit" class="submit-button">Transfer Saldo</button>
    </div>

    <!-- Icon Menu -->
    <div class="bottom-nav" style="margin-top: 20px;">
        <a href="dashboard_user.php" title="Profil"><i class="fas fa-user"></i></a>
        <a href="chat.php" title="Chat"><i class="fas fa-comment-dots"></i></a>
        <a href="payment.php" title="Payment"><i class="fas fa-credit-card"></i></a>
    </div>
</form>

</div>
</body>
</html>

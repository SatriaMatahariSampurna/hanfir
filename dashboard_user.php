<?php
session_start();
include 'db.php';

// Cek apakah user sudah login dan memiliki role 'user'
if ($_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data profil pengguna berdasarkan user_id
$stmt = $conn->prepare("SELECT username, tanggal_lahir, alamat, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($username, $tanggal_lahir, $alamat, $password);

// Jika data ditemukan, tampilkan profil pengguna
if ($stmt->fetch()) {
    // Format tanggal lahir (opsional, jika perlu)
    $tanggal_lahir = date('d-m-Y', strtotime($tanggal_lahir));
} else {
    $username = $tanggal_lahir = $alamat = $password = 'Data tidak ditemukan';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
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
            margin: 60px auto;
            background-color: #ffffff;
            padding: 80px;
            border-radius: 8px;
            box-shadow: 0 30px 40px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
        .profile-info {
            margin-bottom: 30px;
        }
        .profile-info div {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
        }
        .profile-info span {
            font-weight: normal;
            color: #555;
        }
        .button-container {
            text-align: end;
            margin-bottom: 90px;
        }
        .edit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 50px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .edit-button:hover {
            background-color: #45a049;
        }
        .menu-container {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding: 50px 0;
        }
        .menu-container a {
            color: white;
            font-size: 24px;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 30%;
            background-color: #4CAF50;
            transition: background-color 0.3s;
        }
        .menu-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <i class="fas fa-users"></i>
        <h1>Hanfir Multi</h1>
            <h2>Profil Pengguna</h2>
    </div>

    <div class="profile-info">
        <div>
            <strong>Username:</strong>
            <span><?= htmlspecialchars($username); ?></span>
        </div>
        <div>
            <strong>Tanggal Lahir:</strong>
            <span><?= htmlspecialchars($tanggal_lahir); ?></span>
        </div>
        <div>
            <strong>Alamat:</strong>
            <span><?= htmlspecialchars($alamat); ?></span>
        </div>
        <div>
            <strong>Password:</strong>
            <span><?= str_repeat('*', strlen($password)); ?></span> <!-- Password disembunyikan -->
        </div>
    </div>

    <div class="button-container">
        <a href="editprofil.php" class="edit-button">Edit Profil</a>
    </div>

    <!-- Menu Navigation -->
    <div class="menu-container">
        <a href="dashboard_user.php" title="Profil"><i class="fas fa-user"></i></a>
        <a href="chat.php" title="Chat"><i class="fas fa-comment-dots"></i></a>
        <a href="payment.php" title="Payment"><i class="fas fa-credit-card"></i></a>
    </div>

</div>

</body>
</html>

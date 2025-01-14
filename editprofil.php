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

// Jika data ditemukan, tampilkan form untuk mengedit profil
if ($stmt->fetch()) {
    $tanggal_lahir = date('Y-m-d', strtotime($tanggal_lahir)); // Format tanggal untuk input date
} else {
    $username = $tanggal_lahir = $alamat = $password = 'Data tidak ditemukan';
}

$stmt->close();

// Proses perubahan profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_tanggal_lahir = $_POST['tanggal_lahir'];
    $new_alamat = $_POST['alamat'];
    $new_password = $_POST['password'];

    // Query untuk memperbarui data profil pengguna
    $stmt = $conn->prepare("UPDATE users SET username = ?, tanggal_lahir = ?, alamat = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $new_username, $new_tanggal_lahir, $new_alamat, password_hash($new_password, PASSWORD_DEFAULT), $user_id);

    if ($stmt->execute()) {
        header("Location: profil.php"); // Redirect ke halaman profil setelah sukses
        exit();
    } else {
        echo "<p>Gagal memperbarui profil.</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #333;
        }
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-container div {
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
        }
        .form-container input {
            font-weight: normal;
            color: #555;
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .button-container {
            text-align: center;
        }
        .save-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .save-button:hover {
            background-color: #45a049;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Edit Profil Pengguna</h2>
    </div>

    <div class="form-container">
        <form action="" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
            </div>
            <div>
                <label for="tanggal_lahir">Tanggal Lahir:</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($tanggal_lahir); ?>" required>
            </div>
            <div>
                <label for="alamat">Alamat:</label>
                <input type="text" id="alamat" name="alamat" value="<?= htmlspecialchars($alamat); ?>" required>
            </div>
         
            <div class="button-container">
                <button type="submit" class="save-button">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <div class="footer">
        <p>Terima kasih telah menggunakan sistem kami!</p>
    </div>
</div>

</body>
</html>

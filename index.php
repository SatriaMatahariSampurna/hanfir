<?php
session_start();
include 'db.php';

// Variabel untuk menyimpan pesan kesalahan
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk mengambil data pengguna berdasarkan username
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $stored_password, $role);

    if ($stmt->fetch()) {
        // Cek apakah password cocok
        if ($password === $stored_password) { // Tidak menggunakan password hashing
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = $role;

            // Arahkan berdasarkan peran pengguna
            if ($role == 'admin') {
                header("Location: dashboard_admin.php");
                exit();
            } else {
                header("Location: dashboard_user.php");
                exit();
            }
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        .login-container p.welcome-text {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            color: #666;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background-color: #45a049;
        }
        .login-container p {
            text-align: center;
            margin: 0;
        }
        .login-container p a {
            color: #4CAF50;
            text-decoration: none;
        }
        .login-container p a:hover {
            text-decoration: underline;
        }
        .toggle-password {
            position: relative;
        }
        .toggle-password input {
            padding-right: 40px;
        }
        .toggle-password span {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
            color: #666;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <p class="welcome-text">Selamat datang, silahkan login</p>

    <!-- Tampilkan pesan kesalahan jika ada -->
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <div class="toggle-password">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span onclick="togglePassword()">👁️</span>
        </div>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Registrasi di sini</a></p>
</div>

<script>
    // Fungsi untuk toggle lihat/tutup password
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const passwordIcon = passwordField.nextElementSibling;

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            passwordIcon.textContent = '🙈';
        } else {
            passwordField.type = 'password';
            passwordIcon.textContent = '👁️';
        }
    }
</script>

</body>
</html>

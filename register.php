<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email']; // Email pengguna
    $dob = $_POST['dob']; // Tanggal lahir
    $address = $_POST['address']; // Alamat
    $password = $_POST['password']; // Simpan password sebagai teks biasa

    // Cek apakah username atau email sudah ada di database
    $stmt_check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika username atau email sudah ada
        echo "Username atau email sudah digunakan. Silakan pilih yang lain.";
    } else {
        // Query untuk menyimpan data ke dalam tabel users
        $stmt = $conn->prepare("INSERT INTO users (username, password, alamat, email, tanggal_lahir) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $password, $address, $email, $dob);

        if ($stmt->execute()) {
            echo "Registration successful! <a href='index.php'>Login here</a>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
?>

<!-- HTML Form with styling -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"], input[type="date"], textarea, input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .form-container a {
            display: block;
            text-align: center;
            margin-top: 10px;
            text-decoration: none;
            color: #007BFF;
        }

        .form-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Register</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="date" name="dob" placeholder="Tanggal Lahir" required>
        <textarea name="address" placeholder="Alamat" required></textarea>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <a href="index.php">Already have an account? Login here</a>
</div>

</body>
</html>

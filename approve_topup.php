<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$request_id = $_GET['id'];

// Menyetuji permintaan top up
$stmt = $conn->prepare("UPDATE top_up_requests SET status = 'approved' WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->close();

// Menambahkan saldo pengguna
$stmt = $conn->prepare("SELECT amount, user_id FROM top_up_requests WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->bind_result($amount, $user_id);
$stmt->fetch();
$stmt->close();

// Update saldo pengguna
$stmt = $conn->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
$stmt->bind_param("di", $amount, $user_id);
$stmt->execute();
$stmt->close();

header("Location: admin_dashboard.php");
exit();
?>

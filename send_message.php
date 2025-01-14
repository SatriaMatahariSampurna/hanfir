<?php
session_start();
include 'db.php';

// Cek jika user belum login
if ($_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil pesan dan penerima dari form
$message = $_POST['message'];
$receiver_id = $_POST['receiver_id'];

// Simpan pesan ke database
$stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $receiver_id, $message);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect kembali ke halaman chat
header("Location: chat.php?friend_id=" . $receiver_id);
exit();
?>

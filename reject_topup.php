<?php
session_start();
include 'db.php';

// Cek apakah admin sudah login
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$request_id = $_GET['id'];

// Menolak permintaan top up
$stmt = $conn->prepare("UPDATE top_up_requests SET status = 'rejected' WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->close();

header("Location: admin_dashboard.php");
exit();
?>

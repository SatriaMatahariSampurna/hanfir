<?php
session_start();
include 'db.php';

// Cek apakah user sudah login dan memiliki role 'admin'
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Ambil data pengguna
$user_query = "SELECT id, username, email, saldo FROM users";
$user_result = $conn->query($user_query);

// Ambil data permintaan top up
$topup_query = "SELECT tr.request_id, u.username, tr.amount, tr.status FROM top_up_requests tr JOIN users u ON tr.user_id = u.id";
$topup_result = $conn->query($topup_query);

// Ambil data riwayat transaksi dari tabel transaction_logs
$transaction_query = "
    SELECT 
        t.id AS transaction_id,
        u.username AS user,
        t.type,
        t.amount,
        tu.username AS target_user,
        t.created_at
    FROM 
        transaction_logs t
    LEFT JOIN 
        users u ON t.user_id = u.id
    LEFT JOIN 
        users tu ON t.target_user_id = tu.id
    WHERE 
        t.type = 'transfer'
    ORDER BY 
        t.created_at DESC
";
$transaction_result = $conn->query($transaction_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
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
        .table-container {
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            color: #333;
        }
        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #4CAF50;
            color: white;
        }
        .status {
            padding: 6px 12px;
            border-radius: 5px;
        }
        .approved {
            background-color: #4CAF50;
            color: white;
        }
        .rejected {
            background-color: #f44336;
            color: white;
        }
        .pending {
            background-color: #ff9800;
            color: white;
        }
        .button-container {
            text-align: end;
            margin-top: 20px;
        }
        .edit-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .edit-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Admin Dashboard</h2>
        <p>Welcome, Admin!</p>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- Tabel Data Pengguna -->
    <div class="table-container">
        <h3>Data Pengguna</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Saldo</th>
            </tr>
            <?php while ($user = $user_result->fetch_assoc()) { ?>
            <tr>
                <td><?= $user['id']; ?></td>
                <td><?= $user['username']; ?></td>
                <td><?= $user['email']; ?></td>
                <td><?= $user['saldo']; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Tabel Permintaan Top Up -->
    <div class="table-container">
        <h3>Permintaan Top Up</h3>
        <table>
            <tr>
                <th>Request ID</th>
                <th>Username</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($topup = $topup_result->fetch_assoc()) { ?>
            <tr>
                <td><?= $topup['request_id']; ?></td>
                <td><?= $topup['username']; ?></td>
                <td><?= $topup['amount']; ?></td>
                <td class="status <?= strtolower($topup['status']); ?>"><?= ucfirst($topup['status']); ?></td>
                <td>
                    <?php if ($topup['status'] == 'pending') { ?>
                    <a href="approve_topup.php?id=<?= $topup['request_id']; ?>" class="edit-button">Approve</a>
                    <a href="reject_topup.php?id=<?= $topup['request_id']; ?>" class="edit-button">Reject</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

 <!-- Tabel Riwayat Transaksi -->
<div class="table-container">
    <h3>Riwayat Transaksi</h3>
    <table>
        <tr>
            <th>ID Transaksi</th>
            <th>Pengguna</th>
            <th>Jenis Transaksi</th>
            <th>Jumlah</th>
            <th>Pengguna Tujuan</th>
            <th>Tanggal Transaksi</th>
        </tr>
        <?php while ($transaction = $transaction_result->fetch_assoc()) { ?>
        <tr>
            <td><?= $transaction['transaction_id']; ?></td>
            <td><?= $transaction['user']; ?></td>
            <td><?= ucfirst($transaction['type']); ?></td>
            <td><?= $transaction['amount']; ?></td>
            <td><?= $transaction['target_user'] ? $transaction['target_user'] : '-'; ?></td>
            <td><?= $transaction['created_at']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>


</body>
</html>

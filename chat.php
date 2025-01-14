<?php
session_start();
include 'db.php';

// Cek apakah user sudah login
if ($_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk menampilkan semua pengguna selain yang sedang login
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($friend_id, $friend_username);

// Menampilkan teman yang sudah terdaftar
$friends = [];
while ($stmt->fetch()) {
    $friends[] = ['id' => $friend_id, 'username' => $friend_username];
}

// Jika ada teman yang dipilih, tampilkan pesan
$selected_friend_id = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;
$selected_friend_username = '';

if ($selected_friend_id) {
    // Query untuk mengambil pesan antara user yang login dan teman yang dipilih
    $stmt = $conn->prepare("SELECT sender_id, receiver_id, message, sent_at FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at");
    $stmt->bind_param("iiii", $user_id, $selected_friend_id, $selected_friend_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($sender_id, $receiver_id, $message, $sent_at);

    $messages = [];
    while ($stmt->fetch()) {
        // Memasukkan pesan tanpa sanitasi (celah XSS)
        $messages[] = ['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'message' => $message, 'sent_at' => $sent_at];
    }

    // Ambil nama teman yang dipilih
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $selected_friend_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($selected_friend_username);
    $stmt->fetch();
}

// Kirim pesan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message']; // Tidak ada validasi/sanitasi input
    $receiver_id = $_POST['receiver_id'];

    if (!empty($message)) {
        // Query untuk menyimpan pesan tanpa sanitasi
        $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            display: flex;
            flex-direction: row;
        }

        /* Container utama */
        .container {
            width: 100%;
            display: flex;
            max-width: 900px;
            margin: 150px auto;
        }

        /* Sidebar teman */
        .friend-list {
            width: 30%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
            overflow-y: auto;
            height: 600px;
        }

        .friend-list h4 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .friend-list a {
            display: block;
            padding: 10px;
            background-color: #e0e0e0;
            border-radius: 6px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .friend-list a:hover {
            background-color: #4CAF50;
            color: #fff;
        }

        /* Box chat */
        .chat-box {
            width: 70%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .chat-box h4 {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px 0;
            border-bottom: 2px solid #ddd;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .chat-message {
            margin: 15px 0;
            display: flex;
            flex-direction: column;
        }

        .chat-message .message {
            padding: 12px;
            background-color: #f1f1f1;
            border-radius: 8px;
            max-width: 75%;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .chat-message .message.sent {
            background-color: #d1f5d1;
            align-self: flex-end;
        }

        .chat-message .message.received {
            background-color: #e0e0e0;
            align-self: flex-start;
        }

        .chat-message span {
            font-size: 12px;
            color: #888;
            text-align: right;
            margin-top: 5px;
        }

        /* Form kirim pesan */
        .send-message {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .send-message input[type="text"] {
            width: 150%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .send-message button {
            width: 20%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #4CAF50;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .send-message button:hover {
            background-color: #45a049;
        }

        /* Circular 'Cari Teman' button */
        .search-button {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            padding: 18px;
            font-size: 24px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .search-button:hover {
            background-color: #45a049;
        }

        /* Bottom Navigation */
        .bottom-nav {
            display: flex;
            justify-content: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .bottom-nav a {
            color: #333;
            font-size: 24px;
            text-decoration: none;
            padding: 12px 15px;
            margin: 0 10px;
            border-radius: 50%;
            background-color: #f1f1f1;
            transition: background-color 0.3s ease;
        }

        .bottom-nav a:hover {
            background-color: #4CAF50;
            color: #fff;
        }
        .back-button {
    text-align: center;
    margin-top: 20px;
}

.back-button button {
    padding: 12px 20px;
    border-radius: 6px;
    background-color: #4CAF50;
    color: white;
    border: none;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.back-button button:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar Teman -->
    <div class="friend-list">
        <h4>Teman</h4>
        <?php foreach ($friends as $friend): ?>
            <a href="chat.php?friend_id=<?= $friend['id'] ?>"><?= $friend['username'] ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Box Chat -->
    <div class="chat-box">
        <?php if ($selected_friend_id): ?>
            <h4>Chat dengan <?= $selected_friend_username ?></h4>
            <?php if (count($messages) == 0): ?>
                <p style="text-align: center; font-style: italic; color: #888;">Belum ada pesan</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="chat-message">
                        <!-- Menampilkan pesan tanpa htmlspecialchars (celah XSS) -->
                        <div class="message <?= ($msg['sender_id'] == $user_id) ? 'sent' : 'received' ?>">
                            <?= $msg['message'] ?>
                        </div>
                        <span><?= date('d-m-Y H:i', strtotime($msg['sent_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="send-message">
                <form action="chat.php?friend_id=<?= $selected_friend_id ?>" method="POST">
                    <input type="text" name="message" placeholder="Tulis pesan..." required>
                    <input type="hidden" name="receiver_id" value="<?= $selected_friend_id ?>">
                    <button type="submit">Kirim</button>
                </form>
            </div>
        <?php else: ?>
            <p style="text-align: center; font-style: italic; color: #888;">Silakan pilih teman dari sidebar untuk mulai chat</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
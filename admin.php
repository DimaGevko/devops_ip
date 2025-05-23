<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Отримання журналів
$conn = connectDB();
$reg_logs = $conn->query("SELECT r.*, u.username FROM registration_log r JOIN users u ON r.user_id = u.id ORDER BY log_time DESC");
$op_logs = $conn->query("SELECT o.*, u.username FROM operation_log o JOIN users u ON o.user_id = u.id ORDER BY log_time DESC");
$users = $conn->query("SELECT id, username, email, role FROM users");
$conn->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Адмін-панель</h2>
        <h4>Реєстраційний журнал</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Користувач</th>
                    <th>Дія</th>
                    <th>Час</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $reg_logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo $log['action']; ?></td>
                        <td><?php echo $log['log_time']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h4>Операційний журнал</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Користувач</th>
                    <th>Дія</th>
                    <th>Час</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $op_logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                        <td><?php echo $log['log_time']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h4>Користувачі</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логін</th>
                    <th>Email</th>
                    <th>Роль</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['role']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="logout.php" class="btn btn-secondary">Вийти</a>
    </div>
</body>
</html>
<?php
session_start();

require_once 'config.php';
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Перевірка часу останньої аутентифікації (4 хвилини = 240 секунд)
if (time() - $_SESSION['last_auth'] > 240) {
    header("Location: reauth.php");
    exit;
}

// Конвертація валют через API
if (isset($_POST['convert'])) {
    $from = $_POST['from_currency'];
    $to = $_POST['to_currency'];
    $amount = floatval($_POST['amount']);
    
    $api_key = '14decb598e33ebb3320e4084'; // Замініть на ваш ключ exchangerate-api
    $url = "https://v6.exchangerate-api.com/v6/$api_key/latest/$from";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data['result'] === 'success') {
        $rate = $data['conversion_rates'][$to];
        $result = $amount * $rate;
        
        // Збереження в історію конвертацій
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO conversion_history (user_id, from_currency, to_currency, amount, result) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdd", $_SESSION['user_id'], $from, $to, $amount, $result);
        $stmt->execute();
        $stmt->close();
        
        // Логування операції
        logOperation($_SESSION['user_id'], "Конвертація: $amount $from -> $result $to");
        $conn->close();
    } else {
        $error = "Помилка API конвертації";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Панель користувача</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Конвертер валют</h2>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="amount" class="form-label">Сума</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="mb-3">
                <label for="from_currency" class="form-label">З валюти</label>
                <select class="form-select" id="from_currency" name="from_currency" required>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="UAH">UAH</option>
                    <!-- Додайте більше валют -->
                </select>
            </div>
            <div class="mb-3">
                <label for="to_currency" class="form-label">У валюту</label>
                <select class="form-select" id="to_currency" name="to_currency" required>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="UAH">UAH</option>
                </select>
            </div>
            <button type="submit" name="convert" class="btn btn-primary">Конвертувати</button>
        </form>
        <?php if (isset($result)): ?>
            <div class="alert alert-success mt-3">
                <?php echo "$amount $from = $result $to"; ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-secondary mt-3">Вийти</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
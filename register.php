<?php
// register.php
require_once 'config.php';
require_once 'functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $questions = $_POST['questions'];
    $answers = $_POST['answers'];
    
    // Password validation
    $error = null;
    if (empty($password)) {
        $error = "Пароль не може бути порожнім";
    } elseif (strlen($password) < 8) {
        $error = "Пароль має містити щонайменше 8 символів";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Пароль має містити хоча б одну цифру";
    } elseif (!preg_match("/[A-Za-z]/", $password)) {
        $error = "Пароль має містити хоча б одну літеру";
    }

    if (!$error) {
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $password = encryptPassword($password, 'temp'); // Temporary salt, will update after getting user_id
        $stmt->bind_param("sss", $username, $password, $email);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            // Update password with proper salt
            $password = encryptPassword($_POST['password'], $user_id);
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $password, $user_id);
            $stmt_update->execute();
            $stmt_update->close();

            // Save authentication questions
            $stmt_q = $conn->prepare("INSERT INTO auth_questions (user_id, question, answer) VALUES (?, ?, ?)");
            for ($i = 0; $i < count($questions); $i++) {
                $enc_answer = encryptPassword($answers[$i], $user_id);
                $stmt_q->bind_param("iss", $user_id, $questions[$i], $enc_answer);
                $stmt_q->execute();
            }
            $stmt_q->close();
            header("Location: index.php");
        } else {
            $error = "Помилка реєстрації";
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Реєстрація</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Логін</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <h5>Питання для аутентифікації</h5>
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="mb-3">
                        <label class="form-label">Питання <?php echo $i + 1; ?></label>
                        <input type="text" class="form-control" name="questions[]" required>
                        <label class="form-label">Відповідь</label>
                        <input type="text" class="form-control" name="answers[]" required>
                    </div>
                <?php endfor; ?>
                <button type="submit" name="register" class="btn btn-primary">Зареєструватися</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
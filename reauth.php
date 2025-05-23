<?php
// reauth.php
session_start();
require_once 'config.php';
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch 3 random questions
$conn = connectDB();
$stmt = $conn->prepare("SELECT id, question FROM auth_questions WHERE user_id = ? ORDER BY RAND() LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (isset($_POST['reauth'])) {
    $answers = $_POST['answers'];
    $correct = true;
    
    foreach ($answers as $qid => $answer) {
        $stmt = $conn->prepare("SELECT answer FROM auth_questions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $qid, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $encrypted_answer = encryptPassword($answer, $_SESSION['user_id']); // Use user ID as salt
        if (abs($result['answer'] - $encrypted_answer) < 0.000001) { // Allow small floating-point differences
            $stmt->close();
        } else {
            $correct = false;
            $stmt->close();
            break;
        }
    }
    
    if ($correct) {
        $_SESSION['last_auth'] = time();
        logOperation($_SESSION['user_id'], "Успішна повторна аутентифікація");
        header("Location: dashboard.php");
    } else {
        $error = "Невірні відповіді";
        logOperation($_SESSION['user_id'], "Невдала повторна аутентифікація");
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Повторна аутентифікація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Повторна аутентифікація</h2>
        <form method="POST">
            <?php foreach ($questions as $q): ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars($q['question']); ?></label>
                    <input type="text" class="form-control" name="answers[<?php echo $q['id']; ?>]" required>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="reauth" class="btn btn-primary">Підтвердити</button>
        </form>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
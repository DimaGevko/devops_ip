<?php
// functions.php

// Password encryption using x/sin(a) with salt
function encryptPassword($password, $salt = '') {
    // Input validation
    if (!is_string($password)) {
        throw new InvalidArgumentException("Password must be a string");
    }

    // Combine password with salt
    $input = $password . $salt;
    $a = strlen($input); // Length of password + salt
    if ($a == 0) {
        return 0; // Return 0 for empty input to avoid division by zero
    }

    // Calculate sum of ASCII values
    $numeric_value = 0;
    for ($i = 0; $i < $a; $i++) {
        $numeric_value += ord($input[$i]);
    }

    // Calculate sin(a) with protection against small values
    $sin_a = sin($a);
    if (abs($sin_a) < 0.0001) {
        $sin_a = 0.0001; // Prevent division by near-zero
    }

    // Compute x/sin(a) and round to 6 decimal places for consistency
    $result = $numeric_value / $sin_a;
    return round($result, 6);
}

// Database connection
function connectDB() {
    require_once 'config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Log registration actions (login/logout)
function logRegistration($user_id, $action) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO registration_log (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Log operations
function logOperation($user_id, $action_description) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO operation_log (user_id, action_description) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action_description);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>
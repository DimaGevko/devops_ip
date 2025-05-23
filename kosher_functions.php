<?php
function encryptPassword($password) {
    $a = strlen($password); // a - довжина пароля
    if ($a == 0) return $password; // Захист від ділення на нуль
    
    // Convert password to a numeric value (sum of ASCII values)
    $numeric_value = 0;
    for ($i = 0; $i < $a; $i++) {
        $numeric_value += ord($password[$i]);
    }
    
    $sin_a = sin($a);
    if (abs($sin_a) < 0.0001) $sin_a = 0.0001; // Захист від ділення на нуль
    return $numeric_value / $sin_a; // Perform division with numeric value
}
?>
<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    logRegistration($_SESSION['user_id'], 'logout');
    session_destroy();
}
header("Location: index.php");
exit;
?>
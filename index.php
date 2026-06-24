<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: pages/login.php');
    exit;
}

header('Location: pages/dashboard.php');
exit;
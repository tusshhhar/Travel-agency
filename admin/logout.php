<?php
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user']);
session_destroy();

header('Location: ' . BASE_URL . '/admin/login.php');
exit;

<?php
/**
 * Bishnoi Travels - Admin Authentication & Security
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function requireAdminLogin(): void {
    if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_user'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function getAdminUser(): ?array {
    if (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_user'])) {
        return $_SESSION['admin_user'];
    }
    return null;
}

function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken(?string $token): bool {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

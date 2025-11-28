<?php
session_start();
require_once 'config.php';

function login($email, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, email, password, role, student_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['student_id'] = $user['student_id'];
            closeDBConnection($conn);
            return true;
        }
    }
    closeDBConnection($conn);
    return false;
}

function logout() {
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'student_id' => $_SESSION['student_id']
        ];
    }
    return null;
}

function requireRole($role) {
    if (!isLoggedIn() || $_SESSION['user_role'] !== $role) {
        header("Location: ../frontend/login.php");
        exit();
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../frontend/login.php");
        exit();
    }
}
?>

<?php
require_once 'config.php';

function createUser($name, $email, $password, $role, $student_id = null) {
    $conn = getDBConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, student_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $student_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getUsers($role = null) {
    $conn = getDBConnection();
    $query = "SELECT id, name, email, role, student_id, created_at FROM users";
    if ($role) {
        $query .= " WHERE role = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $role);
    } else {
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $users;
}

function updateUser($id, $name, $email, $role, $student_id = null) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, student_id = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $role, $student_id, $id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function deleteUser($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function createCourse($name, $professor_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO courses (name, professor_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $professor_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getCourses($professor_id = null) {
    $conn = getDBConnection();
    $query = "SELECT c.id, c.name, c.professor_id, u.name as professor_name FROM courses c JOIN users u ON c.professor_id = u.id";
    if ($professor_id) {
        $query .= " WHERE c.professor_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $professor_id);
    } else {
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $courses;
}

function createGroup($name, $course_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO groups (name, course_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $course_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getGroups($course_id = null) {
    $conn = getDBConnection();
    $query = "SELECT g.id, g.name, g.course_id, c.name as course_name FROM groups g JOIN courses c ON g.course_id = c.id";
    if ($course_id) {
        $query .= " WHERE g.course_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $course_id);
    } else {
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $groups = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $groups;
}

function addStudentToGroup($student_id, $group_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO students_groups (student_id, group_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE student_id = student_id");
    $stmt->bind_param("ii", $student_id, $group_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getStudentsInGroup($group_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT u.id, u.name, u.email, u.student_id FROM users u JOIN students_groups sg ON u.id = sg.student_id WHERE sg.group_id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $students;
}

function getStudentCourses($student_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT c.id, c.name, g.name as group_name FROM courses c JOIN groups g ON c.id = g.course_id JOIN students_groups sg ON g.id = sg.group_id WHERE sg.student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $courses;
}
?>

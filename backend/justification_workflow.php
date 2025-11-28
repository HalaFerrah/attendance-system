<?php
require_once 'config.php';
require_once 'upload.php';

function submitJustification($student_id, $session_id, $reason, $file_path = null) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO justifications (student_id, session_id, reason, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $student_id, $session_id, $reason, $file_path);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getJustificationsByStudent($student_id, $course_id = null) {
    $conn = getDBConnection();
    $query = "SELECT j.*, s.date, s.start_time, s.end_time, c.name as course_name FROM justifications j JOIN attendance_sessions s ON j.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE j.student_id = ?";
    $params = [$student_id];
    $types = "i";

    if ($course_id) {
        $query .= " AND s.course_id = ?";
        $params[] = $course_id;
        $types .= "i";
    }

    $query .= " ORDER BY j.submitted_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $justifications = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $justifications;
}

function getJustificationsByProfessor($professor_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT j.*, u.name as student_name, u.student_id as student_number, s.date, s.start_time, s.end_time, c.name as course_name FROM justifications j JOIN users u ON j.student_id = u.id JOIN attendance_sessions s ON j.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE c.professor_id = ? ORDER BY j.submitted_at DESC");
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $justifications = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $justifications;
}

function getAllJustifications() {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT j.*, u.name as student_name, u.student_id as student_number, s.date, s.start_time, s.end_time, c.name as course_name FROM justifications j JOIN users u ON j.student_id = u.id JOIN attendance_sessions s ON j.session_id = s.id JOIN courses c ON s.course_id = c.id ORDER BY j.submitted_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $justifications = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $justifications;
}

function updateJustificationStatus($justification_id, $status, $reviewed_by) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE justifications SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $reviewed_by, $justification_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}
?>

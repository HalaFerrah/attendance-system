<?php
require_once 'config.php';

function createAttendanceSession($course_id, $group_id, $date, $start_time, $end_time, $created_by) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, start_time, end_time, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssi", $course_id, $group_id, $date, $start_time, $end_time, $created_by);
    $result = $stmt->execute();
    $session_id = $conn->insert_id;
    closeDBConnection($conn);
    return $session_id;
}

function getAttendanceSessions($course_id = null, $group_id = null, $professor_id = null) {
    $conn = getDBConnection();
    $query = "SELECT s.id, s.course_id, s.group_id, s.date, s.start_time, s.end_time, s.status, s.created_by, c.name as course_name, g.name as group_name, u.name as professor_name FROM attendance_sessions s JOIN courses c ON s.course_id = c.id JOIN groups g ON s.group_id = g.id JOIN users u ON s.created_by = u.id";
    $conditions = [];
    $params = [];
    $types = "";

    if ($course_id) {
        $conditions[] = "s.course_id = ?";
        $params[] = $course_id;
        $types .= "i";
    }
    if ($group_id) {
        $conditions[] = "s.group_id = ?";
        $params[] = $group_id;
        $types .= "i";
    }
    if ($professor_id) {
        $conditions[] = "s.created_by = ?";
        $params[] = $professor_id;
        $types .= "i";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    $query .= " ORDER BY s.date DESC, s.start_time DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $sessions;
}

function markAttendance($session_id, $student_id, $status) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
    $stmt->bind_param("iis", $session_id, $student_id, $status);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getAttendanceRecords($session_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.student_id, r.status, u.name, u.student_id as student_number FROM attendance_records r JOIN users u ON r.student_id = u.id WHERE r.session_id = ?");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $records;
}

function closeAttendanceSession($session_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
    $stmt->bind_param("i", $session_id);
    $result = $stmt->execute();
    closeDBConnection($conn);
    return $result;
}

function getStudentAttendance($student_id, $course_id = null) {
    $conn = getDBConnection();
    $query = "SELECT s.id, s.date, s.start_time, s.end_time, c.name as course_name, r.status FROM attendance_sessions s JOIN courses c ON s.course_id = c.id LEFT JOIN attendance_records r ON s.id = r.session_id AND r.student_id = ? JOIN students_groups sg ON s.group_id = sg.group_id WHERE sg.student_id = ?";
    $params = [$student_id, $student_id];
    $types = "ii";

    if ($course_id) {
        $query .= " AND s.course_id = ?";
        $params[] = $course_id;
        $types .= "i";
    }

    $query .= " ORDER BY s.date DESC, s.start_time DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $attendance;
}
?>

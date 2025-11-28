<?php
require_once 'config.php';

function getAttendanceSummary($course_id = null, $group_id = null, $student_id = null) {
    $conn = getDBConnection();
    $query = "SELECT
        u.id,
        u.name,
        u.student_id,
        COUNT(s.id) as total_sessions,
        SUM(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN r.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        ROUND((SUM(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) / COUNT(s.id)) * 100, 2) as attendance_percentage
    FROM users u
    JOIN students_groups sg ON u.id = sg.student_id
    JOIN groups g ON sg.group_id = g.id
    JOIN courses c ON g.course_id = c.id
    JOIN attendance_sessions s ON g.id = s.group_id
    LEFT JOIN attendance_records r ON s.id = r.session_id AND u.id = r.student_id
    WHERE u.role = 'student'";

    $conditions = [];
    $params = [];
    $types = "";

    if ($course_id) {
        $conditions[] = "c.id = ?";
        $params[] = $course_id;
        $types .= "i";
    }
    if ($group_id) {
        $conditions[] = "g.id = ?";
        $params[] = $group_id;
        $types .= "i";
    }
    if ($student_id) {
        $conditions[] = "u.id = ?";
        $params[] = $student_id;
        $types .= "i";
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " GROUP BY u.id, u.name, u.student_id ORDER BY attendance_percentage DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $summary;
}

function getCourseStatistics() {
    $conn = getDBConnection();
    $query = "SELECT
        c.name as course_name,
        COUNT(DISTINCT s.id) as total_sessions,
        COUNT(DISTINCT sg.student_id) as total_students,
        SUM(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN r.status = 'absent' THEN 1 ELSE 0 END) as total_absent,
        ROUND(AVG(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END), 2) as avg_attendance_per_session
    FROM courses c
    LEFT JOIN groups g ON c.id = g.course_id
    LEFT JOIN students_groups sg ON g.id = sg.group_id
    LEFT JOIN attendance_sessions s ON g.id = s.group_id
    LEFT JOIN attendance_records r ON s.id = r.session_id
    GROUP BY c.id, c.name";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $stats;
}

function getParticipationTracking($course_id = null) {
    $conn = getDBConnection();
    $query = "SELECT
        u.name as student_name,
        u.student_id,
        c.name as course_name,
        COUNT(DISTINCT s.id) as sessions_attended,
        COUNT(DISTINCT j.id) as justifications_submitted,
        AVG(CASE WHEN r.status = 'present' THEN 1 ELSE 0 END) as participation_rate
    FROM users u
    JOIN students_groups sg ON u.id = sg.student_id
    JOIN groups g ON sg.group_id = g.id
    JOIN courses c ON g.course_id = c.id
    LEFT JOIN attendance_sessions s ON g.id = s.group_id
    LEFT JOIN attendance_records r ON s.id = r.session_id AND u.id = r.student_id
    LEFT JOIN justifications j ON u.id = j.student_id AND s.id = j.session_id
    WHERE u.role = 'student'";

    $params = [];
    $types = "";

    if ($course_id) {
        $query .= " AND c.id = ?";
        $params[] = $course_id;
        $types .= "i";
    }

    $query .= " GROUP BY u.id, u.name, u.student_id, c.id, c.name ORDER BY participation_rate DESC";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $participation = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);
    return $participation;
}

function getOverallStatistics() {
    $conn = getDBConnection();
    $query = "SELECT
        COUNT(DISTINCT u.id) as total_students,
        COUNT(DISTINCT c.id) as total_courses,
        COUNT(DISTINCT s.id) as total_sessions,
        COUNT(DISTINCT CASE WHEN r.status = 'present' THEN r.id END) as total_present,
        COUNT(DISTINCT CASE WHEN r.status = 'absent' THEN r.id END) as total_absent,
        ROUND((COUNT(DISTINCT CASE WHEN r.status = 'present' THEN r.id END) / NULLIF(COUNT(r.id), 0)) * 100, 2) as overall_attendance_rate
    FROM users u
    CROSS JOIN courses c
    LEFT JOIN groups g ON c.id = g.course_id
    LEFT JOIN attendance_sessions s ON g.id = s.group_id
    LEFT JOIN attendance_records r ON s.id = r.session_id";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    closeDBConnection($conn);
    return $stats;
}
?>

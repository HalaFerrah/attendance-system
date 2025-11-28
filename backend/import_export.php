<?php
require_once 'config.php';
require_once 'crud.php';

// Note: This requires PHPExcel or PhpSpreadsheet library for Excel handling
// For simplicity, we'll use basic CSV handling. In production, install PhpSpreadsheet.

function importStudentsFromExcel($file_path, $group_id) {
    $conn = getDBConnection();
    $errors = [];
    $success_count = 0;

    if (($handle = fopen($file_path, "r")) !== FALSE) {
        // Skip header row
        fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $name = trim($data[0]);
            $email = trim($data[1]);
            $student_id = trim($data[2]);

            // Validate data
            if (empty($name) || empty($email) || empty($student_id)) {
                $errors[] = "Missing data for student: $name, $email, $student_id";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email: $email";
                continue;
            }

            // Check if student already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
            $stmt->bind_param("ss", $email, $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $existing = $result->fetch_assoc();
                // Add to group if not already
                addStudentToGroup($existing['id'], $group_id);
                $success_count++;
            } else {
                // Create new student
                $password = password_hash('defaultpassword', PASSWORD_DEFAULT); // In production, generate random password
                $user_id = createUser($name, $email, $password, 'student', $student_id);
                if ($user_id) {
                    addStudentToGroup($user_id, $group_id);
                    $success_count++;
                } else {
                    $errors[] = "Failed to create student: $name";
                }
            }
        }
        fclose($handle);
    } else {
        $errors[] = "Could not open file: $file_path";
    }

    closeDBConnection($conn);
    return ['success_count' => $success_count, 'errors' => $errors];
}

function exportStudentsToExcel($group_id = null) {
    $conn = getDBConnection();

    $query = "SELECT u.name, u.email, u.student_id, c.name as course_name, g.name as group_name
              FROM users u
              JOIN students_groups sg ON u.id = sg.student_id
              JOIN groups g ON sg.group_id = g.id
              JOIN courses c ON g.course_id = c.id
              WHERE u.role = 'student'";
    $params = [];
    $types = "";

    if ($group_id) {
        $query .= " AND sg.group_id = ?";
        $params[] = $group_id;
        $types = "i";
    }

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);

    // Generate CSV content
    $csv_content = "Name,Email,Student ID,Course,Group\n";
    foreach ($students as $student) {
        $csv_content .= '"' . $student['name'] . '","' . $student['email'] . '","' . $student['student_id'] . '","' . $student['course_name'] . '","' . $student['group_name'] . "\"\n";
    }

    return $csv_content;
}

function exportAttendanceReport($course_id = null, $start_date = null, $end_date = null) {
    $conn = getDBConnection();

    $query = "SELECT u.name, u.student_id, s.date, s.start_time, s.end_time, r.status, c.name as course_name
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
    if ($start_date) {
        $conditions[] = "s.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    if ($end_date) {
        $conditions[] = "s.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY u.name, s.date";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);
    closeDBConnection($conn);

    // Generate CSV content
    $csv_content = "Student Name,Student ID,Course,Date,Time,Status\n";
    foreach ($records as $record) {
        $status = $record['status'] ? ucfirst($record['status']) : 'Not Marked';
        $csv_content .= '"' . $record['name'] . '","' . $record['student_id'] . '","' . $record['course_name'] . '","' . $record['date'] . '","' . $record['start_time'] . ' - ' . $record['end_time'] . '","' . $status . "\"\n";
    }

    return $csv_content;
}
?>

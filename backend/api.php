<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'crud.php';
require_once 'session_management.php';
require_once 'justification_workflow.php';
require_once 'upload.php';
require_once 'import_export.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (login($email, $password)) {
            $user = getCurrentUser();
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        break;

    case 'mark_attendance':
        requireRole('professor');
        $session_id = $_POST['session_id'] ?? 0;
        $student_id = $_POST['student_id'] ?? 0;
        $status = $_POST['status'] ?? '';

        if (markAttendance($session_id, $student_id, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
        }
        break;

    case 'create_session':
        requireRole('professor');
        $user = getCurrentUser();
        $course_id = $_POST['course_id'] ?? 0;
        $group_id = $_POST['group_id'] ?? 0;
        $date = $_POST['date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';

        if (createAttendanceSession($course_id, $group_id, $date, $start_time, $end_time, $user['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create session']);
        }
        break;

    case 'close_session':
        requireRole('professor');
        $session_id = $_POST['session_id'] ?? 0;

        if (closeAttendanceSession($session_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to close session']);
        }
        break;

    case 'submit_justification':
        requireRole('student');
        $user = getCurrentUser();
        $session_id = $_POST['session_id'] ?? 0;
        $reason = $_POST['reason'] ?? '';

        $file_path = null;
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $upload_result = uploadFile($_FILES['file']);
            if ($upload_result['success']) {
                $file_path = $upload_result['file_path'];
            } else {
                echo json_encode(['success' => false, 'message' => $upload_result['message']]);
                exit();
            }
        }

        if (submitJustification($user['id'], $session_id, $reason, $file_path)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit justification']);
        }
        break;

    case 'review_justification':
        $user = getCurrentUser();
        if ($user['role'] !== 'professor' && $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $justification_id = $_POST['justification_id'] ?? 0;
        $status = $_POST['status'] ?? '';

        if (updateJustificationStatus($justification_id, $status, $user['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update justification']);
        }
        break;

    case 'get_justification_details':
        $user = getCurrentUser();
        if ($user['role'] !== 'professor' && $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $justification_id = $_POST['justification_id'] ?? 0;

        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT j.*, u.name as student_name, u.student_id as student_number, s.date, s.start_time, s.end_time, c.name as course_name FROM justifications j JOIN users u ON j.student_id = u.id JOIN attendance_sessions s ON j.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE j.id = ?");
        $stmt->bind_param("i", $justification_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $justification = $result->fetch_assoc();
        closeDBConnection($conn);

        if ($justification) {
            echo json_encode(['success' => true, 'data' => $justification]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Justification not found']);
        }
        break;

    case 'add_user':
        requireRole('admin');
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';
        $student_id = $_POST['student_id'] ?? null;
        $password = $_POST['password'] ?? '';

        if (createUser($name, $email, $password, $role, $student_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user']);
        }
        break;

    case 'delete_user':
        requireRole('admin');
        $user_id = $_POST['user_id'] ?? 0;

        if (deleteUser($user_id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        break;

    case 'import_students':
        requireRole('admin');
        if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
            $result = importStudentsFromCSV($_FILES['file']['tmp_name'], $_POST['group_id'] ?? 0);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        }
        break;

    case 'export_students':
        requireRole('admin');
        $filename = 'students_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Student ID']);

        $users = getAllUsers();
        foreach ($users as $user) {
            fputcsv($output, [$user['id'], $user['name'], $user['email'], $user['role'], $user['student_id']]);
        }
        fclose($output);
        exit();

    case 'export_attendance':
        $user = getCurrentUser();
        if ($user['role'] !== 'professor' && $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $course_id = $_POST['course_id'] ?? 0;
        $filename = 'attendance_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student Name', 'Student ID', 'Total Sessions', 'Present', 'Absent', 'Attendance Rate']);

        $summary = getAttendanceSummary(null, null, null, $course_id);
        foreach ($summary as $record) {
            fputcsv($output, [
                $record['name'],
                $record['student_id'],
                $record['total_sessions'],
                $record['present_count'],
                $record['absent_count'],
                $record['attendance_percentage'] . '%'
            ]);
        }
        fclose($output);
        exit();

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>

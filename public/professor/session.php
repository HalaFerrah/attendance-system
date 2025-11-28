<?php
require_once '../../backend/auth.php';
require_once '../../backend/session_management.php';

requireRole('professor');
$user = getCurrentUser();

$session_id = $_GET['session_id'] ?? 0;
if (!$session_id) {
    header("Location: home.php");
    exit();
}

$session = getAttendanceSession($session_id);
if (!$session || $session['professor_id'] != $user['id']) {
    header("Location: home.php");
    exit();
}

$students = getStudentsInGroup($session['group_id']);
$attendance = getAttendanceForSession($session_id);
$attendance_map = [];
foreach ($attendance as $record) {
    $attendance_map[$record['student_id']] = $record;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f8ff;
            color: #333;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        nav {
            background-color: #0056b3;
            padding: 0.5rem;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        nav li {
            margin: 0 1rem;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav a:hover {
            background-color: #004085;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .session-info {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin: 0.25rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .status-present {
            color: #28a745;
            font-weight: bold;
        }
        .status-absent {
            color: #dc3545;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .attendance-controls {
            display: flex;
            gap: 0.5rem;
        }
        .session-actions {
            text-align: center;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
            }
            nav li {
                margin: 0.5rem 0;
            }
            .container {
                padding: 1rem;
            }
            table {
                font-size: 0.9rem;
            }
            .attendance-controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Algiers University Attendance System</h1>
        <p>Professor <?php echo htmlspecialchars($user['name']); ?></p>
    </header>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="summary.php">Summary</a></li>
            <li><a href="justifications.php">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="session-info">
            <h2>Mark Attendance</h2>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($session['course_name']); ?></p>
            <p><strong>Group:</strong> <?php echo htmlspecialchars($session['group_name']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($session['date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($session['start_time'] . ' - ' . $session['end_time']); ?></p>
            <p><strong>Status:</strong> <span class="<?php echo $session['status'] == 'open' ? 'status-present' : 'status-absent'; ?>"><?php echo htmlspecialchars(ucfirst($session['status'])); ?></span></p>
        </div>

        <?php if ($session['status'] == 'open'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td>
                                <?php
                                $current_status = isset($attendance_map[$student['id']]) ? $attendance_map[$student['id']]['status'] : 'pending';
                                $status_class = $current_status == 'present' ? 'status-present' : ($current_status == 'absent' ? 'status-absent' : 'status-pending');
                                ?>
                                <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($current_status)); ?></span>
                            </td>
                            <td>
                                <div class="attendance-controls">
                                    <button class="btn btn-success" onclick="markAttendance(<?php echo $student['id']; ?>, 'present')">Present</button>
                                    <button class="btn btn-danger" onclick="markAttendance(<?php echo $student['id']; ?>, 'absent')">Absent</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="session-actions">
                <button class="btn btn-warning" onclick="closeSession()">Close Session</button>
            </div>
        <?php else: ?>
            <div class="session-info">
                <h3>Session Closed</h3>
                <p>This attendance session has been closed. You can view the summary below.</p>
                <a href="summary.php?session_id=<?php echo $session_id; ?>" class="btn">View Summary</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td>
                                <?php
                                $current_status = isset($attendance_map[$student['id']]) ? $attendance_map[$student['id']]['status'] : 'absent';
                                $status_class = $current_status == 'present' ? 'status-present' : 'status-absent';
                                ?>
                                <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($current_status)); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="home.php" class="btn">Back to Home</a>
        </div>
    </div>

    <script>
        function markAttendance(studentId, status) {
            $.ajax({
                url: '../../backend/api.php',
                type: 'POST',
                data: {
                    action: 'mark_attendance',
                    session_id: <?php echo $session_id; ?>,
                    student_id: studentId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error marking attendance: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error marking attendance. Please try again.');
                }
            });
        }

        function closeSession() {
            if (confirm('Are you sure you want to close this attendance session? This action cannot be undone.')) {
                $.ajax({
                    url: '../../backend/api.php',
                    type: 'POST',
                    data: {
                        action: 'close_session',
                        session_id: <?php echo $session_id; ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Session closed successfully!');
                            location.reload();
                        } else {
                            alert('Error closing session: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error closing session. Please try again.');
                    }
                });
            }
        }
    </script>
</body>
</html>

<?php
require_once '../../backend/auth.php';
require_once '../../backend/session_management.php';
require_once '../../backend/justification_workflow.php';

requireRole('student');
$user = getCurrentUser();

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header("Location: home.php");
    exit();
}

// Get course name
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT name FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
closeDBConnection($conn);

if (!$course) {
    header("Location: home.php");
    exit();
}

$attendance = getStudentAttendance($user['id'], $course_id);
$justifications = getJustificationsByStudent($user['id'], $course_id);

$attendance_map = [];
foreach ($attendance as $record) {
    $attendance_map[$record['id']] = $record;
}

$justification_map = [];
foreach ($justifications as $justification) {
    $justification_map[$justification['session_id']] = $justification;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Attendance System</title>
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
        nav a:hover, nav a.active {
            background-color: #004085;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .course-info {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .attendance-summary {
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
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .status-present {
            color: #28a745;
            font-weight: bold;
        }
        .status-absent {
            color: #dc3545;
            font-weight: bold;
        }
        .justification-form {
            display: none;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        }
    </style>
</head>
<body>
    <header>
        <h1>Algiers University Attendance System</h1>
        <p>Student <?php echo htmlspecialchars($user['name']); ?></p>
    </header>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="#" class="active">Attendance</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="course-info">
            <h2>Attendance for <?php echo htmlspecialchars($course['name']); ?></h2>
        </div>

        <div class="attendance-summary">
            <?php
            $total_sessions = count($attendance);
            $present_count = 0;
            foreach ($attendance as $record) {
                if ($record['status'] == 'present') {
                    $present_count++;
                }
            }
            $attendance_percentage = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100, 2) : 0;
            ?>
            <h3>Attendance Summary</h3>
            <p><strong>Total Sessions:</strong> <?php echo $total_sessions; ?></p>
            <p><strong>Present:</strong> <?php echo $present_count; ?></p>
            <p><strong>Absent:</strong> <?php echo $total_sessions - $present_count; ?></p>
            <p><strong>Attendance Rate:</strong> <?php echo $attendance_percentage; ?>%</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Justification</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['date']); ?></td>
                        <td><?php echo htmlspecialchars($record['start_time'] . ' - ' . $record['end_time']); ?></td>
                        <td class="<?php echo $record['status'] == 'present' ? 'status-present' : 'status-absent'; ?>">
                            <?php echo htmlspecialchars(ucfirst($record['status'])); ?>
                        </td>
                        <td>
                            <?php if ($record['status'] == 'absent'): ?>
                                <?php if (isset($justification_map[$record['id']])): ?>
                                    <?php $just = $justification_map[$record['id']]; ?>
                                    <span class="<?php echo $just['status'] == 'approved' ? 'status-present' : ($just['status'] == 'rejected' ? 'status-absent' : ''); ?>">
                                        <?php echo htmlspecialchars(ucfirst($just['status'])); ?>
                                    </span>
                                <?php else: ?>
                                    <button class="btn btn-warning submit-justification" data-session-id="<?php echo $record['id']; ?>">Submit Justification</button>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="justification-form" id="justification-form">
            <h3>Submit Justification</h3>
            <form id="justification-form-data">
                <input type="hidden" id="justification-session-id" name="session_id">
                <div class="form-group">
                    <label for="reason">Reason:</label>
                    <textarea id="reason" name="reason" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="file">Upload File (optional):</label>
                    <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                </div>
                <button type="submit" class="btn">Submit</button>
                <button type="button" class="btn" onclick="$('.justification-form').hide();">Cancel</button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="home.php" class="btn">Back to Home</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.submit-justification').click(function() {
                var sessionId = $(this).data('session-id');
                $('#justification-session-id').val(sessionId);
                $('.justification-form').show();
            });

            $('#justification-form-data').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: '../../backend/api.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Justification submitted successfully!');
                            location.reload();
                        } else {
                            alert('Error submitting justification: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error submitting justification. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>

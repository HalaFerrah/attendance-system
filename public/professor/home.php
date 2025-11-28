<?php
require_once '../../backend/auth.php';
require_once '../../backend/crud.php';
require_once '../../backend/session_management.php';

requireRole('professor');
$user = getCurrentUser();

$courses = getCourses($user['id']);
$sessions = getAttendanceSessions(null, null, $user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Home - Attendance System</title>
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
        .welcome-section {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .dashboard-card {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-card h3 {
            margin-top: 0;
            color: #007bff;
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
        .status-open {
            color: #28a745;
            font-weight: bold;
        }
        .status-closed {
            color: #dc3545;
            font-weight: bold;
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
            .dashboard-grid {
                grid-template-columns: 1fr;
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
        <p>Professor <?php echo htmlspecialchars($user['name']); ?></p>
    </header>
    <nav>
        <ul>
            <li><a href="#" class="active">Home</a></li>
            <li><a href="summary.php">Summary</a></li>
            <li><a href="justifications.php">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="welcome-section">
            <h2>Dashboard</h2>
            <p>Welcome to your attendance management dashboard. Here you can manage your courses and attendance sessions.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>My Courses</h3>
                <p>You are teaching <?php echo count($courses); ?> course(s).</p>
                <ul>
                    <?php foreach ($courses as $course): ?>
                        <li><?php echo htmlspecialchars($course['name']); ?> (<?php echo $course['total_students'] ?? 0; ?> students)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="dashboard-card">
                <h3>Recent Sessions</h3>
                <p>You have <?php echo count($sessions); ?> attendance session(s).</p>
                <p><?php echo count(array_filter($sessions, function($s) { return $s['status'] == 'open'; })); ?> open session(s).</p>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>Attendance Sessions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Group</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($session['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['group_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['date']); ?></td>
                            <td><?php echo htmlspecialchars($session['start_time'] . ' - ' . $session['end_time']); ?></td>
                            <td class="<?php echo $session['status'] == 'open' ? 'status-open' : 'status-closed'; ?>">
                                <?php echo htmlspecialchars(ucfirst($session['status'])); ?>
                            </td>
                            <td>
                                <?php if ($session['status'] == 'open'): ?>
                                    <a href="session.php?session_id=<?php echo $session['id']; ?>" class="btn btn-success">Mark Attendance</a>
                                <?php else: ?>
                                    <a href="summary.php?session_id=<?php echo $session['id']; ?>" class="btn">View Summary</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

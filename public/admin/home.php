<?php
require_once '../../backend/auth.php';
require_once '../../backend/reporting.php';

requireRole('admin');
$user = getCurrentUser();

$overall_stats = getOverallStatistics();
$course_stats = getCourseStatistics();
$participation = getParticipationTracking();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home - Attendance System</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .dashboard-card {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .dashboard-card h3 {
            margin: 0;
            color: #007bff;
            font-size: 2rem;
        }
        .dashboard-card p {
            margin: 0.5rem 0 0 0;
            font-weight: bold;
        }
        .quick-actions {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .action-card {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .action-card h4 {
            margin: 0 0 0.5rem 0;
            color: #007bff;
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
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
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
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Algiers University Attendance System</h1>
        <p>Administrator <?php echo htmlspecialchars($user['name']); ?></p>
    </header>
    <nav>
        <ul>
            <li><a href="#" class="active">Home</a></li>
            <li><a href="stats.php">Statistics</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="justifications.php">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="welcome-section">
            <h2>Dashboard</h2>
            <p>Welcome to the administrative dashboard. Here you can manage the entire attendance system.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3><?php echo $overall_stats['total_students']; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="dashboard-card">
                <h3><?php echo $overall_stats['total_professors']; ?></h3>
                <p>Total Professors</p>
            </div>
            <div class="dashboard-card">
                <h3><?php echo $overall_stats['total_courses']; ?></h3>
                <p>Total Courses</p>
            </div>
            <div class="dashboard-card">
                <h3><?php echo $overall_stats['total_sessions']; ?></h3>
                <p>Total Sessions</p>
            </div>
            <div class="dashboard-card">
                <h3><?php echo $overall_stats['overall_attendance_rate']; ?>%</h3>
                <p>Overall Attendance Rate</p>
            </div>
            <div class="dashboard-card">
                <h3><?php echo count(array_filter($participation, function($p) { return $p['participation_rate'] > 0.8; })); ?></h3>
                <p>High Performers (80%+)</p>
            </div>
        </div>

        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="actions-grid">
                <div class="action-card">
                    <h4>Manage Students</h4>
                    <p>Add, edit, or remove students from the system.</p>
                    <a href="students.php" class="btn btn-success">Go to Students</a>
                </div>
                <div class="action-card">
                    <h4>View Statistics</h4>
                    <p>Check detailed attendance statistics and reports.</p>
                    <a href="stats.php" class="btn btn-success">View Stats</a>
                </div>
                <div class="action-card">
                    <h4>Review Justifications</h4>
                    <p>Approve or reject student absence justifications.</p>
                    <a href="justifications.php" class="btn btn-warning">Review Now</a>
                </div>
                <div class="action-card">
                    <h4>System Settings</h4>
                    <p>Configure system-wide settings and preferences.</p>
                    <button class="btn" onclick="alert('Settings functionality coming soon!')">Settings</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

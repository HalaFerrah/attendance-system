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
    <title>Statistics - Attendance System</title>
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
        .stats-section {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2rem;
            color: #007bff;
        }
        .stat-card p {
            margin: 0.5rem 0 0 0;
            font-weight: bold;
        }
        .chart-container {
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
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
            transition: width 0.3s ease;
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
            .stats-grid {
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
        <p>Administrator <?php echo htmlspecialchars($user['name']); ?></p>
    </header>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="#" class="active">Statistics</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="justifications.php">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="stats-section">
            <h2>Overall Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $overall_stats['total_students']; ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $overall_stats['total_courses']; ?></h3>
                    <p>Total Courses</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $overall_stats['total_sessions']; ?></h3>
                    <p>Total Sessions</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $overall_stats['overall_attendance_rate']; ?>%</h3>
                    <p>Overall Attendance Rate</p>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <h3>Course Statistics</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Total Sessions</th>
                        <th>Total Students</th>
                        <th>Total Present</th>
                        <th>Total Absent</th>
                        <th>Avg Attendance per Session</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($course_stats as $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['course_name']); ?></td>
                            <td><?php echo $stat['total_sessions']; ?></td>
                            <td><?php echo $stat['total_students']; ?></td>
                            <td><?php echo $stat['total_present']; ?></td>
                            <td><?php echo $stat['total_absent']; ?></td>
                            <td><?php echo $stat['avg_attendance_per_session']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="chart-container">
            <h3>Participation Tracking</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Course Name</th>
                        <th>Sessions Attended</th>
                        <th>Justifications Submitted</th>
                        <th>Participation Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participation as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                            <td><?php echo $record['sessions_attended']; ?></td>
                            <td><?php echo $record['justifications_submitted']; ?></td>
                            <td>
                                <?php echo round($record['participation_rate'] * 100, 2); ?>%
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo round($record['participation_rate'] * 100, 2); ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="home.php" class="btn">Back to Home</a>
        </div>
    </div>
</body>
</html>

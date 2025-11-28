<?php
require_once '../../backend/auth.php';
require_once '../../backend/crud.php';

requireRole('student');
$user = getCurrentUser();

$enrolled_courses = getStudentCourses($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home - Attendance System</title>
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
        .courses-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }
        .course-card {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .course-card:hover {
            transform: translateY(-5px);
        }
        .course-card h3 {
            margin-top: 0;
            color: #007bff;
        }
        .course-card p {
            margin: 0.5rem 0;
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
            .courses-list {
                grid-template-columns: 1fr;
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
            <li><a href="#" class="active">Home</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="welcome-section">
            <h2>My Courses</h2>
            <p>Welcome to your attendance dashboard. Here you can view your enrolled courses and check your attendance records.</p>
        </div>

        <div class="courses-list">
            <?php if (empty($enrolled_courses)): ?>
                <div class="course-card">
                    <h3>No Courses Enrolled</h3>
                    <p>You are not enrolled in any courses yet. Please contact your administrator.</p>
                </div>
            <?php else: ?>
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="course-card">
                        <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                        <p><strong>Professor:</strong> <?php echo htmlspecialchars($course['professor_name']); ?></p>
                        <p><strong>Group:</strong> <?php echo htmlspecialchars($course['group_name']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?? 'No description available.'); ?></p>
                        <a href="attendance.php?course_id=<?php echo $course['id']; ?>" class="btn btn-success">View Attendance</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

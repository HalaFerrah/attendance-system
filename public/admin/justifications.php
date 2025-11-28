<?php
require_once '../../backend/auth.php';
require_once '../../backend/justification_workflow.php';

requireRole('admin');
$user = getCurrentUser();

$justifications = getAllJustifications();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justifications - Attendance System</title>
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
        .justifications-section {
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
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
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
            .modal-content {
                width: 95%;
                margin: 10% auto;
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
            <li><a href="stats.php">Statistics</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="#" class="active">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="justifications-section">
            <h2>All Justifications</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($justifications as $justification): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($justification['student_name']); ?> (<?php echo htmlspecialchars($justification['student_number']); ?>)</td>
                            <td><?php echo htmlspecialchars($justification['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($justification['date']); ?></td>
                            <td><?php echo htmlspecialchars(substr($justification['reason'], 0, 50)); ?><?php echo strlen($justification['reason']) > 50 ? '...' : ''; ?></td>
                            <td class="status-<?php echo $justification['status']; ?>"><?php echo htmlspecialchars(ucfirst($justification['status'])); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($justification['submitted_at']))); ?></td>
                            <td>
                                <?php if ($justification['status'] == 'pending'): ?>
                                    <button class="btn btn-success" onclick="reviewJustification(<?php echo $justification['id']; ?>, 'approved')">Approve</button>
                                    <button class="btn btn-danger" onclick="reviewJustification(<?php echo $justification['id']; ?>, 'rejected')">Reject</button>
                                <?php endif; ?>
                                <button class="btn" onclick="viewDetails(<?php echo $justification['id']; ?>)">View Details</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Justification Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Justification Details</h3>
            <div id="justificationDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function reviewJustification(justificationId, status) {
            if (confirm('Are you sure you want to ' + status + ' this justification?')) {
                $.ajax({
                    url: '../../backend/api.php',
                    type: 'POST',
                    data: {
                        action: 'review_justification',
                        justification_id: justificationId,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Justification ' + status + ' successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error processing request. Please try again.');
                    }
                });
            }
        }

        function viewDetails(justificationId) {
            $.ajax({
                url: '../../backend/api.php',
                type: 'POST',
                data: {
                    action: 'get_justification_details',
                    justification_id: justificationId
                },
                success: function(response) {
                    if (response.success) {
                        var details = response.data;
                        var html = '<p><strong>Student:</strong> ' + details.student_name + ' (' + details.student_number + ')</p>';
                        html += '<p><strong>Course:</strong> ' + details.course_name + '</p>';
                        html += '<p><strong>Date:</strong> ' + details.date + '</p>';
                        html += '<p><strong>Time:</strong> ' + details.start_time + ' - ' + details.end_time + '</p>';
                        html += '<p><strong>Reason:</strong> ' + details.reason + '</p>';
                        html += '<p><strong>Status:</strong> ' + details.status + '</p>';
                        html += '<p><strong>Submitted:</strong> ' + details.submitted_at + '</p>';
                        if (details.file_path) {
                            html += '<p><strong>File:</strong> <a href="' + details.file_path + '" target="_blank">View File</a></p>';
                        }
                        $('#justificationDetails').html(html);
                        $('#detailsModal').show();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error loading details. Please try again.');
                }
            });
        }

        function closeModal() {
            $('.modal').hide();
        }
    </script>
</body>
</html>

<?php
require_once '../../backend/auth.php';
require_once '../../backend/crud.php';

requireRole('admin');
$user = getCurrentUser();

$users = getAllUsers();
$groups = getAllGroups();
$courses = getAllCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Attendance System</title>
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
        .management-section {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
            max-width: 500px;
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
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input, select {
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
            <li><a href="#" class="active">Students</a></li>
            <li><a href="justifications.php">Justifications</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <div class="management-section">
            <h2>Student Management</h2>
            <button class="btn btn-success" onclick="openModal('add')">Add New Student</button>
            <button class="btn btn-warning" onclick="openModal('import')">Import Students</button>
        </div>

        <div class="management-section">
            <h3>All Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Student ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                            <td><?php echo htmlspecialchars($user['student_id'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                                <button class="btn btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Add New User</h3>
            <form id="userForm">
                <input type="hidden" id="userId" name="user_id">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="professor">Professor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group" id="studentIdGroup">
                    <label for="student_id">Student ID:</label>
                    <input type="text" id="student_id" name="student_id">
                </div>
                <div class="form-group" id="passwordGroup">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Import Students</h3>
            <form id="importForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="importFile">Select CSV File:</label>
                    <input type="file" id="importFile" name="file" accept=".csv" required>
                </div>
                <div class="form-group">
                    <label for="importGroup">Assign to Group:</label>
                    <select id="importGroup" name="group_id" required>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['name']); ?> (<?php echo htmlspecialchars($group['course_name']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Import</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#role').change(function() {
                if ($(this).val() === 'student') {
                    $('#studentIdGroup').show();
                    $('#passwordGroup').show();
                } else {
                    $('#studentIdGroup').hide();
                    $('#passwordGroup').show();
                }
            });

            $('#userForm').submit(function(e) {
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
                            alert('User saved successfully!');
                            closeModal();
                            location.reload();
                        } else {
                            alert('Error saving user: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error saving user. Please try again.');
                    }
                });
            });

            $('#importForm').submit(function(e) {
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
                            alert('Students imported successfully!');
                            closeModal();
                            location.reload();
                        } else {
                            alert('Error importing students: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error importing students. Please try again.');
                    }
                });
            });
        });

        function openModal(type, userId = null) {
            if (type === 'add') {
                $('#modalTitle').text('Add New User');
                $('#userId').val('');
                $('#name').val('');
                $('#email').val('');
                $('#role').val('student');
                $('#student_id').val('');
                $('#password').val('');
                $('#studentIdGroup').show();
                $('#passwordGroup').show();
                $('#userModal').show();
            } else if (type === 'import') {
                $('#importModal').show();
            }
        }

        function editUser(userId) {
            // In a real application, you'd fetch user data via AJAX
            alert('Edit functionality would fetch user data and populate the form.');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    url: '../../backend/api.php',
                    type: 'POST',
                    data: { action: 'delete_user', user_id: userId },
                    success: function(response) {
                        if (response.success) {
                            alert('User deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error deleting user: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error deleting user. Please try again.');
                    }
                });
            }
        }

        function closeModal() {
            $('.modal').hide();
        }
    </script>
</body>
</html>

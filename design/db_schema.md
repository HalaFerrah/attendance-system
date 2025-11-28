# Database Schema Documentation

## Overview

The Algiers University Attendance Management System uses a MySQL database with the following main entities:

- Users (students, professors, administrators)
- Courses and academic groups
- Attendance sessions and records
- Absence justifications with file attachments

## Tables

### users
Stores user accounts for students, professors, and administrators.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| name | VARCHAR(255) | NOT NULL | Full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address (used for login) |
| password | VARCHAR(255) | NOT NULL | Hashed password |
| role | ENUM('student', 'professor', 'admin') | NOT NULL | User role |
| student_id | VARCHAR(50) | UNIQUE, NULL | Student ID number (for students only) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation time |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update time |

### courses
Academic courses offered by the university.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique course identifier |
| name | VARCHAR(255) | NOT NULL | Course name |
| description | TEXT | NULL | Course description |
| professor_id | INT | NOT NULL, FOREIGN KEY(users.id) | Course professor |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update time |

### groups
Student groups within courses (e.g., different sections or classes).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique group identifier |
| name | VARCHAR(255) | NOT NULL | Group name |
| course_id | INT | NOT NULL, FOREIGN KEY(courses.id) | Associated course |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |

### group_students
Junction table linking students to their enrolled groups.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique enrollment identifier |
| group_id | INT | NOT NULL, FOREIGN KEY(groups.id) | Group ID |
| student_id | INT | NOT NULL, FOREIGN KEY(users.id) | Student ID |
| enrolled_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Enrollment time |

### attendance_sessions
Attendance sessions created by professors for specific course groups.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique session identifier |
| course_id | INT | NOT NULL, FOREIGN KEY(courses.id) | Course ID |
| group_id | INT | NOT NULL, FOREIGN KEY(groups.id) | Group ID |
| professor_id | INT | NOT NULL, FOREIGN KEY(users.id) | Professor who created the session |
| date | DATE | NOT NULL | Session date |
| start_time | TIME | NOT NULL | Session start time |
| end_time | TIME | NOT NULL | Session end time |
| status | ENUM('open', 'closed') | DEFAULT 'open' | Session status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation time |
| closed_at | TIMESTAMP | NULL | Time when session was closed |

### attendance_records
Individual attendance marks for students in sessions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique record identifier |
| session_id | INT | NOT NULL, FOREIGN KEY(attendance_sessions.id) | Attendance session |
| student_id | INT | NOT NULL, FOREIGN KEY(users.id) | Student ID |
| status | ENUM('present', 'absent') | NOT NULL | Attendance status |
| marked_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | When attendance was marked |
| marked_by | INT | NOT NULL, FOREIGN KEY(users.id) | Who marked the attendance |

### justifications
Absence justifications submitted by students.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique justification identifier |
| student_id | INT | NOT NULL, FOREIGN KEY(users.id) | Student submitting justification |
| session_id | INT | NOT NULL, FOREIGN KEY(attendance_sessions.id) | Session being justified |
| reason | TEXT | NOT NULL | Justification reason |
| file_path | VARCHAR(500) | NULL | Path to uploaded supporting document |
| status | ENUM('pending', 'approved', 'rejected') | DEFAULT 'pending' | Justification status |
| submitted_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Submission time |
| reviewed_at | TIMESTAMP | NULL | Review time |
| reviewed_by | INT | NULL, FOREIGN KEY(users.id) | Who reviewed the justification |

## Relationships

### Entity Relationship Diagram
```
users (1) ──── (many) group_students (many) ──── (1) groups
  │                    │                              │
  │                    │                              │
  │                    │                              │
  └── (1) courses (many) ──────────────────────────────┘
       │
       │
       └── (1) attendance_sessions (many) ─── (many) attendance_records
                                              │
                                              │
                                              └── (many) justifications
```

### Key Relationships
- **users** → **courses**: One professor teaches many courses
- **courses** → **groups**: One course has many groups
- **groups** → **users**: Many students belong to one group (via group_students)
- **courses** → **attendance_sessions**: One course has many sessions
- **groups** → **attendance_sessions**: One group has many sessions
- **attendance_sessions** → **attendance_records**: One session has many records
- **users** → **attendance_records**: One student has many records
- **attendance_sessions** → **justifications**: One session has many justifications
- **users** → **justifications**: One student submits many justifications

## Indexes

### Performance Indexes
```sql
-- Users table
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_student_id ON users(student_id);

-- Courses table
CREATE INDEX idx_courses_professor_id ON courses(professor_id);

-- Groups table
CREATE INDEX idx_groups_course_id ON groups(course_id);

-- Group students table
CREATE INDEX idx_group_students_group_id ON group_students(group_id);
CREATE INDEX idx_group_students_student_id ON group_students(student_id);

-- Attendance sessions table
CREATE INDEX idx_attendance_sessions_course_id ON attendance_sessions(course_id);
CREATE INDEX idx_attendance_sessions_group_id ON attendance_sessions(group_id);
CREATE INDEX idx_attendance_sessions_professor_id ON attendance_sessions(professor_id);
CREATE INDEX idx_attendance_sessions_date ON attendance_sessions(date);
CREATE INDEX idx_attendance_sessions_status ON attendance_sessions(status);

-- Attendance records table
CREATE INDEX idx_attendance_records_session_id ON attendance_records(session_id);
CREATE INDEX idx_attendance_records_student_id ON attendance_records(student_id);
CREATE INDEX idx_attendance_records_status ON attendance_records(status);

-- Justifications table
CREATE INDEX idx_justifications_student_id ON justifications(student_id);
CREATE INDEX idx_justifications_session_id ON justifications(session_id);
CREATE INDEX idx_justifications_status ON justifications(status);
```

## Constraints

### Foreign Key Constraints
- All foreign keys use RESTRICT on delete to prevent orphaned records
- Users cannot be deleted if they have associated records
- Courses cannot be deleted if they have groups or sessions
- Groups cannot be deleted if they have students or sessions
- Sessions cannot be deleted if they have records or justifications

### Data Integrity Constraints
- Student IDs must be unique for students
- Email addresses must be unique across all users
- Session times must be valid (end_time > start_time)
- Attendance can only be marked for existing student-group relationships
- Justifications can only be submitted for absent records

## Sample Data

### Initial Users
```sql
-- Admin user
INSERT INTO users (name, email, password, role) VALUES
('System Administrator', 'admin@univ-algiers.dz', '$2y$10$hashedpassword', 'admin');

-- Professor user
INSERT INTO users (name, email, password, role) VALUES
('Dr. Ahmed Bennani', 'professor@univ-algiers.dz', '$2y$10$hashedpassword', 'professor');

-- Student users
INSERT INTO users (name, email, password, role, student_id) VALUES
('Fatima Alami', 'student@univ-algiers.dz', '$2y$10$hashedpassword', 'student', '20240001');
```

### Sample Course and Group
```sql
-- Course
INSERT INTO courses (name, description, professor_id) VALUES
('Computer Science 101', 'Introduction to Computer Science', 2);

-- Group
INSERT INTO groups (name, course_id) VALUES
('CS101-A', 1);

-- Student enrollment
INSERT INTO group_students (group_id, student_id) VALUES
(1, 3);
```

## Backup and Recovery

### Backup Strategy
- Daily automated backups of the database
- File system backups for uploaded documents
- Point-in-time recovery capability

### Backup Commands
```bash
# Database backup
mysqldump -u username -p attendance_db > backup_$(date +%Y%m%d_%H%M%S).sql

# File system backup
tar -czf uploads_backup_$(date +%Y%m%d_%H%M%S).tar.gz public/uploads/
```

## Performance Considerations

### Query Optimization
- Use indexes on frequently queried columns
- Avoid SELECT * in production code
- Use prepared statements for all queries
- Implement pagination for large result sets

### Connection Management
- Use connection pooling in high-traffic scenarios
- Implement proper connection closing
- Set appropriate timeout values

## Security Considerations

### Data Protection
- All passwords hashed with bcrypt
- Sensitive data encrypted at rest
- Regular security updates for MySQL server

### Access Control
- Role-based permissions implemented at application level
- Database user with minimal privileges
- Audit logging for sensitive operations

## Maintenance

### Regular Tasks
- Analyze slow queries and optimize
- Update statistics for query planner
- Clean up old log files
- Monitor disk space usage

### Monitoring
- Set up alerts for database connection issues
- Monitor query performance
- Track database growth
- Regular security scans

# Algiers University Attendance Management System

A comprehensive web-based attendance management system designed for Algiers University, built with PHP, MySQL, and modern web technologies.

## Features

### For Students
- View enrolled courses
- Check personal attendance records
- Submit justifications for absences with file uploads
- Real-time attendance tracking

### For Professors
- Create and manage attendance sessions
- Mark student attendance in real-time
- View attendance summaries and statistics
- Review and approve/reject student justifications
- Export attendance reports

### For Administrators
- Complete system management
- User management (students, professors, admins)
- Course and group management
- System-wide statistics and analytics
- Import/export functionality
- Justification review and management

## System Architecture

### Backend (PHP)
- **config.php**: Database configuration and connection
- **auth.php**: Authentication and authorization functions
- **crud.php**: Basic CRUD operations for users, courses, groups
- **session_management.php**: Attendance session management
- **justification_workflow.php**: Justification submission and review
- **reporting.php**: Statistics and reporting functions
- **import_export.php**: CSV import/export functionality
- **upload.php**: Secure file upload handling
- **api.php**: AJAX API endpoints

### Frontend (HTML/CSS/JavaScript)
- Responsive design with modern UI
- jQuery for dynamic interactions
- Role-based navigation and access control
- Real-time updates via AJAX

### Database (MySQL)
- Users table (students, professors, admins)
- Courses and groups management
- Attendance sessions and records
- Justifications with file attachments
- Comprehensive relationships and constraints

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependency management)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/attendance-system.git
   cd attendance-system
   ```

2. **Database Setup**
   - Create a new MySQL database
   - Import the schema from `db/init.sql`
   - Update database credentials in `backend/config.php`

3. **Web Server Configuration**
   - Point your web server document root to the `public` directory
   - Ensure proper permissions for file uploads (`public/uploads/`)

4. **Initial Data**
   - Run the database initialization script
   - Create admin user through direct database insertion or use the provided demo accounts

## Usage

### Demo Accounts
- **Admin**: admin@univ-algiers.dz / admin123
- **Professor**: professor@univ-algiers.dz / prof123
- **Student**: student@univ-algiers.dz / student123

### Basic Workflow

1. **Login** with appropriate credentials
2. **Students**: View courses and attendance records
3. **Professors**: Create sessions, mark attendance, review justifications
4. **Admins**: Manage users, view system statistics, handle justifications

## Security Features

- Password hashing with bcrypt
- Session-based authentication
- Role-based access control (RBAC)
- Input validation and sanitization
- Secure file upload with type/size restrictions
- CSRF protection on forms

## API Endpoints

The system provides AJAX endpoints for dynamic operations:

- `POST /backend/api.php?action=login` - User authentication
- `POST /backend/api.php?action=mark_attendance` - Mark student attendance
- `POST /backend/api.php?action=submit_justification` - Submit absence justification
- `POST /backend/api.php?action=review_justification` - Approve/reject justifications
- `POST /backend/api.php?action=import_students` - Bulk import students via CSV

## File Structure

```
attendance-system/
├── backend/                 # PHP backend files
│   ├── api.php             # AJAX API endpoints
│   ├── auth.php            # Authentication functions
│   ├── config.php          # Database configuration
│   ├── crud.php            # CRUD operations
│   ├── import_export.php   # CSV import/export
│   ├── justification_workflow.php
│   ├── reporting.php       # Statistics and reports
│   ├── session_management.php
│   └── upload.php          # File upload handling
├── db/                     # Database files
│   └── init.sql           # Database schema
├── design/                 # Design files
│   ├── db_schema.md       # Database schema documentation
│   └── prototype.html     # UI prototype
├── frontend/               # Alternative frontend (if needed)
├── public/                 # Web-accessible files
│   ├── admin/             # Admin pages
│   │   ├── home.php
│   │   ├── stats.php
│   │   ├── students.php
│   │   └── justifications.php
│   ├── professor/         # Professor pages
│   │   ├── home.php
│   │   ├── session.php
│   │   ├── summary.php
│   │   └── justifications.php
│   ├── student/           # Student pages
│   │   ├── home.php
│   │   └── attendance.php
│   ├── login.php          # Login page
│   ├── logout.php         # Logout handler
│   └── uploads/           # File uploads directory
└── README.md              # This file
```

## Database Schema

The system uses the following main tables:

- `users` - User accounts (students, professors, admins)
- `courses` - Course information
- `groups` - Student groups within courses
- `attendance_sessions` - Attendance session details
- `attendance_records` - Individual attendance marks
- `justifications` - Absence justifications with file attachments

See `design/db_schema.md` for detailed schema documentation.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue on GitHub
- Contact the development team
- Check the documentation in the `docs/` directory

## Future Enhancements

- Mobile app development
- Integration with university LMS
- Advanced analytics and reporting
- Email notifications
- QR code attendance marking
- Multi-language support

 Task Management To-Do Web App

-A simple, multi-user task management application built with PHP, MySQL, and HTML/CSS. Users can register, login, manage personal tasks (add/edit/delete/toggle status), view stats, and edit profiles. Designed for local development with XAMPP.

 Features
- User registration and login with password hashing.
- Dashboard with task statistics (total, completed, completion rate).
- Full CRUD for tasks: Add, edit, delete, toggle status (pending/completed).
- Task priorities (low/medium/high), due dates, search, and filtering.
- Profile management (update username, email, password).
- CSRF protection and session management.
- Responsive white/black theme.
- Pagination for large task lists.

 Tech Stack
- Backend: PHP 7+ with PDO for MySQL.
- Database: MySQL/MariaDB (via XAMPP).
- Frontend: HTML5, CSS3 (no JS frameworks).
- Security: Password hashing (bcrypt), prepared statements, CSRF tokens.

 Demo Credentials
- Username: `admin`
- Password: `password123`
- (Create more users via registration.)

 Setup Instructions

 Prerequisites
- XAMPP (Apache + MySQL) installed and running.
- PHP 7+ enabled.

Step 1: Project Setup
1. Download/clone this repo.
2. Place files in XAMPP's `htdocs` folder (e.g., `C:\xampp\htdocs\todo_web\`).
3. Start Apache and MySQL in XAMPP Control Panel.

Step 2: Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`.
2. Create a new database: `todo_app`.
3. Import `config.sql` (via Import tab) to create tables and sample user.

 Step 3: Configuration
- Edit `db.php` if needed (default: host=`localhost`, db=`todo_app`, user=`root`, pass=`""`).
- Access the app: `http://localhost/todo_web/`.

Step 4: Usage
- Register a new account or login with demo credentials.
- Navigate via the top menu: Dashboard, My Tasks, Profile, Logout.
- Add/manage tasks on the Tasks page.

 Potential Improvements
- Email verification (integrate PHPMailer).
- File uploads for task attachments.
- AJAX for real-time updates.
- Deploy to production (e.g., Heroku with ClearDB MySQL).

License
MIT License – feel free to use and modify.

Issues/Contributions
Report bugs or suggest features via GitHub Issues. Pull requests welcome!
# 📋 Project management application PMA API

The **PMA API** is the backend powering the PMA project management application.  
Built with Laravel, it provides a robust RESTful API for team collaboration, real-time communication, task management, and administrative oversight — all secured with Sanctum cookie-based authentication.

---

## ✨ Features

### 👤 Regular User
- 🔐 Authentication: Sign up, Sign in, Forgot/Reset password
- 👤 Profile management with avatar upload
- 📁 **Projects** – Create, edit, delete own projects
- 👥 **Project Members** – Invite users via email, manage team members
- ✅ **Tasks** – Create tasks, assign to members, set priority & due dates
- 🔄 **Task Workflows** – Status transitions (To Do → In Progress → Review → Done)
- 📎 **Document Uploads** – Attach files to tasks with activity logging
- 📜 **Activity Tracking** – Full history of task changes
- 💬 **Real-time Chat** – Private 1-on-1 messaging per task via Pusher WebSockets
- 🔔 **Notifications** – Project invitations, task assignments, new messages
- 📧 **Email Notifications** – Queued emails for invitations and credentials

---

### 🛡️ Admin User
- 👥 Manage all users (create, view, delete)
- 📂 Manage all projects (view, change status, delete)
- 📊 View system-wide statistics (users, projects, activity)

---

## 🛠️ Technology Stack

- **Framework**: [Laravel 12](https://laravel.com/)
- **Authentication**: [Laravel Sanctum](https://laravel.com/docs/sanctum) (Cookie-based SPA authentication)
- **Database**: [MySQL](https://www.mysql.com/)
- **ORM**: [Eloquent](https://laravel.com/docs/eloquent)
- **Real-Time**: [Pusher](https://pusher.com/) with [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- **File Storage**: [Laravel Filesystem](https://laravel.com/docs/filesystem) (Local/S3)
- **Queue**: [Laravel Queues](https://laravel.com/docs/queues) (Database driver)
- **Email**: [MailTrap](https://mailtrap.io/)
- **Validation**: [Form Requests](https://laravel.com/docs/validation#form-request-validation)
- **API Resources**: [Eloquent API Resources](https://laravel.com/docs/eloquent-resources)

---

## 🚀 Getting Started

### 1. Clone the Repository
```bash
git clone https://github.com/bojan-ski/laravel-react-sanctum--project-management-app-be
cd laravel-pma-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Create Accounts
- [Pusher](https://pusher.com/) – For real-time WebSocket communication
- SMTP Provider (e.g., [Mailtrap](https://mailtrap.io/), [Mailgun](https://www.mailgun.com/)) – For email notifications

### 4. Environment Setup - .env
```env
# Application
APP_NAME=PMA
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# CORS
FRONTEND_URL=http://localhost:5173
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_name
DB_USERNAME=db_username
DB_PASSWORD=db_password

# Pusher
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# Queue
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@map.com
MAIL_FROM_NAME="PMA"
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 6. Create Storage Link
```bash
php artisan storage:link
```

### 7. Start the Server
```bash
php artisan serve
```

---

## 👨‍💻 Author

Developed with ❤️ by BPdevelopment (bojan-ski)
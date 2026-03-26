# ✅ task-manager

A RESTful Task Management API built with **Laravel** and **Sanctum**. Features role-based access control, event-driven notifications, async job processing, and policy-based authorization.

---

##  Features

-  Authentication via **Laravel Sanctum** (register, login, logout)
-  **Task CRUD** with user-scoped ownership
-  **Filter tasks** by status and date range
-  **Policy-based authorization** — users manage only their own tasks
-  **Admin role** — only admins can delete tasks (middleware + policy)
-  **Email & database notifications** on task creation
-  **Async job** dispatched on task creation (`ProcessTask`)
-  **Event + Listener** pipeline (`TaskCreated` → `SendTaskNotification`)
-  Form Request validation with JSON error responses
-  API Resource for consistent response shaping

---

## 🛠️ Tech Stack

- **PHP** >= 8.2
- **Laravel** >= 11.x
- **Laravel Sanctum** — API token authentication
- **MySQL** — Primary database
- **Laravel Queues** — Async job processing
- **Laravel Notifications** — Mail + database channels

---

## ⚙️ Installation & Setup

### 1. Clone the repository

```bash
git clone https://github.com/your-username/task-manager.git
cd task-manager
```

### 2. Install dependencies

```bash
composer install
```

### 3. Copy environment file

```bash
cp .env.example .env
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Configure your `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database   # Required for async job processing
MAIL_MAILER=log             # Use smtp for real emails
```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Start the development server

```bash
php artisan serve
```

---

## 🔄 Queue Setup *(required for async jobs & notifications)*

```bash
# Create queue table
php artisan queue:table
php artisan migrate

# Start the queue worker
php artisan queue:work
```

---

## 📡 API Endpoints

All endpoints return JSON. Include these headers:

```
Content-Type: application/json
Accept: application/json
```

###  Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `POST` | `/api/register` | Register a new user | ❌ |
| `POST` | `/api/login` | Login and receive token | ❌ |
| `POST` | `/api/logout` | Logout current session | ✅ |

### 📋 Tasks *(user-scoped)*

| Method | Endpoint | Description | Auth | Admin Only |
|--------|----------|-------------|------|------------|
| `GET` | `/api/tasks` | List user's tasks (filterable) | ✅ | ❌ |
| `POST` | `/api/tasks` | Create a new task | ✅ | ❌ |
| `GET` | `/api/tasks/{id}` | Get a single task (owner only) | ✅ | ❌ |
| `PUT` | `/api/tasks/{id}` | Update a task (owner only) | ✅ | ❌ |
| `DELETE` | `/api/tasks/{id}` | Delete a task | ✅ | ✅ |

---

##  Filtering Tasks

`GET /api/tasks` supports query parameters:

| Param | Type | Description | Example |
|-------|------|-------------|---------|
| `status` | string | Filter by task status | `pending`, `in_progress`, `completed` |
| `from_date` | date | Tasks created from this date | `2026-01-01` |
| `to_date` | date | Tasks created up to this date | `2026-03-31` |

**Example:**
```
GET /api/tasks?status=pending&from_date=2026-01-01&to_date=2026-03-31
```

---

##  Authentication Usage

**Register:**
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```

**Login:**
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Authenticated requests:**
```http
Authorization: Bearer {your_token_here}
```

---

## ✅ Validation Rules

**Task:**

| Field | Rules |
|-------|-------|
| `title` | required, string, max:255 |
| `description` | optional, string |
| `status` | optional, one of: `pending`, `in_progress`, `completed` |

---

##  Role-Based Access

| Action | Regular User | Admin |
|--------|-------------|-------|
| Create task | ✅ | ✅ |
| View own task | ✅ | ✅ |
| Update own task | ✅ | ✅ |
| Delete any task | ❌ | ✅ |

To make a user an admin, set their `role` column to `Admin` in the database.

---

## 🔁 Task Creation Flow

When a task is created via `POST /api/tasks`:

1. Task is saved to the database
2. `ProcessTask` job is dispatched to the queue (async logging/processing)
3. `TaskCreated` event is fired → `SendTaskNotification` listener logs the event
4. `TaskCreatedNotification` is sent to the user via **email** and stored in the **database**

---

## 📁 Project Structure

```
app/
├── Events/
│   └── TaskCreated.php               # Fired on task creation
├── Http/
│   ├── Controllers/api/              # AuthController, TaskController
│   ├── Middleware/
│   │   └── AdminMiddleware.php       # Restricts delete to admin role
│   ├── Requests/
│   │   └── StoreTaskRequest.php      # Validation with JSON errors
│   └── Resources/
│       └── TaskResource.php          # API response shaping
├── Jobs/
│   └── ProcessTask.php               # Async job dispatched on creation
├── Listeners/
│   └── SendTaskNotification.php      # Handles TaskCreated event
├── Models/
│   ├── Task.php
│   └── User.php
├── Notifications/
│   └── TaskCreatedNotification.php   # Mail + database notification
├── Policies/
│   └── TaskPolicy.php                # Ownership & admin authorization
└── Services/
    └── TaskService.php               # Business logic layer
```

---
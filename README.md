# Task Management System

A modern task management system built with Laravel, featuring both a web interface using Filament, Jetstream and Livewire, as well as a RESTful API. The system allows users to create, manage, and track tasks with different statuses and categories.

## Features

- **Web Interface**:
  - Responsive UI built with Filament
  - Status progression (New → In Progress → In Review → Completed)
  - Visual indicators for overdue and due soon tasks
  - Category management

- **RESTful API**:
  - Token-based authentication
  - Complete CRUD operations for tasks
  - Status management endpoints
  - Pagination support

## Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd task-management-system
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Database**
   - Update `.env` with your database credentials
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=task_management
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build Assets**
   ```bash
   npm run build
   ```

7. **Start the Server**
   ```bash
   php artisan serve
   ```

## API Authentication

The API uses Laravel Sanctum for token-based authentication. Here's how to use it:

1. **Register a New User**
   ```bash
   curl -X POST http://localhost:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{
       "name": "John Doe",
       "email": "john@example.com",
       "password": "password123",
       "password_confirmation": "password123",
       "device_name": "my-device"
     }'
   ```

2. **Login and Get Token**
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "john@example.com",
       "password": "password123",
       "device_name": "my-device"
     }'
   ```

3. **Using the Token**
   Include the token in your API requests:
   ```bash
   curl -X GET http://localhost:8000/api/tasks \
     -H "Authorization: Bearer your_token_here"
   ```

## Architecture Overview

The system is built with a clear separation between web and API functionality:

### Web Interface
- Uses Filament for admin panel functionality
- Implements Livewire components for real-time interactivity
- `ListTasks` component handles all task management operations
- Uses Filament's table builder for advanced features like sorting
- Indirectly uses the Form request `StoreTaskRequest` for validation rules

### API Layer
- Separate controllers for API endpoints (`Api/TaskController`, `Api/AuthController`)
- Form requests for validation (`StoreTaskRequest`)
- Resource classes for consistent JSON responses
- Sanctum authentication for API security

### Service Layer
- `TaskService` handles all business logic
- Centralises authorisation checks using Laravel's Gate facade
- Ensures consistent task status progression
- Reused by both web and API controllers

### Testing
- Comprehensive test suite using Pest
- Feature tests for both web and API functionality
- Unit tests for service layer
- Tests organised by feature and type

## Available API Endpoints

- **Authentication**
  - `POST /api/register` - Register a new user
  - `POST /api/login` - Login and get token
  - `POST /api/logout` - Invalidate token

- **Tasks**
  - `GET /api/tasks` - List all tasks
  - `POST /api/tasks` - Create a new task
  - `GET /api/tasks/{id}` - Get task details
  - `PUT /api/tasks/{id}` - Update a task
  - `DELETE /api/tasks/{id}` - Delete a task
  - `POST /api/tasks/{id}/update-status` - Update task status

## Stretch Goals

### Filtering
The system implements a filtering system that works consistently across both the web interface and API:

#### Web Interface Filters
- **Status Filter**: Filter tasks by their current status (New, In Progress, In Review, Completed)
- **Category Filter**: View tasks belonging to specific categories
- **Overdue Filter**: Show only overdue tasks
- **Due Soon Filter**: Show tasks approaching their due date

#### API Filtering
The API supports the same filtering capabilities through query parameters:
```bash
# Filter by status
GET /api/tasks?status=new

# Filter by category
GET /api/tasks?category_id=1

# Filter overdue tasks
GET /api/tasks?overdue=1

# Filter due soon tasks
GET /api/tasks?due_soon=1
```

#### Implementation Details
- Uses a shared `FiltersTasks` trait to maintain consistency between web and API interfaces
    - Current web implementation uses Livewire so the trait is only used on the API
- Filters can be combined for more specific queries
- All filters are thoroughly tested in both web and API contexts
- Leverages Laravel's query scopes for clean and maintainable filtering logic

### Task Statistics and Caching
The system includes a statistics system with caching:

- **Statistics Available**:
  - Task counts by status (New, In Progress, In Review, Completed)
  - Task counts by category
  - Overdue and due soon task counts

- **Caching Implementation**:
  - Uses Laravel's Cache facade for efficient data storage
  - 5-minute cache duration to balance freshness and performance
  - Cache keys are user-specific for data isolation

### Auto-Archiving with Soft Deletes
The system implements an archiving system:

- **Soft Deletes**:
  - Uses Laravel's SoftDeletes trait for non-destructive task removal
  - Maintains data integrity while hiding completed tasks
  - Allows for task recovery if needed

- **Auto-Archiving**:
  - Scheduled job runs daily to archive old tasks
  - Tasks are archived based on completion date and configurable threshold
  - Archived tasks are soft deleted but remain in the database

### Language Selection
Support multiple languages:

- **Features**:
  - Language selection in navigation
  - Support for English and Spanish
  - All UI text stored in language files
  - API responses in selected language

- **Implementation**:
  - Uses Laravel's Session facade for language storage
  - Uses Accept-Language header for api client preference
  
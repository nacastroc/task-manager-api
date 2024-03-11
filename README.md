# task-manager-api

Task Manager Laravel 8 API

## Project Overview

The Task Manager API is a simple task management system that allows users to create, update, delete, and retrieve tasks. Users need to authenticate to access the API.

## Functional Requirements

1. **User Authentication:**
    - Users should be able to register an account with their email and password.
    - Users should be able to log in using their registered credentials.
    - Only authenticated users can perform certain operations.

2. **Task Management:**
    - Authenticated users can create a new task with a title, description, and due date.
    - Users can update the details of their tasks.
    - Users can mark tasks as completed.
    - Users can delete tasks.

3. **User Profile:**
    - Users should be able to view and update their profile information (e.g., name, profile picture).

4. **Authorization:**
    - Users can only perform operations on their own tasks.
    - Administrative users (optional) can manage all tasks and user accounts.

## Technical Specifications

1. **Technology Stack:**
    - Laravel 8.x (or the latest version at the time of the project)
    - MySQL or SQLite database
    - Eloquent ORM for database interaction
    - Sanctum package for API authentication

2. **API Endpoints:**
    - `/api/register` (POST): User registration
    - `/api/login` (POST): User login
    - `/api/logout` (POST): User logout
    - `/api/user` (GET): Retrieve user profile
    - `/api/user` (PUT): Update user profile
    - `/api/tasks` (GET): Retrieve user's tasks
    - `/api/tasks` (POST): Create a new task
    - `/api/tasks/{id}` (GET): Retrieve a specific task
    - `/api/tasks/{id}` (PUT): Update a specific task
    - `/api/tasks/{id}` (DELETE): Delete a specific task

3. **Authentication:**
    - Use Laravel Sanctum for API token-based authentication.
    - Only authenticated users should have access to certain endpoints.

4. **Validation:**
    - Implement request validation to ensure data integrity.

5. **Middleware:**
    - Implement middleware to authorize users to perform specific actions.

6. **Error Handling:**
    - Implement proper error handling and response messages.

7. **Testing:**
    - Write unit tests for critical parts of the application.

8. **Documentation:**
    - Document API endpoints using tools like Swagger or Laravel's built-in API documentation features.

## Learning Objectives

- Understand Laravel's MVC architecture.
- Gain experience with Laravel Eloquent for database interactions.
- Learn how to implement user authentication and authorization.
- Practice building RESTful APIs with Laravel.
- Familiarize yourself with testing in Laravel.

<img src="images\login.png" alt="loginImage">

📋 Overview

The Army Reservist Management System is a web-based administrative application built with Laravel and Livewire.

The project is designed to provide a centralized system for managing military reservist information and related administrative processes.

The application currently focuses on personnel management, authentication, role-based access, ranks, units, and administrative workflows.

🎯 Project Goals
The project is being developed not only as a personnel management application but also as a practical project for learning and applying professional software development practices including:

Laravel application development
Livewire development
Database design
Authentication and authorization
Validation
Automated testing
Git and GitHub workflows
QA automation
CI/CD
Staging and production deployment
Application security

✨ Features
👤 Personnel Management
Create personnel records
View personnel records
Edit personnel information
Delete personnel records
Assign military ranks
Assign personnel to units
Manage personnel status
Store contact and personal information
Paginated personnel listings

🎖️ Military Ranks
Manage military ranks
Assign ranks to personnel
Display rank information alongside personnel records

🏢 Units
Manage parent units and subordinate units
Associate personnel with organizational units
Support hierarchical unit structures

🔐 Authentication
User registration
User login
User logout
Session regeneration after authentication
Password hashing
Role-based application access
Separate administrator and user dashboards

📅 Administrative Records
Planned and/or under development:
Leave management
Promotions
Training records
Personnel history
Unit assignments

🛠️ Technology Stack
Technology Purpose
PHP 8.4+ Backend programming language
Laravel Application framework
Livewire Reactive UI and application interactions
Blade Server-side templating
Tailwind CSS User interface styling
Vite Frontend asset development and bundling
SQLite Local development database
Git / GitHub Version control and source management
🏗️ Application Architecture

The application follows Laravel's MVC architecture with Livewire components handling interactive application features.

Browser
│
▼
Laravel Routes
│
▼
Livewire Components
│
├── Validation
├── Application Logic
└── UI State
│
▼
Eloquent Models
│
▼
Database
🔐 Authentication & Authorization

The application uses Laravel's authentication system.

Users authenticate using their email address and password.

The system is designed around different user roles:

                    ┌─────────────────────┐
                    │       User          │
                    └──────────┬──────────┘
                               │
                       Authentication
                               │
                ┌──────────────┴──────────────┐
                │                             │
          Administrator                    User
                │                             │
                ▼                             ▼
        Admin Dashboard                User Dashboard
                │
                ▼
       Personnel Management

Administrators are intended to manage user accounts and administrative records, while regular users have access to functionality appropriate to their assigned role.

📊 Database

The application currently uses SQLite for local development.

Major database areas include:

users
personnels
ranks
parent_units
units
promotions
training
leaves

Database relationships are implemented using Laravel Eloquent relationships and foreign keys.

⚙️ Requirements

Before installing the project, make sure the following are available:

PHP 8.4+
Composer
Node.js and npm
Git
SQLite

For local development, this project can be run using Laravel Herd or another Laravel-compatible development environment.

🚀 Installation

1. Clone the repository
   git clone https://github.com/jilargo/ph-army-management-app.git

Navigate into the project:

cd ph-army-management-app 2. Install PHP dependencies
composer install 3. Install frontend dependencies
npm install 4. Create the environment file

Copy the example environment file:

cp .env.example .env

On Windows, you can also copy .env.example manually and rename the copy to:

.env 5. Generate the application key
php artisan key:generate 6. Configure the database

For local development, configure SQLite in .env:

DB_CONNECTION=sqlite

Create the SQLite database file if it does not already exist:

database/ph-army.sqlite

The local SQLite database is intentionally excluded from Git using .gitignore.

7. Run database migrations
   php artisan migrate

If seeders are available and you want to populate development data:

php artisan db:seed

Or:

php artisan migrate:fresh --seed

migrate:fresh --seed deletes existing database tables, so use it only when resetting a development database.

8. Build frontend assets

For development:

npm run dev

For a production-style asset build:

npm run build
▶️ Running the Application

When using Laravel Herd, the application can be accessed through the configured Herd domain.

Example:

http://ph-army-management-app.test

If you are not using Herd, Laravel's development server can be started with:

php artisan serve
🧪 Testing

Run the Laravel automated test suite with:

php artisan test

The project is intended to eventually include automated coverage for:

Authentication
Authorization
Personnel CRUD operations
Validation
Database relationships
Leave workflows
Promotion workflows
Administrative functions

Browser-based QA automation may also be implemented using Playwright.

🔄 Development Workflow

The project is developed with a development → staging → production workflow in mind.

Developer
│
▼
Local Development
│
▼
Feature Branch
│
▼
Pull Request
│
▼
CI / Automated Tests
│
▼
Staging
│
▼
QA Testing
│
▼
Production

The main branch is intended to represent the stable application code.

Feature development should preferably be performed on separate branches before being merged into main.

🔒 Security

Sensitive environment configuration must not be committed to the repository.

The following should remain outside Git:

.env
Application secrets
API keys
Production credentials
Database credentials
Local SQLite databases
vendor/
node_modules/

The project uses .gitignore to prevent local development files and sensitive configuration from being committed.

Never share APP_KEY or production environment credentials with QA testers or other users.

🗺️ Roadmap

Planned improvements include:

Complete role-based authorization

Administrator user management

Personnel profile pages

Unit management interface

Leave request workflow

Leave approval workflow

Promotion management

Training management

Personnel history

Dashboard statistics

Audit logging

Automated feature tests

Playwright QA automation

CI/CD pipeline

Staging environment

Production deployment

Laravel application development
Livewire development
Database design
Authentication and authorization
Validation
Automated testing
Git and GitHub workflows
QA automation
CI/CD
Staging and production deployment
Application security

📸 Screenshots
Dashboard
<img src="images\Dashboard.png">

Soldiers List
<img src="images\Index.png">

Army Reservist Application
<img src="images\Application.png">

Task Management
<img src="images\Task.png">

Filling Leave Request
<img src="images\LeaveRequest.png">



Enlistment Applications List
<img src="images\EnlistmentApplications.png">

Promotions
<img src="images\Promotion.png">

Adding Units
<img src="images\addUnits.png">

Reports
<img src="images\Reports.png">

Sample Generated Report
<img src="images\SampleReport.png">

📄 License
This project is currently intended as a personal learning and development project.
License information will be added when the project is ready for distribution.

👨‍💻 Author
James Ian Largo
GitHub: @jilargo

```md
# Matrix App – Backend (Laravel 12 + Sanctum)

This is the backend API for the Matrix Visualization & Editing project.  
It provides secure user authentication and CRUD operations for matrices.

Designed to work with the React frontend using **Sanctum tokens**.

---

## Features

- Laravel 12 (no `AppServiceProvider`, no kernel.php)
- Token-based Auth via **Laravel Sanctum**
- CRUD for matrices and matrix cells
- Per-user isolation (users see only their matrices)
- CORS-ready for Vite + HTTPS
- DDEV-ready local environment
- Sorting endpoints (ASC/DESC by date)

---

## Tech Stack

- **PHP 8.3+**
- **Laravel 12**
- **MySQL / MariaDB**
- **Sanctum**
- **DDEV**
- **HTTPS Local Certificates**

---

## Installation

ddev start
ddev composer install
ddev php artisan migrate
ddev php artisan key:generate

# WST2 – Activity 1: Secure Login System

A PHP + MySQL registration and login system that stores passwords safely and manages sessions securely.

## Features
- Registration with server-side validation (username 3–30 letters/numbers/underscore, valid email, password ≥ 8 chars, passwords must match)
- Duplicate username/email check with a prepared statement
- Passwords stored with `password_hash()` (bcrypt, `$2y$`)
- Login with a prepared statement and `password_verify()`
- `session_regenerate_id(true)` after login; `user_id`, `username`, `role` stored in the session
- One generic error message: "Invalid username or password."
- Logout clears `$_SESSION`, deletes the session cookie and calls `session_destroy()`
- Protected dashboard (redirects to login when not logged in)
- **Bonus:** 15-minute account lockout after 5 failed attempts
- CSRF tokens on forms, HttpOnly session cookie, output escaping

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, open the **SQL** tab and run `database/01_users_table.sql` (creates the `wst2_auth` database and `users` table).
4. Open `http://localhost/secure_login_system/`.

## Files
| File | Purpose |
|---|---|
| `database/01_users_table.sql` | Creates the `users` table |
| `database/users.sql` | SQL export of the `users` table |
| `register.php` | Registration |
| `login.php` | Login + lockout |
| `logout.php` | Destroys the session |
| `dashboard.php` | Protected page |
| `includes/db.php` | PDO database connection |
| `includes/session.php` | Secure session start, CSRF and escaping helpers |

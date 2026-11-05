# Barangay Lower General Luna — PHP Starter

A basic starting point for the Barangay Concern Report System, prepared for an initial commit.
Plain PHP with PDO and MySQL, HTML and a small CSS file. No JavaScript, framework, Composer or npm needed.

## Included
- Resident signup with full name, email, address and password
- Login using email and password
- Dashboard displaying the signed-in resident's profile
- Logout and protected dashboard access
- Password hashing, prepared SQL statements, escaped output and CSRF tokens

This initial version covers accounts only. It does not yet implement account verification, administrator roles, complaints, maps, photos, assignments, feedback, announcements or analytics.

## Run in XAMPP
1. Extract the `lower-general-luna` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin and select **Import**.
4. Choose the included `database.sql`, then click **Import/Go**. It creates a separate `lower_general_luna` database and its `users` table; it does not drop existing data.
5. Open http://localhost/lower-general-luna/signup.php and create your account.
6. Log in at http://localhost/lower-general-luna/login.php using that email and password.

There are no default/demo accounts. Use PHP 8.1 or newer with PDO MySQL enabled. Default database settings in `dbconn.php`: host 127.0.0.1, port 3306, username root, blank password. Edit them if your local setup differs. Use these defaults only for local development; never commit real deployment credentials.

## Files
- `dbconn.php` — PDO MySQL connection
- `session.php` — sessions, output escaping and CSRF helpers
- `index.php` — redirects to login or dashboard
- `signup.php` — resident registration
- `login.php` — authentication
- `dashboard.php` — protected profile page
- `logout.php` — ends the session
- `database.sql` — database and users table
- `style.css` — basic responsive styling

## Get the project from GitHub
Run this inside `C:\xampp\htdocs` with Git installed:

```sh
git clone https://github.com/M4rk0011/WEBSYS.git lower-general-luna
```

Then follow the database import and signup steps above. Add future modules in separate, meaningful commits.

## Verification
File structure and form wiring were reviewed. PHP/MySQL runtime tests could not be run in the build environment; use the XAMPP steps above to verify signup, login, duplicate-email handling, and logout locally.

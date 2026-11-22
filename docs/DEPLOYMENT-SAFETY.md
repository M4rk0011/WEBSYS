# WEBSYS V5 deployment and rollback checklist

This is a student project maintenance guide. GitHub commits do not update a running XAMPP installation automatically.

## Before deployment

1. Copy the entire deployed folder to a dated backup location.
2. Export the active database from phpMyAdmin and keep it separate from the repository.
3. Back up `storage/uploads/` and `storage/place-photos/` without publishing resident data.
4. Save your private `config/local.php` separately; do not commit credentials.
5. Verify the database name, local Apache document root, and PHP version.

## Update safely

1. Review Git changes and test in a separate local copy first.
2. Use `database/database.sql` only for a brand-new database. For existing V3 installs, inspect and use the documented `database/upgrade.sql` procedure after making a backup.
3. Keep existing private configuration and uploaded files; never replace them with empty repository folders.
4. Test login, role-based permissions, resident submissions, approvals, photos, maps, CSV/PDF exports and mobile layout using `docs/TESTING-CHECKLIST.md`.
5. Deploy to the running folder only after the local checks pass.

## If a regression occurs

1. Stop accepting new submissions during recovery to prevent data loss.
2. Restore the backed-up application files and private config.
3. Restore database and uploads together only if the data impact is understood; do not overwrite new resident submissions casually.
4. Record the failing route, error logs (without secrets) and the commit that introduced the issue.

**Important:** The barangay boundary and hazard markers are approximate and require official verification before use as public safety guidance.

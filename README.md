# Lower General Luna — WEBSYS V3 (Organized + Barangay Recommendations)

This is your original V3 complaint monitoring system, **reorganized using the folder layout of WEBSYS_FINAL_PROJECT and extended with the requested barangay features**. No TB-MAS or unrelated code was copied. No Composer, npm, or Angular required.

## Folder guide (simple PHP/PDO + OOP)

```text
LOWER-GENERAL-LUNA-V3-ORGANIZED/
├── index.php                # opens public/index.php
├── config/
│   ├── db.php               # one PDO connection
│   └── local.example.php    # copy to local.php if needed
├── database/
│   ├── database.sql         # fresh installation
│   └── upgrade.sql          # older DATABASE2 installations only
├── src/
│   ├── bootstrap.php        # loads the classes + helpers
│   ├── models/              # User.php, Complaint.php and BarangayDirectory.php: CRUD
│   ├── controllers/         # form processing & validation, including directories
│   ├── middleware/          # sign-in, CSRF, page/role access
│   ├── services/            # complaint logic, photos, map boundary
│   ├── helpers/             # small reused functions
│   └── vendor/fpdf/         # PDF library
├── public/
│   ├── index.php            # homepage / legacy routing
│   ├── login.php, signup.php, dashboard.php, ...
│   ├── partials/            # header, footer, map & popup
│   └── assets/
│       ├── css/app.css
│       ├── js/app.js
│       ├── img/
│       └── vendor/leaflet/  # map CSS/JS/images
├── storage/uploads/         # protected complaint photos
├── storage/place-photos/    # protected landmark/hazard photos
└── docs/                    # archived original V3 notes
```

**How a page works:** Open `public/login.php`, which includes `src/controllers/LoginController.php` for login processing; the view then loads the shared header/footer. Database operations stay in `src/models/`. This is a simple and readable version of the MVC separation in your sample.

## Run on XAMPP

1. Extract this folder to `C:\xampp\htdocs\LOWER-GENERAL-LUNA-V3-ORGANIZED`.
2. Start **Apache** and **MySQL** in XAMPP.
3. In **phpMyAdmin**, import `database/database.sql` **only for a fresh installation**.
4. Open `http://localhost/LOWER-GENERAL-LUNA-V3-ORGANIZED/` or `/public/login.php`.
5. If your MySQL port/credentials/database differ, copy `config/local.example.php` to `config/local.php` and edit the PDO DSN, username and password. **Never commit local.php.**

The included demo SQL may contain example users and weak demo passwords. **Change all demo passwords** and remove demo data before deploying or storing real residents' information.

### Updating a previous V3 deployment

- Back up your database and `storage/uploads` photos first.
- Do **not** reimport `database.sql` over an existing database; this update preserves the same database tables, JSON record format and hashed passwords.
- If you already customized `dbconn.php` or `config.php`, move its DSN/user/password into `config/local.php` in this new folder.
- Copy your existing `storage/uploads` into this new project's `storage/uploads`.
- The original root PHP pages now live under `public/`. Existing `public/index.php?page=...` query bookmarks still work. Reconfigure deployment document root to `public/` when possible.

## Features retained from V3

Resident signup and verification, five roles, PDO user/complaint CRUD, **administrator submission approval**, assignments and processing, feedback, announcements, service monitoring, CSV/PDF reporting, photo evidence, and the red Lower General Luna reporting area. **The polygon is approximate**, not an official administrative boundary.

Security helpers remain in the same PHP code: prepared PDO statements, role & complaint-access checks, CSRF tokens, password hashing, guarded photo downloads, photo upload validation, and private upload storage.

## What has been verified

- PHP syntax lint on every PHP file, JavaScript syntax, PHP require paths, local static asset references, source file coverage, and ZIP integrity are checked when this package is built.
- Full running MySQL/XAMPP/browser workflows **have not been retested** after this restructuring; test login, signup, submission, roles, uploads, and export on your machine.
- Vendor libraries are bundled locally as in the original V3; no dependency installation step is necessary.
- The organized V5 project is maintained in the `M4rk0011/WEBSYS` GitHub repository. Review commit history for the latest updates.


## Barangay recommendations added in this update

1. **Inhabitants profiling** — `public/residents.php`: admin-only create, search, edit, delete and print resident records. This directory is separate from portal user accounts. No real resident profiles have been preloaded.
2. **Business profiling** — `public/businesses.php`: owner, category, address, permit number, renewal date, and notes. Filter and print listings.
3. **Business CCTV monitoring** — tracking fields for yes/no/not checked and camera count. This is a checklist, **not live CCTV streaming**.
4. **Announcements and notifications** — the existing announcement editor and account-specific dismissal are kept. An unread announcement shows `Announcements · New` and appears in the existing popup. This is **in-app**, not an SMS or email notification service.
5. **Complaint approval** — new complaints require administrator approval before assignment/processing. Rejected submissions can be corrected and resubmitted. Pre-existing V3 complaints are treated as approved to avoid breaking active records.
6. **Barangay spot map** — the current map stays centered on the project reporting area. Its border is **approximate and not officially surveyed**.
7. **Hazard identification** — administrators can add typed hazard markers and a description. **No hazard locations are invented or automatically marked as verified.**
8. **Evacuation centers, roads, clinics and landmarks** — same map marker editor supports locations, photo uploads and details. Draft markers are admin-only until verified.
9. **Official barangay logo** — added the provided image to sidebar, sign-in and registration screens.
10. **Simpler interface** — searchable directories, one-page CRUD forms, dashboard shortcuts, printable lists, filterable map layers.

## Database setup for these new modules

**New installation:** Import `database/database.sql` in phpMyAdmin (creates the 7 required tables, five demo accounts and departments). Do not import this as an update into an existing database.

**Existing V3 database:** Take a backup in phpMyAdmin, **select the existing `barangay_service_system` database**, then import `database/upgrade.sql`. Its `CREATE TABLE IF NOT EXISTS` statements add the three new tables without deleting existing records. Back up existing `storage/uploads/` too.

All new tables start empty. For a safe demo, enter made-up profiles; do not enter actual residents' personal data unless authorized by the barangay and an appropriate data-protection process exists. Protect the administrator accounts and change weak demo passwords before real use.

In the app, sign in as an administrator and look in the sidebar for **Inhabitants profiling**, **Business & CCTV records** and **Map spots & hazards**. Review incoming complaints under **Complaint details → Administrator review**. To add a map spot: open Map spots & hazards → fill in the fields → click the map for coordinates → upload an optional image → save as Draft → verify with the barangay → change to Verified. The map then displays it to signed-in residents.

### Important limitations and testing

- Map outlines, hazard points and evacuation sites are **not certified**: their locations must be reviewed with barangay officials. Neighboring barangays remain visible only as background context. Marker coordinates are validated against the approximate outline.
- Date of birth and other profiling information are **admin-only** within this prototype. Real use requires an approved privacy policy, strict access controls, backups, and record retention rules. The app is a student prototype, not an authorized municipal registry or official hazard warning service.
- The new business module tracks permit/renewal details but is **not integrated with official licensing systems**.
- The new installation uses a simple PHP/PDO model and basic HTML forms; no framework or vendor install is needed.
- PHP syntax and JavaScript checks were run, but **full MySQL workflow testing could not be performed in the build environment**. Test with XAMPP before demonstration or deployment; see `docs/TESTING-CHECKLIST.md`.
- Source changes are tracked in GitHub; local deployment still requires separate XAMPP and database setup.

## GitHub V5 review notes

This source tree was imported from the group-provided `V5.zip`. Source files were moved into the repository root without changing their internal relative paths. Maintainer comments explain responsibilities; they do not alter behavior. Review the pull request before merging because the earlier GitHub layout differs substantially. Run the checklist in `docs/TESTING-CHECKLIST.md`, including database backup/migration steps, before deployment.

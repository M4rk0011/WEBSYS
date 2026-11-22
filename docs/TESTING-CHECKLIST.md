# V3 manual test checklist (XAMPP)

1. Import fresh `database/database.sql` in phpMyAdmin, start Apache/MySQL and load `public/login.php`.
2. Sign in as every demo role; check that residents cannot open admin pages and staff cannot submit resident complaints.
3. Sign up a resident, verify the account from an administrator, then sign in again.
4. Submit a complaint inside the red map boundary; reject a point outside the boundary.
5. Upload a JPG, PNG or WebP image and confirm only the allowed account can open its protected photo URL.
6. Assign a complaint, change its status with a note, and submit resident feedback after resolution.
7. Publish an announcement, dismiss it using X, and verify the dismissal persists across page reloads.
8. Check dashboard and analytics reports, CSV export, and PDF complaint export.
9. Edit your profile and change password, then confirm the old password no longer works.
10. Confirm that `/src/`, `/config/`, `/database/`, `/storage/` and `/public/partials/` cannot be opened directly in the browser.

These are steps to **perform**, not claims that live XAMPP/MySQL testing has been completed.


## New barangay requirements: XAMPP checklist

- [ ] Back up the old database and photos; run `database/upgrade.sql` on existing installs.
- [ ] Confirm admin sees Inhabitants and Business directories; resident and personnel cannot open these URLs.
- [ ] Add a fictional resident, search by surname/household, edit and delete.
- [ ] Add a fictional business; filter Has CCTV / No CCTV / Not checked; test date and camera count.
- [ ] Resident submits a complaint: it shows Awaiting approval; assignment/status POST requests are rejected.
- [ ] Admin approves; authorized staff can now assign and process; reject and resubmit path works.
- [ ] Post an announcement and sign in as another account: unread notification popup appears; dismiss then edit announcement to confirm it reappears.
- [ ] Open map editor; click inside boundary, save a Draft hazard/evacuation marker; confirm resident cannot see it.
- [ ] Mark it Verified after barangay confirmation; confirm resident sees the marker and photo in map.
- [ ] Attempt to upload unsupported/oversized marker images and outside-boundary coords: reject.
- [ ] Verify logo appears on login, registration and sidebar, on desktop and mobile.
- [ ] Verify existing V3 login, reports, departments, analytics, export and photo uploads still work.

## Regression checks for maintenance fixes

- [ ] Sign in, remove or deactivate the user in a test database, reload, and confirm the old session cannot access protected pages.
- [ ] Open an evidence-photo URL with no session, wrong user, unknown report, and missing filename; confirm no private photo bytes are returned.
- [ ] Open a map marker that has no photo and confirm the photo endpoint returns not found rather than a PHP warning.
- [ ] Test blank, valid, and malformed date values; malformed values must not render as January 1970.
- [ ] Test a photo input on a page without a preview container; changing the file input should not throw JavaScript errors.
- [ ] Repeat basic report submission and attachment upload after applying the changes.

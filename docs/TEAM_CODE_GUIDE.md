# WEBSYS V5 code guide

This document explains where to find each part of the group project. It does not claim which member authored each file.

| Folder | Responsibility |
| --- | --- |
| `public/` | Browser-facing PHP pages, shared HTML partials, and static assets |
| `src/controllers/` | Process requests, validate inputs, invoke models/services |
| `src/models/` | Database-backed domain objects and queries |
| `src/services/` | Reusable complaint, photo, boundary, and community logic |
| `src/middleware/` | Session initialization, permissions, authorization rules |
| `src/helpers/` | Shared utility functions |
| `config/` | Database configuration; keep local credentials private |
| `database/` | Fresh install and upgrade SQL (choose carefully) |
| `storage/` | Protected uploads, never commit real user data |
| `docs/` | Project documentation and test checklist |

## How to trace a change

1. Start from the browser route under `public/`.
2. Find its included controller under `src/controllers/`.
3. Follow calls into `src/models/`, `src/services/`, or `src/helpers/`.
4. Update documentation if the workflow or table requirements change.

## Team conventions

- Keep existing functionality intact when making documentation-only changes.
- Use short, meaningful comments for non-obvious validation, permissions and data flow.
- Do not assign authorship in comments without the teammates' confirmation. Git history is better for tracking changes.
- Do not edit bundled third-party code in `src/vendor/` and `public/assets/vendor/`.
- Do not commit `config/local.php`, user uploads, or real resident records.
- Lint PHP, check JS syntax, and test role permissions, login, reports, files, and map features before merging.

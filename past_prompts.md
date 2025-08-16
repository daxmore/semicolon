# Project Changes Log (Past Prompts)

This document outlines the modifications made to the Semicolon project based on past prompts.

## General Fixes & Improvements

-   **Database Connection Fixes:**
    -   Ensured `$conn` variable is properly defined by adding `require_once 'includes/db.php';` or `require_once '../includes/db.php';` in:
        -   `dashboard.php`
        -   `videos.php`
        -   `request.php`
        -   `admin/index.php`
-   **Styling Consistency:**
    -   Added complete HTML `<head>` sections with Tailwind CSS CDN and `assets/css/index.css` links to:
        -   `dashboard.php`
        -   `videos.php`
        -   `request.php`
        -   `admin/index.php`
        -   `admin/requests.php`
    -   Applied `shadow-lg` class for a consistent drop shadow effect on cards in:
        -   `books.php`
        -   `videos.php`
        -   `admin/index.php`
-   **Path Correction:**
    -   Fixed `logo.svg` path in `includes/footer.php` from `logo.svg` to `../logo.svg` to correctly locate the file.

## Feature Removals & Refinements

-   **Video Bookmarking Feature Removed:**
    -   Removed all bookmarking logic and UI elements from `videos.php`.
    -   Removed the `video_bookmarks` table creation from `database.sql`.
    -   **Manual Action Required:** You must manually drop the `video_bookmarks` table from your `semicolon_db` database by running `DROP TABLE video_bookmarks;` in your MySQL client.
-   **'Difficulty' Field Removed from Books:**
    -   Removed 'difficulty' filter and display from `books.php`.
    -   Updated `includes/functions.php`:
        -   Modified `get_books` function signature to remove the `$difficulty` parameter.
        -   Removed related SQL conditions and parameter bindings for 'difficulty'.
    -   Updated `admin/books.php`:
        -   Removed `$difficulty` from `add` and `update` operations.
        -   Adjusted SQL queries and parameter bindings accordingly.
    -   Updated `admin/edit_book.php`:
        -   Removed the 'Difficulty' input field from the edit form.
    -   Removed the `difficulty` column from the `books` table creation in `database.sql`.
    -   **Manual Action Required:** You must manually drop the `difficulty` column from your `books` table in your `semicolon_db` database by running `ALTER TABLE books DROP COLUMN difficulty;` in your MySQL client.

## Admin Login Credentials

-   The hardcoded admin login credentials are:
    -   **Username:** `daxmore`
    -   **Password:** `daxmore@!1995`

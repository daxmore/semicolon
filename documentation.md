# Semicolon Project Documentation

## Introduction

Semicolon is a web application for students to access educational resources like books, past papers, and video courses.

## Tech Stack

- **Backend:** PHP + MySQL (XAMPP)
- **Frontend:** HTML, TailwindCSS v4, Vanilla JavaScript

## XAMPP Setup

1.  **Install XAMPP:** Download and install XAMPP from [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html).
2.  **Start Apache and MySQL:** Open the XAMPP control panel and start the Apache and MySQL services.
3.  **Import Database:**
    *   Open your web browser and go to `http://localhost/phpmyadmin/`.
    *   Create a new database named `semicolon_db`.
    *   Click on the `semicolon_db` database and go to the "Import" tab.
    *   Choose the `database.sql` file from the project directory and click "Go".
4.  **Project Directory:** Place the project files in the `htdocs` directory of your XAMPP installation (e.g., `C:\xampp\htdocs\Semicolon`).
5.  **Run Project:** Open your web browser and go to `http://localhost/Semicolon/`.

## TailwindCSS v4 CDN

This project uses the TailwindCSS v4 CDN for styling. The CDN is included in the `includes/header.php` file:

```html
<script src="https://cdn.tailwindcss.com"></script>
```

## Database Schema

The database schema is defined in the `database.sql` file. It consists of the following tables:

-   `users`: Stores user information.
-   `books`: Stores information about books.
-   `papers`: Stores information about past papers.
-   `paper_downloads`: Tracks paper downloads.
-   `videos`: Stores information about video courses.
-   `video_bookmarks`: Tracks video bookmarks.
-   `requests`: Manages user requests.

## PHP Functions

The `includes/functions.php` file contains helper functions used throughout the application:

-   `get_distinct_values($column, $table)`: Fetches distinct values from a database column.
-   `get_books($subject, $semester, $difficulty)`: Fetches books based on filters.
-   `get_papers($subject, $year)`: Fetches past papers based on filters.
-   `get_youtube_id($url)`: Extracts the YouTube video ID from a URL.

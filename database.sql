CREATE DATABASE IF NOT EXISTS semicolon_db;
USE semicolon_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(255),
    semester VARCHAR(255),
    difficulty VARCHAR(255),
    file_path VARCHAR(255) NOT NULL,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS paper_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    user_id INT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES papers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    youtube_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS material_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    material_type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author_publisher VARCHAR(255),
    details TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sample Data



-- Add admin user with role 'admin'
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('daxmore', 'password', 'admin');


-- Books
INSERT INTO `books` (`id`, `title`, `author`, `description`, `subject`, `semester`, `difficulty`, `file_path`, `cover_image`, `created_at`) VALUES
(1, 'How to Code in React.js', 'DigitalOcean', 'A comprehensive guide to React.js.', 'Web Development', '6', 'Beginner', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/react-js.jpg', '2025-08-05 10:01:00'),
(2, 'Introduction to Algorithms', 'Thomas H. Cormen', 'The bible of algorithms.', 'Data Structures and Algorithms', '3', 'Advanced', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/introduction-to-algorithms.jpg', '2025-08-05 10:02:00');

-- Papers
INSERT INTO `papers` (`id`, `title`, `subject`, `year`, `file_path`, `created_at`) VALUES
(1, 'Mid-Term Exam 2024', 'Database Management Systems', 2024, 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', '2025-08-05 10:03:00'),
(2, 'Final Exam 2023', 'Operating Systems', 2023, 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', '2025-08-05 10:04:00');

-- Videos
INSERT INTO `videos` (`id`, `title`, `description`, `youtube_url`, `created_at`) VALUES
(1, 'PHP for Beginners', 'A comprehensive tutorial for getting started with PHP.', 'https://youtu.be/jmpUP1MaQ9Q?si=ZjcC863awapqv1Gk', '2025-08-05 10:05:00'),
(2, 'Tailwind CSS Crash Course', 'Learn the basics of Tailwind CSS in this crash course.', 'https://youtu.be/jmpUP1MaQ9Q?si=ZjcC863awapqv1Gk', '2025-08-05 10:06:00');

-- Requests
INSERT INTO `requests` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'Request for a new book', 'Please add the book "The Pragmatic Programmer".', 'pending', '2025-08-05 10:07:00');
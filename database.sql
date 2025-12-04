CREATE DATABASE IF NOT EXISTS semicolon_db;
USE semicolon_db;

-- 1. Users Table (Updated)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    status ENUM('active', 'banned') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Books Table (Updated)
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    subject VARCHAR(255),
    -- semester removed
    difficulty VARCHAR(255),
    private_path VARCHAR(255) NOT NULL, -- Renamed from file_path
    cover_image VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    token VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Papers Table (Updated)
CREATE TABLE IF NOT EXISTS papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    -- semester removed
    private_path VARCHAR(255) NOT NULL, -- Renamed from file_path
    slug VARCHAR(255) UNIQUE,
    token VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Videos Table (Unchanged mostly, maybe add slug/token for consistency if needed, but not strictly required by prompt unless we want to track views same way)
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    youtube_url TEXT NOT NULL,
    slug VARCHAR(255) UNIQUE, -- Added for consistency
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Requests Table (General Contact/Requests)
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Material Requests Table (Specific Resource Requests)
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

-- 7. Secure Files Table (New - For mapping tokens to real paths if we want a separate layer, but books/papers have token/path columns now. 
-- The prompt mentions "Create secure_files table" AND "Books/Papers viewer pages use /view.php?token=RANDOM_STRING".
-- It also says "Books... Add token". 
-- If we have token in books table, we might not strictly need secure_files for *those* items, but the prompt explicitly asks for `secure_files` table.
-- Let's create it as a unified index or for additional attachments.)
CREATE TABLE IF NOT EXISTS secure_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_type ENUM('book','paper') NOT NULL,
    resource_id INT NOT NULL,
    random_token VARCHAR(255) UNIQUE NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. User History Table (New)
CREATE TABLE IF NOT EXISTS user_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_type ENUM('book','paper','video') NOT NULL,
    resource_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. Pro Plans Table (New - Optional but requested)
CREATE TABLE IF NOT EXISTS pro_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    features TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. Notifications Table (New - Feature 3)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- or NULL for system-wide? Let's assume targeted for now, or 0 for all.
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 11. Reactions Table (New - Feature 6)
CREATE TABLE IF NOT EXISTS reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_type ENUM('book','paper','video') NOT NULL,
    resource_id INT NOT NULL,
    is_helpful BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (user_id, resource_type, resource_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample Data (Updated)

-- Admin
INSERT INTO `users` (`username`, `password`, `role`, `status`) VALUES
('daxmore', 'password', 'admin', 'active');

-- Books (Updated columns)
INSERT INTO `books` (`title`, `author`, `description`, `subject`, `difficulty`, `private_path`, `cover_image`, `slug`, `token`) VALUES
('How to Code in React.js', 'DigitalOcean', 'A comprehensive guide to React.js.', 'Web Development', 'Beginner', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/react-js.jpg', 'how-to-code-in-react-js', 'tok_react123'),
('Introduction to Algorithms', 'Thomas H. Cormen', 'The bible of algorithms.', 'Data Structures and Algorithms', 'Advanced', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/introduction-to-algorithms.jpg', 'introduction-to-algorithms', 'tok_algo456');

-- Papers (Updated columns)
INSERT INTO `papers` (`title`, `subject`, `year`, `private_path`, `slug`, `token`) VALUES
('Mid-Term Exam 2024', 'Database Management Systems', 2024, 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'dbms-mid-2024', 'tok_dbms24'),
('Final Exam 2023', 'Operating Systems', 2023, 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'os-final-2023', 'tok_os23');

-- Videos
INSERT INTO `videos` (`title`, `description`, `youtube_url`, `slug`) VALUES
('PHP for Beginners', 'A comprehensive tutorial for getting started with PHP.', 'https://youtu.be/jmpUP1MaQ9Q?si=ZjcC863awapqv1Gk', 'php-for-beginners'),
('Tailwind CSS Crash Course', 'Learn the basics of Tailwind CSS in this crash course.', 'https://youtu.be/jmpUP1MaQ9Q?si=ZjcC863awapqv1Gk', 'tailwind-css-crash-course');

-- Requests
INSERT INTO `requests` (`name`, `email`, `subject`, `message`, `status`) VALUES
('John Doe', 'john.doe@example.com', 'Request for a new book', 'Please add the book "The Pragmatic Programmer".', 'pending');
<?php
require_once __DIR__ . '/db.php';

function get_distinct_values($column, $table = 'books')
{
    global $conn;
    $sql = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($sql);
    $values = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!empty($row[$column])) {
                $values[] = $row[$column];
            }
        }
    }
    return $values;
}

function get_system_categories() {
    return [
        'Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 
        'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 
        'Data Science', 'AI & ML', 'Cybersecurity', 'DevOps', 
        'Cloud Computing', 'General Tech'
    ];
}

function get_category_icons() {
    return [
        'Frontend' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>',
        'Backend' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>',
        'Full Stack' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
        'App Dev' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'Game Dev' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 11h.01M11 15h.01M15 15h.01M11 11h.01M5 15h14M5 9h14M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>',
        'UI/UX Design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'Graphic Design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
        'Video Editing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'Motion Graphics' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'Data Science' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'AI & ML' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
        'Cybersecurity' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'DevOps' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'Cloud Computing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>',
        'General Tech' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m14-6h2m-2 6h2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>'
    ];
}

function get_books($category = null, $semester = null, $search_query = null)
{
    global $conn;
    $sql = "SELECT * FROM books WHERE 1=1";
    
    $types = '';
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $types .= 's';
        $params[] = &$category;
    }
    if ($semester) {
        $sql .= " AND semester = ?";
        $types .= 's';
        $params[] = &$semester;
    }
    if ($search_query) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ?)";
        $search = "%$search_query%";
        $types .= 'sss';
        $params[] = &$search;
        $params[] = &$search;
        $params[] = &$search;
    }
    
    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);

    if ($types) {
        // PHP 8+ supports variadic unwrapping of parameters directly for bind_param
        if (PHP_VERSION_ID >= 81000) {
            $stmt->bind_param($types, ...array_map(function($p) { return $p; }, $params));
        } else {
            // PHP 7+ fallback
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    return $books;
}

function get_papers($category = null, $year = null, $search_query = null)
{
    global $conn;
    $sql = "SELECT * FROM papers WHERE 1=1";
    
    $types = '';
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $types .= 's';
        $params[] = &$category;
    }
    if ($year) {
        $sql .= " AND year = ?";
        $types .= 'i';
        $params[] = &$year;
    }
    if ($search_query) {
        $sql .= " AND (title LIKE ? OR subject LIKE ?)";
        $search = "%$search_query%";
        $types .= 'ss';
        $params[] = &$search;
        $params[] = &$search;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);

    if ($types) {
        if (PHP_VERSION_ID >= 81000) {
            $stmt->bind_param($types, ...array_map(function($p) { return $p; }, $params));
        } else {
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $papers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $papers[] = $row;
        }
    }
    return $papers;
}

function get_videos($category = null, $year = null, $search_query = null)
{
    global $conn;
    $sql = "SELECT * FROM videos WHERE 1=1";
    
    $types = '';
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $types .= 's';
        $params[] = &$category;
    }
    if ($year) {
        $sql .= " AND year = ?";
        $types .= 'i';
        $params[] = &$year;
    }
    if ($search_query) {
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $search = "%$search_query%";
        $types .= 'ss';
        $params[] = &$search;
        $params[] = &$search;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);

    if ($types) {
        if (PHP_VERSION_ID >= 81000) {
            $stmt->bind_param($types, ...array_map(function($p) { return $p; }, $params));
        } else {
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $videos = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
    }
    return $videos;
}

function get_youtube_id($url)
{
    $video_id = false;
    
    // Handle youtu.be short URLs
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Handle youtube.com/embed/ URLs
    elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Handle youtube.com/watch?v= URLs
    elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    // Fallback: try parse_url method
    else {
        $url_parts = parse_url($url);
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query_params);
            if (isset($query_params['v'])) {
                $video_id = $query_params['v'];
            }
        }
    }
    
    return $video_id;
}
function getUserByUsername($username)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_user_by_id($user_id)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createUser($username, $email, $password, $avatar_url = null, $security_question = null, $security_answer = null)
{
    global $conn;
    $sql = "INSERT INTO users (username, email, password, avatar_url, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $username, $email, $password, $avatar_url, $security_question, $security_answer);
    return $stmt->execute();
}

function get_count($table)
{
    global $conn;
    $sql = "SELECT COUNT(*) FROM " . $table;
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return $row[0];
    }
    return 0;
}

function generate_token($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

function generate_slug($string)
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

function record_view($user_id, $resource_type, $resource_id)
{
    global $conn;
    // Verify user exists first to avoid FK error
    $check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check->bind_param('i', $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        return false; // User doesn't exist
    }

    // Check if the history entry already exists
    $check_hist = $conn->prepare("SELECT history_id FROM user_history WHERE user_id = ? AND resource_type = ? AND resource_id = ?");
    $check_hist->bind_param('isi', $user_id, $resource_type, $resource_id);
    $check_hist->execute();
    $hist_res = $check_hist->get_result();

    if ($hist_res->num_rows > 0) {
        $sql = "UPDATE user_history SET viewed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND resource_type = ? AND resource_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $user_id, $resource_type, $resource_id);
    } else {
        $sql = "INSERT INTO user_history (user_id, resource_type, resource_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isi', $user_id, $resource_type, $resource_id);
    }
    return $stmt->execute();
}

function get_user_history($user_id, $limit = null, $offset = null)
{
    global $conn;
    $sql = "SELECT h.*, 
            CASE 
                WHEN h.resource_type = 'book' THEN b.title 
                WHEN h.resource_type = 'paper' THEN p.title 
                WHEN h.resource_type = 'video' THEN v.title 
                WHEN h.resource_type = 'post' THEN cp.title 
            END as title,
            CASE 
                WHEN h.resource_type = 'book' THEN b.token 
                WHEN h.resource_type = 'paper' THEN p.token 
                WHEN h.resource_type = 'video' THEN v.token 
                WHEN h.resource_type = 'post' THEN cp.id 
            END as token
            FROM user_history h
            LEFT JOIN books b ON h.resource_type = 'book' AND h.resource_id = b.id
            LEFT JOIN papers p ON h.resource_type = 'paper' AND h.resource_id = p.id
            LEFT JOIN videos v ON h.resource_type = 'video' AND h.resource_id = v.id
            LEFT JOIN community_posts cp ON h.resource_type = 'post' AND h.resource_id = cp.id
            WHERE h.user_id = ? 
            ORDER BY h.viewed_at DESC";
    
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
    } elseif ($limit !== null) {
        $sql .= " LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $user_id, $limit);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_history_count($user_id)
{
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM user_history WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}

function get_notifications($user_id)
{
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function mark_notification_read($notif_id, $user_id)
{
    global $conn;
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $notif_id, $user_id);
    return $stmt->execute();
}

function toggle_reaction($user_id, $resource_type, $resource_id, $is_helpful)
{
    global $conn;
    // Check if exists
    $sql = "SELECT id FROM reactions WHERE user_id = ? AND resource_type = ? AND resource_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isi', $user_id, $resource_type, $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update
        $sql = "UPDATE reactions SET is_helpful = ? WHERE user_id = ? AND resource_type = ? AND resource_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iisi', $is_helpful, $user_id, $resource_type, $resource_id);
    } else {
        // Insert
        $sql = "INSERT INTO reactions (user_id, resource_type, resource_id, is_helpful) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isii', $user_id, $resource_type, $resource_id, $is_helpful);
    }
    return $stmt->execute();
}

function get_reaction_stats($resource_type, $resource_id)
{
    global $conn;
    $sql = "SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN is_helpful = 1 THEN 1 ELSE 0 END) as helpful 
            FROM reactions 
            WHERE resource_type = ? AND resource_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $resource_type, $resource_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function is_pro_user($user_id) {
    // Placeholder logic. In future check pro_plans subscription.
    return false; 
}
function create_notification($user_id, $title, $message, $type = 'system', $action_url = null)
{
    global $conn;
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isss', $user_id, $title, $message, $type);
    
    $success = $stmt->execute();
    
    if ($success) {
        // Fetch user email
        $user_sql = "SELECT email FROM users WHERE id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param('i', $user_id);
        $user_stmt->execute();
        $user_res = $user_stmt->get_result();
        
        if ($user_row = $user_res->fetch_assoc()) {
            $to = $user_row['email'];
            
            // Construct the base URL dynamically
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Assume the project is hosted under /semicolon folder currently. Adjust as needed.
            $base_dir = '/semicolon'; 
            $base_url = $protocol . "://" . $host . $base_dir;
            
            $url = $action_url ? $base_url . '/' . ltrim($action_url, '/') : $base_url . '/dashboard.php';
            
            $template_path = __DIR__ . '/email_template.html';
            if (file_exists($template_path)) {
                $html = file_get_contents($template_path);
                
                $html = str_replace('{{TITLE}}', htmlspecialchars($title), $html);
                $html = str_replace('{{MESSAGE}}', htmlspecialchars($message), $html);
                $html = str_replace('{{ACTION_URL}}', htmlspecialchars($url), $html);
                $html = str_replace('{{YEAR}}', date('Y'), $html);
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: Semicolon <noreply@" . ($host != 'localhost' ? $host : 'semicolon.local') . ">\r\n";
                
                @mail($to, $title, $html, $headers);
            }
        }
    }
    
    return $success;
}
function get_unread_notification_count($user_id)
{
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}

function get_latest_unread_notification_type($user_id)
{
    global $conn;
    $sql = "SELECT type FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['type'] ?? null;
}

/**
 * Validates an uploaded image file.
 * Returns an array with ['ext' => extension] on success, or false on failure.
 */
function validate_image_upload($file_tmp, $file_size, $max_size = 2097152) {
    // Check size
    if ($file_size > $max_size) {
        return false;
    }

    // Check MIME type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!array_key_exists($mime_type, $allowed_mimes)) {
        return false;
    }

    return [
        'ext' => $allowed_mimes[$mime_type],
        'mime' => $mime_type
    ];
}

/* ========================================================================= */
/* ==================== SEMICOLON RPG ACADEMY ENGINE ======================= */
/* ========================================================================= */

/**
 * Calculates user level based on total XP.
 * Formula: Level = floor(sqrt(xp_total / 100)) + 1
 */
function calculate_level_from_xp($xp_total) {
    if ($xp_total < 0) return 1;
    return floor(sqrt($xp_total / 100)) + 1;
}

/**
 * Updates a user's level based on their current total XP.
 */
function update_user_level($user_id) {
    global $conn;
    
    // Get current total XP
    $stmt = $conn->prepare("SELECT xp_total, level FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $new_level = calculate_level_from_xp($row['xp_total']);
        if ($new_level != $row['level']) {
            $upd = $conn->prepare("UPDATE users SET level = ? WHERE id = ?");
            $upd->bind_param('ii', $new_level, $user_id);
            $upd->execute();
            
            if ($new_level > $row['level']) {
                create_notification($user_id, "Level Up!", "Congratulations! You have reached Level {$new_level}!", "system", "profile.php");
                if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
                $_SESSION['toasts'][] = ['type' => 'success', 'title' => 'Level Up!', 'message' => "Congratulations! You reached Level {$new_level}!"];
            }
        }
    }
}

/**
 * Adds XP to a user, respecting daily caps (Max 100 XP per day).
 * @param int $user_id
 * @param int $amount Amount of XP to grant
 * @return bool True if XP was fully or partially granted
 */
function add_user_xp($user_id, $amount) {
    global $conn;
    $max_daily_xp = 100;
    
    // Check how much XP the user has earned today
    $stmt = $conn->prepare("SELECT daily_xp_earned, last_activity_date FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $today = date('Y-m-d');
        $current_daily_xp = 0;
        
        // Reset daily XP if it's a new day
        if ($row['last_activity_date'] === $today) {
            $current_daily_xp = (int)$row['daily_xp_earned'];
        }
        
        // Check if cap reached
        if ($current_daily_xp >= $max_daily_xp) {
            return false; // Cap reached
        }
        
        // Calculate allowed XP
        $allowed_xp = min($amount, $max_daily_xp - $current_daily_xp);
        
        // Update user XP
        $sql = "UPDATE users SET xp_total = xp_total + ?, xp_weekly = xp_weekly + ?, daily_xp_earned = ?, last_activity_date = ? WHERE id = ?";
        $new_daily_xp = $current_daily_xp + $allowed_xp;
        $upd_stmt = $conn->prepare($sql);
        $upd_stmt->bind_param('iiisi', $allowed_xp, $allowed_xp, $new_daily_xp, $today, $user_id);
        $success = $upd_stmt->execute();
        
        if ($success) {
            if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
            $_SESSION['toasts'][] = ['type' => 'xp', 'title' => 'XP Gained', 'message' => "+{$allowed_xp} XP earned!"];
            update_user_level($user_id);
            check_badge_unlocks($user_id);
        }
        
        return $success;
    }
    
    return false;
}

/**
 * Checks and unlocks badges automatically based on user's total XP.
 */
function check_badge_unlocks($user_id) {
    global $conn;
    
    // Get user's current total XP
    $stmt = $conn->prepare("SELECT xp_total FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $current_xp = (int)$user_data['xp_total'];
    $stmt->close();
    
    // Check for newly unlocked badges based on required_xp
    $stmt = $conn->prepare("
        SELECT id, badge_name FROM badges 
        WHERE required_xp > 0 AND required_xp <= ? 
        AND id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
    ");
    $stmt->bind_param("ii", $current_xp, $user_id);
    $stmt->execute();
    $unlocked_badges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (!empty($unlocked_badges)) {
        $insert_stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        
        foreach ($unlocked_badges as $badge) {
            $badge_id = $badge['id'];
            $insert_stmt->bind_param("ii", $user_id, $badge_id);
            $insert_stmt->execute();
            
            // Create a notification for the badge unlock
            $message = "Congratulations! You've unlocked the '" . htmlspecialchars($badge['badge_name']) . "' badge by reaching " . $current_xp . " XP!";
            create_notification($user_id, "badge_unlocked", $message, "profile.php#badges");
            
            // Toast notification
            if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
            $_SESSION['toasts'][] = ['type' => 'badge', 'title' => 'Badge Unlocked!', 'message' => "You earned the {$badge['badge_name']} badge!"];
        }
        $insert_stmt->close();
    }
}

/**
 * Updates user daily streak.
 */
function update_daily_streak($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT last_activity_date, daily_streak FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $today = date('Y-m-d');
        $last_activity = $row['last_activity_date'];
        
        if ($last_activity == $today) {
            return; // Already active today
        }
        
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $new_streak = 1; // Default
        
        if ($last_activity == $yesterday) {
            $new_streak = $row['daily_streak'] + 1; // Increment streak
        }
        
        $upd = $conn->prepare("UPDATE users SET daily_streak = ?, last_activity_date = ? WHERE id = ?");
        $upd->bind_param('isi', $new_streak, $today, $user_id);
        $upd->execute();
        
        check_and_award_badges($user_id, 'streak', $new_streak);
    }
}

/**
 * Evaluates and awards badges to a user based on type and value.
 */
function check_and_award_badges($user_id, $check_type, $value) {
    global $conn;
    
    $badge_to_award = null;
    
    if ($check_type == 'streak') {
        if ($value >= 30) $badge_to_award = '30-Day Milestone';
        elseif ($value >= 7) $badge_to_award = '7-Day Streak';
        elseif ($value >= 3) $badge_to_award = '3-Day Streak';
    }
    
    if ($badge_to_award) {
        // Find badge ID
        $b_stmt = $conn->prepare("SELECT id, badge_name FROM badges WHERE badge_name = ?");
        $b_stmt->bind_param('s', $badge_to_award);
        $b_stmt->execute();
        $b_res = $b_stmt->get_result();
        
        if ($badge = $b_res->fetch_assoc()) {
            $badge_id = $badge['id'];
            
            // Give user the badge if they don't have it
            $chk = $conn->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
            $chk->bind_param('ii', $user_id, $badge_id);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) {
                $ins = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
                $ins->bind_param('ii', $user_id, $badge_id);
                $ins->execute();
                
                create_notification($user_id, "Badge Earned!", "You've earned the '{$badge['badge_name']}' badge!", "system", "profile.php");
                if (!isset($_SESSION['toasts'])) $_SESSION['toasts'] = [];
                $_SESSION['toasts'][] = ['type' => 'badge', 'title' => 'Badge Earned!', 'message' => "Unlocked: {$badge['badge_name']}!"];
            }
        }
    }
}

/**
 * Gets skills progress for a user.
 */
function get_user_skills_progress($user_id) {
    global $conn;
    $sql = "SELECT s.id, s.name, s.description, usp.current_level, usp.interview_unlocked
            FROM skills s
            LEFT JOIN user_skill_progress usp ON s.id = usp.skill_id AND usp.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_all(MYSQLI_ASSOC);
}

/**
 * Gets all user badges.
 */
function get_user_badges($user_id, $only_equipped = false) {
    global $conn;
    $sql = "SELECT b.id, b.badge_name, b.description, b.svg_icon, ub.is_equipped, ub.earned_at
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ?";
            
    if ($only_equipped) {
        $sql .= " AND ub.is_equipped = 1";
    }
    $sql .= " ORDER BY ub.earned_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>
<?php
function get_distinct_values($column, $table = 'books')
{
    global $conn;
    $sql = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($sql);
    $values = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
    }
    return $values;
}

function get_books($subject = null, $semester = null)
{
    global $conn;
    $sql = "SELECT * FROM books WHERE 1=1";
    if ($subject) {
        $sql .= " AND subject = ?";
    }
    if ($semester) {
        $sql .= " AND semester = ?";
    }

    $stmt = $conn->prepare($sql);

    $types = '';
    $params = [];
    if ($subject) {
        $types .= 's';
        $params[] = &$subject;
    }
    if ($semester) {
        $types .= 's';
        $params[] = &$semester;
    }


    if ($types) {
        $stmt->bind_param($types, ...$params);
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

function get_papers($subject = null, $year = null)
{
    global $conn;
    $sql = "SELECT * FROM papers WHERE 1=1";
    if ($subject) {
        $sql .= " AND subject = ?";
    }
    if ($year) {
        $sql .= " AND year = ?";
    }
    $stmt = $conn->prepare($sql);

    $types = '';
    $params = [];
    if ($subject) {
        $types .= 's';
        $params[] = &$subject;
    }
    if ($year) {
        $types .= 'i';
        $params[] = &$year;
    }

    if ($types) {
        $stmt->bind_param($types, ...$params);
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

function createUser($username, $password, $avatar_url = null)
{
    global $conn;
    $sql = "INSERT INTO users (username, password, avatar_url) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $username, $password, $avatar_url);
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
    $check_hist = $conn->prepare("SELECT id FROM user_history WHERE user_id = ? AND resource_type = ? AND resource_id = ?");
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
                WHEN h.resource_type = 'video' THEN v.slug 
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
function create_notification($user_id, $title, $message, $type = 'system')
{
    global $conn;
    $sql = "INSERT INTO notifications (user_id, title, message, notification_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $user_id, $title, $message);
    return $stmt->execute();
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
    $sql = "SELECT notification_type FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $result = $stmt->get_result()->fetch_assoc();
    return $result['notification_type'] ?? null;
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
?>
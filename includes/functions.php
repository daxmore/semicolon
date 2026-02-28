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
function create_notification($user_id, $title, $message, $type = 'system')
{
    global $conn;
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isss', $user_id, $title, $message, $type);
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
                create_notification($user_id, "Level Up!", "Congratulations! You have reached Level {$new_level}!", "system");
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
        }
        
        return $success;
    }
    
    return false;
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
                
                create_notification($user_id, "Badge Earned!", "You've earned the '{$badge['badge_name']}' badge!", "system");
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
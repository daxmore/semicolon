<?php
require_once 'includes/db.php';
$conn->query("ALTER TABLE badges ADD COLUMN required_xp INT DEFAULT 0");

$func = <<<PHP

/**
 * Adds XP to a user and checks for any newly unlocked badges based on required_xp thresholds.
 */
function add_xp(\$user_id, \$amount) {
    global \$conn;
    
    // Update user's XP
    \$stmt = \$conn->prepare("UPDATE users SET xp_total = xp_total + ?, xp_weekly = xp_weekly + ?, daily_xp_earned = daily_xp_earned + ? WHERE id = ?");
    \$stmt->bind_param("iiii", \$amount, \$amount, \$amount, \$user_id);
    \$stmt->execute();
    \$stmt->close();
    
    // Get user's new total XP
    \$stmt = \$conn->prepare("SELECT xp_total FROM users WHERE id = ?");
    \$stmt->bind_param("i", \$user_id);
    \$stmt->execute();
    \$result = \$stmt->get_result();
    \$user_data = \$result->fetch_assoc();
    \$current_xp = \$user_data['xp_total'];
    \$stmt->close();
    
    // Check for newly unlocked badges
    \$stmt = \$conn->prepare("
        SELECT id, badge_name FROM badges 
        WHERE required_xp > 0 AND required_xp <= ? 
        AND id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
    ");
    \$stmt->bind_param("ii", \$current_xp, \$user_id);
    \$stmt->execute();
    \$unlocked_badges = \$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    \$stmt->close();
    
    if (!empty(\$unlocked_badges)) {
        \$insert_stmt = \$conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        
        foreach (\$unlocked_badges as \$badge) {
            \$badge_id = \$badge['id'];
            \$insert_stmt->bind_param("ii", \$user_id, \$badge_id);
            \$insert_stmt->execute();
            
            // Create a notification for the badge unlock
            \$message = "Congratulations! You've unlocked the '" . \$badge['badge_name'] . "' badge by reaching " . \$current_xp . " XP!";
            create_notification(\$user_id, "badge_unlocked", \$message, "profile.php#badges");
        }
        \$insert_stmt->close();
    }
}
PHP;

// Remove the closing ?> if it exists and append
$content = file_get_contents('includes/functions.php');
$content = preg_replace('/\?>\s*$/', '', $content);
$content .= "\n" . $func . "\n?>";
file_put_contents('includes/functions.php', $content);
echo "Added function successfully";

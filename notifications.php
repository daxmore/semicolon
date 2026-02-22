<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];

// Handle Mark as Read Actions
if (isset($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt_update = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt_update->bind_param('ii', $notif_id, $user_id);
    $stmt_update->execute();
    header('Location: notifications.php');
    exit();
}

if (isset($_GET['mark_all_read'])) {
    $stmt_update_all = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt_update_all->bind_param('i', $user_id);
    $stmt_update_all->execute();
    header('Location: notifications.php');
    exit();
}

// Fetch user's notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <section class="py-12">
        <div class="container mx-auto px-6 max-w-3xl">
            
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-zinc-900 flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 rounded-xl text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    Notifications
                </h1>
                
                <?php if (!empty($notifications)): ?>
                    <a href="notifications.php?mark_all_read=true" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">Mark all as read</a>
                <?php endif; ?>
            </div>

            <!-- Notifications List -->
            <div class="bg-white border border-zinc-100 rounded-2xl shadow-sm overflow-hidden">
                <?php if (empty($notifications)): ?>
                    <div class="p-12 text-center text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-lg font-medium text-zinc-800">You're all caught up!</p>
                        <p class="mt-1">You don't have any notifications right now.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-zinc-100">
                        <?php foreach ($notifications as $notif): ?>
                            <?php 
                                $is_read = $notif['is_read'];
                                $type = $notif['type'] ?? 'system'; 
                                
                                // Determine icon and styles based on type
                                if ($type === 'new_comment') {
                                    $icon_bg = 'bg-amber-50';
                                    $icon_text = 'text-amber-600';
                                    $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />';
                                } else {
                                    // Default system message styling
                                    $icon_bg = 'bg-indigo-50';
                                    $icon_text = 'text-indigo-600';
                                    $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                                }
                            ?>
                            <div class="p-6 transition hover:bg-zinc-50 flex gap-4 <?php echo !$is_read ? 'bg-indigo-50/20' : ''; ?>">
                                
                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-full <?php echo $icon_bg . ' ' . $icon_text; ?> flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <?php echo $icon_svg; ?>
                                    </svg>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-bold text-zinc-900 <?php echo !$is_read ? '' : 'text-zinc-600'; ?>">
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                            <?php if (!$is_read): ?>
                                                <span class="inline-block w-2.5 h-2.5 bg-indigo-500 rounded-full ml-2 align-middle"></span>
                                            <?php endif; ?>
                                        </h3>
                                        <span class="text-xs text-zinc-400 flex-shrink-0 whitespace-nowrap">
                                            <?php echo date('M j, g:i a', strtotime($notif['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-zinc-600 mt-1 line-clamp-2">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                    </p>
                                    
                                    <div class="mt-3 flex items-center gap-4 text-sm">
                                        <?php if (!empty($notif['link'])): ?>
                                            <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="font-medium text-indigo-600 hover:text-indigo-700">View Detail &rarr;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Mark as Read Action (Right Side) -->
                                <?php if (!$is_read): ?>
                                    <div class="flex-shrink-0 flex items-start pt-1">
                                        <a href="notifications.php?mark_read=<?php echo $notif['id']; ?>" class="text-zinc-300 hover:text-indigo-600 transition" title="Mark as read">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="flex-shrink-0 flex items-start pt-1 text-zinc-200" title="Read">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

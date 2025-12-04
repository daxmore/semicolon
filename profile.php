<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Get current user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();

    // Validate current password
    if (!password_verify($current_password, $current_user['password'])) {
        $message = 'Current password is incorrect.';
        $message_type = 'error';
    } elseif (empty($new_username)) {
        $message = 'Username cannot be empty.';
        $message_type = 'error';
    } elseif (strlen($new_username) < 3) {
        $message = 'Username must be at least 3 characters.';
        $message_type = 'error';
    } else {
        // Check if username is taken by another user
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_stmt->bind_param("si", $new_username, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = 'Username is already taken.';
            $message_type = 'error';
        } else {
            // Update username
            $update_sql = "UPDATE users SET username = ?";
            $params = [$new_username];
            $types = "s";

            // Update password if provided
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $message = 'New password must be at least 6 characters.';
                    $message_type = 'error';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'New passwords do not match.';
                    $message_type = 'error';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql .= ", password = ?";
                    $params[] = $hashed_password;
                    $types .= "s";
                }
            }

            if (empty($message)) {
                $update_sql .= " WHERE id = ?";
                $params[] = $user_id;
                $types .= "i";

                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param($types, ...$params);
                
                if ($update_stmt->execute()) {
                    $_SESSION['username'] = $new_username;
                    $message = 'Profile updated successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to update profile. Please try again.';
                    $message_type = 'error';
                }
            }
        }
    }
}

$user = get_user_by_id($user_id); 
if (!$user) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

$history = get_user_history($user_id, 5);
$notifications = get_notifications($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Semicolon</title>
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
    <style>
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Profile Hero -->
    <section class="relative pt-10 pb-8 overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6">
            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="rounded-2xl p-4 mb-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                    <div class="flex items-center gap-3">
                        <?php if ($message_type === 'success'): ?>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <?php else: ?>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <?php endif; ?>
                        <p class="font-medium"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-zinc-100 p-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex flex-col md:flex-row items-center gap-5 text-center md:text-left">
                        <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-3xl font-bold text-white shadow-lg shadow-indigo-500/25">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-zinc-900"><?php echo htmlspecialchars($user['username']); ?></h1>
                            <div class="flex items-center justify-center md:justify-start gap-3 mt-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <?php echo ucfirst($user['role'] ?? 'User'); ?>
                                </span>
                                <span class="text-zinc-500 text-sm">Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="auth/logout.php" class="px-5 py-2.5 border border-zinc-200 rounded-xl text-zinc-700 hover:bg-zinc-50 transition font-medium">
                            Logout
                        </a>
                        <button onclick="openEditModal()" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="pb-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Column -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <a href="books.php" class="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-indigo-200 hover:shadow-lg transition-all group">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-zinc-900">Browse Books</h3>
                            <p class="text-sm text-zinc-500 mt-1">Access library</p>
                        </a>
                        <a href="papers.php" class="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-teal-200 hover:shadow-lg transition-all group">
                            <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-zinc-900">Read Papers</h3>
                            <p class="text-sm text-zinc-500 mt-1">Latest research</p>
                        </a>
                        <a href="videos.php" class="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-rose-200 hover:shadow-lg transition-all group">
                            <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-zinc-900">Watch Videos</h3>
                            <p class="text-sm text-zinc-500 mt-1">Tutorials & more</p>
                        </a>
                    </div>

                    <!-- Recently Viewed -->
                    <div class="bg-white rounded-2xl border border-zinc-100 overflow-hidden">
                        <div class="p-6 border-b border-zinc-100">
                            <h3 class="text-lg font-bold text-zinc-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Recently Viewed
                            </h3>
                        </div>
                        
                        <?php if (empty($history)): ?>
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="text-zinc-500 mb-2">No activity yet</p>
                                <a href="books.php" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm">Start exploring resources →</a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-zinc-100">
                                <?php foreach ($history as $item): ?>
                                    <a href="view.php?token=<?php echo $item['token'] ?? ''; ?>" class="flex items-center gap-4 p-4 hover:bg-zinc-50 transition">
                                        <?php 
                                        $iconBg = $item['resource_type'] === 'book' ? 'bg-indigo-100 text-indigo-600' : 
                                                  ($item['resource_type'] === 'paper' ? 'bg-teal-100 text-teal-600' : 'bg-rose-100 text-rose-600');
                                        ?>
                                        <div class="w-10 h-10 <?php echo $iconBg; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                            <?php if ($item['resource_type'] === 'book'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            <?php elseif ($item['resource_type'] === 'paper'): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-zinc-900 truncate"><?php echo htmlspecialchars($item['title'] ?? 'Unknown Resource'); ?></p>
                                            <p class="text-sm text-zinc-500"><?php echo ucfirst($item['resource_type']); ?> • <?php echo date('M d, Y', strtotime($item['viewed_at'])); ?></p>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Notifications -->
                    <div id="notifications" class="bg-white rounded-2xl border border-zinc-100 p-6">
                        <h3 class="text-lg font-bold text-zinc-900 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifications
                        </h3>
                        <?php if (empty($notifications)): ?>
                            <p class="text-zinc-500 text-sm">No new notifications.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php foreach ($notifications as $notif): ?>
                                    <li class="p-3 rounded-xl bg-zinc-50 <?php echo !$notif['is_read'] ? 'border-l-2 border-l-indigo-500' : ''; ?>">
                                        <p class="text-sm font-medium text-zinc-900"><?php echo htmlspecialchars($notif['title']); ?></p>
                                        <p class="text-xs text-zinc-500 mt-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Account Status -->
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white">
                        <h3 class="font-bold mb-4 text-white">Account Status</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-indigo-200">Plan</span>
                                <span class="font-medium">Free Tier</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-indigo-200">Status</span>
                                <span class="font-medium"><?php echo ucfirst($user['status'] ?? 'Active'); ?></span>
                            </div>
                        </div>
                        <a href="pricing.php" class="block w-full text-center py-3 bg-white text-indigo-600 font-semibold rounded-xl mt-4 hover:bg-indigo-50 transition">
                            Upgrade to Pro
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0" onclick="closeEditModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-lg relative z-10 overflow-hidden">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">Edit Profile</h2>
                        <button onclick="closeEditModal()" class="text-white/70 hover:text-white transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <form action="profile.php" method="POST" class="p-6 space-y-5">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-zinc-700 mb-2">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($user['username']); ?>" 
                            required 
                            minlength="3"
                            class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                        >
                    </div>
                    
                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-zinc-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white px-3 text-zinc-500">Change Password (optional)</span>
                        </div>
                    </div>
                    
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-zinc-700 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            required
                            placeholder="Enter your current password"
                            class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                        >
                        <p class="text-xs text-zinc-500 mt-1">Required to confirm changes</p>
                    </div>
                    
                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-zinc-700 mb-2">New Password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            minlength="6"
                            placeholder="Leave blank to keep current"
                            class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                        >
                    </div>
                    
                    <!-- Confirm New Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-zinc-700 mb-2">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Repeat new password"
                            class="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                        >
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button 
                            type="button" 
                            onclick="closeEditModal()" 
                            class="flex-1 px-4 py-3 border-2 border-zinc-200 text-zinc-700 font-semibold rounded-xl hover:bg-zinc-50 transition"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 px-4 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function openEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });

        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
            }
        });
    </script>
</body>
</html>

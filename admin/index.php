<?php
session_start();
require_once '../includes/db.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// If logged in, show the dashboard content.
// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_papers = $conn->query("SELECT COUNT(*) as count FROM papers")->fetch_assoc()['count'];
$total_videos = $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Users</h2>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_users; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Books</h2>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_books; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Papers</h2>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_papers; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Total Videos</h2>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_videos; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Pending Requests</h2>
                <p class="text-3xl font-bold text-gray-900"><?php echo $pending_requests; ?></p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
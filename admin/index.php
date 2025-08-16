<?php
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
<?php
include 'header.php';

// Hardcoded admin authentication
if (!isset($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['username'] === 'daxmore' && $_POST['password'] === 'daxmore@!1995') {
            $_SESSION['admin'] = true;
        } else {
            $error = "Invalid credentials";
        }
    }
    if (!isset($_SESSION['admin'])) {
        echo '<div class="flex justify-center items-center h-screen bg-gray-100">';
        echo '<form method="POST" class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">';
        echo '<h1 class="text-2xl font-bold mb-6 text-center">Admin Login</h1>';
        if (isset($error)) {
            echo '<p class="text-red-500 text-center mb-4">' . $error . '</p>';
        }
        echo '<div class="mb-4">';
        echo '<label for="username" class="block text-gray-700 font-bold mb-2">Username</label>';
        echo '<input type="text" id="username" name="username" class="w-full px-3 py-2 border rounded-lg" required>';
        echo '</div>';
        echo '<div class="mb-6">';
        echo '<label for="password" class="block text-gray-700 font-bold mb-2">Password</label>';
        echo '<input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded-lg" required>';
        echo '</div>';
        echo '<button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Login</button>';
        echo '</form>';
        echo '</div>';
        exit();
    }
}

// Fetch statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_papers = $conn->query("SELECT COUNT(*) as count FROM papers")->fetch_assoc()['count'];
$total_videos = $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'")->fetch_assoc()['count'];

?>

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

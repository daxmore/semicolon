<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Redirect admin users to the admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $material_type = $_POST['material_type'] ?? '';
    $title = $_POST['title'] ?? '';
    $author_publisher = $_POST['author_publisher'] ?? '';
    $details = $_POST['details'] ?? '';

    if (empty($material_type) || empty($title)) {
        $message = 'Material type and title are required.';
        $message_type = 'error';
    } else {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO material_requests (user_id, material_type, title, author_publisher, details) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->bind_param("issss", $user_id, $material_type, $title, $author_publisher, $details) && $stmt->execute()) {
            $message = 'Your request has been submitted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to submit your request. Please try again.';
            $message_type = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Study Material - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Request Study Material</h1>

        <?php if ($message): ?>
            <div id="alertMessage"
                class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative mb-6"
                role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 <?php echo $message_type === 'dark' ? 'text-green-500' : 'text-red-500'; ?>"
                        role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        onclick="document.getElementById('alertMessage').style.display='none';">
                        <title>Close</title>
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </span>
            </div>
        <?php endif; ?>

        <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-xl">
            <form action="request.php" method="POST" class="space-y-6">
                <div>
                    <label for="material_type" class="block text-sm font-medium text-gray-700">Material Type</label>
                    <select name="material_type" id="material_type" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select Type</option>
                        <option value="book">Book</option>
                        <option value="paper">Exam Paper</option>
                        <option value="video">Video Tutorial</option>
                    </select>
                </div>
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title / Topic</label>
                    <input type="text" name="title" id="title" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="author_publisher" class="block text-sm font-medium text-gray-700">Author / Publisher
                        (Optional)</label>
                    <input type="text" name="author_publisher" id="author_publisher"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="details" class="block text-sm font-medium text-gray-700">Additional Details
                        (Optional)</label>
                    <textarea id="details" name="details" rows="4"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // header('Location: auth/login.php');
    // exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-teal-500 to-blue-600 text-white rounded-lg shadow-lg p-8 mb-8 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Welcome, <?php echo $_SESSION['username'] ?? 'Guest'; ?>!</h1>
            <p class="text-lg md:text-xl mb-6">Explore a world of knowledge at Semicolon. Your academic journey starts here.</p>
            <a href="papers.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 px-8 py-3 rounded-full text-lg font-semibold transition duration-300 ease-in-out shadow-md">
                Start Exploring Papers
            </a>
        </div>

        <!-- Quick Access / Featured Content Section -->
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Quick Access & Highlights</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Papers Card -->
            <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-shadow duration-300 ease-in-out p-6 flex flex-col items-center text-center">
                <div class="text-blue-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3">Research Papers</h3>
                <p class="text-gray-600 mb-4">Dive into a vast collection of academic papers across various subjects and years.</p>
                <a href="papers.php" class="mt-auto inline-block bg-blue-600 text-white hover:bg-blue-700 px-6 py-2 rounded-full text-md font-medium transition duration-300 ease-in-out">
                    View All Papers
                </a>
            </div>

            <!-- Books Card -->
            <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-shadow duration-300 ease-in-out p-6 flex flex-col items-center text-center">
                <div class="text-green-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.747 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3">Digital Books</h3>
                <p class="text-gray-600 mb-4">Access a curated selection of digital books to enhance your learning experience.</p>
                <a href="books.php" class="mt-auto inline-block bg-green-600 text-white hover:bg-green-700 px-6 py-2 rounded-full text-md font-medium transition duration-300 ease-in-out">
                    Browse Books
                </a>
            </div>

            <!-- Videos Card -->
            <div class="bg-white rounded-lg shadow-xl hover:shadow-2xl transition-shadow duration-300 ease-in-out p-6 flex flex-col items-center text-center">
                <div class="text-red-500 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3">Educational Videos</h3>
                <p class="text-gray-600 mb-4">Watch insightful videos and tutorials to deepen your understanding of complex topics.</p>
                <a href="videos.php" class="mt-auto inline-block bg-red-600 text-white hover:bg-red-700 px-6 py-2 rounded-full text-md font-medium transition duration-300 ease-in-out">
                    Watch Videos
                </a>
            </div>
        </div>

        <!-- Optional: Add more sections here, e.g., "Latest Additions", "Popular Categories" -->

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
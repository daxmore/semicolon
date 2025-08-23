<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Fetch some data for the dashboard
$total_papers = get_count('papers');
$total_books = get_count('books');
$total_videos = get_count('videos');

// You can add more complex queries here for recent activity, recommendations, etc.
// For now, let's just use placeholders or simple fetches.

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div
            class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white rounded-lg shadow-lg p-8 mb-8 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Welcome Back,
                <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p class="text-lg md:text-xl mb-6">Your personalized hub for academic resources.</p>
            <a href="request.php"
                class="inline-block bg-white text-purple-700 hover:bg-gray-100 px-8 py-3 rounded-full text-lg font-semibold transition duration-300 ease-in-out shadow-md">
                Request New Material
            </a>
        </div>

        <!-- Statistics Section -->
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Your Semicolon Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Total Papers Card -->
            <div
                class="bg-white rounded-lg shadow-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-blue-500 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Total Papers</h3>
                <p class="text-4xl font-bold text-blue-600 mt-2"><?php echo $total_papers; ?></p>
            </div>

            <!-- Total Books Card -->
            <div
                class="bg-white rounded-lg shadow-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-green-500 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.747 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Total Books</h3>
                <p class="text-4xl font-bold text-green-600 mt-2"><?php echo $total_books; ?></p>
            </div>

            <!-- Total Videos Card -->
            <div
                class="bg-white rounded-lg shadow-xl p-6 text-center transform hover:scale-105 transition-transform duration-300">
                <div class="text-red-500 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Total Videos</h3>
                <p class="text-4xl font-bold text-red-600 mt-2"><?php echo $total_videos; ?></p>
            </div>
        </div>

        <!-- Recent Activity / Recommendations Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
                <ul class="space-y-4">
                    <li class="flex items-center space-x-3">
                        <div class="flex-shrink-0 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">Downloaded: <span class="text-blue-600">Mid-Term Exam
                                    2024</span></p>
                            <p class="text-sm text-gray-500">2 hours ago</p>
                        </div>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div class="flex-shrink-0 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">Watched: <span class="text-red-600">PHP for Beginners
                                    Tutorial</span></p>
                            <p class="text-sm text-gray-500">Yesterday</p>
                        </div>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div class="flex-shrink-0 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.747 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">Viewed: <span class="text-green-600">Introduction to
                                    Algorithms</span></p>
                            <p class="text-sm text-gray-500">3 days ago</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Quick Links / Recommendations -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Links</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="papers.php"
                        class="block bg-blue-50 hover:bg-blue-100 rounded-lg p-4 text-center transition duration-300">
                        <h3 class="font-semibold text-blue-700">Browse Papers</h3>
                        <p class="text-sm text-gray-600">Find research and past exams.</p>
                    </a>
                    <a href="books.php"
                        class="block bg-green-50 hover:bg-green-100 rounded-lg p-4 text-center transition duration-300">
                        <h3 class="font-semibold text-green-700">Explore Books</h3>
                        <p class="text-sm text-gray-600">Discover digital textbooks.</p>
                    </a>
                    <a href="videos.php"
                        class="block bg-red-50 hover:bg-red-100 rounded-lg p-4 text-center transition duration-300">
                        <h3 class="font-semibold text-red-700">Watch Videos</h3>
                        <p class="text-sm text-gray-600">Learn with video tutorials.</p>
                    </a>
                    <a href="request.php"
                        class="block bg-purple-50 hover:bg-purple-100 rounded-lg p-4 text-center transition duration-300">
                        <h3 class="font-semibold text-purple-700">Request Material</h3>
                        <p class="text-sm text-gray-600">Can't find it? Request it!</p>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
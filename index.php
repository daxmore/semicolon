<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
        exit();
    } else {
        header('Location: dashboard.php');
        exit();
    }
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch some featured content for the homepage
$featured_books = $conn->query("SELECT * FROM books ORDER BY id DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$featured_papers = $conn->query("SELECT * FROM papers ORDER BY id DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$featured_videos = $conn->query("SELECT * FROM videos ORDER BY id DESC LIMIT 2")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon - Educational Resources for BCA Students</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-teal-500 to-teal-700 text-white">
        <div class="mx-auto max-w-screen-xl px-4 py-16 lg:flex lg:items-center lg:justify-between">
            <div class="mx-auto max-w-xl text-center lg:text-left lg:mx-0">
                <h1 class="text-3xl font-extrabold sm:text-5xl">
                    Empower Your Learning Journey
                    <span class="block font-extrabold text-yellow-300 mt-2">
                        With Semicolon.
                    </span>
                </h1>
                <p class="mt-4 sm:text-xl/relaxed">
                    Access educational materials, past papers, and video courses to enhance your academic success.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4 lg:justify-start">
                    <a href="books.php"
                        class="block w-full rounded bg-white px-12 py-3 text-sm font-medium text-teal-600 shadow hover:bg-gray-100 focus:outline-none focus:ring active:text-teal-500 sm:w-auto">
                        Explore Books
                    </a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="auth/signup.php"
                            class="block w-full rounded px-12 py-3 text-sm font-medium text-white shadow border border-white hover:bg-teal-600 focus:outline-none focus:ring active:bg-teal-500 sm:w-auto">
                            Join Now
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hidden lg:block">
                <img src="assets/images/hero-image.png" alt="Education Illustration" class="w-full max-w-md">
            </div>
        </div>
    </section>

    <!-- Feature Section -->
    <section class="bg-white py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">What We Offer</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex flex-col items-center p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                    <div class="bg-teal-100 p-3 rounded-full mb-4">
                        <i class="fas fa-book text-teal-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Digital Books</h3>
                    <p class="text-gray-600 text-center">Access a comprehensive library of academic books and reference
                        materials.</p>
                    <a href="books.php" class="mt-4 text-teal-600 hover:underline">Browse Books →</a>
                </div>

                <div class="flex flex-col items-center p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                    <div class="bg-teal-100 p-3 rounded-full mb-4">
                        <i class="fas fa-file-alt text-teal-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Past Papers</h3>
                    <p class="text-gray-600 text-center">Practice with previous years' examination papers to prepare
                        effectively.</p>
                    <a href="papers.php" class="mt-4 text-teal-600 hover:underline">View Papers →</a>
                </div>

                <div class="flex flex-col items-center p-6 rounded-lg shadow-lg hover:shadow-xl transition">
                    <div class="bg-teal-100 p-3 rounded-full mb-4">
                        <i class="fas fa-video text-teal-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Video Courses</h3>
                    <p class="text-gray-600 text-center">Learn with our curated collection of educational videos and
                        tutorials.</p>
                    <a href="videos.php" class="mt-4 text-teal-600 hover:underline">Watch Videos →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Content Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center">Recently Added Resources</h2>

            <!-- Featured Books -->
            <?php if (!empty($featured_books)): ?>
                <div class="mb-12">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-semibold">Latest Books</h3>
                        <a href="books.php" class="text-teal-600 hover:underline">View All →</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($featured_books as $book): ?>
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="p-4">
                                    <h4 class="font-semibold"><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($book['author']); ?></p>
                                    <a href="<?php echo htmlspecialchars($book['file_path']); ?>"
                                        class="mt-4 inline-block px-4 py-2 bg-teal-600 text-white text-sm rounded hover:bg-teal-700 transition"
                                        download>Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Featured Papers -->
            <?php if (!empty($featured_papers)): ?>
                <div class="mb-12">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-semibold">Latest Papers</h3>
                        <a href="papers.php" class="text-teal-600 hover:underline">View All →</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($featured_papers as $paper): ?>
                            <div class="bg-white p-4 rounded-lg shadow-lg">
                                <div class="flex items-center mb-3">
                                    <i class="fas fa-file-pdf text-red-500 mr-2 text-lg"></i>
                                    <span class="font-semibold"><?php echo htmlspecialchars($paper['title']); ?></span>
                                </div>
                                <div class="text-sm text-gray-600 mb-4">
                                    <p>Subject: <?php echo htmlspecialchars($paper['subject']); ?></p>
                                    <p>Year: <?php echo htmlspecialchars($paper['year']); ?></p>
                                </div>
                                <a href="download_paper.php?id=<?php echo $paper['id']; ?>"
                                    class="inline-block px-4 py-2 bg-teal-600 text-white text-sm rounded hover:bg-teal-700 transition">
                                    Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Featured Videos -->
            <?php if (!empty($featured_videos)): ?>
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-semibold">Latest Videos</h3>
                        <a href="videos.php" class="text-teal-600 hover:underline">View All →</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($featured_videos as $video): ?>
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <div class="aspect-w-16 aspect-h-9">
                                    <?php echo htmlspecialchars_decode($video['youtube_url']); ?>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold"><?php echo htmlspecialchars($video['title']); ?></h4>
                                    <p class="text-sm text-gray-600">
                                        <?php
                                        $description = htmlspecialchars($video['description']);
                                        if (strlen($description) > 100) {
                                            echo substr($description, 0, 100) . '...';
                                        } else {
                                            echo $description;
                                        }
                                        ?>
                                    </p>
                                    <a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" target="_blank" class="mt-4 inline-block px-4 py-2 bg-teal-600 text-white text-sm rounded hover:bg-teal-700 transition">Watch Video</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16 bg-teal-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Can't Find What You Need?</h2>
            <p class="text-xl mb-8">Submit a request and we'll try to add the resources you're looking for.</p>
            <a href="request.php"
                class="inline-block px-6 py-3 bg-white text-teal-600 font-medium rounded-md hover:bg-gray-100 transition">
                Submit Resource Request
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Optional: Add a simple scroll-to-top button -->
    <button id="scrollToTop" class="fixed bottom-8 right-8 bg-teal-600 text-white py-3 px-5 rounded-full shadow-lg hidden">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Simple scroll-to-top functionality
        const scrollToTopBtn = document.getElementById('scrollToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.remove('hidden');
            } else {
                scrollToTopBtn.classList.add('hidden');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>
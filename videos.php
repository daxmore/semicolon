<?php
require_once 'includes/db.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <?php
    include 'includes/header.php';

    // Fetch videos from the database
    $result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
    $videos = $result->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8">Video Courses</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($videos as $video): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="aspect-w-16 aspect-h-9">
                        <iframe
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars(get_youtube_id($video['youtube_url'])); ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($video['title']); ?></h2>
                        <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($video['description']); ?></p>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch distinctive years
$years = [];
$year_result = $conn->query("SELECT DISTINCT YEAR(created_at) as year FROM videos ORDER BY year DESC");
while ($row = $year_result->fetch_assoc()) {
    if ($row['year']) $years[] = $row['year'];
}

$year = $_GET['year'] ?? null;
$sql = "SELECT * FROM videos WHERE 1=1";
if ($year) {
    $sql .= " AND YEAR(created_at) = ?";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($year) {
    $stmt->bind_param('i', $year);
}
$stmt->execute();
$videos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Semicolon</title>
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

    <!-- Hero Section -->
    <section class="relative pt-10 pb-6 overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-rose-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-pink-200/20 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h1 class="text-4xl font-bold text-zinc-900">Video <span class="bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">Tutorials</span></h1>
                    <p class="text-zinc-500 mt-2">Learn with our curated video tutorials</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="pb-20">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <div class="w-full lg:w-64 flex-shrink-0">
                    <div class="sticky top-24 space-y-6">
                        <!-- Year Filter -->
                        <?php if (!empty($years)): ?>
                        <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                            <h3 class="text-lg font-bold text-zinc-900 mb-4 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Filter by Year
                            </h3>
                            <select onchange="window.location.href='videos.php?year='+this.value"
                                    class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none transition">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?php echo htmlspecialchars($y); ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>><?php echo htmlspecialchars($y); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="bg-white rounded-2xl border border-zinc-100 p-6 shadow-sm">
                            <div class="flex items-center justify-between text-sm text-zinc-500">
                                <span>Results</span>
                                <span class="font-medium text-zinc-900 bg-zinc-100 px-2 py-0.5 rounded-md"><?php echo count($videos); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Videos Grid -->
                <div class="flex-1">
                    <?php if (empty($videos)): ?>
                        <div class="text-center py-20 bg-white rounded-2xl border border-zinc-100 shadow-sm">
                            <div class="w-20 h-20 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-xl text-zinc-500 mb-2">No videos found</p>
                            <?php if ($year): ?>
                                <p class="text-zinc-400">Try selecting a different year</p>
                            <?php else: ?>
                                <p class="text-zinc-400">Check back later for new content</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($videos as $video): 
                        $video_content = $video['youtube_url'];
                        $is_iframe = (strpos(trim($video_content), '<iframe') === 0);
                        $youtube_id = $is_iframe ? false : get_youtube_id($video_content);
                    ?>
                        <div class="bg-white rounded-2xl border border-zinc-100 hover:border-rose-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
                            <!-- YouTube Embed -->
                            <div class="aspect-video bg-zinc-900 flex items-center justify-center overflow-hidden">
                                <?php if ($is_iframe): ?>
                                    <div class="w-full h-full [&_iframe]:w-full [&_iframe]:h-full">
                                        <?php echo $video_content; ?>
                                    </div>
                                <?php elseif ($youtube_id): ?>
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                        title="<?php echo htmlspecialchars($video['title']); ?>"
                                        class="w-full h-full"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                    </iframe>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-rose-500 to-pink-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-5">
                                <h3 class="font-bold text-zinc-900 mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($video['title']); ?>
                                </h3>
                                <p class="text-zinc-500 text-sm line-clamp-2">
                                    <?php echo htmlspecialchars($video['description']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
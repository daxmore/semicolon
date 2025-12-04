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

$subjects = get_distinct_values('subject', 'papers');
$years = [];
$year_result = $conn->query("SELECT DISTINCT year FROM papers ORDER BY year DESC");
while ($row = $year_result->fetch_assoc()) {
    $years[] = $row['year'];
}

$subject = $_GET['subject'] ?? null;
$year = $_GET['year'] ?? null;
$papers = get_papers($subject, $year);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papers - Semicolon</title>
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
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-emerald-200/20 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-teal-600"><?php echo count($papers); ?> Papers Available</span>
                    </div>
                    <h1 class="text-4xl font-bold text-zinc-900">Research <span class="bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">Papers</span></h1>
                    <p class="text-zinc-500 mt-2">Access exam papers and research documents</p>
                </div>
            </div>
            
            <!-- Filter Bar -->
            <div class="mt-8 bg-white rounded-2xl border border-zinc-100 p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <!-- Subject Filters -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 text-sm text-zinc-500 mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Subject:
                        </div>
                        <a href="papers.php<?php echo $year ? '?year='.$year : ''; ?>" 
                           class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo !$subject ? 'bg-teal-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'; ?>">
                            All
                        </a>
                        <?php foreach ($subjects as $s): ?>
                            <a href="papers.php?subject=<?php echo urlencode($s); ?><?php echo $year ? '&year='.$year : ''; ?>" 
                               class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo ($subject === $s) ? 'bg-teal-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'; ?>">
                                <?php echo htmlspecialchars($s); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Year Filter -->
                    <?php if (!empty($years)): ?>
                    <div class="flex items-center gap-3 md:ml-auto md:border-l md:border-zinc-200 md:pl-4">
                        <span class="text-sm text-zinc-500">Year:</span>
                        <select onchange="window.location.href='papers.php?<?php echo $subject ? 'subject='.urlencode($subject).'&' : ''; ?>year='+this.value"
                                class="px-3 py-2 bg-zinc-100 border-0 rounded-lg text-sm text-zinc-700 focus:ring-2 focus:ring-teal-500">
                            <option value="">All Years</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Papers Grid -->
    <section class="pb-20">
        <div class="container mx-auto px-6">
            <?php if (empty($papers)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-xl text-zinc-500 mb-2">No papers found</p>
                    <p class="text-zinc-400">Try selecting a different filter</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($papers as $paper): ?>
                        <div class="bg-white rounded-2xl border border-zinc-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300 overflow-hidden group">
                            <!-- Header -->
                            <div class="h-28 bg-gradient-to-br from-teal-500 to-emerald-600 p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                                        <?php echo htmlspecialchars($paper['subject']); ?>
                                    </span>
                                    <span class="text-white/80 text-sm font-mono"><?php echo htmlspecialchars($paper['year']); ?></span>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-zinc-900 mb-4 line-clamp-2 group-hover:text-teal-600 transition">
                                    <?php echo htmlspecialchars($paper['title']); ?>
                                </h3>
                                
                                <div class="flex gap-3">
                                    <a href="view.php?token=<?php echo $paper['token']; ?>" 
                                       class="flex-1 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl text-center transition">
                                        View Paper
                                    </a>
                                    <a href="pricing.php" 
                                       class="py-2.5 px-4 border border-zinc-200 hover:border-zinc-300 text-zinc-700 text-sm font-medium rounded-xl transition flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
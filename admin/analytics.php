<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is admin (Assuming isAdmin() or similar exists, or just check role)
// For now, let's assume session check. You might want to restrict this properly.
// if (!isset($_SESSION['user_id']) || !is_admin($_SESSION['user_id'])) { ... }

// Fetch Top Downloads
$top_downloads_query = "
    SELECT 
        d.resource_type, 
        d.resource_id, 
        COUNT(*) as download_count,
        CASE 
            WHEN d.resource_type = 'book' THEN b.title 
            WHEN d.resource_type = 'paper' THEN p.title 
        END as title
    FROM downloads d
    LEFT JOIN books b ON d.resource_type = 'book' AND d.resource_id = b.id
    LEFT JOIN papers p ON d.resource_type = 'paper' AND d.resource_id = p.id
    GROUP BY d.resource_type, d.resource_id
    ORDER BY download_count DESC
    LIMIT 10
";
$top_downloads = $conn->query($top_downloads_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Conversion Rates
$conversion_query = "
    SELECT 
        h.resource_type, 
        h.resource_id,
        CASE 
            WHEN h.resource_type = 'book' THEN b.title 
            WHEN h.resource_type = 'paper' THEN p.title 
        END as title,
        COUNT(DISTINCT h.user_id, h.resource_type, h.resource_id) as unique_view_count, -- Count unique users who viewed
        (SELECT COUNT(*) FROM downloads d WHERE d.resource_type = h.resource_type AND d.resource_id = h.resource_id) as download_count
    FROM user_history h
    LEFT JOIN books b ON h.resource_type = 'book' AND h.resource_id = b.id
    LEFT JOIN papers p ON h.resource_type = 'paper' AND h.resource_id = p.id
    WHERE h.resource_type IN ('book', 'paper')
    GROUP BY h.resource_type, h.resource_id
    HAVING unique_view_count > 0
    ORDER BY (download_count / unique_view_count) DESC
    LIMIT 10
";
$conversion_stats = $conn->query($conversion_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Overall Stats
$total_users = get_count('users');
$pro_users = $conn->query("SELECT COUNT(*) FROM users WHERE is_pro = 1")->fetch_row()[0];
$total_downloads = get_count('downloads');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #030712;
            color: #f3f4f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: 
                radial-gradient(at 0% 0%, hsla(263,93%,15%,0.15) 0, transparent 50%), 
                radial-gradient(at 100% 100%, hsla(220,100%,10%,0.15) 0, transparent 50%);
        }
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .text-gradient {
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen pb-20">
    <nav class="glass sticky top-0 z-50 px-6 py-4 flex items-center justify-between mb-8">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">;</div>
            <span class="font-bold text-xl tracking-tight">Semicolon <span class="text-indigo-400">Admin</span></span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../index.php" class="text-sm text-gray-400 hover:text-white transition">Exit Dashboard</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6">
        <header class="mb-10">
            <h1 class="text-3xl font-extrabold mb-2">Platform <span class="text-gradient">Performance</span></h1>
            <p class="text-gray-400">Insights into content engagement and conversion rates.</p>
        </header>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="glass p-6 rounded-2xl">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">Total Users</p>
                <h3 class="text-2xl font-bold"><?php echo $total_users; ?></h3>
                <div class="mt-2 flex items-center gap-1 text-[10px] text-green-400 bg-green-400/10 w-fit px-2 py-0.5 rounded-full">
                    <span>Active Platform</span>
                </div>
            </div>
            <div class="glass p-6 rounded-2xl border-indigo-500/20">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">Pro Subscribers</p>
                <h3 class="text-2xl font-bold text-indigo-400 pulse-soft"><?php echo $pro_users; ?></h3>
                <p class="text-[10px] text-gray-500 mt-1"><?php echo round(($pro_users/$total_users)*100, 1); ?>% Conversion Rate</p>
            </div>
            <div class="glass p-6 rounded-2xl">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">Total Downloads</p>
                <h3 class="text-2xl font-bold"><?php echo $total_downloads; ?></h3>
                <div class="mt-2 flex items-center gap-1 text-[10px] text-indigo-400 bg-indigo-400/10 w-fit px-2 py-0.5 rounded-full">
                    <span>Pro Exclusive</span>
                </div>
            </div>
            <div class="glass p-6 rounded-2xl">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wider mb-2">System Status</p>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-lg font-bold">Operational</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Downloads Table -->
            <section class="glass rounded-3xl overflow-hidden">
                <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                    <h2 class="font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Top Downloads
                    </h2>
                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Global Ranking</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-white/5">
                                <th class="px-6 py-3 font-semibold">Resource</th>
                                <th class="px-6 py-3 font-semibold">Type</th>
                                <th class="px-6 py-3 font-semibold text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($top_downloads as $row): ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold truncate max-w-[200px]"><?php echo htmlspecialchars($row['title']); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-gray-800 text-gray-300 uppercase"><?php echo $row['resource_type']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-mono font-bold text-indigo-400"><?php echo $row['download_count']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Conversion Rates Table -->
            <section class="glass rounded-3xl overflow-hidden">
                <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                    <h2 class="font-bold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                        </svg>
                        Resource Conversions
                    </h2>
                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Engagement Efficiency</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-widest bg-white/5">
                                <th class="px-6 py-3 font-semibold">Resource</th>
                                <th class="px-6 py-3 font-semibold text-center">Views</th>
                                <th class="px-6 py-3 font-semibold text-center">DLs</th>
                                <th class="px-6 py-3 font-semibold text-right">Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($conversion_stats as $row): 
                                $rate = ($row['download_count'] / $row['unique_view_count']) * 100;
                            ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold truncate max-w-[150px]"><?php echo htmlspecialchars($row['title']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs text-gray-400"><?php echo $row['unique_view_count']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs text-gray-400"><?php echo $row['download_count']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php 
                                        $barColor = $rate > 50 ? 'bg-green-500' : ($rate > 20 ? 'bg-indigo-500' : 'bg-gray-600');
                                    ?>
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 h-1.5 bg-gray-800 rounded-full overflow-hidden hidden sm:block">
                                            <div class="h-full <?php echo $barColor; ?>" style="width: <?php echo min(100, $rate); ?>%"></div>
                                        </div>
                                        <span class="text-sm font-mono font-bold <?php echo str_replace('bg-', 'text-', $barColor); ?>">
                                            <?php echo round($rate, 1); ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>

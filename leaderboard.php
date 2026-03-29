<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Ensure streak is updated on visit
update_daily_streak($user_id);

// Fetch Weekly Top 10
$weekly_stmt = $conn->prepare("SELECT id, username, avatar_url, xp_weekly as xp, level FROM users WHERE role != 'admin' ORDER BY xp_weekly DESC LIMIT 10");
$weekly_stmt->execute();
$weekly_leaders = $weekly_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Lifetime Top 10
$lifetime_stmt = $conn->prepare("SELECT id, username, avatar_url, xp_total as xp, level FROM users WHERE role != 'admin' ORDER BY xp_total DESC LIMIT 10");
$lifetime_stmt->execute();
$lifetime_leaders = $lifetime_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$current_tab = isset($_GET['tab']) && $_GET['tab'] == 'lifetime' ? 'lifetime' : 'weekly';
$active_leaders = $current_tab == 'weekly' ? $weekly_leaders : $lifetime_leaders;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon Academy - Hall of Fame</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/index.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
        }
        .leaderboard-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .rank-row {
            transition: all 0.2s ease;
        }
        .rank-row:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.01);
        }
        
        /* Top 3 Styling */
        .rank-1 .rank-badge { background: linear-gradient(135deg, #fbbf24, #d97706); }
        .rank-2 .rank-badge { background: linear-gradient(135deg, #94a3b8, #475569); }
        .rank-3 .rank-badge { background: linear-gradient(135deg, #b45309, #78350f); }
        
        .rank-1 { border-left: 3px solid #f59e0b; background: rgba(245, 158, 11, 0.02); }
        .rank-2 { border-left: 3px solid #94a3b8; background: rgba(148, 163, 184, 0.02); }
        .rank-3 { border-left: 3px solid #b45309; background: rgba(180, 83, 9, 0.02); }
        
        /* The glow on the avatar for rank 1 */
        .rank-1 .avatar-ring { border-color: #f59e0b; }
    </style>
</head>
<body class="bg-[var(--bg-primary)] min-h-screen font-sans antialiased text-slate-800">

<?php include 'includes/header.php'; ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-indigo-400 tracking-tight mb-4 drop-shadow-sm">Hall of Fame</h1>
        <p class="text-lg text-slate-500 max-w-2xl mx-auto">The top performers and dedicated engineers ascending the ranks of Semicolon Academy.</p>
    </div>

    <!-- Toggle Tabs -->
    <div class="flex justify-center mb-10">
        <div class="bg-white p-1.5 rounded-2xl flex gap-2 border border-slate-200 shadow-sm">
            <a href="?tab=weekly" class="px-8 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?php echo $current_tab == 'weekly' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100 font-semibold' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50'; ?>">
                Weekly Legends
            </a>
            <a href="?tab=lifetime" class="px-8 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?php echo $current_tab == 'lifetime' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100 font-semibold' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50'; ?>">
                Lifetime Masters
            </a>
        </div>
    </div>

    <!-- Leaderboard Container -->
    <div class="leaderboard-panel rounded-3xl overflow-hidden p-2 sm:p-4">
        
        <!-- Header Row -->
        <div class="hidden sm:grid grid-cols-12 gap-4 px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest border-b border-slate-700/50 mb-2">
            <div class="col-span-2 text-center">Rank</div>
            <div class="col-span-6">Engineer</div>
            <div class="col-span-2 text-center">Level</div>
            <div class="col-span-2 text-right">Earned XP</div>
        </div>
        
        <div class="flex flex-col gap-2">
            <?php if(empty($active_leaders)): ?>
                <div class="py-12 text-center text-slate-500 italic">No heroes have emerged yet.</div>
            <?php else: ?>
                <?php foreach($active_leaders as $index => $leader): 
                    $rank = $index + 1;
                    $rank_class = $rank <= 3 ? "rank-{$rank}" : "";
                    $is_current_user = ($leader['id'] == $user_id);
                ?>
                <div class="rank-row <?php echo $rank_class; ?> <?php echo $is_current_user ? 'ring-1 ring-inset ring-slate-200 bg-slate-50' : ''; ?> rounded-2xl p-4 sm:px-6 grid grid-cols-12 items-center gap-4 relative">
                    
                    <?php if($is_current_user): ?>
                        <div class="absolute -left-1 sm:-left-2 top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-indigo-500 shadow-sm"></div>
                    <?php endif; ?>

                    <!-- Rank -->
                    <div class="col-span-2 flex justify-center">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-sm sm:text-base <?php echo $rank <= 3 ? 'rank-badge text-white font-extrabold shadow-sm' : 'bg-slate-100 text-slate-500 border border-slate-200'; ?>">
                            #<?php echo $rank; ?>
                        </div>
                    </div>
                    
                    <!-- User Info -->
                    <div class="col-span-10 sm:col-span-6 flex items-center gap-4">
                        <div class="avatar-ring w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden bg-white border border-slate-200 flex-shrink-0 flex items-center justify-center text-lg font-bold text-indigo-500 shadow-sm">
                            <?php if (!empty($leader['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($leader['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($leader['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-bold text-slate-800 truncate <?php echo $is_current_user ? 'text-indigo-600' : ''; ?>">
                                <?php echo htmlspecialchars($leader['username']); ?>
                                <?php if($is_current_user): ?><span class="text-xs font-normal text-indigo-500 ml-2">(You)</span><?php endif; ?>
                            </h3>
                            <div class="text-xs text-slate-500 sm:hidden mt-0.5">
                                Lvl <?php echo $leader['level']; ?> • <?php echo number_format($leader['xp']); ?> XP
                            </div>
                        </div>
                    </div>
                    
                    <!-- Level (Desktop) -->
                    <div class="hidden sm:flex col-span-2 justify-center items-center">
                        <span class="px-3 py-1 bg-white text-slate-600 font-semibold border border-slate-200 rounded-lg text-sm drop-shadow-sm">
                            L<?php echo $leader['level']; ?>
                        </span>
                    </div>
                    
                    <!-- Score (Desktop) -->
                    <div class="hidden sm:flex col-span-2 justify-end items-center">
                        <span class="font-mono text-lg font-bold text-slate-700">
                            <?php echo number_format($leader['xp']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>
    
    <p class="text-center text-slate-500 text-sm mt-8 pb-8">
        <?php echo $current_tab == 'weekly' ? 'Weekly XP resets every Monday. Climb the ranks to earn exclusive badges!' : 'Lifetime XP shows the true legends of Semicolon Academy.'; ?>
    </p>

</main>

</body>
</html>

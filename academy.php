<?php
session_start();
require_once 'includes/functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// If user details are missing somehow, redirect.
if (!$user) {
    header('Location: auth/login.php');
    exit();
}

// Ensure streak is updated on visit
update_daily_streak($user_id);
// Re-fetch user to get latest stats after streak update
$user = get_user_by_id($user_id);

$xp_total = $user['xp_total'] ?? 0;
$level = $user['level'] ?? 1;
$streak = $user['daily_streak'] ?? 0;

// Calculate next level requirements
$next_level = $level + 1;
// Level = floor(sqrt(xp_total / 100)) + 1 -> so xp for next_level = ((next_level - 1)^2) * 100
$xp_for_next_level = pow(($next_level - 1), 2) * 100;
$xp_for_current_level = pow(($level - 1), 2) * 100;

// Progress bar math
$xp_into_level = $xp_total - $xp_for_current_level;
$xp_needed_for_level = $xp_for_next_level - $xp_for_current_level;
$progress_percentage = ($xp_needed_for_level > 0) ? min(100, round(($xp_into_level / $xp_needed_for_level) * 100)) : 100;

// Fetch skills and user progress
$skills_progress = get_user_skills_progress($user_id);

// Fetch equipped badges
$equipped_badges = get_user_badges($user_id, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon Academy - RPG Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/index.css" rel="stylesheet">
    <!-- Gamified Custom Styling -->
    <style>
        .rpg-bg {
            background-color: var(--bg-primary);
            background-image: radial-gradient(circle at 15% 50%, rgba(99, 102, 241, 0.03), transparent 25%),
                              radial-gradient(circle at 85% 30%, rgba(20, 184, 166, 0.03), transparent 25%);
        }
        .glass-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .rpg-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .rpg-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-light);
        }
        .level-badge {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            position: relative;
            overflow: hidden;
        }
        .progress-bar-fill::after {
            content: "";
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body class="rpg-bg text-slate-800 min-h-screen font-sans selection:bg-indigo-500/30 selection:text-indigo-900 antialiased">

<?php include 'includes/header.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
        <!-- Player Card -->
        <div class="glass-panel rounded-2xl p-6 flex items-center gap-6 lg:col-span-2">
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-white border-4 border-indigo-100 flex items-center justify-center text-3xl font-bold text-indigo-600 overflow-hidden shadow-sm">
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <!-- Mini Level indicator overlay -->
                <div class="absolute -bottom-2 -right-2 bg-white border border-indigo-200 rounded-full w-10 h-10 flex items-center justify-center shadow-md">
                    <span class="text-indigo-600 font-bold text-sm">L<?php echo $level; ?></span>
                </div>
            </div>
            
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="text-slate-500 text-sm font-medium mb-3">Level <?php echo $level; ?> Challenger</p>
                
                <!-- XP Bar -->
                <div class="flex justify-between text-xs text-slate-500 mb-1 font-medium">
                    <span><?php echo number_format($xp_total); ?> XP</span>
                    <span>Next: <?php echo number_format($xp_for_next_level); ?> XP</span>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                    <div class="h-full progress-bar-fill rounded-full" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Stats / Equipped Badges -->
        <div class="glass-panel rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Active Status</h3>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 border border-orange-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white"><?php echo $streak; ?> Days</div>
                        <div class="text-xs text-orange-400">Current Streak</div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Equipped Badges</h3>
                <div class="flex gap-2">
                    <?php if (empty($equipped_badges)): ?>
                        <div class="text-sm text-slate-500 italic">No badges equipped.</div>
                    <?php else: ?>
                        <?php foreach($equipped_badges as $badge): ?>
                            <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center" title="<?php echo htmlspecialchars($badge['badge_name']); ?>">
                                <?php echo $badge['svg_icon']; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Skill Trees</h2>
            <p class="text-slate-500 mt-1">Conquer challenges to unlock higher tiers and earn certifications.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($skills_progress as $skill): ?>
            <?php 
                $skill_id = $skill['id'];
                $skill_name = htmlspecialchars($skill['name']);
                $skill_desc = htmlspecialchars($skill['description']);
                $current_level = $skill['current_level'] ?? 'easy';
                
                // Determine icon and color based on skill name
                switch(strtolower($skill['name'])) {
                    case 'react':
                        $icon = '<img src="assets/images/skills-logo/programing.png" class="w-10 h-10 object-contain">';
                        $color_class = 'text-sky-400'; $bg_class = 'bg-sky-400';
                        break;
                    case 'java':
                        $icon = '<img src="assets/images/skills-logo/java.png" class="w-10 h-10 object-contain">';
                        $color_class = 'text-orange-500'; $bg_class = 'bg-orange-500';
                        break;
                    case 'javascript':
                        $icon = '<img src="assets/images/skills-logo/js.png" class="w-10 h-10 object-contain">';
                        $color_class = 'text-yellow-400'; $bg_class = 'bg-yellow-400';
                        break;
                    case 'python':
                        $icon = '<img src="assets/images/skills-logo/python.png" class="w-10 h-10 object-contain">';
                        $color_class = 'text-blue-500'; $bg_class = 'bg-blue-500';
                        break;
                    default:
                        $icon = '<img src="assets/images/skills-logo/programing.png" class="w-10 h-10 object-contain">';
                        $color_class = 'text-slate-400'; $bg_class = 'bg-slate-500';
                }
                
                // Determine progression states
                $levels = ['easy', 'medium', 'hard', 'interview'];
                $current_index = array_search($current_level, $levels);
                $percentage = min(100, (($current_index + 1) / 4) * 100);
            ?>
            <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="rpg-card glass-panel rounded-2xl p-6 relative overflow-hidden group block">
                <!-- Hover gradient effect behind icon -->
                <div class="absolute -top-10 -right-10 w-32 h-32 <?php echo $bg_class; ?> rounded-full blur-3xl opacity-10 group-hover:opacity-20 transition-opacity"></div>
                
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center">
                        <?php echo $icon; ?>
                    </div>
                    <?php if ($current_index == 3 && $skill['interview_unlocked'] == 1): ?>
                        <span class="px-2 py-1 text-xs font-bold text-amber-900 bg-amber-100 rounded uppercase tracking-wider shadow-sm border border-amber-200">Mastered</span>
                    <?php else: ?>
                        <span class="px-2 py-1 text-xs font-bold <?php echo $color_class; ?> bg-slate-50 rounded border border-slate-200 uppercase tracking-wider"><?php echo $current_level; ?></span>
                    <?php endif; ?>
                </div>
                
                <h3 class="text-xl font-bold text-slate-800 mb-2 relative z-10"><?php echo $skill_name; ?></h3>
                <p class="text-slate-500 text-sm mb-6 line-clamp-2 relative z-10"><?php echo $skill_desc; ?></p>
                
                <!-- Skill Progress Bar -->
                <div class="mt-auto relative z-10">
                    <div class="flex justify-between text-xs font-medium text-slate-500 mb-1">
                        <span>Progress</span>
                        <span><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full <?php echo $bg_class; ?> rounded-full" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

</main>

<script>
    // Add particle effects or ambient interactions here in the future
</script>
</body>
</html>

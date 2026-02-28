<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$skill_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$skill_id) {
    header('Location: academy.php');
    exit();
}

$user = get_user_by_id($user_id);
$xp_total = $user['xp_total'] ?? 0;

// Fetch skill details
$stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
$stmt->bind_param('i', $skill_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();

if (!$skill) {
    header('Location: academy.php');
    exit();
}

// Fetch all levels for this skill
$l_stmt = $conn->prepare("SELECT * FROM skill_levels WHERE skill_id = ? ORDER BY unlock_order ASC");
$l_stmt->bind_param('i', $skill_id);
$l_stmt->execute();
$levels = $l_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user progress for this skill
$p_stmt = $conn->prepare("SELECT * FROM user_skill_progress WHERE user_id = ? AND skill_id = ?");
$p_stmt->bind_param('ii', $user_id, $skill_id);
$p_stmt->execute();
$progress = $p_stmt->get_result()->fetch_assoc();

$completed_levels = [];
if ($progress && $progress['completed_levels_json']) {
    $completed_levels = json_decode($progress['completed_levels_json'], true) ?? [];
}

// Helper to check if a level is unlocked
function is_level_unlocked($level_idx, $levels, $completed_levels, $xp_total, $interview_unlocked) {
    if ($level_idx == 0) return true; // Easy is always unlocked
    
    $prev_level = $levels[$level_idx - 1];
    
    // For Interview (idx 3)
    if ($level_idx == 3) {
        return $interview_unlocked && in_array($prev_level['level_name'], $completed_levels) && $xp_total >= $levels[$level_idx]['required_xp'];
    }
    
    return in_array($prev_level['level_name'], $completed_levels) && $xp_total >= $levels[$level_idx]['required_xp'];
}

$color_map = [
    'easy' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30',
    'medium' => 'text-amber-400 bg-amber-500/10 border-amber-500/30',
    'hard' => 'text-rose-400 bg-rose-500/10 border-rose-500/30',
    'interview' => 'text-fuchsia-400 bg-fuchsia-500/10 border-fuchsia-500/30'
];

$icon_map = [
    'easy' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'medium' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
    'hard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />',
    'interview' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon Academy - <?php echo htmlspecialchars($skill['name']); ?> Path</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/index.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
        }
        .glass-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .locked-layer {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .tier-card:hover .locked-layer {
            opacity: 1;
        }
        .progress-line {
            width: 2px;
            background: linear-gradient(to bottom, var(--accent) 50%, rgba(0,0,0,0.05) 50%);
            background-size: 100% 200%;
            transition: background-position 1s ease;
        }
        .connect-line {
            height: 48px;
            width: 2px;
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] min-h-screen font-sans antialiased text-slate-800">

<?php include 'includes/header.php'; ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="mb-10 flex items-center justify-between">
        <div>
            <a href="academy.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1 mb-4 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                Back to Academy
            </a>
            <h1 class="text-4xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                <?php echo htmlspecialchars($skill['name']); ?> Path
            </h1>
            <p class="text-slate-500 mt-2 text-lg"><?php echo htmlspecialchars($skill['description']); ?></p>
        </div>
        
        <div class="text-right glass-box p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total XP</div>
            <div class="text-3xl font-extrabold text-indigo-600 font-mono"><?php echo number_format($xp_total); ?></div>
        </div>
    </div>

    <!-- Timeline Layout -->
    <div class="relative py-8 flex flex-col items-center">
        <?php 
        $all_complete = true;
        foreach($levels as $idx => $lvl): 
            $lvl_name = $lvl['level_name'];
            $is_completed = in_array($lvl_name, $completed_levels);
            if (!$is_completed) $all_complete = false;
            
            $is_unlocked = is_level_unlocked($idx, $levels, $completed_levels, $xp_total, $progress['interview_unlocked'] ?? 0);
            
            $style = $color_map[$lvl_name];
            $icon = $icon_map[$lvl_name];
        ?>
            
            <!-- Tier Card -->
            <div class="w-full relative tier-card group transition-transform hover:scale-[1.01] duration-300 z-10 <?php echo !$is_unlocked ? 'opacity-75' : ''; ?>">
                <div class="glass-box rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6 border <?php echo $is_completed ? 'border-emerald-300 shadow-sm bg-emerald-50' : 'border-slate-200 hover:border-slate-300'; ?> relative overflow-hidden">
                    
                    <?php if (!$is_unlocked): ?>
                        <div class="locked-layer rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight mb-1">Tier Locked</h3>
                            <p class="text-slate-600 text-sm flex items-center gap-2">
                                <?php if ($idx > 0 && !in_array($levels[$idx-1]['level_name'], $completed_levels)): ?>
                                    <span class="text-rose-500">✗</span> Complete previous tier
                                <?php endif; ?>
                            </p>
                            <p class="text-slate-600 text-sm flex items-center gap-2 mt-1">
                                <?php if ($xp_total < $lvl['required_xp']): ?>
                                    <span class="text-rose-500">✗</span> Need <?php echo number_format($lvl['required_xp']); ?> XP
                                <?php endif; ?>
                            </p>
                            <?php if ($idx == 3 && !($progress['interview_unlocked'] ?? 0)): ?>
                                <p class="text-slate-600 text-sm flex items-center gap-2 mt-1 text-center max-w-xs">
                                    <span class="text-amber-600">!</span> Reserved for top performers. Continue learning to unlock.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="w-20 h-20 rounded-2xl flex-shrink-0 flex items-center justify-center border <?php echo $style; ?> shadow-inner relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <?php echo $icon; ?>
                        </svg>
                        <?php if ($is_completed): ?>
                            <div class="absolute -top-2 -right-2 bg-emerald-500 text-white rounded-full p-1 shadow-lg border-2 border-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1 text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
                            <h2 class="text-2xl font-bold text-slate-900 capitalize tracking-tight"><?php echo $lvl_name; ?> Tier</h2>
                            <div class="text-slate-500 text-sm font-medium flex items-center justify-center gap-2 mt-2 sm:mt-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Requires <?php echo number_format($lvl['required_xp']); ?> XP
                            </div>
                        </div>
                        <p class="text-slate-500 text-sm mb-4">
                            <?php 
                                if($lvl_name == 'easy') echo "Fundamental concepts and basic usage.";
                                if($lvl_name == 'medium') echo "Intermediate challenges focusing on logic and application.";
                                if($lvl_name == 'hard') echo "Advanced scenarios, edge cases, and architectural decisions.";
                                if($lvl_name == 'interview') echo "Real-world engineering interview questions. No code execution.";
                            ?>
                        </p>
                        
                        <div class="flex items-center justify-center sm:justify-start gap-4">
                            <?php if ($is_completed): ?>
                                <button disabled class="px-5 py-2.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium cursor-not-allowed">Completed</button>
                            <?php elseif ($is_unlocked): ?>
                                <a href="quiz.php?skill=<?php echo $skill_id; ?>&level=<?php echo $lvl['id']; ?>" class="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md transition-all font-medium border border-indigo-500 hover:scale-105 active:scale-95">Start Challenge</a>
                            <?php else: ?>
                                <button disabled class="px-5 py-2.5 rounded-lg bg-slate-100 text-slate-400 cursor-not-allowed font-medium border border-slate-200">Locked</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($idx < count($levels) - 1): ?>
                <!-- Connection Line -->
                <div class="connect-line <?php echo in_array($lvl_name, $completed_levels) ? 'bg-indigo-500 shadow-sm' : 'bg-slate-200'; ?> my-2 z-0 relative">
                    <?php if (in_array($lvl_name, $completed_levels) && !in_array($levels[$idx+1]['level_name'], $completed_levels) && $is_unlocked): ?>
                        <div class="absolute inset-0 bg-indigo-400 blur-[1px] animate-pulse"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php endforeach; ?>
    </div>

    <!-- Certification Block -->
    <div class="mt-8 relative overflow-hidden rounded-2xl <?php echo $all_complete ? 'bg-gradient-to-r from-amber-500 to-orange-600 border border-amber-300' : 'glass-box border-slate-200'; ?> p-8 text-center group">
        <?php if ($all_complete): ?>
            <!-- Confetti/Shine background effect when complete -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-50"></div>
            <div class="relative z-10">
                <div class="w-20 h-20 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-4 shadow-md animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <h2 class="text-3xl font-extrabold text-white mb-2 tracking-tight drop-shadow-sm">Skill Mastered!</h2>
                <p class="text-amber-50 mb-6 font-medium text-lg">You have conquered all tiers of <?php echo htmlspecialchars($skill['name']); ?>.</p>
                <a href="certificate.php?skill=<?php echo $skill_id; ?>" class="inline-block px-8 py-3 rounded-xl bg-white text-amber-600 font-bold hover:bg-amber-50 shadow-sm border border-amber-100 transition-all hover:-translate-y-1">View Certificate</a>
            </div>
        <?php else: ?>
            <div class="opacity-80 group-hover:opacity-100 transition-opacity">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center mb-4 border border-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-700 tracking-tight">Certification Locked</h2>
                <p class="text-slate-500 mt-2 max-w-lg mx-auto">Complete all 4 tiers of <?php echo htmlspecialchars($skill['name']); ?> to unlock your verified certificate of mastery.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>

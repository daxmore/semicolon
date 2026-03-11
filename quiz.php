<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$skill_id = isset($_GET['skill']) ? intval($_GET['skill']) : 0;
$level_id = isset($_GET['level']) ? intval($_GET['level']) : 0;

if (!$skill_id || !$level_id) {
    header('Location: academy.php');
    exit();
}

$user = get_user_by_id($user_id);
update_daily_streak($user_id);

// Fetch skill
$stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
$stmt->bind_param('i', $skill_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();

// Fetch level
$l_stmt = $conn->prepare("SELECT * FROM skill_levels WHERE id = ?");
$l_stmt->bind_param('i', $level_id);
$l_stmt->execute();
$level = $l_stmt->get_result()->fetch_assoc();

// Enforce daily XP limit check before allowing the exam
$stmt_xp = $conn->prepare("SELECT daily_xp_earned, last_activity_date FROM users WHERE id = ?");
$stmt_xp->bind_param('i', $user_id);
$stmt_xp->execute();
$user_xp_data = $stmt_xp->get_result()->fetch_assoc();
$today = date('Y-m-d');
$current_daily_xp = 0;
if ($user_xp_data['last_activity_date'] === $today) {
    $current_daily_xp = (int)$user_xp_data['daily_xp_earned'];
}
$is_xp_capped = $current_daily_xp >= 100;

// Hardcoded multiple questions for demonstration
$dummy_questions = [
    [
        'id' => 1,
        'text' => "Which of the following describes the purpose of " . htmlspecialchars($skill['name']) . " in modern architecture?",
        'options' => [
            ['id' => 1, 'text' => "It manages database migrations exclusively."],
            ['id' => 2, 'text' => "It is primarily used for styling and CSS pre-processing."],
            ['id' => 3, 'text' => "It provides structural logic or UI rendering depending on its ecosystem."],
            ['id' => 4, 'text' => "It serves as a hardware abstraction layer."]
        ],
        'correct' => 3
    ],
    [
        'id' => 2,
        'text' => "What is a key benefit of mastering " . htmlspecialchars($skill['name']) . "?",
        'options' => [
            ['id' => 1, 'text' => "Increased reliance on legacy systems."],
            ['id' => 2, 'text' => "Better performance, scalability, and maintainability."],
            ['id' => 3, 'text' => "It entirely eliminates the need for testing."],
            ['id' => 4, 'text' => "It restricts you to a single deployment platform."]
        ],
        'correct' => 2
    ]
];

// Initialize quiz session state
if (!isset($_SESSION['quiz_state']) || $_SESSION['quiz_state']['skill_id'] !== $skill_id || $_SESSION['quiz_state']['level_id'] !== $level_id) {
    $_SESSION['quiz_state'] = [
        'skill_id' => $skill_id,
        'level_id' => $level_id,
        'current_q_index' => 0,
        'correct_answers' => 0,
        'total_questions' => count($dummy_questions),
        'completed' => false
    ];
}

$state = &$_SESSION['quiz_state'];
$message = '';
$reward_xp = 50; // XP per passing

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$state['completed'] && !$is_xp_capped) {
    $answer = isset($_POST['answer']) ? intval($_POST['answer']) : 0;
    
    $current_q = $dummy_questions[$state['current_q_index']];
    if ($answer == $current_q['correct']) {
        $state['correct_answers']++;
    }
    
    $state['current_q_index']++;
    
    // Check if quiz is finished
    if ($state['current_q_index'] >= $state['total_questions']) {
        $state['completed'] = true;
        $passed = ($state['correct_answers'] === $state['total_questions']);
        
        if ($passed) {
            // Award XP
            add_user_xp($user_id, $reward_xp);
            
            // Advance tier logic
            $p_stmt = $conn->prepare("SELECT * FROM user_skill_progress WHERE user_id = ? AND skill_id = ?");
            $p_stmt->bind_param('ii', $user_id, $skill_id);
            $p_stmt->execute();
            $progress = $p_stmt->get_result()->fetch_assoc();
            
            $completed_levels = [];
            if ($progress) {
                $completed_levels = json_decode($progress['completed_levels_json'], true) ?? [];
            }
            
            if (!in_array($level['level_name'], $completed_levels)) {
                $completed_levels[] = $level['level_name'];
                $new_json = json_encode($completed_levels);
                
                $interview_unlocked = ($level['level_name'] == 'hard') ? 1 : ($progress['interview_unlocked'] ?? 0);
                
                $next_level = 'easy';
                if ($level['level_name'] == 'easy') $next_level = 'medium';
                if ($level['level_name'] == 'medium') $next_level = 'hard';
                if ($level['level_name'] == 'hard') $next_level = 'interview';
                
                if ($progress) {
                    $upd = $conn->prepare("UPDATE user_skill_progress SET current_level = ?, completed_levels_json = ?, interview_unlocked = ? WHERE user_id = ? AND skill_id = ?");
                    $upd->bind_param('ssiii', $next_level, $new_json, $interview_unlocked, $user_id, $skill_id);
                    $upd->execute();
                } else {
                    $ins = $conn->prepare("INSERT INTO user_skill_progress (user_id, skill_id, current_level, completed_levels_json, interview_unlocked) VALUES (?, ?, ?, ?, ?)");
                    $ins->bind_param('iissi', $user_id, $skill_id, $next_level, $new_json, $interview_unlocked);
                    $ins->execute();
                }
            }
            
            $att = $conn->prepare("INSERT INTO user_attempts (user_id, skill_id, level_id, score, status) VALUES (?, ?, ?, 100, 'passed')");
            $att->bind_param('iii', $user_id, $skill_id, $level_id);
            $att->execute();
        } else {
            $att = $conn->prepare("INSERT INTO user_attempts (user_id, skill_id, level_id, score, status) VALUES (?, ?, ?, 0, 'failed')");
            $att->bind_param('iii', $user_id, $skill_id, $level_id);
            $att->execute();
        }
    }
}

if (isset($_GET['retry'])) {
    unset($_SESSION['quiz_state']);
    header("Location: quiz.php?skill=$skill_id&level=$level_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon Academy - Challenge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/index.css" rel="stylesheet">
    <style>
        body { background-color: var(--bg-primary); }
        .glass-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }
        .option-label {
            transition: all 0.2s;
        }
        .option-radio:checked + .option-label {
            background-color: rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3) inset;
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-slate-800 min-h-screen font-sans selection:bg-indigo-500/30 selection:text-indigo-900 antialiased flex flex-col">

<?php include 'includes/header.php'; ?>

<main class="flex-grow flex items-center justify-center p-4 py-12">
    <div class="w-full max-w-2xl">
        
        <div class="mb-6">
            <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium flex items-center gap-1 transition-colors w-max">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                Flee Challenge
            </a>
        </div>

        <div class="glass-box rounded-2xl p-8 sm:p-12 relative overflow-hidden text-center">
            
            <!-- Top Right decorative -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-200 rounded-full blur-[80px] opacity-40"></div>

            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 border border-slate-200 rounded-full mb-6">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider"><?php echo htmlspecialchars($skill['name']); ?></span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider"><?php echo htmlspecialchars($level['level_name']); ?> Tier</span>
            </div>

            <?php if ($is_xp_capped): ?>
                <div class="py-8 animate-in zoom-in duration-500">
                    <div class="w-24 h-24 mx-auto bg-amber-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-amber-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 mb-2 tracking-tight">Daily Limit Reached</h2>
                    <p class="text-slate-600 font-medium text-lg mb-8 px-4">
                        You have already earned the maximum 100 XP for today. Come back tomorrow to continue training!
                    </p>
                    <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
                        Return to Academy
                    </a>
                </div>

            <?php elseif ($state['completed']): 
                $passed = ($state['correct_answers'] === $state['total_questions']);
            ?>
                
                <div class="py-8 animate-in zoom-in duration-500">
                    <?php if ($passed): ?>
                        <div class="w-24 h-24 mx-auto bg-emerald-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 mb-2 tracking-tight">Challenge Cleared!</h2>
                        <p class="text-emerald-600 font-bold text-xl mb-8 flex items-center justify-center gap-2">
                            +<?php echo $reward_xp; ?> XP Earned
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </p>
                        <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
                            Continue Journey
                        </a>
                    <?php else: ?>
                        <div class="w-24 h-24 mx-auto bg-rose-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-rose-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 mb-2 tracking-tight">Challenge Failed</h2>
                        <p class="text-slate-600 font-medium text-lg mb-8">
                            You got <?php echo $state['correct_answers']; ?> out of <?php echo $state['total_questions']; ?> correct. You must answer all questions correctly to pass this level.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="quiz.php?skill=<?php echo $skill_id; ?>&level=<?php echo $level_id; ?>&retry=1" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
                                Try Again
                            </a>
                            <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-zinc-700 bg-zinc-100 rounded-xl hover:bg-zinc-200 transition-colors shadow-md">
                                Back to Skill
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: 
                $current_q = $dummy_questions[$state['current_q_index']];
            ?>

                <div class="text-left">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm font-bold text-slate-500">Question <?php echo $state['current_q_index'] + 1; ?> of <?php echo $state['total_questions']; ?></span>
                        <div class="w-32 h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: <?php echo (($state['current_q_index']) / $state['total_questions']) * 100; ?>%"></div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-slate-800 mb-8 leading-snug"><?php echo $current_q['text']; ?></h2>

                    <form method="POST" action="">
                        <div class="space-y-4 mb-8">
                            <?php foreach ($current_q['options'] as $opt): ?>
                                <label class="block relative cursor-pointer group">
                                    <input type="radio" name="answer" value="<?php echo $opt['id']; ?>" class="peer sr-only option-radio" required>
                                    <div class="option-label p-5 rounded-xl border border-slate-200 bg-white dark:bg-zinc-900 hover:bg-slate-50 flex items-start gap-4">
                                        <div class="w-5 h-5 mt-0.5 rounded-full border-2 border-slate-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 flex-shrink-0 flex items-center justify-center">
                                            <div class="w-2.5 h-2.5 rounded-full bg-white dark:bg-zinc-900 opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </div>
                                        <span class="text-slate-700 font-medium leading-relaxed"><?php echo $opt['text']; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="w-full py-4 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-md active:scale-[0.98]">
                            Submit Answer
                        </button>
                    </form>
                </div>

            <?php endif; ?>

        </div>
    </div>
</main>

</body>
</html>

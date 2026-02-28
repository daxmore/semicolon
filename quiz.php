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

if (!$skill || !$level) {
    header('Location: academy.php');
    exit();
}

// In a real system, we would fetch questions from the `questions` and `options` tables.
// For demonstration of the RPG UI and mechanic, we will hardcode a single question and allow passing it.
$dummy_question = [
    'text' => "Which of the following describes the purpose of " . htmlspecialchars($skill['name']) . " in modern architecture?",
    'options' => [
        ['id' => 1, 'text' => "It manages database migrations exclusively."],
        ['id' => 2, 'text' => "It is primarily used for styling and CSS pre-processing."],
        ['id' => 3, 'text' => "It provides structural logic or UI rendering depending on its ecosystem."],
        ['id' => 4, 'text' => "It serves as a hardware abstraction layer."]
    ],
    'correct' => 3
];

// Handle submission
$message = '';
$passed = false;
$reward_xp = 50; // XP per passing

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = isset($_POST['answer']) ? intval($_POST['answer']) : 0;
    if ($answer == $dummy_question['correct']) {
        $passed = true;
        // Award XP
        add_user_xp($user_id, $reward_xp);
        
        // Update user progress JSON
        // Find current progress
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
            
            // Advance logic: if interview, set unlocked.
            $interview_unlocked = ($level['level_name'] == 'hard') ? 1 : ($progress['interview_unlocked'] ?? 0);
            
            // Set next level string
            $next_level = 'easy';
            if ($level['level_name'] == 'easy') $next_level = 'medium';
            if ($level['level_name'] == 'medium') $next_level = 'hard';
            if ($level['level_name'] == 'hard') $next_level = 'interview';
            
            if ($progress) {
                // Update
                $upd = $conn->prepare("UPDATE user_skill_progress SET current_level = ?, completed_levels_json = ?, interview_unlocked = ? WHERE user_id = ? AND skill_id = ?");
                $upd->bind_param('ssiii', $next_level, $new_json, $interview_unlocked, $user_id, $skill_id);
                $upd->execute();
            } else {
                // Insert
                $ins = $conn->prepare("INSERT INTO user_skill_progress (user_id, skill_id, current_level, completed_levels_json, interview_unlocked) VALUES (?, ?, ?, ?, ?)");
                $ins->bind_param('iissi', $user_id, $skill_id, $next_level, $new_json, $interview_unlocked);
                $ins->execute();
            }
        }
        
        // Log attempt
        $att = $conn->prepare("INSERT INTO user_attempts (user_id, skill_id, level_id, score, status) VALUES (?, ?, ?, 100, 'passed')");
        $att->bind_param('iii', $user_id, $skill_id, $level_id);
        $att->execute();
        
    } else {
        $message = "Incorrect answer. Try again!";
        // Log failed attempt
        $att = $conn->prepare("INSERT INTO user_attempts (user_id, skill_id, level_id, score, status) VALUES (?, ?, ?, 0, 'failed')");
        $att->bind_param('iii', $user_id, $skill_id, $level_id);
        $att->execute();
    }
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

            <?php if ($passed): ?>
                
                <div class="py-8 animate-in zoom-in duration-500">
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
                </div>

            <?php else: ?>

                <div class="text-left">
                    <h2 class="text-2xl font-bold text-slate-800 mb-8 leading-snug"><?php echo $dummy_question['text']; ?></h2>
                    
                    <?php if ($message): ?>
                        <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-sm font-medium flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="space-y-4 mb-8">
                            <?php foreach ($dummy_question['options'] as $opt): ?>
                                <label class="block relative cursor-pointer group">
                                    <input type="radio" name="answer" value="<?php echo $opt['id']; ?>" class="peer sr-only option-radio" required>
                                    <div class="option-label p-5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 flex items-start gap-4">
                                        <div class="w-5 h-5 mt-0.5 rounded-full border-2 border-slate-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 flex-shrink-0 flex items-center justify-center">
                                            <div class="w-2.5 h-2.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
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

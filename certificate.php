<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$skill_id = isset($_GET['skill']) ? intval($_GET['skill']) : 0;

if (!$skill_id) {
    header('Location: academy.php');
    exit();
}

$user = get_user_by_id($user_id);

// Fetch skill details
$stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
$stmt->bind_param('i', $skill_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();

if (!$skill) {
    header('Location: academy.php');
    exit();
}

// Fetch user progress for this skill to ensure they actually mastered it
$p_stmt = $conn->prepare("SELECT * FROM user_skill_progress WHERE user_id = ? AND skill_id = ?");
$p_stmt->bind_param('ii', $user_id, $skill_id);
$p_stmt->execute();
$progress = $p_stmt->get_result()->fetch_assoc();

$completed_levels = [];
if ($progress && $progress['completed_levels_json']) {
    $completed_levels = json_decode($progress['completed_levels_json'], true) ?? [];
}

// Check if all 4 tiers are completed (easy, medium, hard, interview)
$required_tiers = ['easy', 'medium', 'hard', 'interview'];
$has_mastered = empty(array_diff($required_tiers, $completed_levels));

if (!$has_mastered) {
    // Redirect back to skill detail if they haven't actually completed it
    header('Location: skill_detail.php?id=' . $skill_id);
    exit();
}

// Generative Date
$issue_date = date('F j, Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Mastery - <?php echo htmlspecialchars($skill['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="assets/css/index.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
        }
        .cert-container {
            background: #ffffff;
            /* Certificate texture pattern */
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0,0,0,0.02) 10px, rgba(0,0,0,0.02) 20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }
        .cert-border {
            position: absolute;
            inset: 20px;
            border: 2px solid #ebedef;
            outline: 1px solid #c79a29;
            outline-offset: -8px;
            pointer-events: none;
        }
        .cert-ribbon {
            background: linear-gradient(135deg, #fbbf24, #b45309);
        }
        .cert-stamp {
            background: radial-gradient(circle, #fcd34d 0%, #d97706 80%);
            border: 2px dashed #78350f;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) inset, 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-slate-800 min-h-screen font-sans antialiased flex flex-col justify-center py-12 px-4">

    <div class="max-w-4xl mx-auto w-full mb-6">
        <a href="skill_detail.php?id=<?php echo $skill_id; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center gap-1 transition-colors w-max">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
            Back to Skill Path
        </a>
    </div>

    <!-- The Certificate -->
    <div class="max-w-4xl mx-auto w-full cert-container rounded-sm p-12 sm:p-20 text-center text-slate-800 relative shadow-xl border border-slate-200">
        <div class="cert-border"></div>
        
        <!-- Header -->
        <h1 class="text-3xl sm:text-5xl font-serif text-slate-400 text-center uppercase tracking-[0.2em] mb-4">Semicolon Academy</h1>
        <div class="w-24 h-1 cert-ribbon mx-auto mb-10"></div>
        
        <h2 class="text-4xl sm:text-6xl font-bold font-serif text-amber-600 mb-8 italic">Certificate of Mastery</h2>
        
        <!-- Recipient -->
        <p class="text-lg text-slate-500 uppercase tracking-widest mb-2 font-medium">This is proudly presented to</p>
        <div class="text-4xl sm:text-5xl font-black text-slate-800 mb-8 pb-4 border-b border-slate-200 inline-block px-12">
            <?php echo htmlspecialchars($user['username']); ?>
        </div>
        
        <!-- Achievement -->
        <p class="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed mb-12">
            For successfully completing all rigorous tiers of training, demonstrating exceptional technical proficiency, and achieving mastery in <strong class="text-slate-800 text-xl block mt-2"><?php echo htmlspecialchars($skill['name']); ?></strong>
        </p>
        
        <!-- Footer / Signatures -->
        <div class="flex flex-col sm:flex-row justify-between items-end mt-16 px-8 relative z-10">
            <div class="text-center w-48 mb-6 sm:mb-0">
                <div class="border-b border-slate-400 pb-2 mb-2 font-serif text-2xl text-slate-700 italic">Semicolon Team</div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Authorized Signature</div>
            </div>
            
            <!-- Gold Seal -->
            <div class="cert-stamp w-32 h-32 rounded-full absolute left-1/2 -translate-x-1/2 bottom-0 hidden sm:flex items-center justify-center flex-col transform rotate-12">
                <span class="text-[10px] uppercase font-bold text-amber-900 tracking-widest">Official</span>
                <span class="text-2xl font-black text-amber-900">VERIFIED</span>
                <span class="text-[10px] uppercase font-bold text-amber-900 tracking-widest">Mastery</span>
            </div>
            
            <div class="text-center w-48">
                <div class="border-b border-slate-400 pb-2 mb-2 font-mono text-lg text-slate-700"><?php echo $issue_date; ?></div>
                <div class="text-xs text-slate-500 uppercase tracking-wider font-bold">Date of Issuance</div>
            </div>
        </div>
        
    </div>
    
    <div class="max-w-4xl mx-auto mt-8 text-center text-slate-500 text-sm">
        <button onclick="window.print()" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-md">
            Print Certificate
        </button>
    </div>

</body>
</html>

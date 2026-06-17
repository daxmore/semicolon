<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle Create Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_question') {
    $skill_id = intval($_POST['skill_id']);
    $level_id = intval($_POST['level_id']);
    $question_text = trim($_POST['question_text']);
    
    // Calculate custom XP if provided, otherwise a low default (5-10)
    $xp_reward = isset($_POST['xp_reward']) ? intval($_POST['xp_reward']) : rand(5, 10);

    if (empty($skill_id) || empty($level_id) || empty($question_text)) {
        $message = "All fields are required.";
        $message_type = "error";
    } else {
        $question_type = 'mcq';
        $difficulty = 'medium'; // Default or based on level
        
        $stmt = $conn->prepare("INSERT INTO questions (skill_id, level_id, question_text, xp_reward, question_type, difficulty) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt && $stmt->bind_param("iisiss", $skill_id, $level_id, $question_text, $xp_reward, $question_type, $difficulty) && $stmt->execute()) {
            $question_id = $conn->insert_id;
            
            // Insert options
            $options = $_POST['options'] ?? [];
            $correct_option_index = isset($_POST['correct_option']) ? intval($_POST['correct_option']) : -1;
            
            if (is_array($options) && count($options) > 0) {
                $opt_stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                foreach ($options as $index => $opt_text) {
                    $opt_text = trim($opt_text);
                    if (!empty($opt_text)) {
                        $is_correct = ($index == $correct_option_index) ? 1 : 0;
                        $opt_stmt->bind_param("isi", $question_id, $opt_text, $is_correct);
                        $opt_stmt->execute();
                    }
                }
            }
            
            $message = "Question added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding question.";
            $message_type = "error";
        }
    }
}

// Handle Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_question') {
    $question_id = intval($_POST['question_id']);
    
    // First delete options
    $conn->query("DELETE FROM options WHERE question_id = $question_id");
    
    // Then delete question
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    if ($stmt && $stmt->bind_param("i", $question_id) && $stmt->execute()) {
        $message = "Question deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting question.";
        $message_type = "error";
    }
}

// Fetch lists for dropdowns
$skills = $conn->query("SELECT * FROM skills")->fetch_all(MYSQLI_ASSOC);
$levels = $conn->query("SELECT * FROM skill_levels")->fetch_all(MYSQLI_ASSOC);

// Fetch existing questions with skill and level names
$sql = "SELECT q.*, s.name as skill_name, sl.level_name 
        FROM questions q
        JOIN skills s ON q.skill_id = s.id
        JOIN skill_levels sl ON q.level_id = sl.id
        ORDER BY q.id DESC";
$questions_list = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="max-w-4xl mx-auto">
            
            <header class="mb-10 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manage Quizzes</h1>
                    <p class="text-gray-500 mt-2">Add new questions and configure custom XP rewards to keep users engaged.</p>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-xl font-semibold mb-6">Add New Question</h2>
                <form action="manage_quiz.php" method="POST">
                    <input type="hidden" name="action" value="create_question">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Skill Category</label>
                            <select name="skill_id" id="skill_select" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none appearance-none bg-white">
                                <option value="">Select Skill...</option>
                                <?php foreach($skills as $skill): ?>
                                    <option value="<?php echo $skill['id']; ?>"><?php echo htmlspecialchars($skill['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Skill Level</label>
                            <select name="level_id" id="level_select" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none appearance-none bg-white">
                                <option value="">Select Level...</option>
                                <?php foreach($levels as $lvl): ?>
                                    <option value="<?php echo $lvl['id']; ?>" data-skill="<?php echo $lvl['skill_id']; ?>" class="level-option"><?php echo htmlspecialchars($lvl['level_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <script>
                        const skillSelect = document.getElementById('skill_select');
                        const levelSelect = document.getElementById('level_select');
                        const levelOptions = levelSelect.querySelectorAll('.level-option');

                        skillSelect.addEventListener('change', function() {
                            const selectedSkill = this.value;
                            let firstMatch = false;
                            
                            levelOptions.forEach(opt => {
                                if (selectedSkill === "" || opt.dataset.skill === selectedSkill) {
                                    opt.style.display = 'block';
                                    if (!firstMatch && selectedSkill !== "") {
                                        levelSelect.value = opt.value;
                                        firstMatch = true;
                                    }
                                } else {
                                    opt.style.display = 'none';
                                }
                            });
                            
                            if (!firstMatch) levelSelect.value = "";
                        });
                    </script>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">XP Reward (Low points recommended, 5-10)</label>
                        <input type="number" name="xp_reward" value="10" min="1" max="100" class="w-48 border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                        <textarea name="question_text" rows="3" required class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400" placeholder="e.g. What does CPU stand for?"></textarea>
                    </div>

                    <div class="space-y-4 mb-8">
                        <h3 class="font-medium text-gray-900 border-b pb-2">Options (Select the correct one)</h3>
                        
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="flex items-center gap-4">
                            <input type="radio" name="correct_option" value="<?php echo $i; ?>" <?php echo $i == 0 ? 'checked' : ''; ?> class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <input type="text" name="options[]" required class="flex-1 border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-indigo-500 focus:border-indigo-500" placeholder="Option <?php echo $i + 1; ?>">
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="flex justify-end border-t pt-6">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition-colors shadow-sm">
                            Save Question
                        </button>
                    </div>
                </form>
            </div>
            <div class="mt-12">
                <h2 class="text-xl font-semibold mb-6">Existing Questions (<?php echo count($questions_list); ?>)</h2>
                
                <?php if (empty($questions_list)): ?>
                    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <p class="text-gray-500">No questions added yet. Use the form above to get started!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($questions_list as $q): ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase rounded border border-indigo-100">
                                                <?php echo htmlspecialchars($q['skill_name']); ?>
                                            </span>
                                            <span class="px-2 py-0.5 bg-gray-50 text-gray-600 text-[10px] font-bold uppercase rounded border border-gray-100">
                                                <?php echo htmlspecialchars($q['level_name']); ?>
                                            </span>
                                            <span class="text-xs text-gray-400 font-medium">
                                                XP: <?php echo $q['xp_reward']; ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-900 font-medium mb-4"><?php echo htmlspecialchars($q['question_text']); ?></p>
                                        
                                        <?php 
                                        // Fetch options for this question
                                        $opt_res = $conn->query("SELECT * FROM options WHERE question_id = " . $q['id']);
                                        $options = $opt_res->fetch_all(MYSQLI_ASSOC);
                                        ?>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <?php foreach ($options as $opt): ?>
                                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm <?php echo $opt['is_correct'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-50 text-gray-600 border border-gray-100'; ?>">
                                                    <?php if ($opt['is_correct']): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                    <?php else: ?>
                                                        <div class="w-4 h-4 rounded-full border border-gray-300"></div>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($opt['option_text']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <form action="manage_quiz.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');" class="mb-0">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>


<?php include 'footer.php'; ?>

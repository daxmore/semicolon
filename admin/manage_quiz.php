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
        $stmt = $conn->prepare("INSERT INTO questions (skill_id, level_id, question_text, xp_reward) VALUES (?, ?, ?, ?)");
        if ($stmt && $stmt->bind_param("iisi", $skill_id, $level_id, $question_text, $xp_reward) && $stmt->execute()) {
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

// Fetch lists for dropdowns
$skills = $conn->query("SELECT * FROM skills")->fetch_all(MYSQLI_ASSOC);
$levels = $conn->query("SELECT * FROM skill_levels")->fetch_all(MYSQLI_ASSOC);

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
                            <select name="skill_id" required class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Skill...</option>
                                <?php foreach($skills as $sk): ?>
                                    <option value="<?php echo $sk['id']; ?>"><?php echo htmlspecialchars($sk['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Skill Level</label>
                            <select name="level_id" required class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Level...</option>
                                <?php foreach($levels as $lvl): ?>
                                    <option value="<?php echo $lvl['id']; ?>"><?php echo htmlspecialchars($lvl['level_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

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
            
</div>

<?php include 'footer.php'; ?>

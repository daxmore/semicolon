<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create' || $action === 'edit') {
        $badge_name = trim($_POST['badge_name'] ?? '');
        $badge_type = $_POST['badge_type'] ?? 'mastery';
        $required_xp = (int)($_POST['required_xp'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $svg_icon = trim($_POST['svg_icon'] ?? '');

        if (!empty($badge_name)) {
            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO badges (badge_name, badge_type, required_xp, description, svg_icon) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiss", $badge_name, $badge_type, $required_xp, $description, $svg_icon);
                $stmt->execute();
            } else {
                $badge_id = (int)$_POST['badge_id'];
                $stmt = $conn->prepare("UPDATE badges SET badge_name = ?, badge_type = ?, required_xp = ?, description = ?, svg_icon = ? WHERE id = ?");
                $stmt->bind_param("ssissi", $badge_name, $badge_type, $required_xp, $description, $svg_icon, $badge_id);
                $stmt->execute();
            }
        }
    } elseif ($action === 'delete') {
        $badge_id = (int)$_POST['badge_id'];
        $stmt = $conn->prepare("DELETE FROM badges WHERE id = ?");
        $stmt->bind_param("i", $badge_id);
        $stmt->execute();
    }
    
    header('Location: badges.php');
    exit();
}

// Fetch Badges
$sql = "SELECT * FROM badges ORDER BY required_xp ASC, id ASC";
$result = $conn->query($sql);
$badges = $result->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<!-- Header with count -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl font-bold text-zinc-900 dark:text-white">Gamification Badges</h2>
        <p class="text-zinc-500 dark:text-zinc-400">Manage unlocked automated badges and XP milestones</p>
    </div>
    <div class="flex items-center gap-4">
        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            Total Badges: <span class="font-bold text-zinc-900 dark:text-white"><?php echo count($badges); ?></span>
        </div>
        <button onclick="showCreateModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Badge
        </button>
    </div>
</div>

<!-- Badges Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($badges)): ?>
        <div class="col-span-full py-12 text-center text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
            No badges found. Create one to get started!
        </div>
    <?php else: ?>
        <?php foreach ($badges as $badge): ?>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 flex-shrink-0">
                            <?php 
                            if (!empty($badge['svg_icon'])) {
                                echo $badge['svg_icon'];
                            } else {
                                echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                            }
                            ?>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-700">
                                <?php echo ucfirst($badge['badge_type']); ?>
                            </span>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                <?php echo number_format($badge['required_xp']); ?> XP Req.
                            </span>
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-lg text-zinc-900 dark:text-white leading-tight mb-2">
                        <?php echo htmlspecialchars($badge['badge_name']); ?>
                    </h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-3 mb-6">
                        <?php echo htmlspecialchars($badge['description']); ?>
                    </p>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($badge)); ?>)" class="px-3 py-1.5 text-sm font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition">Edit</button>
                    <button onclick="showDeleteModal(<?php echo $badge['id']; ?>, '<?php echo htmlspecialchars(addslashes($badge['badge_name'])); ?>')" class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Create / Edit Badge Modal -->
<div id="badgeFormModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeFormModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl">
            <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-800/50">
                <h3 id="formModalTitle" class="font-bold text-lg text-zinc-900 dark:text-white">Add New Badge</h3>
                <button onclick="closeFormModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="badgeForm" action="badges.php" method="POST" class="p-6">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="badge_id" id="formBadgeId" value="">
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-zinc-900 dark:text-white mb-2">Badge Name</label>
                    <input type="text" name="badge_name" id="badge_name" required class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-zinc-900 dark:text-white mb-2">Required XP</label>
                        <input type="number" name="required_xp" id="required_xp" min="0" value="0" class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <p class="text-xs text-zinc-500 mt-1">XP to auto-unlock</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-zinc-900 dark:text-white mb-2">Type</label>
                        <select name="badge_type" id="badge_type" class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="skill">Skill</option>
                            <option value="streak">Streak</option>
                            <option value="leaderboard">Leaderboard</option>
                            <option value="mastery">Mastery</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-zinc-900 dark:text-white mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-zinc-900 dark:text-white mb-2">SVG Icon Code</label>
                    <textarea name="svg_icon" id="svg_icon" rows="4" placeholder="<svg>...</svg>" class="w-full px-4 py-2 text-xs font-mono bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-zinc-600 dark:text-zinc-400 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" onclick="closeFormModal()" class="px-5 py-2 text-zinc-600 dark:text-zinc-400 font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg transition">Cancel</button>
                    <button type="submit" id="formSubmitBtn" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition">Create Badge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-900 dark:text-white">Delete Badge</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">This action cannot be undone.</p>
                </div>
            </div>
            <p id="deleteModalMessage" class="text-zinc-600 dark:text-zinc-300 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-zinc-600 dark:text-zinc-400 font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-xl transition">Cancel</button>
                <form action="badges.php" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="badge_id" id="deleteBadgeId">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('formModalTitle').innerText = 'Add New Badge';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formBadgeId').value = '';
    document.getElementById('badge_name').value = '';
    document.getElementById('required_xp').value = '0';
    document.getElementById('badge_type').value = 'mastery';
    document.getElementById('description').value = '';
    document.getElementById('svg_icon').value = '';
    document.getElementById('formSubmitBtn').innerText = 'Create Badge';
    document.getElementById('badgeFormModal').classList.remove('hidden');
}

function showEditModal(badge) {
    document.getElementById('formModalTitle').innerText = 'Edit Badge';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formBadgeId').value = badge.id;
    document.getElementById('badge_name').value = badge.badge_name;
    document.getElementById('required_xp').value = badge.required_xp;
    document.getElementById('badge_type').value = badge.badge_type;
    document.getElementById('description').value = badge.description;
    document.getElementById('svg_icon').value = badge.svg_icon;
    document.getElementById('formSubmitBtn').innerText = 'Save Changes';
    document.getElementById('badgeFormModal').classList.remove('hidden');
}

function closeFormModal() {
    document.getElementById('badgeFormModal').classList.add('hidden');
}

function showDeleteModal(id, name) {
    document.getElementById('deleteBadgeId').value = id;
    document.getElementById('deleteModalMessage').innerText = `Are you sure you want to permanently delete the "${name}" badge? Users who unlocked it will lose it.`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFormModal();
        closeDeleteModal();
    }
});
</script>

<?php include 'footer.php'; ?>

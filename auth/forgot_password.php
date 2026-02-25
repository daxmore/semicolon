<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$step = 1;
$error = '';
$success = '';
$user_id = null;
$username = '';
$question = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['find_account'])) {
        $identifier = trim($_POST['identifier'] ?? '');
        
        // Search by username or email
        $stmt = $conn->prepare("SELECT id, security_question FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        
        if ($user_data) {
            if (!empty($user_data['security_question'])) {
                $step = 2;
                $_SESSION['recovery_user_id'] = $user_data['id'];
                $question = $user_data['security_question'];
            } else {
                $error = "This account has no security question set. Please contact admin@semicolon.edu.";
            }
        } else {
            $error = "No account found with that username.";
        }
    } elseif (isset($_POST['verify_answer'])) {
        $answer = trim($_POST['answer'] ?? '');
        $user_id = $_SESSION['recovery_user_id'] ?? null;
        
        if ($user_id) {
            $user_data = get_user_by_id($user_id);
            if ($user_data && strtolower(trim($user_data['security_answer'])) === strtolower($answer)) {
                $step = 3;
                $_SESSION['recovery_verified'] = true;
            } else {
                $step = 2;
                $question = $user_data['security_question'] ?? 'Your security question';
                $error = "Incorrect answer. Please try again.";
            }
        } else {
            $error = "Session expired. Please start over.";
            $step = 1;
        }
    } elseif (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_id = $_SESSION['recovery_user_id'] ?? null;
        $verified = $_SESSION['recovery_verified'] ?? false;

        if ($user_id && $verified) {
            if (strlen($new_password) < 6) {
                $error = "Password must be at least 6 characters.";
                $step = 3;
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
                $step = 3;
            } else {
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password, $user_id);
                if ($stmt->execute()) {
                    $success = "Password reset successfully! You can now log in.";
                    $step = 4;
                    // Clear session
                    unset($_SESSION['recovery_user_id']);
                    unset($_SESSION['recovery_verified']);
                } else {
                    $error = "System error. Please try again later.";
                    $step = 3;
                }
            }
        } else {
            $error = "Session invalid. Please start over.";
            $step = 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Semicolon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Cormorant Garamond', serif; }
        .indigo-gradient {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%);
        }
        .input-underline {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0;
            padding: 12px 0;
            background: transparent;
            transition: border-color 0.3s;
        }
        .input-underline:focus {
            outline: none;
            border-bottom-color: #4338ca;
        }
    </style>
</head>
<body class="antialiased bg-zinc-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden p-8 lg:p-12 text-center">
        <!-- Logo -->
        <a href="../index.php" class="inline-block mb-10">
            <svg width="60" height="36" viewBox="0 0 50 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.705 0L19.95 4.935V5.9325L15.015 13.23H14.0175L6.7725 8.295V7.2975L11.7075 0H12.705ZM4.935 10.08H5.9325L13.1775 15.015V16.0125L4.1475 29.4H3.3075L2.625 28.9275V28.0875L6.3 22.68L0 18.375V17.3775L4.935 10.08ZM27.4194 0L34.6644 4.935V5.9325L29.7294 13.23H28.7319L21.4869 8.295V7.2975L26.4219 0H27.4194ZM19.6494 10.08H20.6469L27.8919 15.015V16.0125L18.8619 29.4H18.0219L17.3394 28.9275V28.0875L21.0144 22.68L14.7144 18.375V17.3775L19.6494 10.08ZM42.1337 0L49.3787 4.935V5.9325L44.4437 13.23H43.4462L36.2012 8.295V7.2975L41.1362 0H42.1337ZM34.3637 10.08H35.3612L42.6062 15.015V16.0125L33.5762 29.4H32.7362L32.0537 28.9275V28.0875L35.7287 22.68L29.4287 18.375V17.3775L34.3637 10.08Z" fill="#4338ca"/>
            </svg>
        </a>

        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>

        <h1 class="text-3xl font-semibold text-zinc-900 mb-4">
            <?php 
                if ($step === 1) echo "Recovery";
                elseif ($step === 2) echo "Security Check";
                elseif ($step === 3) echo "Reset Password";
                else echo "Success!";
            ?>
        </h1>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 text-sm rounded-2xl border border-red-100">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 text-sm rounded-2xl border border-green-100">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <p class="text-zinc-500 mb-8 leading-relaxed">
                Enter your username or email to begin the recovery process.
            </p>
            <form action="forgot_password.php" method="POST" class="space-y-6">
                <div>
                    <input 
                        type="text" 
                        name="identifier" 
                        required 
                        class="input-underline w-full text-center text-lg" 
                        placeholder="Username or Email"
                        value="<?php echo htmlspecialchars($identifier ?? ''); ?>"
                    >
                </div>
                <button type="submit" name="find_account" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-2xl transition-colors">
                    Continue &rarr;
                </button>
            </form>
        <?php elseif ($step === 2): ?>
            <p class="text-zinc-500 mb-2 leading-relaxed italic">
                Step 2: Security Question
            </p>
            <h2 class="text-xl font-bold text-zinc-900 mb-8"><?php echo htmlspecialchars($question); ?></h2>
            <form action="forgot_password.php" method="POST" class="space-y-6">
                <div>
                    <input 
                        type="text" 
                        name="answer" 
                        required 
                        autofocus
                        class="input-underline w-full text-center text-lg" 
                        placeholder="Type your answer here"
                    >
                </div>
                <button type="submit" name="verify_answer" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-2xl transition-colors">
                    Verify Answer
                </button>
            </form>
        <?php elseif ($step === 3): ?>
            <p class="text-zinc-500 mb-8 leading-relaxed">
                Choose a strong new password.
            </p>
            <form action="forgot_password.php" method="POST" class="space-y-6">
                <div>
                    <input 
                        type="password" 
                        name="new_password" 
                        required 
                        minlength="6"
                        class="input-underline w-full text-center text-lg" 
                        placeholder="New Password"
                    >
                </div>
                <div>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        required 
                        minlength="6"
                        class="input-underline w-full text-center text-lg" 
                        placeholder="Confirm New Password"
                    >
                </div>
                <button type="submit" name="reset_password" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-2xl transition-colors">
                    Reset Password
                </button>
            </form>
        <?php else: ?>
            <p class="text-zinc-500 mb-8 leading-relaxed text-lg">
                Your password has been securely updated.
            </p>
            <a href="login.php" class="block w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-2xl transition-colors">
                Back to Login
            </a>
        <?php endif; ?>

        <?php if ($step < 4): ?>
            <div class="mt-8 pt-8 border-t border-zinc-100 flex flex-col gap-4">
                <a href="login.php" class="text-sm font-medium text-zinc-400 hover:text-indigo-600 transition">
                    Back to Login
                </a>
                <p class="text-xs text-zinc-400">
                    If you're still having trouble, contact <span class="text-indigo-600 font-medium">admin@semicolon.edu</span>
                </p>
            </div>
        <?php endif; ?>
        
        <p class="mt-8 text-zinc-400 text-xs">
            © <?php echo date('Y'); ?> Semicolon Learning Systems. All rights reserved.
        </p>
    </div>
    
</body>
</html>

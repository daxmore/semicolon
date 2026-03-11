<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = 'Invalid username format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $errors[] = 'Invalid password format.';
    }

    if (empty($errors)) {
        $user = getUserByUsername($username);

        if ($user && $password === $user['password']) {
            // Check if user is banned
            if (isset($user['status']) && $user['status'] === 'banned') {
                $errors[] = 'Your account has been suspended. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../dashboard.php');
            }
                exit();
            }
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Semicolon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#FAFAFA',
                        secondary: '#F4F4F5',
                        accent: '#6366F1',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Cormorant Garamond', 'serif'],
                    }
                }
            }
        }
    </script>
    <script src="../assets/js/theme.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Cormorant Garamond', serif; }
        .input-underline {
            border: none;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 0;
            padding: 12px 0;
            background: transparent;
            transition: border-color 0.3s;
        }
        .dark .input-underline {
            border-bottom-color: #3f3f46;
            color: #f4f4f5;
        }
        .input-underline:focus {
            outline: none;
            border-bottom-color: #4f46e5;
        }
        .indigo-gradient {
            background: linear-gradient(135deg, #312e81 0%, #4338ca 50%, #6366f1 100%);
        }
        .card-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="antialiased bg-zinc-100 dark:bg-zinc-950 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-5xl bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl overflow-hidden flex min-h-[600px] border border-zinc-200 dark:border-zinc-800">
        
        <!-- Left Side - Form -->
        <div class="w-full lg:w-1/2 p-10 lg:p-16 flex flex-col">
            <!-- Logo -->
            <a href="../index.php" class="block mb-12">
                <?php echo file_get_contents('../assets/images/logo.svg'); ?>
            </a>
            
            <!-- Content -->
            <div class="flex-1 flex flex-col justify-center max-w-sm">
                <!-- Icon + Title side by side -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Welcome Back</h1>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">Sign in to your account</p>
                    </div>
                </div>
                
                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Separator -->
                <div class="w-12 h-px bg-zinc-200 mb-8"></div>
                
                <!-- Form -->
                <form action="login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm text-zinc-600 dark:text-zinc-400 mb-1">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            pattern="^[a-zA-Z0-9_]{3,20}$"
                            title="Username must be 3-20 characters and can only contain letters, numbers, and underscores."
                            class="input-underline w-full text-zinc-900 dark:text-white"
                            placeholder="Enter your username"
                        >
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-sm text-zinc-600 dark:text-zinc-400">Password</label>
                            <a href="forgot_password.php" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400">Forgot?</a>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            minlength="6"
                            pattern="(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}"
                            title="Password must be 6+ chars with at least one uppercase letter, one number, and one symbol."
                            class="input-underline w-full text-zinc-900 dark:text-white"
                            placeholder="Enter your password"
                        >
                    </div>
                    
                    <button 
                        type="submit"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-full transition-colors mt-8"
                    >
                        Sign in
                    </button>
                </form>
                
                <p class="mt-8 text-center text-zinc-500 dark:text-zinc-400 text-sm">
                    Don't have an account? <a href="signup.php" class="text-zinc-900 dark:text-white font-medium hover:text-indigo-600 dark:hover:text-indigo-400">Sign up</a>
                </p>
            </div>
        </div>
        
        <!-- Right Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 indigo-gradient relative overflow-hidden p-12 flex-col justify-between">
            <!-- Background glow -->
            <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 left-1/4 w-64 h-64 bg-purple-400/10 rounded-full blur-3xl"></div>
            
            <!-- Top -->
            <div></div>
            
            <!-- Main headline -->
            <div class="relative z-10">
                <h2 class="font-serif text-5xl text-white leading-tight">
                    <span class="italic">Enter</span><br>
                    <span class="italic">the Future</span><br>
                    <span class="font-semibold">of Learning,</span><br>
                    <span class="font-semibold">today</span>
                </h2>
            </div>
            
            <!-- Floating Card -->
            <div class="relative z-10 card-float">
                <div class="bg-white/95 backdrop-blur rounded-2xl p-6 shadow-xl max-w-xs">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-zinc-900">847</p>
                            <p class="text-sm text-zinc-500">Total Resources</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-zinc-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="text-sm text-zinc-600">Active Learners</span>
                        </div>
                        <span class="text-sm font-medium text-zinc-900">5,234</span>
                    </div>
                </div>
                
                <!-- Small floating icon -->
                <div class="absolute -bottom-4 -right-4 w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>
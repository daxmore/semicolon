<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$username = '';
$email = '';
$password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = 'Username must be 3-20 characters and can only contain letters, numbers, and underscores.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $errors[] = 'Password must be 6+ chars with at least one uppercase letter, one number, and one symbol.';
    }

    if (empty($errors)) {
        if (getUserByUsername($username)) {
            $errors[] = 'Username already taken.';
        } else {
            $new_avatar_url = null; // System default or null as requested

            if (empty($errors)) {
                $security_question = $_POST['security_question'] ?? null;
                $security_answer = $_POST['security_answer'] ?? null;
                
                if (createUser($username, $email, $password, $new_avatar_url, $security_question, $security_answer)) {
                    header('Location: /Semicolon/auth/login.php');
                    exit();
                } else {
                    $errors[] = 'Error creating user. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Semicolon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
        .input-underline:focus {
            outline: none;
            border-bottom-color: #0d9488;
        }
        .teal-gradient {
            background: linear-gradient(135deg, #134e4a 0%, #0f766e 50%, #14b8a6 100%);
        }
        .card-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .step-transition {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hidden-step {
            display: none;
            opacity: 0;
            transform: translateX(20px);
        }
    </style>
    <script>
        function nextStep() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!username || !email || !password) {
                alert('Please fill in all required fields in Step 1.');
                return;
            }

            // Simple regex for email
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                alert('Please enter a valid email address.');
                return;
            }

            if (password.length < 6) {
                alert('Password must be at least 6 characters.');
                return;
            }

            document.getElementById('step1').classList.add('hidden-step');
            document.getElementById('step2').classList.remove('hidden-step');
            setTimeout(() => {
                document.getElementById('step2').style.opacity = '1';
                document.getElementById('step2').style.transform = 'translateX(0)';
            }, 50);
            
            document.getElementById('step-title').innerText = "Security Question";
            document.getElementById('step-subtitle').innerText = "Step 2 of 2";
        }

        function prevStep() {
            document.getElementById('step2').classList.add('hidden-step');
            document.getElementById('step1').classList.remove('hidden-step');
            
            document.getElementById('step-title').innerText = "Get Started";
            document.getElementById('step-subtitle').innerText = "Step 1 of 2";
        }
    </script>
</head>
<body class="antialiased bg-zinc-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex min-h-[650px]">
        
        <!-- Left Side - Form -->
        <div class="w-full lg:w-1/2 p-10 lg:p-16 flex flex-col">
            <!-- Logo -->
            <a href="../index.php" class="block mb-10">
                <svg width="50" height="30" viewBox="0 0 50 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.705 0L19.95 4.935V5.9325L15.015 13.23H14.0175L6.7725 8.295V7.2975L11.7075 0H12.705ZM4.935 10.08H5.9325L13.1775 15.015V16.0125L4.1475 29.4H3.3075L2.625 28.9275V28.0875L6.3 22.68L0 18.375V17.3775L4.935 10.08ZM27.4194 0L34.6644 4.935V5.9325L29.7294 13.23H28.7319L21.4869 8.295V7.2975L26.4219 0H27.4194ZM19.6494 10.08H20.6469L27.8919 15.015V16.0125L18.8619 29.4H18.0219L17.3394 28.9275V28.0875L21.0144 22.68L14.7144 18.375V17.3775L19.6494 10.08ZM42.1337 0L49.3787 4.935V5.9325L44.4437 13.23H43.4462L36.2012 8.295V7.2975L41.1362 0H42.1337ZM34.3637 10.08H35.3612L42.6062 15.015V16.0125L33.5762 29.4H32.7362L32.0537 28.9275V28.0875L35.7287 22.68L29.4287 18.375V17.3775L34.3637 10.08Z" fill="#171717"/>
                </svg>
            </a>
            
            <!-- Content -->
            <div class="flex-1 flex flex-col justify-center max-w-sm">
                <!-- Icon + Title side by side -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-teal-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h1 id="step-title" class="text-2xl font-semibold text-zinc-900">Get Started</h1>
                        <p id="step-subtitle" class="text-zinc-500 text-sm">Step 1 of 2</p>
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
                <div class="w-12 h-px bg-zinc-200 mb-6"></div>
                
                <!-- Form -->
                <form action="signup.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    
                    <!-- Step 1 -->
                    <div id="step1" class="step-transition space-y-4">
                        <div>
                            <label for="username" class="block text-sm text-zinc-600 mb-1">Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                pattern="^[a-zA-Z0-9_]{3,20}$"
                                title="Username must be 3-20 characters and can only contain letters, numbers, and underscores."
                                class="input-underline w-full text-zinc-900"
                                placeholder="Choose a username"
                                value="<?php echo htmlspecialchars($username); ?>"
                            >
                        </div>

                        <div>
                            <label for="email" class="block text-sm text-zinc-600 mb-1">Email ID</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="input-underline w-full text-zinc-900"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email); ?>"
                            >
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm text-zinc-600 mb-1">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                minlength="6"
                                pattern="(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}"
                                title="Password must be 6+ chars with at least one uppercase letter, one number, and one symbol."
                                class="input-underline w-full text-zinc-900"
                                placeholder="Create a password"
                            >
                        </div>

                        <button 
                            type="button"
                            onclick="nextStep()"
                            class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-full transition-colors mt-6 flex items-center justify-center gap-2"
                        >
                            Next Step 
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2 -->
                    <div id="step2" class="step-transition hidden-step space-y-4">
                        <div>
                            <label for="security_question" class="block text-sm text-zinc-600 mb-1">Security Question</label>
                            <select 
                                id="security_question" 
                                name="security_question" 
                                required
                                class="input-underline w-full text-zinc-900"
                            >
                                <option value="">Select a security question</option>
                                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                <option value="In what city were you born?">In what city were you born?</option>
                            <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
                        </select>
                    </div>

                        <div>
                            <label for="security_answer" class="block text-sm text-zinc-600 mb-1">Security Answer</label>
                            <input 
                                type="text" 
                                id="security_answer" 
                                name="security_answer" 
                                required
                                class="input-underline w-full text-zinc-900"
                                placeholder="Your answer"
                            >
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button 
                                type="button"
                                onclick="prevStep()"
                                class="flex-1 py-4 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium rounded-full transition-colors"
                            >
                                Back
                            </button>
                            <button 
                                type="submit"
                                class="flex-[2] py-4 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-full transition-colors"
                            >
                                Complete Sign up
                            </button>
                        </div>
                    </div>
                </form>
                
                <p class="mt-6 text-center text-zinc-500 text-sm">
                    Already have an account? <a href="login.php" class="text-zinc-900 font-medium hover:text-teal-600">Log in</a>
                </p>
            </div>
        </div>
        
        <!-- Right Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 teal-gradient relative overflow-hidden p-12 flex-col justify-between">
            <!-- Background glow -->
            <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-teal-400/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 left-1/4 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl"></div>
            
            <!-- Top icon -->
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            
            <!-- Main headline -->
            <div class="relative z-10">
                <h2 class="font-serif text-5xl text-white leading-tight">
                    <span class="italic">Start</span><br>
                    <span class="italic">Your Journey</span><br>
                    <span class="font-semibold">to Mastery,</span><br>
                    <span class="font-semibold">today</span>
                </h2>
            </div>
            
            <!-- Floating Card -->
            <div class="relative z-10 card-float">
                <div class="bg-white/95 backdrop-blur rounded-2xl p-5 shadow-xl max-w-xs">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-teal-600 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-semibold text-zinc-900">5,234</p>
                                <p class="text-sm text-zinc-500">Happy Learners</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 pt-3 border-t border-zinc-100">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600">Books</span>
                            <span class="font-medium text-zinc-900">500+</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600">Papers</span>
                            <span class="font-medium text-zinc-900">247</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600">Videos</span>
                            <span class="font-medium text-zinc-900">100+</span>
                        </div>
                    </div>
                    
                    <button class="mt-4 w-full py-2 text-sm font-medium text-teal-700 bg-teal-50 rounded-full hover:bg-teal-100 transition">
                        View All Resources
                    </button>
                </div>
                
                <!-- Bottom icon -->
                <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>
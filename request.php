<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db.php';
require_once 'includes/functions.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $material_type = $_POST['material_type'] ?? '';
    $community_category = $_POST['community_category'] ?? null;
    $title = $_POST['title'] ?? '';
    $author_publisher = $_POST['author_publisher'] ?? '';
    $details = $_POST['details'] ?? '';

    if (empty($material_type) || empty($title)) {
        $message = 'Material type and title are required.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO material_requests (user_id, material_type, community_category, title, author_publisher, details) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt && $stmt->bind_param("isssss", $user_id, $material_type, $community_category, $title, $author_publisher, $details) && $stmt->execute()) {
            $message = 'Your request has been submitted successfully! We\'ll review it soon.';
            $message_type = 'success';
        } else {
            // Fallback in case column doesn't match
            $stmt_fallback = $conn->prepare("INSERT INTO material_requests (user_id, material_type, title, author_publisher, details) VALUES (?, ?, ?, ?, ?)");
            if ($stmt_fallback && $stmt_fallback->bind_param("issss", $user_id, $material_type, $title, $author_publisher, $details) && $stmt_fallback->execute()) {
                 $message = 'Your request has been submitted successfully! We\'ll review it soon. (Legacy save)';
                 $message_type = 'success';
            } else {
                 $message = 'Failed to submit your request. Please try again.';
                 $message_type = 'error';
            }
        }
    }
}

// Define categories specifically for the dropdown
$categories = [
    'Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 
    'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 
    'Data Science', 'AI & ML', 'Cybersecurity', 'DevOps', 
    'Cloud Computing', 'General Tech'
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Material - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 2s infinite; }
        .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .animate-slideUp { animation: slideUp 0.5s ease-out forwards; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .type-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .type-card:hover {
            transform: translateY(-4px);
        }
        .type-card.selected {
            transform: scale(1.02);
        }
        .form-input {
            transition: all 0.2s ease;
        }
        .form-input:focus {
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="antialiased bg-zinc-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section with Animated Background -->
    <section class="relative overflow-hidden">
        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600"></div>
        
        <!-- Animated Mesh Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff06_1px,transparent_1px),linear-gradient(to_bottom,#ffffff06_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        
        <!-- Floating Orbs -->
        <div class="absolute top-20 left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 right-20 w-96 h-96 bg-fuchsia-400/20 rounded-full blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-purple-400/10 rounded-full blur-3xl animate-pulse-glow"></div>
        
        <!-- Floating Icons -->
        <div class="absolute top-32 right-1/4 text-white/20 animate-float">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <div class="absolute bottom-32 left-1/4 text-white/15 animate-float-delayed">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center text-white">
            <!-- Breadcrumb Badge -->
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md rounded-full px-5 py-2 mb-8 border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">Can't find what you need?</span>
            </div>
            
            <!-- Main Title -->
            <h1 class="text-5xl md:text-6xl font-bold mb-6 tracking-tight">
               <span class="text-white/90">Request Study Material</span>
            </h1>
            <p class="text-xl text-white/70 max-w-2xl mx-auto leading-relaxed">
                Let us know what you're looking for and we'll do our best to add it to our library.
            </p>
            
            <!-- Steps Indicator -->
            <div class="flex items-center justify-center gap-4 mt-12">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white text-purple-600 font-bold text-sm flex items-center justify-center">1</div>
                    <span class="text-sm text-white/80 hidden sm:inline">Choose Type</span>
                </div>
                <div class="w-8 h-px bg-white/30"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-sm flex items-center justify-center border border-white/30">2</div>
                    <span class="text-sm text-white/60 hidden sm:inline">Add Details</span>
                </div>
                <div class="w-8 h-px bg-white/30"></div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 text-white font-bold text-sm flex items-center justify-center border border-white/30">3</div>
                    <span class="text-sm text-white/60 hidden sm:inline">Submit Request</span>
                </div>
            </div>
        </div>
        
        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 50L48 45.7C96 41.3 192 32.7 288 35.8C384 39 480 54 576 57.2C672 60.3 768 51.7 864 48.5C960 45.3 1056 47.7 1152 51.8C1248 56 1344 62 1392 65L1440 68V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V50Z" fill="#FAFAFA"/>
            </svg>
        </div>
    </section>

    <!-- Form Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 pb-24 relative z-10">
        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="rounded-2xl p-5 mb-8 animate-slideUp <?php echo $message_type === 'success' ? 'bg-emerald-50 border-2 border-emerald-200' : 'bg-red-50 border-2 border-red-200'; ?>">
                <div class="flex items-center gap-4">
                    <?php if ($message_type === 'success'): ?>
                        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-emerald-800">Request Submitted!</p>
                            <p class="text-sm text-emerald-600"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-red-800">Something went wrong</p>
                            <p class="text-sm text-red-600"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Form Card -->
        <form id="request-form" action="request.php" method="POST">
            <div class="glass-card rounded-3xl shadow-2xl shadow-purple-500/10 overflow-hidden">
                
                <!-- Material Type Section -->
                <div class="p-8 md:p-10 border-b border-zinc-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-zinc-900">What are you looking for?</h2>
                            <p class="text-sm text-zinc-500">Select the type of material you need</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Book -->
                        <label class="type-card cursor-pointer group">
                            <input type="radio" name="material_type" value="book" class="peer sr-only" required>
                            <div class="p-6 rounded-2xl border-2 border-zinc-200 bg-zinc-50/50 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-lg peer-checked:shadow-indigo-500/20 hover:border-indigo-300 hover:bg-indigo-50/50">
                                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-zinc-900 mb-1">Book</h3>
                                <p class="text-xs text-zinc-500">Textbooks & References</p>
                            </div>
                        </label>
                        
                        <!-- Paper -->
                        <label class="type-card cursor-pointer group">
                            <input type="radio" name="material_type" value="paper" class="peer sr-only">
                            <div class="p-6 rounded-2xl border-2 border-zinc-200 bg-zinc-50/50 transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:shadow-lg peer-checked:shadow-teal-500/20 hover:border-teal-300 hover:bg-teal-50/50">
                                <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-teal-500/30 group-hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-zinc-900 mb-1">Paper</h3>
                                <p class="text-xs text-zinc-500">Exam Papers & Notes</p>
                            </div>
                        </label>
                        
                        <!-- Video -->
                        <label class="type-card cursor-pointer group">
                            <input type="radio" name="material_type" value="video" class="peer sr-only">
                            <div class="p-6 rounded-2xl border-2 border-zinc-200 bg-zinc-50/50 transition-all peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:shadow-lg peer-checked:shadow-rose-500/20 hover:border-rose-300 hover:bg-rose-50/50">
                                <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-rose-500/30 group-hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="font-semibold text-zinc-900 mb-1">Video</h3>
                                <p class="text-xs text-zinc-500">Tutorials & Lectures</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Form Fields Section -->
                <div class="p-8 md:p-10 space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-zinc-900">Tell us more</h2>
                            <p class="text-sm text-zinc-500">The more details, the better we can help</p>
                        </div>
                    </div>
                    
                    <!-- Community Category Field -->
                    <div class="space-y-2">
                        <label for="community_category" class="block text-sm font-semibold text-zinc-700">
                            Community Category <span class="text-zinc-400 font-normal">(Optional)</span>
                        </label>
                        <select 
                            name="community_category" 
                            id="community_category" 
                            class="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 appearance-none"
                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1.25rem top 50%; background-size: 0.85rem auto;"
                        >
                            <option value="">Select a community to target...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Title Field -->
                    <div class="space-y-2">
                        <label for="title" class="block text-sm font-semibold text-zinc-700">
                            Title / Topic <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            required 
                            placeholder="e.g. Introduction to Algorithms, 3rd Edition"
                            class="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400"
                        >
                    </div>
                    
                    <!-- Author/Publisher Field -->
                    <div class="space-y-2">
                        <label for="author_publisher" class="block text-sm font-semibold text-zinc-700">
                            Author / Publisher <span class="text-zinc-400 font-normal">(Optional)</span>
                        </label>
                        <input 
                            type="text" 
                            name="author_publisher" 
                            id="author_publisher" 
                            placeholder="e.g. Thomas H. Cormen, MIT Press"
                            class="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400"
                        >
                    </div>
                    
                    <!-- Details Field -->
                    <div class="space-y-2">
                        <label for="details" class="block text-sm font-semibold text-zinc-700">
                            Additional Details <span class="text-zinc-400 font-normal">(Optional)</span>
                        </label>
                        <textarea 
                            id="details" 
                            name="details" 
                            rows="4" 
                            placeholder="Any specific edition, year, chapter, or context that would help us find the right material..."
                            class="form-input w-full px-5 py-4 bg-zinc-50 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 focus:bg-white transition text-zinc-900 placeholder:text-zinc-400 resize-none"
                        ></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            class="w-full relative group bg-gradient-to-r from-violet-600 via-purple-600 to-fuchsia-600 text-white px-8 py-5 rounded-2xl font-semibold text-lg hover:shadow-xl hover:shadow-purple-500/30 transition-all duration-300 flex justify-center items-center gap-3 overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center gap-3">
                                Submit Request
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-700 via-purple-700 to-fuchsia-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
            <div class="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-amber-500/30 group-hover:scale-105 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-zinc-900 mb-2">Quick Response</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">We review all requests within 24-48 hours and get back to you</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-zinc-900 mb-2">High Success Rate</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">Most popular requests are successfully fulfilled and added</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 border border-zinc-200/50 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all duration-300 group">
                <div class="w-12 h-12 bg-gradient-to-br from-violet-400 to-purple-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-violet-500/30 group-hover:scale-105 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="font-bold text-zinc-900 mb-2">Get Notified</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">We'll notify you immediately when your material is available</p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Animate cards on type selection
        document.querySelectorAll('input[name="material_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.type-card > div').forEach(card => {
                    card.classList.remove('animate-pulse');
                });
                this.closest('label').querySelector('div').classList.add('animate-pulse');
                setTimeout(() => {
                    this.closest('label').querySelector('div').classList.remove('animate-pulse');
                }, 300);
            });
        });

        // Update step indicator on form progress
        const form = document.getElementById('request-form');
        const steps = document.querySelectorAll('.flex.items-center.justify-center.gap-4 > div');
        
        document.querySelectorAll('input[name="material_type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                if (steps[1]) {
                    steps[1].querySelector('div').classList.remove('bg-white/20', 'border', 'border-white/30');
                    steps[1].querySelector('div').classList.add('bg-white/40');
                }
            });
        });

        document.getElementById('title')?.addEventListener('input', function() {
            if (this.value.length > 0 && steps[2]) {
                steps[2].querySelector('div').classList.remove('bg-white/20', 'border', 'border-white/30');
                steps[2].querySelector('div').classList.add('bg-white/40');
            }
        });
    </script>
</body>

</html>
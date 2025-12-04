<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect admin users to the admin dashboard if logged in
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Semicolon</title>
    <meta name="description" content="Learn about Semicolon's mission to democratize education through curated developer resources.">
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FAFAFA',
                        secondary: '#F4F4F5',
                        accent: '#6366F1',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Rubik', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-24 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/3 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/3 w-80 h-80 bg-teal-200/30 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto text-center">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About Us
                </span>
                <h1 class="text-5xl md:text-6xl font-bold text-zinc-900 mb-6 tracking-tight">
                    We're Building the Future of <span class="text-gradient">Learning</span>
                </h1>
                <p class="text-xl text-zinc-500 leading-relaxed">
                    Semicolon is on a mission to democratize education by providing curated, high-quality resources for developers worldwide.
                </p>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Image -->
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-indigo-100 to-teal-100 rounded-3xl blur-2xl opacity-50"></div>
                    <img 
                        src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80" 
                        alt="Team collaboration" 
                        class="relative rounded-2xl shadow-xl w-full object-cover aspect-[4/3]"
                    >
                </div>
                
                <!-- Content -->
                <div>
                    <span class="text-sm font-semibold text-indigo-600 uppercase tracking-wider">Our Story</span>
                    <h2 class="text-4xl font-bold text-zinc-900 mt-2 mb-6">Started with a Simple Idea</h2>
                    <p class="text-zinc-500 text-lg leading-relaxed mb-6">
                        We started Semicolon with a simple belief: quality learning resources should be accessible to everyone. As developers ourselves, we understood the struggle of finding reliable, curated content.
                    </p>
                    <p class="text-zinc-500 text-lg leading-relaxed mb-8">
                        Today, we're proud to serve thousands of students and professionals who trust Semicolon as their go-to platform for technical books, research papers, and video tutorials.
                    </p>
                    
                    <div class="flex flex-wrap gap-8">
                        <div>
                            <p class="text-4xl font-bold text-zinc-900">2023</p>
                            <p class="text-zinc-500">Founded</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold text-zinc-900">5K+</p>
                            <p class="text-zinc-500">Active Users</p>
                        </div>
                        <div>
                            <p class="text-4xl font-bold text-zinc-900">800+</p>
                            <p class="text-zinc-500">Resources</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-20 bg-zinc-50/50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-4">
                    Our Values
                </span>
                <h2 class="text-4xl md:text-5xl font-bold text-zinc-900 mb-4">What Drives Us</h2>
                <p class="text-lg text-zinc-500 max-w-2xl mx-auto">Our core principles guide everything we do.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Value 1 -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-3">Accessibility</h3>
                    <p class="text-zinc-500 leading-relaxed">Knowledge should be available to everyone, everywhere. We're committed to breaking down barriers to education.</p>
                </div>

                <!-- Value 2 -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-3">Quality</h3>
                    <p class="text-zinc-500 leading-relaxed">Every resource is carefully curated to ensure accuracy, reliability, and real-world value for our users.</p>
                </div>

                <!-- Value 3 -->
                <div class="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-rose-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-3">Innovation</h3>
                    <p class="text-zinc-500 leading-relaxed">We constantly evolve our platform to meet the changing needs of learners in the digital age.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mission -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 to-indigo-700 p-10 text-white">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl text-white font-bold mb-4">Our Mission</h3>
                        <p class="text-indigo-100 text-lg leading-relaxed">
                            To democratize knowledge by creating a seamless platform where students and professionals can discover, access, and share high-quality educational materials without barriers.
                        </p>
                    </div>
                </div>

                <!-- Vision -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-teal-700 p-10 text-white">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl text-white font-bold mb-4">Our Vision</h3>
                        <p class="text-teal-100 text-lg leading-relaxed">
                            To become the premier digital library for developers worldwide, recognized for quality, accessibility, and a user-centric experience that adapts to the evolving needs of learners.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff12_1px,transparent_1px),linear-gradient(to_bottom,#ffffff12_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        </div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ready to Start Learning?</h2>
            <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">Join our community of learners and unlock access to hundreds of curated resources.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="auth/signup.php" class="inline-flex items-center gap-2 bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-50 transition-all hover:shadow-xl">
                    Get Started Free
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="books.php" class="inline-flex items-center gap-2 bg-transparent border-2 border-white/30 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-white/10 transition-all">
                    Browse Library
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-16 bg-zinc-900 text-zinc-400">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div>
                    <h3 class="text-white font-bold text-xl mb-4">Semicolon<span class="text-indigo-500">;</span></h3>
                    <p class="text-sm">Your gateway to developer excellence. Curated resources for modern developers.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="books.php" class="hover:text-white transition-colors">Books</a></li>
                        <li><a href="papers.php" class="hover:text-white transition-colors">Papers</a></li>
                        <li><a href="videos.php" class="hover:text-white transition-colors">Videos</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="about.php" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="pricing.php" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="contact.php" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="privacy.php" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="terms.php" class="hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-zinc-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm">&copy; <?php echo date('Y'); ?> Semicolon Inc. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
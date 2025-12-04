<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semicolon - Premium Developer Resources</title>
    <meta name="description" content="Curated books, research papers, and video tutorials for the discerning developer. Elevate your craft with Semicolon.">
    <link href="assets/css/index.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
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
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-[#FAFAFA]">
    <?php include 'includes/header.php'; ?>

    <!-- ===== HERO SECTION ===== -->
    <section class="relative min-h-screen flex items-center justify-center pt-16 pb-24 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 left-1/4 w-72 h-72 bg-indigo-200/40 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-1/4 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
        </div>

        <div class="container mx-auto px-6 text-center">
            <div class="max-w-4xl mx-auto">
                <!-- Badge -->
                <div class="hero-badge inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-8">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Trusted by 1,000+ students
                </div>
                
                <!-- Headline -->
                <h1 class="hero-title text-5xl md:text-6xl lg:text-7xl font-bold text-zinc-900 mb-6 tracking-tight leading-tight">
                    Your Gateway to<br>
                    <span class="text-gradient">Developer Excellence</span>
                </h1>
                
                <!-- Subheadline -->
                <p class="hero-desc text-xl text-zinc-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                    Curated books, research papers, and video tutorials. Everything you need to level up your skills, organized and accessible.
                </p>
                
                <!-- CTAs -->
                <div class="hero-cta flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="books.php" class="btn-primary w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        Explore Library
                    </a>
                    <a href="#features" class="btn-secondary w-full sm:w-auto group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Watch Demo
                    </a>
                </div>
            </div>

            <!-- Floating Cards -->
            <div class="hidden lg:block absolute top-1/4 left-12 w-48 bg-white rounded-2xl shadow-xl p-4 border border-zinc-100 float-animation">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">📚</div>
                    <div class="text-left">
                        <p class="text-xs text-zinc-400">Latest Book</p>
                        <p class="text-sm font-semibold text-zinc-900 truncate">Clean Code</p>
                    </div>
                </div>
                <div class="h-1 bg-zinc-100 rounded-full overflow-hidden">
                    <div class="h-full w-3/4 bg-indigo-500 rounded-full"></div>
                </div>
            </div>

            <div class="hidden lg:block absolute top-1/3 right-16 w-44 bg-white rounded-2xl shadow-xl p-4 border border-zinc-100 float-animation-delayed">
                <div class="text-center">
                    <p class="text-3xl font-bold text-zinc-900">98%</p>
                    <p class="text-xs text-zinc-400">Student Satisfaction</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== INFINITE MARQUEE / SOCIAL PROOF ===== -->
    <section class="py-8 border-y border-zinc-100 bg-zinc-50/50">
        <div class="marquee-container">
            <div class="marquee-track">
                <!-- First set -->
                <div class="marquee-item"><span class="text-2xl">🎓</span> Stanford University</div>
                <div class="marquee-item"><span class="text-2xl">🏛️</span> MIT</div>
                <div class="marquee-item"><span class="text-2xl">📖</span> Harvard</div>
                <div class="marquee-item"><span class="text-2xl">🔬</span> Caltech</div>
                <div class="marquee-item"><span class="text-2xl">💡</span> Berkeley</div>
                <div class="marquee-item"><span class="text-2xl">🚀</span> Carnegie Mellon</div>
                <div class="marquee-item"><span class="text-2xl">⭐</span> 4.9/5 Rating</div>
                <div class="marquee-item"><span class="text-2xl">👥</span> 5,000+ Users</div>
                <!-- Duplicate for seamless loop -->
                <div class="marquee-item"><span class="text-2xl">🎓</span> Stanford University</div>
                <div class="marquee-item"><span class="text-2xl">🏛️</span> MIT</div>
                <div class="marquee-item"><span class="text-2xl">📖</span> Harvard</div>
                <div class="marquee-item"><span class="text-2xl">🔬</span> Caltech</div>
                <div class="marquee-item"><span class="text-2xl">💡</span> Berkeley</div>
                <div class="marquee-item"><span class="text-2xl">🚀</span> Carnegie Mellon</div>
                <div class="marquee-item"><span class="text-2xl">⭐</span> 4.9/5 Rating</div>
                <div class="marquee-item"><span class="text-2xl">👥</span> 5,000+ Users</div>
            </div>
        </div>
    </section>

    <!-- ===== BENTO GRID FEATURES ===== -->
    <section id="features" class="section-padding bg-zinc-50/30">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="badge mb-4">Features</span>
                <h2 class="text-4xl md:text-5xl font-bold text-zinc-900 mb-4">Everything You Need</h2>
                <p class="text-lg text-zinc-500 max-w-2xl mx-auto">One platform, endless learning possibilities. Discover resources curated for developers.</p>
            </div>

            <!-- Fixed Bento Grid: 3 columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Book Library Card -->
                <a href="books.php" class="bento-item group bg-gradient-to-br from-indigo-50 to-white hover:from-indigo-100 min-h-[280px]">
                    <div class="icon-box mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-zinc-900 mb-2">Book Library</h3>
                    <p class="text-zinc-500 mb-6">Hand-picked collection of technical books covering algorithms, system design, and more.</p>
                    <div class="flex gap-2">
                        <div class="w-12 h-16 bg-indigo-200 rounded-lg"></div>
                        <div class="w-12 h-16 bg-teal-200 rounded-lg"></div>
                        <div class="w-12 h-16 bg-amber-200 rounded-lg"></div>
                        <div class="w-12 h-16 bg-rose-200 rounded-lg"></div>
                    </div>
                    <span class="absolute bottom-6 right-6 text-zinc-400 group-hover:text-indigo-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </span>
                </a>

                <!-- Your History Card (Side by Side with Book Library) -->
                <div class="bento-item group bg-gradient-to-b from-violet-50 to-white min-h-[280px]">
                    <div class="icon-box mb-4 bg-violet-100 text-violet-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Your History</h3>
                    <p class="text-zinc-500 text-sm mb-4">Pick up right where you left off.</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-2 bg-white rounded-lg border border-zinc-100">
                            <div class="w-8 h-8 bg-indigo-100 rounded flex items-center justify-center text-xs">📚</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-zinc-900 truncate">Clean Architecture</p>
                                <p class="text-xs text-zinc-400">2h ago</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-white rounded-lg border border-zinc-100">
                            <div class="w-8 h-8 bg-teal-100 rounded flex items-center justify-center text-xs">📄</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-zinc-900 truncate">Design Patterns</p>
                                <p class="text-xs text-zinc-400">Yesterday</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-white rounded-lg border border-zinc-100">
                            <div class="w-8 h-8 bg-rose-100 rounded flex items-center justify-center text-xs">🎥</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-zinc-900 truncate">React Masterclass</p>
                                <p class="text-xs text-zinc-400">3 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Smart Search Card -->
                <div class="bento-item group bg-gradient-to-r from-amber-50 to-white min-h-[280px]">
                    <div class="icon-box bg-amber-100 text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Smart Search</h3>
                    <p class="text-zinc-500 text-sm mb-4">Find any resource instantly with our powerful search.</p>
                    <div class="flex items-center gap-2 bg-white rounded-full border border-zinc-200 px-4 py-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <span class="text-zinc-400 text-sm">Search books, papers, videos...</span>
                    </div>
                </div>

                <!-- Research Papers Card -->
                <a href="papers.php" class="bento-item group bg-gradient-to-br from-teal-50 to-white hover:from-teal-100">
                    <div class="icon-box mb-4 bg-teal-100 text-teal-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Research Papers</h3>
                    <p class="text-zinc-500 text-sm">Latest academic papers and whitepapers from top researchers.</p>
                </a>

                <!-- Video Tutorials Card -->
                <a href="videos.php" class="bento-item group bg-gradient-to-br from-rose-50 to-white hover:from-rose-100">
                    <div class="icon-box mb-4 bg-rose-100 text-rose-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Video Tutorials</h3>
                    <p class="text-zinc-500 text-sm">High-quality video content for visual learners.</p>
                </a>

                <!-- Pro Access Card -->
                <a href="pricing.php" class="bento-item group bg-gradient-to-br from-purple-50 to-white hover:from-purple-100">
                    <div class="icon-box mb-4 bg-purple-100 text-purple-600 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Pro Access</h3>
                    <p class="text-zinc-500 text-sm">Unlock premium content and exclusive features.</p>
                </a>
            </div>
        </div>
    </section>

    <!-- ===== STATS SECTION ===== -->
    <section class="py-20 bg-white border-y border-zinc-100">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="stat-item">
                    <p class="text-4xl md:text-5xl font-bold text-zinc-900 mb-2">500+</p>
                    <p class="text-zinc-500 font-medium">Books</p>
                </div>
                <div class="stat-item">
                    <p class="text-4xl md:text-5xl font-bold text-zinc-900 mb-2">200+</p>
                    <p class="text-zinc-500 font-medium">Papers</p>
                </div>
                <div class="stat-item">
                    <p class="text-4xl md:text-5xl font-bold text-zinc-900 mb-2">100+</p>
                    <p class="text-zinc-500 font-medium">Videos</p>
                </div>
                <div class="stat-item">
                    <p class="text-4xl md:text-5xl font-bold text-zinc-900 mb-2">5k+</p>
                    <p class="text-zinc-500 font-medium">Happy Users</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="section-padding">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="badge mb-4">How It Works</span>
                <h2 class="text-4xl md:text-5xl font-bold text-zinc-900 mb-4">Simple as 1-2-3</h2>
                <p class="text-lg text-zinc-500 max-w-2xl mx-auto">Get started in minutes and unlock a world of knowledge.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <!-- Connecting Line (Desktop) -->
                <div class="hidden md:block absolute top-24 left-1/4 right-1/4 h-0.5 bg-gradient-to-r from-indigo-200 via-teal-200 to-rose-200"></div>

                <div class="step-card text-center relative">
                    <div class="w-16 h-16 mx-auto mb-6 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-bold text-2xl relative z-10">1</div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Sign Up Free</h3>
                    <p class="text-zinc-500">Create your account in seconds. No credit card required.</p>
                </div>

                <div class="step-card text-center relative">
                    <div class="w-16 h-16 mx-auto mb-6 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-600 font-bold text-2xl relative z-10">2</div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Explore Resources</h3>
                    <p class="text-zinc-500">Browse our curated collection of books, papers, and videos.</p>
                </div>

                <div class="step-card text-center relative">
                    <div class="w-16 h-16 mx-auto mb-6 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 font-bold text-2xl relative z-10">3</div>
                    <h3 class="text-xl font-bold text-zinc-900 mb-2">Start Learning</h3>
                    <p class="text-zinc-500">Access content anytime, anywhere. Your progress is saved.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section class="section-padding bg-zinc-50/50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="badge mb-4">Testimonials</span>
                <h2 class="text-4xl md:text-5xl font-bold text-zinc-900 mb-4">Loved by Students</h2>
                <p class="text-lg text-zinc-500 max-w-2xl mx-auto">Don't just take our word for it.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-xl">👩‍💻</div>
                        <div>
                            <p class="font-bold text-zinc-900">Sarah Chen</p>
                            <p class="text-sm text-zinc-400">Computer Science, Stanford</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 italic">"Semicolon has been a game-changer for my studies. The curated book collection saved me hours of searching for quality resources."</p>
                    <div class="flex gap-1 mt-4 text-amber-400">★★★★★</div>
                </div>

                <div class="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-xl">👨‍🎓</div>
                        <div>
                            <p class="font-bold text-zinc-900">Alex Kumar</p>
                            <p class="text-sm text-zinc-400">Software Engineering, MIT</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 italic">"The research papers section is incredible. I found papers I couldn't find anywhere else. Highly recommend!"</p>
                    <div class="flex gap-1 mt-4 text-amber-400">★★★★★</div>
                </div>

                <div class="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center text-xl">👩‍🔬</div>
                        <div>
                            <p class="font-bold text-zinc-900">Emily Rodriguez</p>
                            <p class="text-sm text-zinc-400">Data Science, Berkeley</p>
                        </div>
                    </div>
                    <p class="text-zinc-600 italic">"The video tutorials are top-notch. Clear explanations and real-world examples. This is the learning platform I wish I had earlier."</p>
                    <div class="flex gap-1 mt-4 text-amber-400">★★★★★</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA BANNER ===== -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff12_1px,transparent_1px),linear-gradient(to_bottom,#ffffff12_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        </div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Ready to Level Up?</h2>
            <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">Join thousands of students who are already accelerating their learning journey.</p>
            <a href="auth/signup.php" class="inline-flex items-center gap-2 bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-50 transition-all hover:shadow-xl">
                Get Started Free
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
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

    <!-- GSAP Scroll Animations -->
    <script>
        gsap.registerPlugin(ScrollTrigger);

        // Hero Animations
        gsap.from('.hero-badge', { opacity: 0, y: 20, duration: 0.6, delay: 0.3 });
        gsap.from('.hero-title', { opacity: 0, y: 30, duration: 0.8, delay: 0.5 });
        gsap.from('.hero-desc', { opacity: 0, y: 20, duration: 0.6, delay: 0.8 });
        gsap.from('.hero-cta', { opacity: 0, y: 20, duration: 0.6, delay: 1 });

        // Bento Grid Animation
        gsap.utils.toArray('.bento-item').forEach((item, i) => {
            gsap.from(item, {
                scrollTrigger: {
                    trigger: item,
                    start: 'top 85%',
                },
                opacity: 0,
                y: 40,
                scale: 0.95,
                duration: 0.6,
                delay: i * 0.1
            });
        });

        // Stats Counter Animation
        gsap.utils.toArray('.stat-item').forEach((item, i) => {
            gsap.from(item, {
                scrollTrigger: {
                    trigger: item,
                    start: 'top 85%',
                },
                opacity: 0,
                y: 30,
                duration: 0.5,
                delay: i * 0.1
            });
        });

        // Steps Animation
        gsap.utils.toArray('.step-card').forEach((item, i) => {
            gsap.from(item, {
                scrollTrigger: {
                    trigger: item,
                    start: 'top 85%',
                },
                opacity: 0,
                y: 40,
                duration: 0.6,
                delay: i * 0.15
            });
        });

        // Testimonials Animation
        gsap.utils.toArray('.testimonial-card').forEach((item, i) => {
            gsap.from(item, {
                scrollTrigger: {
                    trigger: item,
                    start: 'top 85%',
                },
                opacity: 0,
                y: 40,
                duration: 0.6,
                delay: i * 0.15
            });
        });
    </script>
</body>
</html>
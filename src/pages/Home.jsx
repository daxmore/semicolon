import React from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { useAuth } from '../contexts/AuthContext';
import {
  BookOpen,
  FileText,
  Video,
  Search,
  Sparkles,
  ArrowRight,
  ShieldCheck,
  Check
} from 'lucide-react';

export default function Home() {
  const { user } = useAuth();

  return (
    <div className="antialiased bg-[#FAFAFA]">

      {/* ===== HERO SECTION ===== */}
      <section className="relative isolate min-h-screen flex items-center justify-center pt-16 pb-24 overflow-hidden">
        {/* Background Pattern: 2 large blur blobs + faint 24px grid overlay */}
        <div className="absolute inset-0 -z-10">
          <div className="absolute top-20 left-1/4 w-72 h-72 bg-indigo-200/40 rounded-full blur-3xl"></div>
          <div className="absolute bottom-20 right-1/4 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl"></div>
          <div className="absolute inset-0 hero-grid-pattern"></div>
        </div>

        <div className="container mx-auto px-6 text-center">
          <div className="max-w-4xl mx-auto">
            {/* Pill Badge */}
            <motion.div
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="hero-badge inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-8"
            >
              <span className="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
              Trusted by 1,000+ students
            </motion.div>

            {/* Headline */}
            <motion.h1
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.1 }}
              className="hero-title text-5xl md:text-6xl lg:text-7xl font-bold text-zinc-900 mb-6 tracking-tight leading-tight font-heading"
            >
              Your Gateway to<br />
              <span className="text-gradient">Developer Excellence</span>
            </motion.h1>

            {/* Subheadline */}
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="hero-desc text-xl text-zinc-500 mb-10 max-w-2xl mx-auto leading-relaxed"
            >
              Curated books, research papers, and video tutorials. Everything you need to level up your skills, organized and accessible.
            </motion.p>

            {/* Two CTAs */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.3 }}
              className="hero-cta flex flex-col sm:flex-row items-center justify-center gap-4"
            >
              <Link to="/books" className="btn-primary w-full sm:w-auto">
                <BookOpen className="h-5 w-5" />
                Explore Library
              </Link>
              <button
                onClick={() => {
                  const el = document.getElementById('features');
                  if (el) el.scrollIntoView({ behavior: 'smooth' });
                }}
                className="btn-secondary w-full sm:w-auto group cursor-pointer"
              >
                <Video className="h-5 w-5" />
                Watch Demo
              </button>
            </motion.div>
          </div>

          {/* Floating Card 1 (Latest Book) */}
          <motion.div
            animate={{ y: [0, -10, 0] }}
            transition={{ repeat: Infinity, duration: 6, ease: "easeInOut" }}
            className="hidden lg:block absolute top-1/4 left-12 w-48 bg-white rounded-2xl shadow-xl p-4 border border-zinc-100"
          >
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 text-lg">
                📚
              </div>
              <div className="text-left">
                <p className="text-xs text-zinc-400">Latest Book</p>
                <p className="text-sm font-semibold text-zinc-900 truncate">Clean Code</p>
              </div>
            </div>
            <div className="h-1 bg-zinc-100 rounded-full overflow-hidden">
              <div className="h-full w-3/4 bg-indigo-500 rounded-full"></div>
            </div>
          </motion.div>

          {/* Floating Card 2 (Student Satisfaction) */}
          <motion.div
            animate={{ y: [0, -10, 0] }}
            transition={{ repeat: Infinity, duration: 6, ease: "easeInOut", delay: 2 }}
            className="hidden lg:block absolute top-1/3 right-16 w-44 bg-white rounded-2xl shadow-xl p-4 border border-zinc-100"
          >
            <div className="text-center">
              <p className="text-3xl font-bold text-zinc-900 font-heading">98%</p>
              <p className="text-xs text-zinc-400">Student Satisfaction</p>
            </div>
          </motion.div>
        </div>
      </section>

      {/* ===== INFINITE MARQUEE STRIP ===== */}
      <section className="py-8 border-y border-zinc-100 bg-zinc-50/50">
        <div className="marquee-container">
          <div className="marquee-track">
            {/* First set */}
            <div className="marquee-item"><span className="text-2xl">🎓</span> Stanford University</div>
            <div className="marquee-item"><span className="text-2xl">🏛️</span> MIT</div>
            <div className="marquee-item"><span className="text-2xl">📖</span> Harvard</div>
            <div className="marquee-item"><span className="text-2xl">🔬</span> Caltech</div>
            <div className="marquee-item"><span className="text-2xl">💡</span> Berkeley</div>
            <div className="marquee-item"><span className="text-2xl">🚀</span> Carnegie Mellon</div>
            <div className="marquee-item"><span className="text-2xl">⭐</span> 4.9/5 Rating</div>
            <div className="marquee-item"><span className="text-2xl">👥</span> 5,000+ Users</div>
            {/* Duplicate for seamless loop */}
            <div className="marquee-item"><span className="text-2xl">🎓</span> Stanford University</div>
            <div className="marquee-item"><span className="text-2xl">🏛️</span> MIT</div>
            <div className="marquee-item"><span className="text-2xl">📖</span> Harvard</div>
            <div className="marquee-item"><span className="text-2xl">🔬</span> Caltech</div>
            <div className="marquee-item"><span className="text-2xl">💡</span> Berkeley</div>
            <div className="marquee-item"><span className="text-2xl">🚀</span> Carnegie Mellon</div>
            <div className="marquee-item"><span className="text-2xl">⭐</span> 4.9/5 Rating</div>
            <div className="marquee-item"><span className="text-2xl">👥</span> 5,000+ Users</div>
          </div>
        </div>
      </section>

      {/* ===== BENTO GRID FEATURES SECTION ===== */}
      <section id="features" className="py-24 bg-zinc-50/30">
        <div className="container mx-auto px-6">
          <div className="text-center mb-16">
            <span className="badge mb-4">Features</span>
            <h2 className="text-4xl md:text-5xl font-bold text-zinc-900 mb-4 font-heading">Everything You Need</h2>
            <p className="text-lg text-zinc-500 max-w-2xl mx-auto">One platform, endless learning possibilities. Discover resources curated for developers.</p>
          </div>

          {/* 3-Column Responsive Bento Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {/* 1. Book Library Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.05 }}
            >
              <Link to="/books" className="bento-item group bg-gradient-to-br from-indigo-50 to-white hover:from-indigo-100 min-h-[280px] block">
                <div className="icon-box mb-4 group-hover:scale-110 transition-transform">
                  <BookOpen className="h-6 w-6 text-indigo-600" />
                </div>
                <h3 className="text-2xl font-bold text-zinc-900 mb-2 font-heading">Book Library</h3>
                <p className="text-zinc-500 mb-6 text-sm">Hand-picked collection of technical books covering algorithms, system design, and more.</p>
                <div className="flex gap-2">
                  <div className="w-12 h-16 bg-indigo-200 rounded-lg"></div>
                  <div className="w-12 h-16 bg-teal-200 rounded-lg"></div>
                  <div className="w-12 h-16 bg-amber-200 rounded-lg"></div>
                  <div className="w-12 h-16 bg-rose-200 rounded-lg"></div>
                </div>
                <span className="absolute bottom-6 right-6 text-zinc-400 group-hover:text-indigo-600 transition-colors">
                  <ArrowRight className="h-6 w-6" />
                </span>
              </Link>
            </motion.div>

            {/* 2. Your History Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.1 }}
              className="bento-item group bg-gradient-to-b from-violet-50 to-white min-h-[280px]"
            >
              <div className="icon-box mb-4 bg-violet-100 text-violet-600 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Your History</h3>
              <p className="text-zinc-500 text-sm mb-4">Pick up right where you left off.</p>
              <div className="space-y-2.5">
                <div className="flex items-center gap-3 p-2 bg-white rounded-lg border border-zinc-100">
                  <div className="w-8 h-8 bg-indigo-100 rounded flex items-center justify-center text-xs">📚</div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-medium text-zinc-900 truncate">Clean Architecture</p>
                    <p className="text-[11px] text-zinc-400">2h ago</p>
                  </div>
                </div>
                <div className="flex items-center gap-3 p-2 bg-white rounded-lg border border-zinc-100">
                  <div className="w-8 h-8 bg-teal-100 rounded flex items-center justify-center text-xs">📄</div>
                  <div className="flex-1 min-w-0">
                    <p className="text-xs font-medium text-zinc-900 truncate">Design Patterns</p>
                    <p className="text-[11px] text-zinc-400">Yesterday</p>
                  </div>
                </div>
              </div>
            </motion.div>

            {/* 3. Smart Search Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.15 }}
              className="bento-item group bg-gradient-to-r from-amber-50 to-white min-h-[280px]"
            >
              <div className="icon-box bg-amber-100 text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                <Search className="h-6 w-6 text-amber-600" />
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Smart Search</h3>
              <p className="text-zinc-500 text-sm mb-4">Find any resource instantly with our powerful search.</p>
              <div className="flex items-center gap-2 bg-white rounded-full border border-zinc-200 px-4 py-3 shadow-sm">
                <Search className="h-4 w-4 text-zinc-400" />
                <span className="text-zinc-400 text-xs truncate">Search books, papers, videos...</span>
              </div>
            </motion.div>

            {/* 4. Research Papers Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.2 }}
            >
              <Link to="/papers" className="bento-item group bg-gradient-to-br from-teal-50 to-white hover:from-teal-100 block">
                <div className="icon-box mb-4 bg-teal-100 text-teal-600 group-hover:scale-110 transition-transform">
                  <FileText className="h-6 w-6 text-teal-600" />
                </div>
                <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Research Papers</h3>
                <p className="text-zinc-500 text-sm">Latest academic papers and whitepapers from top researchers.</p>
              </Link>
            </motion.div>

            {/* 5. Video Tutorials Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.25 }}
            >
              <Link to="/videos" className="bento-item group bg-gradient-to-br from-rose-50 to-white hover:from-rose-100 block">
                <div className="icon-box mb-4 bg-rose-100 text-rose-600 group-hover:scale-110 transition-transform">
                  <Video className="h-6 w-6 text-rose-600" />
                </div>
                <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Video Tutorials</h3>
                <p className="text-zinc-500 text-sm">High-quality video content for visual learners.</p>
              </Link>
            </motion.div>

            {/* 6. Pro Access Card */}
            <motion.div
              whileInView={{ opacity: 1, y: 0, scale: 1 }}
              initial={{ opacity: 0, y: 30, scale: 0.95 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.3 }}
            >
              <Link to="/pricing" className="bento-item group bg-gradient-to-br from-purple-50 to-white hover:from-purple-100 block">
                <div className="icon-box mb-4 bg-purple-100 text-purple-600 group-hover:scale-110 transition-transform">
                  <Sparkles className="h-6 w-6 text-purple-600" />
                </div>
                <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Pro Access</h3>
                <p className="text-zinc-500 text-sm">Unlock premium content and exclusive features.</p>
              </Link>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ===== STATS BAR SECTION ===== */}
      <section className="py-20 bg-white border-y border-zinc-100">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.4 }}
              className="stat-item"
            >
              <p className="text-4xl md:text-5xl font-bold text-zinc-900 mb-2 font-heading">500+</p>
              <p className="text-zinc-500 font-medium text-sm">Books</p>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.4, delay: 0.1 }}
              className="stat-item"
            >
              <p className="text-4xl md:text-5xl font-bold text-zinc-900 mb-2 font-heading">200+</p>
              <p className="text-zinc-500 font-medium text-sm">Papers</p>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.4, delay: 0.2 }}
              className="stat-item"
            >
              <p className="text-4xl md:text-5xl font-bold text-zinc-900 mb-2 font-heading">100+</p>
              <p className="text-zinc-500 font-medium text-sm">Videos</p>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.4, delay: 0.3 }}
              className="stat-item"
            >
              <p className="text-4xl md:text-5xl font-bold text-zinc-900 mb-2 font-heading">5k+</p>
              <p className="text-zinc-500 font-medium text-sm">Happy Users</p>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ===== HOW IT WORKS (3 steps) ===== */}
      <section className="py-24">
        <div className="container mx-auto px-6">
          <div className="text-center mb-16">
            <span className="badge mb-4">How It Works</span>
            <h2 className="text-4xl md:text-5xl font-bold text-zinc-900 mb-4 font-heading">Simple as 1-2-3</h2>
            <p className="text-lg text-zinc-500 max-w-2xl mx-auto">Get started in minutes and unlock a world of knowledge.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 relative">

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5 }}
              className="step-card text-center relative"
            >
              <div className="w-16 h-16 mx-auto mb-6 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-bold text-2xl relative z-10 font-heading">
                1
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Sign Up Free</h3>
              <p className="text-zinc-500 text-sm">Create your account in seconds. No credit card required.</p>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.15 }}
              className="step-card text-center relative"
            >
              <div className="w-16 h-16 mx-auto mb-6 bg-teal-100 rounded-2xl flex items-center justify-center text-teal-600 font-bold text-2xl relative z-10 font-heading">
                2
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Explore Resources</h3>
              <p className="text-zinc-500 text-sm">Browse our curated collection of books, papers, and videos.</p>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="step-card text-center relative"
            >
              <div className="w-16 h-16 mx-auto mb-6 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 font-bold text-2xl relative z-10 font-heading">
                3
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-2 font-heading">Start Learning</h3>
              <p className="text-zinc-500 text-sm">Access content anytime, anywhere. Your progress is saved.</p>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ===== TESTIMONIALS SECTION ===== */}
      <section className="py-24 bg-zinc-50/50">
        <div className="container mx-auto px-6">
          <div className="text-center mb-16">
            <span className="badge mb-4">Testimonials</span>
            <h2 className="text-4xl md:text-5xl font-bold text-zinc-900 mb-4 font-heading">Loved by Students</h2>
            <p className="text-lg text-zinc-500 max-w-2xl mx-auto">Don't just take our word for it.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5 }}
              className="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm"
            >
              <div className="flex items-center gap-4 mb-6">
                <div className="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-xl">
                  👩‍💻
                </div>
                <div>
                  <p className="font-bold text-zinc-900">Sarah Chen</p>
                  <p className="text-xs text-zinc-400">Computer Science, Stanford</p>
                </div>
              </div>
              <p className="text-zinc-600 italic text-sm leading-relaxed">
                "Semicolon has been a game-changer for my studies. The curated book collection saved me hours of searching for quality resources."
              </p>
              <div className="flex gap-1 mt-4 text-amber-400 text-sm">★★★★★</div>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.15 }}
              className="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm"
            >
              <div className="flex items-center gap-4 mb-6">
                <div className="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-xl">
                  👨‍🎓
                </div>
                <div>
                  <p className="font-bold text-zinc-900">Alex Kumar</p>
                  <p className="text-xs text-zinc-400">Software Engineering, MIT</p>
                </div>
              </div>
              <p className="text-zinc-600 italic text-sm leading-relaxed">
                "The research papers section is incredible. I found papers I couldn't find anywhere else. Highly recommend!"
              </p>
              <div className="flex gap-1 mt-4 text-amber-400 text-sm">★★★★★</div>
            </motion.div>

            <motion.div
              whileInView={{ opacity: 1, y: 0 }}
              initial={{ opacity: 0, y: 20 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="testimonial-card bg-white rounded-2xl p-8 border border-zinc-100 shadow-sm"
            >
              <div className="flex items-center gap-4 mb-6">
                <div className="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center text-xl">
                  👩‍🔬
                </div>
                <div>
                  <p className="font-bold text-zinc-900">Emily Rodriguez</p>
                  <p className="text-xs text-zinc-400">Data Science, Berkeley</p>
                </div>
              </div>
              <p className="text-zinc-600 italic text-sm leading-relaxed">
                "The video tutorials are top-notch. Clear explanations and real-world examples. This is the learning platform I wish I had earlier."
              </p>
              <div className="flex gap-1 mt-4 text-amber-400 text-sm">★★★★★</div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* ===== CTA BANNER ===== */}
      <section className="py-24 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700"></div>
        <div className="absolute inset-0 opacity-10">
          <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff12_1px,transparent_1px),linear-gradient(to_bottom,#ffffff12_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        </div>
        <div className="container mx-auto px-6 text-center relative z-10">
          <h2 className="text-4xl md:text-5xl font-bold text-white mb-6 font-heading">Ready to Level Up?</h2>
          <p className="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">Join thousands of students who are already accelerating their learning journey.</p>
          <Link
            to={user ? "/dashboard" : "/signup"}
            className="inline-flex items-center gap-2 bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-50 transition-all hover:shadow-xl shadow-md cursor-pointer"
          >
            Get Started Free
            <ArrowRight className="h-5 w-5" />
          </Link>
        </div>
      </section>

    </div>
  );
}

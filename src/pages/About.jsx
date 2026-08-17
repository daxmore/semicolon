import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { BookOpen, Sparkles, Users } from 'lucide-react';

export default function About() {
  const { user } = useAuth();

  return (
    <div className="antialiased bg-[#FAFAFA]">
      {/* Hero Section */}
      <section className="relative pt-24 pb-24 overflow-hidden">
        {/* Background */}
        <div className="absolute inset-0 -z-10">
          <div className="absolute top-0 left-1/3 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 right-1/3 w-80 h-80 bg-teal-200/30 rounded-full blur-3xl"></div>
        </div>

        <div className="container mx-auto px-6">
          <div className="max-w-3xl mx-auto text-center">
            <span className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              About Us
            </span>
            <h1 className="text-5xl md:text-6xl font-bold text-zinc-900 mb-6 tracking-tight">
              We're Building the Future of <span className="text-gradient">Learning</span>
            </h1>
            <p className="text-xl text-zinc-500 leading-relaxed">
              Semicolon is on a mission to democratize education by providing curated, high-quality resources for developers worldwide.
            </p>
          </div>
        </div>
      </section>

      {/* Story Section */}
      <section className="py-20">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            {/* Image */}
            <div className="relative">
              <div className="absolute -inset-4 bg-gradient-to-r from-indigo-100 to-teal-100 rounded-3xl blur-2xl opacity-50"></div>
              <img 
                src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80" 
                alt="Team collaboration" 
                className="relative rounded-2xl shadow-xl w-full object-cover aspect-[4/3]"
              />
            </div>
            
            {/* Content */}
            <div>
              <span className="text-sm font-semibold text-indigo-600 uppercase tracking-wider">Our Story</span>
              <h2 className="text-4xl font-bold text-zinc-900 mt-2 mb-6">Started with a Simple Idea</h2>
              <p className="text-zinc-500 text-lg leading-relaxed mb-6">
                We started Semicolon with a simple belief: quality learning resources should be accessible to everyone. As developers ourselves, we understood the struggle of finding reliable, curated content.
              </p>
              <p className="text-zinc-500 text-lg leading-relaxed mb-8">
                Today, we're proud to serve thousands of students and professionals who trust Semicolon as their go-to platform for technical books, research papers, and video tutorials.
              </p>
              
              <div className="flex flex-wrap gap-8">
                <div>
                  <p className="text-4xl font-bold text-zinc-900">2023</p>
                  <p className="text-zinc-500">Founded</p>
                </div>
                <div>
                  <p className="text-4xl font-bold text-zinc-900">5K+</p>
                  <p className="text-zinc-500">Active Users</p>
                </div>
                <div>
                  <p className="text-4xl font-bold text-zinc-900">800+</p>
                  <p className="text-zinc-500">Resources</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Values Section */}
      <section className="py-20 bg-zinc-50/50">
        <div className="container mx-auto px-6">
          <div className="text-center mb-16">
            <span className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-4">
              Our Values
            </span>
            <h2 className="text-4xl md:text-5xl font-bold text-zinc-900 mb-4">What Drives Us</h2>
            <p className="text-lg text-zinc-500 max-w-2xl mx-auto">Our core principles guide everything we do.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {/* Value 1 */}
            <div className="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-indigo-200 hover:shadow-xl transition-all duration-300">
              <div className="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-3">Accessibility</h3>
              <p className="text-zinc-500 leading-relaxed">Knowledge should be available to everyone, everywhere. We're committed to breaking down barriers to education.</p>
            </div>

            {/* Value 2 */}
            <div className="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-teal-200 hover:shadow-xl transition-all duration-300">
              <div className="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-3">Quality</h3>
              <p className="text-zinc-500 leading-relaxed">Every resource is carefully curated to ensure accuracy, reliability, and real-world value for our users.</p>
            </div>

            {/* Value 3 */}
            <div className="bg-white rounded-2xl p-8 border border-zinc-100 hover:border-rose-200 hover:shadow-xl transition-all duration-300">
              <div className="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-zinc-900 mb-3">Innovation</h3>
              <p className="text-zinc-500 leading-relaxed">We constantly evolve our platform to meet the changing needs of learners in the digital age.</p>
            </div>
          </div>
        </div>
      </section>

      {/* Mission & Vision */}
      <section className="py-20">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* Mission */}
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 to-indigo-700 p-10 text-white">
              <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
              <div className="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
              <div className="relative z-10">
                <div className="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                  </svg>
                </div>
                <h3 className="text-2xl text-white font-bold mb-4">Our Mission</h3>
                <p className="text-indigo-100 text-lg leading-relaxed">
                  To democratize knowledge by creating a seamless platform where students and professionals can discover, access, and share high-quality educational materials without barriers.
                </p>
              </div>
            </div>

            {/* Vision */}
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-teal-700 p-10 text-white">
              <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
              <div className="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
              <div className="relative z-10">
                <div className="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </div>
                <h3 className="text-2xl text-white font-bold mb-4">Our Vision</h3>
                <p className="text-teal-100 text-lg leading-relaxed">
                  To become the premier digital library for developers worldwide, recognized for quality, accessibility, and a user-centric experience that adapts to the evolving needs of learners.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-24 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-indigo-600 to-indigo-700"></div>
        <div className="absolute inset-0 opacity-10">
          <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff12_1px,transparent_1px),linear-gradient(to_bottom,#ffffff12_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        </div>
        <div className="container mx-auto px-6 text-center relative z-10">
          <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">Ready to Start Learning?</h2>
          <p className="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">Join our community of learners and unlock access to hundreds of curated resources.</p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link to={user ? "/dashboard" : "/signup"} className="inline-flex items-center gap-2 bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-indigo-50 transition-all hover:shadow-xl">
              Get Started Free
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </Link>
            <Link to="/books" className="inline-flex items-center gap-2 bg-transparent border-2 border-white/30 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-white/10 transition-all">
              Browse Library
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}

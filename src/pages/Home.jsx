import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { 
  BookOpen, 
  FileText, 
  Video, 
  Award, 
  Flame, 
  Trophy, 
  CheckCircle2, 
  ArrowRight, 
  Sparkles, 
  Code2, 
  Terminal, 
  Cpu, 
  ShieldCheck 
} from 'lucide-react';
import Logo from '../components/common/Logo';

export default function Home() {
  const { user } = useAuth();

  const highlights = [
    {
      icon: BookOpen,
      title: 'Architectural Books & Blueprints',
      desc: 'Carefully curated technical manuals, clean architecture references, and design pattern guides.',
      link: '/books',
      tag: 'Library',
    },
    {
      icon: FileText,
      title: 'Academic & Technical Papers',
      desc: 'Seminal distributed systems whitepapers, database papers, and exam archives.',
      link: '/papers',
      tag: 'Research',
    },
    {
      icon: Video,
      title: 'Curated Video Deep Dives',
      desc: 'Practical walk-throughs, system design sessions, and full-stack video series.',
      link: '/videos',
      tag: 'Video Academy',
    },
    {
      icon: Award,
      title: 'Sequential Mastery Quizzes',
      desc: 'Progressive challenge tiers from Easy to Interview with real-time level awards.',
      link: '/academy',
      tag: 'Academy',
    },
  ];

  return (
    <div className="space-y-20 py-10 pb-20">
      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 pt-10">
        <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 border border-indigo-200/80 text-indigo-700 text-xs font-semibold shadow-sm animate-in fade-in slide-in-from-top-2">
          <Sparkles className="h-3.5 w-3.5" />
          <span>The Next Generation Developer Knowledge Platform</span>
        </div>

        <div className="max-w-4xl mx-auto space-y-4">
          <h1 className="text-4xl sm:text-6xl font-extrabold font-heading text-zinc-950 tracking-tight leading-[1.1]">
            Master Software Architecture.{' '}
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-indigo-700 to-amber-500">
              One Concept at a Time.
            </span>
          </h1>
          <p className="text-sm sm:text-base text-zinc-600 max-w-2xl mx-auto leading-relaxed">
            A curated ecosystem of engineering books, seminal research papers, interactive skill quizzes, and developer discussions.
          </p>
        </div>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
          <Link
            to={user ? '/dashboard' : '/signup'}
            className="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-bold shadow-xl shadow-indigo-600/25 transition flex items-center justify-center gap-2 hover:-translate-y-0.5"
          >
            <span>{user ? 'Go to Your Dashboard' : 'Get Started for Free'}</span>
            <ArrowRight className="h-4 w-4" />
          </Link>
          <Link
            to="/books"
            className="w-full sm:w-auto px-8 py-3.5 bg-white border border-zinc-200 hover:bg-zinc-50 text-zinc-800 rounded-2xl text-xs font-bold shadow-sm transition hover:-translate-y-0.5"
          >
            Browse Books Library
          </Link>
        </div>
      </section>

      {/* Bento Grid Highlights */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center space-y-2 mb-12">
          <span className="text-xs font-bold uppercase tracking-wider text-indigo-600">Core Pillars</span>
          <h2 className="text-3xl font-bold font-heading text-zinc-900">
            Everything You Need to Level Up
          </h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {highlights.map((item, idx) => {
            const Icon = item.icon;
            return (
              <Link
                key={idx}
                to={item.link}
                className="group bg-white p-8 rounded-3xl border border-zinc-200/90 hover:border-indigo-400 hover:shadow-xl hover:shadow-indigo-500/5 transition flex flex-col justify-between"
              >
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div className="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition">
                      <Icon className="h-6 w-6" />
                    </div>
                    <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-wider bg-zinc-50 px-2 py-0.5 rounded border border-zinc-100">
                      {item.tag}
                    </span>
                  </div>

                  <h3 className="font-bold text-base text-zinc-900 group-hover:text-indigo-600 transition">
                    {item.title}
                  </h3>

                  <p className="text-xs text-zinc-500 leading-relaxed">
                    {item.desc}
                  </p>
                </div>

                <div className="mt-8 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
                  <span>Explore</span>
                  <ArrowRight className="h-3.5 w-3.5 transform group-hover:translate-x-1 transition" />
                </div>
              </Link>
            );
          })}
        </div>
      </section>

      {/* Gamification Callout */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-gradient-to-r from-zinc-950 via-indigo-950 to-zinc-950 rounded-3xl p-10 sm:p-14 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
          <div className="space-y-4 max-w-xl text-center md:text-left">
            <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-semibold border border-amber-400/30">
              <Flame className="h-3.5 w-3.5 fill-amber-400" />
              RPG Developer Progression
            </span>
            <h2 className="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight">
              Earn Badges, Streaks, and verified Certificates
            </h2>
            <p className="text-xs text-zinc-300 leading-relaxed">
              Every solved quiz, technical read, and accepted community solution grants real XP points. Level up and claim your spot on the global Hall of Fame.
            </p>
            <div className="pt-2">
              <Link
                to="/leaderboard"
                className="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold text-xs rounded-xl shadow-lg shadow-amber-500/20 transition"
              >
                <Trophy className="h-4 w-4" />
                <span>View Hall of Fame</span>
              </Link>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4 w-full md:w-auto shrink-0">
            <div className="bg-white/10 p-5 rounded-2xl border border-white/10 text-center">
              <ShieldCheck className="h-6 w-6 text-emerald-400 mx-auto mb-1.5" />
              <span className="text-2xl font-black">100%</span>
              <p className="text-[10px] text-zinc-300 uppercase font-semibold">Verified Materials</p>
            </div>
            <div className="bg-white/10 p-5 rounded-2xl border border-white/10 text-center">
              <Code2 className="h-6 w-6 text-indigo-300 mx-auto mb-1.5" />
              <span className="text-2xl font-black">15+</span>
              <p className="text-[10px] text-zinc-300 uppercase font-semibold">Tech Domains</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

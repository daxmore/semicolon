import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useUserHistory, useUserBadges } from '../../hooks/useGamification';
import { useBooks } from '../../hooks/useBooks';
import { 
  Flame, 
  Trophy, 
  Award, 
  BookOpen, 
  FileText, 
  Video, 
  ArrowRight, 
  Sparkles, 
  Zap, 
  CheckCircle2 
} from 'lucide-react';

export default function Dashboard() {
  const { profile, user, isPro } = useAuth();
  const { data: userBadges } = useUserBadges(user?.id);
  const { data: history } = useUserHistory(user?.id);
  const { data: featuredBooks } = useBooks();

  const nextLevelXp = Math.pow((profile?.level || 1), 2) * 100;
  const currentLevelBaseXp = Math.pow((profile?.level || 1) - 1, 2) * 100;
  const xpInCurrentLevel = (profile?.xp_total || 0) - currentLevelBaseXp;
  const xpNeededForNext = nextLevelXp - currentLevelBaseXp;
  const progressPercent = Math.min(100, Math.max(0, (xpInCurrentLevel / (xpNeededForNext || 1)) * 100));

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
      {/* Welcome Hero Banner */}
      <div className="bg-gradient-to-r from-zinc-900 via-indigo-950 to-zinc-900 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden">
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold">
                <Sparkles className="h-3.5 w-3.5" />
                Level {profile?.level || 1} Engineer
              </span>
              {isPro && (
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-500 text-zinc-950 text-[10px] font-black uppercase tracking-wider">
                  Pro Member
                </span>
              )}
            </div>

            <h1 className="text-3xl sm:text-4xl font-bold font-heading tracking-tight">
              Welcome back, {profile?.username || 'Developer'}!
            </h1>
            <p className="text-xs text-zinc-300 max-w-lg leading-relaxed">
              Continue reading guides, tackling quiz challenges, and leveling up your software engineering skills.
            </p>

            {/* Level Progress Bar */}
            <div className="pt-2 max-w-md space-y-1.5">
              <div className="flex justify-between text-[11px] font-semibold text-zinc-300">
                <span>Progress to Level {(profile?.level || 1) + 1}</span>
                <span>{profile?.xp_total || 0} / {nextLevelXp} XP</span>
              </div>
              <div className="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                <div
                  className="h-full bg-gradient-to-r from-indigo-500 to-amber-400 rounded-full transition-all duration-500"
                  style={{ width: `${progressPercent}%` }}
                />
              </div>
            </div>
          </div>

          {/* Quick Metrics */}
          <div className="grid grid-cols-2 gap-3 shrink-0">
            <div className="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center">
              <Flame className="h-6 w-6 text-amber-400 fill-amber-400 mx-auto mb-1" />
              <span className="text-xl font-bold">{profile?.daily_streak || 0}</span>
              <p className="text-[10px] text-zinc-300 uppercase font-semibold">Day Streak</p>
            </div>
            <div className="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center">
              <Award className="h-6 w-6 text-indigo-300 mx-auto mb-1" />
              <span className="text-xl font-bold">{userBadges?.length || 0}</span>
              <p className="text-[10px] text-zinc-300 uppercase font-semibold">Badges</p>
            </div>
          </div>
        </div>
      </div>

      {/* Quick Action Bento Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Link
          to="/academy"
          className="group p-6 rounded-2xl bg-white border border-zinc-200/90 hover:border-amber-400 hover:shadow-xl transition flex flex-col justify-between"
        >
          <div className="space-y-3">
            <div className="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
              <Zap className="h-5 w-5" />
            </div>
            <h3 className="font-bold text-base text-zinc-900 group-hover:text-amber-600 transition">
              Take Skill Quiz
            </h3>
            <p className="text-xs text-zinc-500 leading-relaxed">
              Complete progressive MCQ challenges to test your knowledge and unlock mastery badges.
            </p>
          </div>
          <div className="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-amber-600">
            <span>Explore academy</span>
            <ArrowRight className="h-4 w-4 transform group-hover:translate-x-1 transition" />
          </div>
        </Link>

        <Link
          to="/books"
          className="group p-6 rounded-2xl bg-white border border-zinc-200/90 hover:border-indigo-400 hover:shadow-xl transition flex flex-col justify-between"
        >
          <div className="space-y-3">
            <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
              <BookOpen className="h-5 w-5" />
            </div>
            <h3 className="font-bold text-base text-zinc-900 group-hover:text-indigo-600 transition">
              Read Developer Books
            </h3>
            <p className="text-xs text-zinc-500 leading-relaxed">
              Explore curated handbooks and architectural blueprints across 15 tech stacks.
            </p>
          </div>
          <div className="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
            <span>Browse library</span>
            <ArrowRight className="h-4 w-4 transform group-hover:translate-x-1 transition" />
          </div>
        </Link>

        <Link
          to="/community"
          className="group p-6 rounded-2xl bg-white border border-zinc-200/90 hover:border-indigo-400 hover:shadow-xl transition flex flex-col justify-between"
        >
          <div className="space-y-3">
            <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
              <Trophy className="h-5 w-5" />
            </div>
            <h3 className="font-bold text-base text-zinc-900 group-hover:text-indigo-600 transition">
              Join Community
            </h3>
            <p className="text-xs text-zinc-500 leading-relaxed">
              Ask architecture questions, discuss tech trends, and earn XP for quality answers.
            </p>
          </div>
          <div className="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
            <span>Open discussions</span>
            <ArrowRight className="h-4 w-4 transform group-hover:translate-x-1 transition" />
          </div>
        </Link>
      </div>

      {/* Recent Books & Recommendations */}
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold font-heading text-zinc-900">Featured Reading Material</h2>
          <Link to="/books" className="text-xs font-semibold text-indigo-600 hover:underline">
            View all books &rarr;
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {featuredBooks?.slice(0, 3).map((book) => (
            <Link
              key={book.id}
              to={`/books/${book.id}`}
              className="group p-6 bg-white rounded-2xl border border-zinc-200 hover:border-indigo-300 transition flex flex-col justify-between"
            >
              <div className="space-y-2">
                <span className="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full">
                  {book.subject}
                </span>
                <h3 className="font-bold text-sm text-zinc-900 group-hover:text-indigo-600 transition">
                  {book.title}
                </h3>
                <p className="text-xs text-zinc-500 font-medium">By {book.author}</p>
              </div>
              <div className="mt-4 pt-3 border-t border-zinc-100 text-xs font-semibold text-indigo-600 flex items-center gap-1">
                <span>Read now</span> &rarr;
              </div>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}

import React from 'react';
import { Link } from 'react-router-dom';
import { useSkills } from '../../hooks/useAcademy';
import { useAuth } from '../../contexts/AuthContext';
import { Flame, Trophy, Award, ChevronRight, Zap, Target, BookCheck, Shield } from 'lucide-react';

export default function Academy() {
  const { profile } = useAuth();
  const { data: skills, isLoading } = useSkills();

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
      {/* Top Banner / XP Progress */}
      <div className="bg-gradient-to-r from-zinc-900 via-indigo-950 to-zinc-900 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden">
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-3">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-semibold">
              <Flame className="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
              Level {profile?.level || 1} Developer
            </div>
            <h1 className="text-3xl sm:text-4xl font-bold font-heading tracking-tight">
              Semicolon Academy
            </h1>
            <p className="text-xs text-zinc-300 max-w-lg leading-relaxed">
              Test your engineering knowledge, unlock sequential skill levels, earn mastery badges, and ascend the Hall of Fame.
            </p>
          </div>

          {/* User Stats Card */}
          <div className="grid grid-cols-3 gap-3 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center">
            <div className="p-3">
              <span className="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold block">Total XP</span>
              <span className="text-lg font-bold text-amber-400">{profile?.xp_total || 0}</span>
            </div>
            <div className="p-3 border-x border-white/10">
              <span className="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold block">Streak</span>
              <span className="text-lg font-bold text-emerald-400">{profile?.daily_streak || 0} Days</span>
            </div>
            <div className="p-3">
              <span className="text-[10px] text-zinc-400 uppercase tracking-wider font-semibold block">Rank</span>
              <span className="text-lg font-bold text-indigo-300">#{(profile?.level || 1) * 2}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Quick Nav Pills */}
      <div className="flex items-center gap-3 overflow-x-auto pb-2 border-b border-zinc-200">
        <Link
          to="/academy"
          className="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold shadow-sm shrink-0 flex items-center gap-1.5"
        >
          <Target className="h-4 w-4" /> Skill Paths
        </Link>
        <Link
          to="/leaderboard"
          className="px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl text-xs font-semibold transition shrink-0 flex items-center gap-1.5"
        >
          <Trophy className="h-4 w-4 text-amber-500" /> Hall of Fame
        </Link>
        <Link
          to="/profile#badges"
          className="px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl text-xs font-semibold transition shrink-0 flex items-center gap-1.5"
        >
          <Award className="h-4 w-4 text-indigo-500" /> My Badges
        </Link>
      </div>

      {/* Skill Paths Section */}
      <div className="space-y-6">
        <div>
          <h2 className="text-xl font-bold font-heading text-zinc-900">Available Mastery Paths</h2>
          <p className="text-xs text-zinc-500">Each path includes 4 difficulty tiers: Easy, Medium, Hard, and Interview.</p>
        </div>

        {isLoading ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[1, 2, 3].map((n) => (
              <div key={n} className="bg-white p-6 rounded-2xl border border-zinc-200 animate-pulse h-48"></div>
            ))}
          </div>
        ) : skills && skills.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {skills.map((skill) => (
              <Link
                key={skill.id}
                to={`/academy/skill/${skill.id}`}
                className="group bg-white p-6 rounded-2xl border border-zinc-200/90 hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-500/5 transition flex flex-col justify-between"
              >
                <div className="space-y-3">
                  <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    <Zap className="h-5 w-5" />
                  </div>
                  <h3 className="font-bold text-base text-zinc-900 group-hover:text-indigo-600 transition">
                    {skill.name}
                  </h3>
                  <p className="text-xs text-zinc-500 line-clamp-3 leading-relaxed">
                    {skill.description || 'Master key concepts, algorithms, and practical interview questions.'}
                  </p>
                </div>

                <div className="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
                  <span>4 Level Journey</span>
                  <ChevronRight className="h-4 w-4 transform group-hover:translate-x-1 transition" />
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="bg-white p-8 rounded-2xl border border-zinc-200 text-center space-y-3">
            <BookCheck className="h-8 w-8 text-zinc-400 mx-auto" />
            <h3 className="text-sm font-bold text-zinc-900">No skill paths found</h3>
            <p className="text-xs text-zinc-500">Seed the skills table in Supabase to start taking quizzes.</p>
          </div>
        )}
      </div>
    </div>
  );
}

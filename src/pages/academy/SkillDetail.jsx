import React from 'react';
import { useParams, Link } from 'react-router-dom';
import { useSkillDetail, useUserSkillProgress } from '../../hooks/useAcademy';
import { useAuth } from '../../contexts/AuthContext';
import { ArrowLeft, Lock, Unlock, Play, CheckCircle2, Award, Zap } from 'lucide-react';

export default function SkillDetail() {
  const { id } = useParams();
  const { user, profile } = useAuth();
  const { data: skill, isLoading } = useSkillDetail(id);
  const { data: progress } = useUserSkillProgress(user?.id, id);

  const completedLevels = progress?.completed_levels_json || [];
  const userXp = profile?.xp_total || 0;

  const isLevelUnlocked = (idx, level) => {
    if (idx === 0) return true; // First tier is always unlocked
    const prevLevel = skill.levels[idx - 1];
    const prevCompleted =
      completedLevels.includes(prevLevel?.level_name) ||
      completedLevels.includes(prevLevel?.id) ||
      completedLevels.includes(`level_${prevLevel?.id}`);
    const xpMet = userXp >= (level.required_xp || 0);

    // Unlocked if user has enough XP or has completed the previous tier
    return xpMet || prevCompleted;
  };

  if (isLoading) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-16 animate-pulse space-y-6">
        <div className="h-6 bg-zinc-200 rounded w-1/4"></div>
        <div className="h-32 bg-zinc-200 rounded-2xl"></div>
      </div>
    );
  }

  if (!skill) {
    return (
      <div className="max-w-xl mx-auto px-4 py-16 text-center space-y-3">
        <h2 className="text-lg font-bold text-zinc-900">Skill Path Not Found</h2>
        <Link to="/academy" className="text-xs text-indigo-600 font-semibold">
          &larr; Back to Academy
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      <Link
        to="/academy"
        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition"
      >
        <ArrowLeft className="h-3.5 w-3.5" /> Back to Academy Paths
      </Link>

      {/* Skill Banner */}
      <div className="bg-white rounded-2xl border border-zinc-200/80 p-8 shadow-sm space-y-3">
        <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
          Mastery Path
        </span>
        <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
          {skill.name}
        </h1>
        <p className="text-xs text-zinc-600 leading-relaxed max-w-2xl">
          {skill.description || 'Advance through four challenging tiers to earn verified mastery badges.'}
        </p>
      </div>

      {/* Levels Progression List */}
      <div className="space-y-4">
        <h2 className="text-sm font-bold uppercase tracking-wider text-zinc-700">Tier Progression</h2>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {skill.levels?.map((lvl, idx) => {
            const unlocked = isLevelUnlocked(idx, lvl);
            const isCompleted = completedLevels.includes(lvl.level_name);

            return (
              <div
                key={lvl.id}
                className={`p-6 rounded-2xl border transition relative flex flex-col justify-between ${
                  unlocked
                    ? 'bg-white border-zinc-200 shadow-sm hover:border-indigo-300'
                    : 'bg-zinc-50/80 border-zinc-200/60 opacity-75'
                }`}
              >
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold uppercase tracking-wider text-indigo-600">
                      Tier {idx + 1}
                    </span>
                    {isCompleted ? (
                      <span className="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                        <CheckCircle2 className="h-3.5 w-3.5" /> Completed
                      </span>
                    ) : unlocked ? (
                      <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                        <Unlock className="h-3.5 w-3.5" /> Available
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-zinc-500 bg-zinc-200 px-2 py-0.5 rounded-full">
                        <Lock className="h-3.5 w-3.5" /> Locked
                      </span>
                    )}
                  </div>

                  <h3 className="font-bold text-lg text-zinc-900 capitalize">{lvl.level_name} Challenge</h3>

                  <p className="text-xs text-zinc-500">
                    {lvl.required_xp > 0
                      ? `Requires ${lvl.required_xp} Total XP & Tier ${idx} completion`
                      : 'Free starting tier. No prerequisite.'}
                  </p>
                </div>

                <div className="mt-6 pt-4 border-t border-zinc-100">
                  {unlocked ? (
                    <Link
                      to={`/academy/quiz?skill=${skill.id}&level=${lvl.id}`}
                      className="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition"
                    >
                      <Play className="h-3.5 w-3.5 fill-white" />
                      {isCompleted ? 'Retake Quiz' : 'Start Challenge'}
                    </Link>
                  ) : (
                    <button
                      disabled
                      className="w-full py-2.5 px-4 bg-zinc-200 text-zinc-400 rounded-xl text-xs font-semibold cursor-not-allowed flex items-center justify-center gap-2"
                    >
                      <Lock className="h-3.5 w-3.5" /> Locked
                    </button>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}

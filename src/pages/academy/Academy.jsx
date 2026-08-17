import React from 'react';
import { Link } from 'react-router-dom';
import { useSkills, useUserSkillsProgress } from '../../hooks/useAcademy';
import { useAuth } from '../../contexts/AuthContext';
import { useUserBadges } from '../../hooks/useGamification';

export default function Academy() {
  const { user, profile } = useAuth();
  const { data: skills, isLoading } = useSkills();
  const { data: userBadges } = useUserBadges(user?.id);
  const { data: userProgress } = useUserSkillsProgress(user?.id);

  const xpTotal = profile?.xp_total || 0;
  const level = profile?.level || 1;
  const streak = profile?.daily_streak || 0;

  // Calculate next level requirements
  const nextLevel = level + 1;
  const xpForNextLevel = Math.pow(nextLevel - 1, 2) * 100;
  const xpForCurrentLevel = Math.pow(level - 1, 2) * 100;

  // Progress bar math
  const xpIntoLevel = xpTotal - xpForCurrentLevel;
  const xpNeededForLevel = xpForNextLevel - xpForCurrentLevel;
  const progressPercentage = xpNeededForLevel > 0 ? Math.min(100, Math.round((xpIntoLevel / xpNeededForLevel) * 100)) : 100;

  const equippedBadges = (userBadges || []).filter((b) => b.is_equipped);

  return (
    <div className="rpg-bg text-[var(--text-primary)] min-h-screen font-sans selection:bg-indigo-500/30 selection:text-indigo-900 antialiased transition-colors duration-300">
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        {/* Top Stats Row */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
          {/* Player Card */}
          <div className="glass-panel rounded-2xl p-6 flex items-center gap-6 lg:col-span-2">
            <div className="relative">
              <div className="w-24 h-24 rounded-full bg-white border-4 border-indigo-100 flex items-center justify-center text-3xl font-bold text-indigo-600 overflow-hidden shadow-sm">
                {profile?.avatar_url ? (
                  <img src={profile.avatar_url} alt="Avatar" className="w-full h-full object-cover" />
                ) : (
                  <span>{(profile?.username || user?.email || 'U').charAt(0).toUpperCase()}</span>
                )}
              </div>
              {/* Mini Level indicator overlay */}
              <div className="absolute -bottom-2 -right-2 bg-white border border-indigo-200 rounded-full w-10 h-10 flex items-center justify-center shadow-md">
                <span className="text-indigo-600 font-bold text-sm">L{level}</span>
              </div>
            </div>
            
            <div className="flex-1">
              <h1 className="text-2xl font-bold text-zinc-900 tracking-tight mb-1">{profile?.username || 'Challenger'}</h1>
              <p className="text-zinc-500 text-sm font-medium mb-3">Level {level} Challenger</p>
              
              {/* XP Bar */}
              <div className="flex justify-between text-xs text-zinc-500 mb-1 font-medium">
                <span>{xpTotal.toLocaleString()} XP</span>
                <span>Next: {xpForNextLevel.toLocaleString()} XP</span>
              </div>
              <div className="w-full h-3 bg-zinc-100 rounded-full overflow-hidden border border-zinc-200">
                <div className="h-full progress-bar-fill rounded-full" style={{ width: `${progressPercentage}%` }}></div>
              </div>
            </div>
          </div>

          {/* Stats / Equipped Badges */}
          <div className="glass-panel rounded-2xl p-6 flex flex-col justify-between">
            <div>
              <h3 className="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Active Status</h3>
              <div className="flex items-center gap-3 mb-4">
                <div className="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500 border border-orange-500/20">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clipRule="evenodd" />
                  </svg>
                </div>
                <div>
                  <div className="text-2xl font-bold text-zinc-900">{streak} Days</div>
                  <div className="text-xs text-orange-400">Current Streak</div>
                </div>
              </div>
            </div>
            
            <div>
              <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Equipped Badges</h3>
              <div className="flex gap-2 min-h-[40px] items-center">
                {equippedBadges.length === 0 ? (
                  <div className="text-sm text-slate-500 italic">No badges equipped.</div>
                ) : (
                  equippedBadges.map((badge) => (
                    <div 
                      key={badge.id} 
                      className="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-lg" 
                      title={badge.badge?.name || 'Badge'}
                    >
                      {badge.badge?.icon || '🏆'}
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="mb-8 flex items-center justify-between">
          <div>
            <h2 className="text-3xl font-bold text-zinc-900 tracking-tight font-heading">Skill Trees</h2>
            <p className="text-zinc-500 mt-1">Conquer challenges to unlock higher tiers and earn certifications.</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {isLoading ? (
            [1, 2, 3, 4].map((n) => (
              <div key={n} className="glass-panel rounded-2xl p-6 h-64 animate-pulse"></div>
            ))
          ) : (
            (skills || []).map((skill) => {
              const skillProgress = (userProgress || []).find((p) => p.skill_id === skill.id);
              const currentLevel = skillProgress?.current_level || 'easy';
              
              const levels = ['easy', 'medium', 'hard', 'interview'];
              const currentIndex = levels.indexOf(currentLevel);
              const percentage = Math.min(100, Math.round(((currentIndex + 1) / 4) * 100));

              // Determine icon and color based on skill name
              let colorClass = 'text-slate-400';
              let bgClass = 'bg-slate-500';
              let iconEmoji = '💻';

              const nameLower = (skill.name || '').toLowerCase();
              if (nameLower.includes('react')) {
                colorClass = 'text-sky-400';
                bgClass = 'bg-sky-400';
                iconEmoji = '⚛️';
              } else if (nameLower.includes('java') && !nameLower.includes('script')) {
                colorClass = 'text-orange-500';
                bgClass = 'bg-orange-500';
                iconEmoji = '☕';
              } else if (nameLower.includes('javascript') || nameLower.includes('js')) {
                colorClass = 'text-yellow-400';
                bgClass = 'bg-yellow-400';
                iconEmoji = 'JS';
              } else if (nameLower.includes('python')) {
                colorClass = 'text-blue-500';
                bgClass = 'bg-blue-500';
                iconEmoji = '🐍';
              }

              return (
                <Link 
                  key={skill.id}
                  to={`/academy/skill/${skill.id}`} 
                  className="rpg-card glass-panel rounded-2xl p-6 relative overflow-hidden group block"
                >
                  {/* Hover gradient effect behind icon */}
                  <div className={`absolute -top-10 -right-10 w-32 h-32 ${bgClass} rounded-full blur-3xl opacity-10 group-hover:opacity-20 transition-opacity`}></div>
                  
                  <div className="flex items-center justify-between mb-4 relative z-10">
                    <div className="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-2xl font-bold">
                      {iconEmoji}
                    </div>
                    {currentIndex === 3 && skillProgress?.interview_unlocked ? (
                      <span className="px-2 py-1 text-xs font-bold text-amber-900 bg-amber-100 rounded uppercase tracking-wider shadow-sm border border-amber-200">
                        Mastered
                      </span>
                    ) : (
                      <span className={`px-2 py-1 text-xs font-bold ${colorClass} bg-slate-50 rounded border border-slate-200 uppercase tracking-wider`}>
                        {currentLevel}
                      </span>
                    )}
                  </div>
                  
                  <h3 className="text-xl font-bold text-zinc-900 mb-2 relative z-10 font-heading">{skill.name}</h3>
                  <p className="text-zinc-500 text-sm mb-6 line-clamp-2 relative z-10">{skill.description}</p>
                  
                  {/* Skill Progress Bar */}
                  <div className="mt-auto relative z-10">
                    <div className="flex justify-between text-xs font-medium text-zinc-500 mb-1">
                      <span>Progress</span>
                      <span>{percentage}%</span>
                    </div>
                    <div className="w-full h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                      <div className={`h-full ${bgClass} rounded-full`} style={{ width: `${percentage}%` }}></div>
                    </div>
                  </div>
                </Link>
              );
            })
          )}
        </div>
      </main>
    </div>
  );
}

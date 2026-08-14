import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useLeaderboard } from '../../hooks/useGamification';
import { useAuth } from '../../contexts/AuthContext';
import { Trophy, Flame, Award, Medal, Crown, Sparkles, TrendingUp } from 'lucide-react';

export default function Leaderboard() {
  const [tab, setTab] = useState('weekly'); // 'weekly' | 'lifetime'
  const { user } = useAuth();
  const { data: leaders, isLoading } = useLeaderboard(tab);

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      {/* Banner */}
      <div className="bg-gradient-to-r from-amber-950 via-zinc-900 to-amber-950 rounded-3xl p-8 sm:p-10 text-white shadow-xl text-center space-y-3">
        <div className="w-14 h-14 rounded-2xl bg-amber-500/20 border border-amber-400/30 text-amber-400 flex items-center justify-center mx-auto shadow-inner">
          <Trophy className="h-7 w-7" />
        </div>
        <h1 className="text-3xl sm:text-4xl font-bold font-heading tracking-tight">
          Hall of Fame
        </h1>
        <p className="text-xs text-zinc-300 max-w-md mx-auto leading-relaxed">
          Celebrating the top engineers advancing their craft through technical readings and quiz completions.
        </p>

        {/* Tab Toggle */}
        <div className="inline-flex items-center gap-1 bg-white/10 p-1 rounded-2xl backdrop-blur-md border border-white/10 mt-4">
          <button
            onClick={() => setTab('weekly')}
            className={`px-5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 ${
              tab === 'weekly' ? 'bg-amber-500 text-zinc-950 shadow-md' : 'text-zinc-300 hover:text-white'
            }`}
          >
            <TrendingUp className="h-3.5 w-3.5" /> Weekly Champions
          </button>
          <button
            onClick={() => setTab('lifetime')}
            className={`px-5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 ${
              tab === 'lifetime' ? 'bg-amber-500 text-zinc-950 shadow-md' : 'text-zinc-300 hover:text-white'
            }`}
          >
            <Crown className="h-3.5 w-3.5" /> Lifetime Legends
          </button>
        </div>
      </div>

      {/* Leaderboard Table */}
      <div className="bg-white rounded-2xl border border-zinc-200/80 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5 w-16 text-center">Rank</th>
                <th className="px-6 py-3.5">Developer</th>
                <th className="px-6 py-3.5 text-center">Level</th>
                <th className="px-6 py-3.5 text-center">Daily Streak</th>
                <th className="px-6 py-3.5 text-right">{tab === 'weekly' ? 'Weekly XP' : 'Total XP'}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-100 text-zinc-800">
              {isLoading ? (
                [1, 2, 3, 4, 5].map((n) => (
                  <tr key={n} className="animate-pulse">
                    <td colSpan="5" className="px-6 py-4 h-12 bg-zinc-50"></td>
                  </tr>
                ))
              ) : leaders && leaders.length > 0 ? (
                leaders.map((lead, idx) => {
                  const isCurrent = user?.id === lead.id;
                  const rank = idx + 1;

                  return (
                    <tr
                      key={lead.id}
                      className={`transition ${
                        isCurrent
                          ? 'bg-amber-50/60 font-semibold'
                          : rank <= 3
                          ? 'hover:bg-zinc-50/80'
                          : 'hover:bg-zinc-50/50'
                      }`}
                    >
                      {/* Rank Medal */}
                      <td className="px-6 py-4 text-center font-bold">
                        {rank === 1 ? (
                          <div className="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto shadow-sm">
                            👑
                          </div>
                        ) : rank === 2 ? (
                          <div className="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center mx-auto">
                            🥈
                          </div>
                        ) : rank === 3 ? (
                          <div className="w-7 h-7 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center mx-auto">
                            🥉
                          </div>
                        ) : (
                          <span className="text-zinc-500 font-mono text-xs">#{rank}</span>
                        )}
                      </td>

                      {/* Developer */}
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center overflow-hidden shrink-0">
                            {lead.avatar_url ? (
                              <img src={lead.avatar_url} alt="" className="w-full h-full object-cover" />
                            ) : (
                              <span>{(lead.username || 'U').charAt(0).toUpperCase()}</span>
                            )}
                          </div>
                          <div>
                            <div className="flex items-center gap-1.5">
                              <span className="font-bold text-zinc-900">{lead.username}</span>
                              {lead.is_pro && (
                                <span className="text-[9px] font-bold text-amber-700 bg-amber-100 px-1 rounded">
                                  PRO
                                </span>
                              )}
                              {isCurrent && (
                                <span className="text-[9px] font-bold text-indigo-700 bg-indigo-100 px-1 rounded">
                                  YOU
                                </span>
                              )}
                            </div>
                          </div>
                        </div>
                      </td>

                      {/* Level */}
                      <td className="px-6 py-4 text-center">
                        <span className="bg-indigo-50 text-indigo-700 font-semibold px-2 py-0.5 rounded-full border border-indigo-100 text-[11px]">
                          Lvl {lead.level || 1}
                        </span>
                      </td>

                      {/* Streak */}
                      <td className="px-6 py-4 text-center font-medium text-zinc-600">
                        <span className="inline-flex items-center gap-1">
                          <Flame className="h-3.5 w-3.5 text-amber-500 fill-amber-500" />
                          {lead.daily_streak || 0}d
                        </span>
                      </td>

                      {/* XP Points */}
                      <td className="px-6 py-4 text-right font-extrabold text-amber-600 text-sm">
                        {(tab === 'weekly' ? lead.xp_weekly : lead.xp_total) || 0} XP
                      </td>
                    </tr>
                  );
                })
              ) : (
                <tr>
                  <td colSpan="5" className="px-6 py-12 text-center text-zinc-400">
                    No leaderboard activity recorded yet.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

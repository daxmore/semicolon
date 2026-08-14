import React, { useState } from 'react';
import { useAdminUsers, useGamificationMutations } from '../../hooks/useGamification';
import { useAuth } from '../../contexts/AuthContext';
import { Users, Search, Shield, Award, CheckCircle2, XCircle } from 'lucide-react';

export default function AdminUsers() {
  const [searchTerm, setSearchTerm] = useState('');
  const { user: currentAuthUser } = useAuth();
  const { data: usersList, isLoading } = useAdminUsers();
  const { toggleUserProStatus, changeUserRole } = useGamificationMutations();

  const filtered = usersList?.filter(
    (u) =>
      u.username?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      u.email?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">User Management</h2>
          <p className="text-xs text-zinc-500">View developer accounts, toggle Pro subscriptions, and manage admin privileges.</p>
        </div>
      </div>

      {/* Search toolbar */}
      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
        <div className="relative w-full max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
          <input
            type="text"
            placeholder="Search users by name or email..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
          />
        </div>
        <span className="text-xs text-zinc-500">Registered: <strong>{filtered?.length || 0}</strong> users</span>
      </div>

      {/* Users Table */}
      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Developer</th>
                <th className="px-6 py-3.5">Role</th>
                <th className="px-6 py-3.5">RPG Stats</th>
                <th className="px-6 py-3.5">Pro Status</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-400">
                    Loading users...
                  </td>
                </tr>
              ) : filtered && filtered.length > 0 ? (
                filtered.map((u) => (
                  <tr key={u.id} className="hover:bg-zinc-50/80 transition">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center overflow-hidden shrink-0">
                          {u.avatar_url ? (
                            <img src={u.avatar_url} alt="" className="w-full h-full object-cover" />
                          ) : (
                            <span>{(u.username || 'U').charAt(0).toUpperCase()}</span>
                          )}
                        </div>
                        <div>
                          <p className="font-bold text-zinc-900">{u.username}</p>
                          <p className="text-[11px] text-zinc-400">{u.email}</p>
                        </div>
                      </div>
                    </td>

                    <td className="px-6 py-4">
                      <select
                        value={u.role || 'user'}
                        disabled={u.id === currentAuthUser?.id}
                        onChange={(e) => changeUserRole.mutate({ userId: u.id, role: e.target.value })}
                        className="px-2 py-1 bg-zinc-50 border border-zinc-200 rounded-lg text-xs font-semibold uppercase tracking-wider focus:outline-none"
                      >
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                      </select>
                    </td>

                    <td className="px-6 py-4">
                      <div className="space-y-0.5">
                        <span className="font-bold text-indigo-600">Level {u.level || 1}</span>
                        <span className="text-[11px] text-zinc-500 block">{u.xp_total || 0} XP • {u.daily_streak || 0}d streak</span>
                      </div>
                    </td>

                    <td className="px-6 py-4">
                      {u.is_pro ? (
                        <span className="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full">
                          <CheckCircle2 className="h-3 w-3 text-amber-600" /> PRO
                        </span>
                      ) : (
                        <span className="text-[10px] font-semibold text-zinc-400 bg-zinc-100 px-2 py-0.5 rounded-full">
                          FREE
                        </span>
                      )}
                    </td>

                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => toggleUserProStatus.mutate({ userId: u.id, isPro: u.is_pro })}
                        className={`px-3 py-1 rounded-lg text-xs font-semibold transition ${
                          u.is_pro
                            ? 'bg-rose-50 text-rose-700 hover:bg-rose-100'
                            : 'bg-amber-50 text-amber-700 hover:bg-amber-100'
                        }`}
                      >
                        {u.is_pro ? 'Revoke Pro' : 'Grant Pro'}
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-500">
                    No users matching criteria.
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

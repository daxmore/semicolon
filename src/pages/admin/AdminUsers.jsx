import React, { useState } from 'react';
import { useAdminUsers, useGamificationMutations } from '../../hooks/useGamification';
import { Search, CheckCircle2, ShieldOff, Shield, Trash2, Crown } from 'lucide-react';

export default function AdminUsers() {
  const [searchTerm, setSearchTerm] = useState('');
  const [confirmDelete, setConfirmDelete] = useState(null); // userId to confirm
  const { data: usersList, isLoading } = useAdminUsers();
  const { toggleUserProStatus, banUser, deleteUser } = useGamificationMutations();

  const filtered = usersList
    ?.filter((u) => u.role !== 'admin')
    .filter(
      (u) =>
        u.username?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.email?.toLowerCase().includes(searchTerm.toLowerCase())
    );

  const handleDelete = (userId) => {
    if (confirmDelete === userId) {
      deleteUser.mutate(userId);
      setConfirmDelete(null);
    } else {
      setConfirmDelete(userId);
      // auto-reset after 3s if not confirmed
      setTimeout(() => setConfirmDelete(null), 3000);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">User Management</h2>
          <p className="text-xs text-zinc-500">View user accounts and manage Pro subscriptions, bans, and deletions.</p>
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
                <th className="px-6 py-3.5">User</th>
                <th className="px-6 py-3.5">RPG Stats</th>
                <th className="px-6 py-3.5">Pro Status</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-400">
                    Loading users...
                  </td>
                </tr>
              ) : filtered && filtered.length > 0 ? (
                filtered.map((u) => (
                  <tr key={u.id} className={`hover:bg-zinc-50/80 transition ${u.is_banned ? 'opacity-60' : ''}`}>
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
                          <p className="font-bold text-zinc-900 flex items-center gap-1.5">
                            {u.username}
                            {u.is_banned && (
                              <span className="text-[9px] font-bold text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded-full uppercase tracking-wide">Banned</span>
                            )}
                          </p>
                          <p className="text-[11px] text-zinc-400">{u.email}</p>
                        </div>
                      </div>
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

                    <td className="px-6 py-4">
                      <div className="flex items-center justify-end gap-1">
                        {/* Grant / Revoke Pro */}
                        <button
                          title={u.is_pro ? 'Revoke Pro' : 'Grant Pro'}
                          onClick={() => toggleUserProStatus.mutate({ userId: u.id, isPro: u.is_pro })}
                          className={`p-1.5 rounded-lg transition ${
                            u.is_pro
                              ? 'text-violet-600 bg-violet-50 hover:bg-violet-100'
                              : 'text-zinc-400 bg-zinc-50 hover:bg-violet-50 hover:text-violet-600'
                          }`}
                        >
                          <Crown className="h-3.5 w-3.5" fill={u.is_pro ? 'currentColor' : 'none'} />
                        </button>

                        {/* Ban / Unban */}
                        <button
                          title={u.is_banned ? 'Unban User' : 'Ban User'}
                          onClick={() => banUser.mutate({ userId: u.id, isBanned: u.is_banned })}
                          className={`p-1.5 rounded-lg transition ${
                            u.is_banned
                              ? 'text-green-600 bg-green-50 hover:bg-green-100'
                              : 'text-orange-500 bg-orange-50 hover:bg-orange-100'
                          }`}
                        >
                          {u.is_banned
                            ? <Shield className="h-3.5 w-3.5" />
                            : <ShieldOff className="h-3.5 w-3.5" />
                          }
                        </button>

                        {/* Delete */}
                        <button
                          title={confirmDelete === u.id ? 'Click again to confirm delete' : 'Delete User'}
                          onClick={() => handleDelete(u.id)}
                          className={`p-1.5 rounded-lg transition ${
                            confirmDelete === u.id
                              ? 'text-white bg-red-500 hover:bg-red-600 animate-pulse'
                              : 'text-red-400 bg-red-50 hover:bg-red-100'
                          }`}
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-500">
                    No users found.
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

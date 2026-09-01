import React, { useState } from 'react';
import { useAdminUsers, useGamificationMutations } from '../../hooks/useGamification';
import { calculateLevel } from '../../lib/utils';
import { Search, ShieldOff, Shield, Trash2, Zap, X } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

export default function AdminUsers() {
  const [searchTerm, setSearchTerm] = useState('');
  const [userToDelete, setUserToDelete] = useState(null); // user object to confirm deletion
  const [userToEditXp, setUserToEditXp] = useState(null); // user object for XP edit modal
  const [newXpTotal, setNewXpTotal] = useState(0);

  const { data: usersList, isLoading } = useAdminUsers();
  const { banUser, deleteUser, updateUserXp } = useGamificationMutations();

  const filtered = usersList
    ?.filter(
      (u) =>
        u.username?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.email?.toLowerCase().includes(searchTerm.toLowerCase())
    );

  const handleOpenEditXp = (user) => {
    setUserToEditXp(user);
    setNewXpTotal(user.xp_total || 0);
  };

  const handleSaveXp = async (e) => {
    e.preventDefault();
    if (!userToEditXp) return;
    const total = parseInt(newXpTotal, 10) || 0;
    const newLvl = calculateLevel(total);

    try {
      await updateUserXp.mutateAsync({
        userId: userToEditXp.id,
        xpTotal: total,
        xpWeekly: userToEditXp.xp_weekly ?? total,
        level: newLvl,
      });
      setUserToEditXp(null);
    } catch (err) {
      console.error('Failed to update XP:', err);
    }
  };

  const confirmDeleteUser = async () => {
    if (!userToDelete) return;
    try {
      await deleteUser.mutateAsync(userToDelete.id);
      setUserToDelete(null);
    } catch (err) {
      console.error('Failed to delete user:', err);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">User Management</h2>
          <p className="text-xs text-zinc-500">View user accounts and manage user levels, bans, and deletions.</p>
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
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="3" className="px-6 py-8 text-center text-zinc-400">
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
                      <div className="flex items-center justify-end gap-1">
                        {/* Edit XP Points */}
                        <button
                          title="Edit XP Points & Level"
                          onClick={() => handleOpenEditXp(u)}
                          className="p-1.5 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 transition cursor-pointer"
                        >
                          <Zap className="h-3.5 w-3.5 fill-amber-500 text-amber-500" />
                        </button>

                        {/* Ban / Unban */}
                        <button
                          title={u.is_banned ? 'Unban User' : 'Ban User'}
                          onClick={() => banUser.mutate({ userId: u.id, isBanned: u.is_banned })}
                          className={`p-1.5 rounded-lg transition cursor-pointer ${
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
                          title="Delete User"
                          onClick={() => setUserToDelete(u)}
                          className="p-1.5 rounded-lg text-red-400 bg-red-50 hover:bg-red-100 hover:text-red-600 transition cursor-pointer"
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

      {/* Edit XP Modal */}
      {userToEditXp && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                  <Zap className="h-4 w-4 fill-amber-500" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-900">Edit User XP & Stats</h3>
                  <p className="text-[11px] text-zinc-400">{userToEditXp.username} ({userToEditXp.email})</p>
                </div>
              </div>
              <button onClick={() => setUserToEditXp(null)} className="p-1 text-zinc-400 hover:text-zinc-600">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSaveXp} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">
                  Total XP Points
                </label>
                <input
                  type="number"
                  min="0"
                  required
                  value={newXpTotal}
                  onChange={(e) => setNewXpTotal(e.target.value)}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 font-mono text-sm font-bold text-zinc-900"
                />
                <p className="text-[11px] text-indigo-600 font-semibold mt-1.5">
                  Calculated Level: Level {calculateLevel(parseInt(newXpTotal, 10) || 0)}
                </p>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setUserToEditXp(null)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 font-semibold hover:bg-zinc-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={updateUserXp.isPending}
                  className="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer flex items-center gap-1.5"
                >
                  <Zap className="h-3.5 w-3.5 fill-white" />
                  {updateUserXp.isPending ? 'Saving...' : 'Save XP'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete User Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!userToDelete}
        onClose={() => setUserToDelete(null)}
        onConfirm={confirmDeleteUser}
        title="Delete User Account"
        itemName={userToDelete ? `${userToDelete.username} (${userToDelete.email})` : ''}
        message="Are you sure you want to permanently delete this user account? All their profile data, XP, activity history, and comments will be wiped."
        isLoading={deleteUser.isPending}
      />
    </div>
  );
}


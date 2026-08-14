import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useBadges, useUserBadges, useGamificationMutations } from '../../hooks/useGamification';
import { axiosClient } from '../../lib/axiosClient';
import { 
  User, 
  Award, 
  Flame, 
  Trophy, 
  ShieldCheck, 
  Camera, 
  Check, 
  Lock, 
  CheckCircle2, 
  AlertCircle,
  KeyRound
} from 'lucide-react';

export default function Profile() {
  const { user, profile, isPro, refreshProfile } = useAuth();
  const { data: allBadges } = useBadges();
  const { data: userBadges } = useUserBadges(user?.id);
  const { toggleEquipBadge } = useGamificationMutations();

  const [activeTab, setActiveTab] = useState('profile'); // 'profile' | 'badges' | 'security'
  const [username, setUsername] = useState(profile?.username || '');
  const [avatarUrl, setAvatarUrl] = useState(profile?.avatar_url || '');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState('');
  const [errorMsg, setErrorMsg] = useState('');

  const unlockedBadgeIds = userBadges?.map((ub) => ub.badge_id) || [];
  const equippedUserBadge = userBadges?.find((ub) => ub.is_equipped);

  const handleProfileUpdate = async (e) => {
    e.preventDefault();
    if (!user) return;

    try {
      setSaving(true);
      setErrorMsg('');
      setToast('');

      // Check username if changed
      if (username !== profile?.username) {
        const { data: exist } = await axiosClient.get(
          `/rest/v1/profiles?username=eq.${encodeURIComponent(username)}&id=neq.${user.id}&select=id`
        );
        if (exist && exist.length > 0) {
          setErrorMsg('Username is already taken.');
          setSaving(false);
          return;
        }
      }

      await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, {
        username,
        avatar_url: avatarUrl || null,
      });

      await refreshProfile();
      setToast('Profile updated successfully!');
      setTimeout(() => setToast(''), 3000);
    } catch (err) {
      console.error(err);
      setErrorMsg(err.message || 'Failed to update profile.');
    } finally {
      setSaving(false);
    }
  };

  const handleEquipBadge = (userBadge) => {
    toggleEquipBadge.mutate({
      userBadgeId: userBadge.id,
      isEquipped: userBadge.is_equipped,
      userId: user.id,
    });
  };

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      {/* Header Profile Card */}
      <div className="bg-white rounded-3xl border border-zinc-200/80 p-8 shadow-sm flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden">
        {/* Avatar */}
        <div className="relative">
          <div className="w-24 h-24 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-extrabold text-3xl overflow-hidden shadow-lg shadow-indigo-600/20 border-2 border-white">
            {profile?.avatar_url ? (
              <img src={profile.avatar_url} alt="Avatar" className="w-full h-full object-cover" />
            ) : (
              <span>{(profile?.username || 'U').charAt(0).toUpperCase()}</span>
            )}
          </div>
          {isPro && (
            <span className="absolute -bottom-2 -right-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-black text-[10px] px-2 py-0.5 rounded-full shadow-md border border-white">
              PRO
            </span>
          )}
        </div>

        {/* User Info */}
        <div className="flex-1 text-center md:text-left space-y-2">
          <div className="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
            <h1 className="text-2xl font-bold font-heading text-zinc-900 tracking-tight">
              {profile?.username || 'Developer'}
            </h1>
            <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
              Level {profile?.level || 1}
            </span>
          </div>

          <p className="text-xs text-zinc-500">{user?.email}</p>

          {/* Gamification Stats */}
          <div className="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2 text-xs">
            <div className="flex items-center gap-1.5 font-semibold text-zinc-700">
              <Flame className="h-4 w-4 text-amber-500 fill-amber-500" />
              <span>{profile?.daily_streak || 0} Day Streak</span>
            </div>
            <div className="flex items-center gap-1.5 font-semibold text-zinc-700">
              <Trophy className="h-4 w-4 text-indigo-600" />
              <span>{profile?.xp_total || 0} Total XP</span>
            </div>
            <div className="flex items-center gap-1.5 font-semibold text-zinc-700">
              <Award className="h-4 w-4 text-amber-500" />
              <span>{userBadges?.length || 0} Badges Earned</span>
            </div>
          </div>
        </div>
      </div>

      {toast && (
        <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-2">
          <CheckCircle2 className="h-4 w-4 shrink-0" />
          <span>{toast}</span>
        </div>
      )}

      {errorMsg && (
        <div className="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle className="h-4 w-4 shrink-0" />
          <span>{errorMsg}</span>
        </div>
      )}

      {/* Tabs */}
      <div className="flex items-center gap-2 border-b border-zinc-200 pb-2">
        <button
          onClick={() => setActiveTab('profile')}
          className={`px-4 py-2 rounded-xl text-xs font-semibold transition ${
            activeTab === 'profile'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-zinc-600 hover:bg-zinc-100'
          }`}
        >
          Profile Settings
        </button>
        <button
          onClick={() => setActiveTab('badges')}
          className={`px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 ${
            activeTab === 'badges'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-zinc-600 hover:bg-zinc-100'
          }`}
        >
          <Award className="h-3.5 w-3.5" /> Badges & Achievements
        </button>
      </div>

      {/* Tab 1: Profile Settings */}
      {activeTab === 'profile' && (
        <div className="bg-white rounded-2xl border border-zinc-200/80 p-6 sm:p-8 shadow-sm max-w-xl space-y-6">
          <h2 className="text-sm font-bold uppercase tracking-wider text-zinc-900">
            Account Information
          </h2>

          <form onSubmit={handleProfileUpdate} className="space-y-4 text-xs">
            <div>
              <label className="block font-semibold text-zinc-700 mb-1.5">Username</label>
              <input
                type="text"
                required
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full px-3.5 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
              />
            </div>

            <div>
              <label className="block font-semibold text-zinc-700 mb-1.5">Avatar Image URL</label>
              <input
                type="url"
                value={avatarUrl}
                onChange={(e) => setAvatarUrl(e.target.value)}
                placeholder="https://images.unsplash.com/photo-..."
                className="w-full px-3.5 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
              />
            </div>

            <div>
              <label className="block font-semibold text-zinc-700 mb-1.5">Email (Read Only)</label>
              <input
                type="email"
                disabled
                value={user?.email || ''}
                className="w-full px-3.5 py-2.5 bg-zinc-100 border border-zinc-200 rounded-xl text-zinc-500 cursor-not-allowed"
              />
            </div>

            <div className="pt-4 border-t border-zinc-100 flex justify-end">
              <button
                type="submit"
                disabled={saving}
                className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl font-semibold shadow-md shadow-indigo-500/20 transition"
              >
                {saving ? 'Saving...' : 'Save Profile'}
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Tab 2: Badges Grid */}
      {activeTab === 'badges' && (
        <div className="space-y-6">
          <div>
            <h2 className="text-base font-bold font-heading text-zinc-900">Gamification Badges</h2>
            <p className="text-xs text-zinc-500">
              Badges unlock automatically when you achieve total XP thresholds or maintain continuous login streaks.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {allBadges?.map((badge) => {
              const isUnlocked = unlockedBadgeIds.includes(badge.id);
              const userBadge = userBadges?.find((ub) => ub.badge_id === badge.id);
              const isEquipped = userBadge?.is_equipped;

              return (
                <div
                  key={badge.id}
                  className={`p-6 rounded-2xl border transition flex flex-col justify-between ${
                    isUnlocked
                      ? isEquipped
                        ? 'bg-gradient-to-b from-indigo-50/70 to-white border-indigo-500 shadow-md shadow-indigo-500/10'
                        : 'bg-white border-zinc-200 shadow-sm'
                      : 'bg-zinc-50/70 border-zinc-200/60 opacity-60'
                  }`}
                >
                  <div className="space-y-4">
                    <div className="flex items-center justify-between">
                      <div className="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 shrink-0">
                        {badge.svg_icon ? (
                          <div
                            dangerouslySetInnerHTML={{ __html: badge.svg_icon }}
                            className="w-6 h-6"
                          />
                        ) : (
                          <Award className="h-6 w-6" />
                        )}
                      </div>

                      {isUnlocked ? (
                        <span className="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                          Unlocked
                        </span>
                      ) : (
                        <span className="text-[10px] font-bold text-zinc-500 bg-zinc-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                          <Lock className="h-3 w-3" /> {badge.required_xp} XP
                        </span>
                      )}
                    </div>

                    <div className="space-y-1">
                      <h3 className="font-bold text-sm text-zinc-900">{badge.badge_name}</h3>
                      <p className="text-xs text-zinc-500 leading-relaxed">
                        {badge.description || `Earn at least ${badge.required_xp} XP to unlock.`}
                      </p>
                    </div>
                  </div>

                  <div className="pt-4 mt-4 border-t border-zinc-100">
                    {isUnlocked ? (
                      <button
                        onClick={() => handleEquipBadge(userBadge)}
                        className={`w-full py-2 px-3 rounded-xl text-xs font-semibold transition ${
                          isEquipped
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200'
                        }`}
                      >
                        {isEquipped ? 'Equipped Badge' : 'Equip on Profile'}
                      </button>
                    ) : (
                      <div className="w-full py-2 px-3 text-center text-xs text-zinc-400 font-medium">
                        Requires {badge.required_xp} Total XP
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}

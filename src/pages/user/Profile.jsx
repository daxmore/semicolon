import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import {
  useUserHistory,
  useBadges,
  useUserBadges,
  useGamificationMutations,
} from '../../hooks/useGamification';
import { useNotifications } from '../../hooks/useNotifications';
import { useCommunityPosts } from '../../hooks/useCommunity';
import { axiosClient } from '../../lib/axiosClient';
import {
  BookOpen,
  FileText,
  Video,
  MessageSquare,
  Award,
  Flame,
  Check,
  X,
  ChevronRight,
  Bell,
} from 'lucide-react';

export default function Profile() {
  const { user, profile, isPro, signOut, refreshProfile } = useAuth();
  const navigate = useNavigate();

  const { data: history } = useUserHistory(user?.id);
  const { data: badges } = useBadges(); // user's earned badges (equivalent to $badges in PHP)
  const { data: userPosts } = useCommunityPosts({ authorId: user?.id });
  const { data: notifications } = useNotifications ? useNotifications(user?.id) : { data: [] };
  const { toggleEquipBadge } = useGamificationMutations();

  // Edit Modal State
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [cancelModalOpen, setCancelModalOpen] = useState(false);
  const [username, setUsername] = useState(profile?.username || '');
  const [avatarUrl, setAvatarUrl] = useState(profile?.avatar_url || '');
  const [avatarFile, setAvatarFile] = useState(null);
  const [securityQuestion, setSecurityQuestion] = useState(profile?.security_question || '');
  const [securityAnswer, setSecurityAnswer] = useState('');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ text: '', type: '' });
  const [avatarImgError, setAvatarImgError] = useState(false);

  useEffect(() => {
    if (profile) {
      setUsername(profile.username || '');
      setAvatarUrl(profile.avatar_url || '');
      setSecurityQuestion(profile.security_question || '');
    }
  }, [profile]);

  const handleLogout = async () => {
    await signOut();
    navigate('/login');
  };

  const handleSaveProfile = async (e) => {
    e.preventDefault();
    if (!user) return;

    if (newPassword && newPassword !== confirmPassword) {
      setMessage({ text: 'New passwords do not match.', type: 'error' });
      return;
    }

    try {
      setSaving(true);
      setMessage({ text: '', type: '' });

      if (username !== profile?.username) {
        const { data: exist } = await axiosClient.get(
          `/rest/v1/profiles?username=eq.${encodeURIComponent(username)}&id=neq.${user.id}&select=id`
        );
        if (exist && exist.length > 0) {
          setMessage({ text: 'Username is already taken.', type: 'error' });
          setSaving(false);
          return;
        }
      }

      let finalAvatarUrl = avatarUrl || null;

      // If user selected a file to upload, read as Base64 Data URL
      if (avatarFile) {
        finalAvatarUrl = await new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = (event) => resolve(event.target?.result);
          reader.onerror = (error) => reject(error);
          reader.readAsDataURL(avatarFile);
        });
      }

      const payload = {
        username,
        avatar_url: finalAvatarUrl,
      };
      if (securityQuestion) payload.security_question = securityQuestion;
      if (securityAnswer) payload.security_answer = securityAnswer;
      if (newPassword) payload.password = newPassword; // hashing handled server-side

      await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, payload);

      setAvatarImgError(false);
      await refreshProfile();
      setMessage({ text: 'Profile updated successfully!', type: 'success' });
      setTimeout(() => {
        setEditModalOpen(false);
        setMessage({ text: '', type: '' });
      }, 1200);
    } catch (err) {
      console.error(err);
      setMessage({ text: err.message || 'Failed to update profile.', type: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const handleEquipBadge = (badge) => {
    toggleEquipBadge.mutate({
      userBadgeId: badge.id,
      isEquipped: badge.is_equipped,
      userId: user.id,
    });
  };

  const displayUsername = profile?.username || user?.email || 'Learner';
  const xpTotal = profile?.xp_total || 0;
  const level = profile?.level || 1;

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      {/* Profile Hero */}
      <section className="relative pt-10 pb-8 overflow-hidden isolate">
        <div className="absolute inset-0 -z-10">
          <div className="absolute top-0 left-1/4 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 right-1/4 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl"></div>
        </div>

        <div className="container mx-auto px-6">
          {/* Alert Message */}
          {message.text && (
            <div
              className={`rounded-2xl p-4 mb-6 ${message.type === 'success'
                ? 'bg-green-50 text-green-700 border border-green-200'
                : 'bg-red-50 text-red-700 border border-red-200'
                }`}
            >
              <div className="flex items-center gap-3">
                {message.type === 'success' ? (
                  <Check className="h-5 w-5 flex-shrink-0" />
                ) : (
                  <X className="h-5 w-5 flex-shrink-0" />
                )}
                <p className="font-medium">{message.text}</p>
              </div>
            </div>
          )}

          <div className="bg-white rounded-2xl border border-zinc-100 p-8">
            <div className="flex flex-col md:flex-row items-center justify-between gap-6">
              <div className="flex flex-col md:flex-row items-center gap-5 text-center md:text-left">
                <div className="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-3xl font-bold text-white shadow-lg shadow-indigo-500/25 overflow-hidden flex-shrink-0">
                  {profile?.avatar_url ? (
                    <img
                      src={profile.avatar_url}
                      alt=""
                      onError={(e) => { e.currentTarget.style.display = 'none'; }}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <span>{displayUsername.charAt(0).toUpperCase()}</span>
                  )}
                </div>
                <div>
                  <h1 className="text-3xl font-bold text-zinc-900">{displayUsername}</h1>
                  <div className="flex items-center justify-center md:justify-start gap-3 mt-2 flex-wrap">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                      <span className="w-2 h-2 rounded-full bg-green-500"></span>
                      {profile?.role ? profile.role.charAt(0).toUpperCase() + profile.role.slice(1) : 'User'}
                    </span>
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm font-bold border border-amber-200">
                      <Award className="h-4 w-4" />
                      Level {level} ({xpTotal.toLocaleString()} XP)
                    </span>
                    <span className="text-zinc-500 text-sm">
                      Member since{' '}
                      {new Date(profile?.created_at || Date.now()).toLocaleDateString('en-US', {
                        month: 'short',
                        year: 'numeric',
                      })}
                    </span>
                  </div>
                </div>
              </div>
              <div className="flex gap-3">
                <button
                  onClick={handleLogout}
                  className="px-5 py-2.5 border border-zinc-200 rounded-xl text-zinc-700 hover:bg-zinc-50 transition font-medium cursor-pointer"
                >
                  Logout
                </button>
                <button
                  onClick={() => setEditModalOpen(true)}
                  className="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium flex items-center gap-2 cursor-pointer"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Profile
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <section className="pb-20">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Main Column */}
            <div className="lg:col-span-2 space-y-6">
              {/* Quick Actions */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Link to="/books" className="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-indigo-200 hover:shadow-lg transition-all group">
                  <div className="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <BookOpen className="h-6 w-6 text-indigo-600" />
                  </div>
                  <h3 className="font-bold text-zinc-900">Browse Books</h3>
                  <p className="text-sm text-zinc-500 mt-1">Access library</p>
                </Link>

                <Link to="/papers" className="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-teal-200 hover:shadow-lg transition-all group">
                  <div className="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <FileText className="h-6 w-6 text-teal-600" />
                  </div>
                  <h3 className="font-bold text-zinc-900">Read Papers</h3>
                  <p className="text-sm text-zinc-500 mt-1">Latest research</p>
                </Link>

                <Link to="/videos" className="bg-white rounded-2xl border border-zinc-100 p-6 hover:border-rose-200 hover:shadow-lg transition-all group">
                  <div className="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <Video className="h-6 w-6 text-rose-600" />
                  </div>
                  <h3 className="font-bold text-zinc-900">Watch Videos</h3>
                  <p className="text-sm text-zinc-500 mt-1">Tutorials & more</p>
                </Link>
              </div>

              {/* Side-by-Side Activity Grid: Recently Viewed & My Discussions */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {/* Recently Viewed */}
                <div className="bg-white rounded-2xl border border-zinc-100 overflow-hidden">
                  <div className="p-6 border-b border-zinc-100">
                    <h3 className="text-lg font-bold text-zinc-900 flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Recently Viewed
                    </h3>
                  </div>

                  {!history || history.length === 0 ? (
                    <div className="p-12 text-center">
                      <div className="w-16 h-16 bg-zinc-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <BookOpen className="h-8 w-8 text-zinc-400" />
                      </div>
                      <p className="text-zinc-500 mb-2">No activity yet</p>
                      <Link to="/books" className="text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                        Start exploring resources →
                      </Link>
                    </div>
                  ) : (
                    <div className="divide-y divide-zinc-100">
                      {history.slice(0, 5).map((item, idx) => {
                        const iconBg =
                          item.resource_type === 'book'
                            ? 'bg-indigo-100 text-indigo-600'
                            : item.resource_type === 'paper'
                              ? 'bg-teal-100 text-teal-600'
                              : 'bg-rose-100 text-rose-600';
                        const link =
                          item.resource_type === 'book'
                            ? `/books/${item.resource_id}`
                            : item.resource_type === 'paper'
                              ? `/papers/${item.resource_id}`
                              : `/videos/${item.resource_id}`;
                        return (
                          <Link key={idx} to={link} className="flex items-center gap-4 p-4 hover:bg-zinc-50 transition">
                            <div className={`w-10 h-10 ${iconBg} rounded-xl flex items-center justify-center flex-shrink-0`}>
                              {item.resource_type === 'book' ? (
                                <BookOpen className="h-5 w-5" />
                              ) : item.resource_type === 'paper' ? (
                                <FileText className="h-5 w-5" />
                              ) : (
                                <Video className="h-5 w-5" />
                              )}
                            </div>
                            <div className="flex-1 min-w-0">
                              <p className="font-medium text-zinc-900 truncate">
                                {item.title || 'Unknown Resource'}
                              </p>
                              <p className="text-sm text-zinc-500">
                                {item.resource_type.charAt(0).toUpperCase() + item.resource_type.slice(1)} •{' '}
                                {new Date(item.viewed_at).toLocaleDateString('en-US', {
                                  month: 'short',
                                  day: 'numeric',
                                  year: 'numeric',
                                })}
                              </p>
                            </div>
                            <ChevronRight className="h-5 w-5 text-zinc-400" />
                          </Link>
                        );
                      })}
                    </div>
                  )}
                </div>

                {/* My Discussions */}
                <div className="bg-white rounded-2xl border border-zinc-100 overflow-hidden">
                  <div className="p-6 border-b border-zinc-100 flex items-center justify-between">
                    <h3 className="text-lg font-bold text-zinc-900 flex items-center gap-2">
                      <MessageSquare className="h-5 w-5 text-amber-500" />
                      My Discussions
                    </h3>
                    <div className="flex gap-4">
                      <Link to="/community" className="text-sm font-medium text-amber-600 hover:text-amber-700 transition">
                        Start New →
                      </Link>
                    </div>
                  </div>

                  {!userPosts || userPosts.length === 0 ? (
                    <div className="p-10 text-center">
                      <p className="text-zinc-500 mb-2">You haven't started any discussions yet.</p>
                      <Link to="/community" className="text-amber-600 hover:text-amber-700 font-medium text-sm">
                        Browse the community →
                      </Link>
                    </div>
                  ) : (
                    <>
                      <div className="divide-y divide-zinc-100">
                        {userPosts.slice(0, 5).map((post) => (
                          <Link key={post.id} to={`/community/post/${post.id}`} className="block p-5 hover:bg-zinc-50 transition">
                            <div className="flex items-center gap-2 mb-1">
                              <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-600">
                                {post.category}
                              </span>
                              <span className="text-xs text-zinc-400">
                                {new Date(post.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </span>
                            </div>
                            <h4 className="font-bold text-zinc-900 mb-1 truncate">{post.title}</h4>
                            <div className="flex items-center gap-4 text-xs text-zinc-500 font-medium mt-2">
                              <span className="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                                {post.upvotes || 0}
                              </span>
                            </div>
                          </Link>
                        ))}
                      </div>
                      <div className="p-4 border-t border-zinc-100 text-center">
                        <Link to="/community" className="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition">
                          View all community posts
                        </Link>
                      </div>
                    </>
                  )}
                </div>
              </div>

              {/* Badges Collection */}
              <div id="badges" className="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden relative mt-6">
                <div
                  className="absolute inset-0 opacity-50"
                  style={{
                    backgroundImage:
                      "url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')",
                  }}
                ></div>

                <div className="p-6 border-b border-slate-800 relative z-10 flex items-center justify-between">
                  <h3 className="text-xl font-bold text-white flex items-center gap-2">
                    <Award className="h-6 w-6 text-amber-500" />
                    My Badges Collection
                  </h3>
                </div>

                <div className="p-8 relative z-10">
                  {!badges || badges.length === 0 ? (
                    <div className="text-center py-6">
                      <div className="w-16 h-16 mx-auto bg-slate-800 rounded-full flex items-center justify-center mb-4 border border-slate-700">
                        <Award className="h-8 w-8 text-slate-500" />
                      </div>
                      <p className="text-slate-400">
                        No badges yet. we’re still building something awesome!
                      </p>
                    </div>
                  ) : (
                    <div className="flex flex-wrap gap-6">
                      {badges.map((badge) => (
                        <div
                          key={badge.id}
                          className="group relative flex flex-col items-center cursor-pointer"
                          onClick={() => handleEquipBadge(badge)}
                        >
                          {/* Tooltip */}
                          <div className="absolute bottom-full mb-2 hidden group-hover:block w-48 p-2 bg-slate-800 text-xs text-slate-200 text-center rounded-lg border border-slate-700 shadow-xl z-20">
                            <strong className="block text-amber-400 mb-1">{badge.badge_name}</strong>
                            {badge.description}
                          </div>

                          {/* Badge Icon */}
                          <div
                            className={`w-20 h-20 rounded-2xl bg-slate-800 border-2 ${badge.is_equipped
                              ? 'border-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.4)]'
                              : 'border-slate-700 hover:border-slate-500'
                              } flex items-center justify-center transition-all hover:scale-105 relative overflow-hidden`}
                          >
                            {badge.is_equipped && <div className="absolute inset-0 bg-amber-500/10"></div>}
                            <div
                              className="w-12 h-12 relative z-10 flex items-center justify-center text-2xl"
                              dangerouslySetInnerHTML={badge.svg_icon ? { __html: badge.svg_icon } : undefined}
                            >
                              {!badge.svg_icon && (badge.icon || '🏆')}
                            </div>
                          </div>

                          {/* Badge Name */}
                          <span className="mt-3 text-xs font-semibold text-slate-300 text-center max-w-[5rem] leading-tight">
                            {badge.badge_name}
                            {badge.is_equipped && (
                              <span className="block text-[10px] text-amber-500 mt-1 uppercase tracking-wider">
                                Equipped
                              </span>
                            )}
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Notifications */}
              <div id="notifications" className="bg-white rounded-2xl border border-zinc-100 p-6">
                <h3 className="text-lg font-bold text-zinc-900 mb-4 flex items-center gap-2">
                  <Bell className="h-5 w-5 text-amber-500" />
                  Notifications
                </h3>
                {!notifications || notifications.length === 0 ? (
                  <p className="text-zinc-500 text-sm">No new notifications.</p>
                ) : (
                  <>
                    <ul className="space-y-3">
                      {notifications.slice(0, 5).map((notif) => (
                        <li
                          key={notif.id}
                          className={`p-3 rounded-xl bg-zinc-50 ${!notif.is_read ? 'border-l-2 border-l-indigo-500' : ''}`}
                        >
                          <p className="text-sm font-medium text-zinc-900">{notif.title}</p>
                          <p className="text-xs text-zinc-500 mt-1 line-clamp-1">{notif.message}</p>
                        </li>
                      ))}
                    </ul>
                    <div className="mt-4 pt-4 border-t border-zinc-100 text-center">
                      <Link to="/notifications" className="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">
                        View all activity →
                      </Link>
                    </div>
                  </>
                )}
              </div>

              {/* Account Status */}
              <div className="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <h3 className="font-bold mb-4 text-white flex items-center gap-2">
                  <Award className="h-5 w-5" />
                  Account Status
                </h3>
                <div className="space-y-4 text-sm mt-2">
                  <div className="flex justify-between items-center py-2 border-b border-white/10">
                    <span className="text-indigo-200">Current Plan</span>
                    <span className="font-bold flex items-center gap-1.5">
                      {isPro ? (
                        <>
                          <span className="w-2 h-2 rounded-full bg-indigo-300 animate-pulse"></span>
                          Semicolon Pro
                        </>
                      ) : (
                        'Free Tier'
                      )}
                    </span>
                  </div>
                  <div className="flex justify-between items-center py-2 border-b border-white/10">
                    <span className="text-indigo-200">Account Status</span>
                    <span className="font-medium px-2 py-0.5 bg-white/10 rounded-md">
                      {profile?.status ? profile.status.charAt(0).toUpperCase() + profile.status.slice(1) : 'Active'}
                    </span>
                  </div>
                </div>

                {isPro ? (
                  <button
                    onClick={() => setCancelModalOpen(true)}
                    className="block w-full text-center py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-xl mt-6 transition backdrop-blur-sm cursor-pointer"
                  >
                    Manage Subscription
                  </button>
                ) : (
                  <Link
                    to="/pricing"
                    className="block w-full text-center py-3 bg-white text-indigo-600 font-bold rounded-xl mt-6 hover:bg-indigo-50 transition shadow-md"
                  >
                    Upgrade to Pro
                  </Link>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Edit Profile Modal */}
      {editModalOpen && (
        <div className="fixed inset-0 z-50">
          <div
            className="absolute inset-0 bg-black/50 backdrop-blur-sm"
            onClick={() => setEditModalOpen(false)}
          ></div>
          <div className="relative flex items-center justify-center min-h-screen p-4">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md lg:max-w-lg relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
              {/* Modal Header */}
              <div className="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 flex-shrink-0">
                <div className="flex items-center justify-between">
                  <h2 className="text-xl font-bold text-white">Edit Profile</h2>
                  <button onClick={() => setEditModalOpen(false)} className="text-white/70 hover:text-white transition cursor-pointer">
                    <X className="h-6 w-6" />
                  </button>
                </div>
              </div>

              {/* Modal Body */}
              <form onSubmit={handleSaveProfile} className="p-6 space-y-5 overflow-y-auto flex-1">
                {message.text && (
                  <div
                    className={`p-3 rounded-xl text-xs font-semibold ${message.type === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                      }`}
                  >
                    {message.text}
                  </div>
                )}

                {/* Username */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Username</label>
                  <input
                    type="text"
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                    required
                    minLength={3}
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                </div>

                {/* Avatar Upload */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Profile Image (Upload, max 1MB)</label>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => setAvatarFile(e.target.files?.[0] || null)}
                    className="w-full px-4 py-2 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 transition file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                  />
                </div>

                {/* Avatar URL */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Or Image URL</label>
                  <input
                    type="url"
                    value={avatarUrl}
                    onChange={(e) => setAvatarUrl(e.target.value)}
                    placeholder="https://example.com/avatar.jpg"
                    className="w-full px-4 py-2 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                </div>

                {/* Divider */}
                <div className="relative">
                  <div className="absolute inset-0 flex items-center">
                    <div className="w-full border-t border-zinc-200"></div>
                  </div>
                  <div className="relative flex justify-center text-sm">
                    <span className="bg-white px-3 text-zinc-500">Recovery Settings</span>
                  </div>
                </div>

                {/* Security Question */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Security Question</label>
                  <select
                    value={securityQuestion}
                    onChange={(e) => setSecurityQuestion(e.target.value)}
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  >
                    <option value="">Select a security question</option>
                    <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    <option value="In what city were you born?">In what city were you born?</option>
                    <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
                  </select>
                </div>

                {/* Security Answer */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Security Answer</label>
                  <input
                    type="text"
                    value={securityAnswer}
                    onChange={(e) => setSecurityAnswer(e.target.value)}
                    placeholder={profile?.security_answer ? '•••••••• (Hidden for security)' : 'Enter your answer'}
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                </div>

                {/* Divider */}
                <div className="relative">
                  <div className="absolute inset-0 flex items-center">
                    <div className="w-full border-t border-zinc-200"></div>
                  </div>
                  <div className="relative flex justify-center text-sm">
                    <span className="bg-white px-3 text-zinc-500">Change Password (optional)</span>
                  </div>
                </div>

                {/* Current Password */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Current Password</label>
                  <input
                    type="password"
                    value={currentPassword}
                    onChange={(e) => setCurrentPassword(e.target.value)}
                    placeholder="Enter your current password"
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                  <p className="text-xs text-zinc-500 mt-1">Only required if you are changing your password</p>
                </div>

                {/* New Password */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">New Password</label>
                  <input
                    type="password"
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    minLength={6}
                    placeholder="Leave blank to keep current"
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                </div>

                {/* Confirm New Password */}
                <div>
                  <label className="block text-sm font-semibold text-zinc-700 mb-2">Confirm New Password</label>
                  <input
                    type="password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    placeholder="Repeat new password"
                    className="w-full px-4 py-3 border-2 border-zinc-200 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                  />
                </div>

                {/* Submit Buttons */}
                <div className="flex gap-3 pt-2">
                  <button
                    type="button"
                    onClick={() => setEditModalOpen(false)}
                    className="flex-1 px-4 py-3 border-2 border-zinc-200 text-zinc-700 font-semibold rounded-xl hover:bg-zinc-50 transition cursor-pointer"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={saving}
                    className="flex-1 px-4 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition disabled:opacity-50 cursor-pointer"
                  >
                    {saving ? 'Saving...' : 'Save Changes'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Cancellation Modal */}
      {cancelModalOpen && (
        <div className="fixed inset-0 z-[60]">
          <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={() => setCancelModalOpen(false)}></div>
          <div className="relative flex items-center justify-center min-h-screen p-4">
            <div className="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
              <div className="p-8 text-center">
                <div className="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>

                <h3 className="text-2xl font-bold mb-2">Giving up on Pro?</h3>
                <p className="text-zinc-500 mb-8 px-4">
                  Your skills won't grow themselves… but we respect the confidence. Ready to go back to basic? 😉
                </p>

                <div className="flex flex-col gap-3">
                  <Link
                    to="/cancel-subscription"
                    className="w-full py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl transition shadow-lg shadow-rose-200"
                  >
                    Yes, Cancel My Plan
                  </Link>
                  <button
                    onClick={() => setCancelModalOpen(false)}
                    className="w-full py-4 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold rounded-2xl transition cursor-pointer"
                  >
                    No, Keep Pro Benefits
                  </button>
                </div>
              </div>
              <div className="bg-zinc-50 py-4 px-8 border-t border-zinc-100">
                <p className="text-[10px] text-zinc-400 text-center uppercase tracking-widest font-bold">
                  You will lose access to all premium downloads & resources instantly.
                </p>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
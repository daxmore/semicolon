import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useUserHistory } from '../../hooks/useGamification';
import { useBooks } from '../../hooks/useBooks';
import { usePapers } from '../../hooks/usePapers';
import { useVideos } from '../../hooks/useVideos';
import { BookOpen, FileText, Video, Sparkles, ArrowRight, Flame } from 'lucide-react';

export default function Dashboard() {
  const { profile, user, isPro, signOut } = useAuth();
  const navigate = useNavigate();
  const { data: history } = useUserHistory(user?.id);
  const { data: books } = useBooks();
  const { data: papers } = usePapers();
  const { data: videos } = useVideos();

  const handleLogout = async () => {
    await signOut();
    navigate('/login');
  };

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      {/* Dashboard Hero */}
      <section className="relative pt-24 pb-16 overflow-hidden">
        <div className="absolute inset-0 -z-10">
          <div className="absolute top-0 left-1/4 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 right-1/4 w-80 h-80 bg-teal-200/30 rounded-full blur-3xl"></div>
        </div>

        <div className="container mx-auto px-6 sm:px-12 lg:px-28">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="flex flex-col md:flex-row items-center gap-5 text-center md:text-left">
              <div className="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl font-bold text-white shadow-lg shadow-indigo-500/25 overflow-hidden flex-shrink-0">
                {profile?.avatar_url ? (
                  <img 
                    src={profile.avatar_url} 
                    alt="" 
                    onError={(e) => { e.currentTarget.style.display = 'none'; }}
                    className="w-full h-full object-cover" 
                  />
                ) : (
                  <span>{(profile?.username || user?.email || 'U').charAt(0).toUpperCase()}</span>
                )}
              </div>
              <div>
                <h1 className="text-2xl md:text-3xl font-bold text-zinc-900">
                  Welcome back, {profile?.username || 'Learner'}!
                </h1>
                <p className="text-zinc-500 flex items-center justify-center md:justify-start gap-2 mt-1 text-sm">
                  <span className="w-2 h-2 rounded-full bg-green-500"></span>
                  {profile?.role === 'admin' ? 'Admin Account' : 'User Account'}
                </p>
              </div>
            </div>
            <div className="flex gap-3">
              <button
                onClick={handleLogout}
                className="px-5 py-2.5 border border-zinc-200 rounded-xl text-zinc-700 hover:bg-zinc-50 transition font-medium text-sm"
              >
                Logout
              </button>
              {isPro ? (
                <div className="px-5 py-2.5 bg-indigo-50 border border-indigo-200 rounded-xl text-indigo-700 font-medium text-sm flex items-center gap-2">
                  <span className="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                  Active Plan: Pro
                </div>
              ) : (
                <Link
                  to="/pricing"
                  className="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium text-sm"
                >
                  Upgrade to Pro
                </Link>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-8">
        <div className="container mx-auto px-6 sm:px-12 lg:px-28">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Total Books */}
            <div className="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-indigo-200 transition-all duration-300">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-zinc-500 text-sm font-medium">Total Books</p>
                  <p className="text-4xl font-bold text-zinc-900 mt-1">{books?.length || 0}</p>
                </div>
                <div className="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                  <BookOpen className="h-7 w-7 text-indigo-600" />
                </div>
              </div>
              <Link to="/books" className="inline-flex items-center gap-1 text-sm text-indigo-600 font-medium mt-4 hover:gap-2 transition-all">
                Browse Books
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>

            {/* Total Papers */}
            <div className="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-teal-200 transition-all duration-300">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-zinc-500 text-sm font-medium">Total Papers</p>
                  <p className="text-4xl font-bold text-zinc-900 mt-1">{papers?.length || 0}</p>
                </div>
                <div className="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center">
                  <FileText className="h-7 w-7 text-teal-600" />
                </div>
              </div>
              <Link to="/papers" className="inline-flex items-center gap-1 text-sm text-teal-600 font-medium mt-4 hover:gap-2 transition-all">
                Browse Papers
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>

            {/* Total Videos */}
            <div className="bg-white rounded-2xl p-6 border border-zinc-100 hover:shadow-lg hover:border-rose-200 transition-all duration-300">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-zinc-500 text-sm font-medium">Total Videos</p>
                  <p className="text-4xl font-bold text-zinc-900 mt-1">{videos?.length || 0}</p>
                </div>
                <div className="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center">
                  <Video className="h-7 w-7 text-rose-600" />
                </div>
              </div>
              <Link to="/videos" className="inline-flex items-center gap-1 text-sm text-rose-600 font-medium mt-4 hover:gap-2 transition-all">
                Watch Videos
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <section className="py-8 pb-20">
        <div className="container mx-auto px-6 sm:px-12 lg:px-28">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {/* Recent Activity */}
            <div className="lg:col-span-2">
              <div className="bg-white rounded-2xl border border-zinc-100 overflow-hidden">
                <div className="p-6 border-b border-zinc-100">
                  <h2 className="text-xl font-bold text-zinc-900 flex items-center gap-2">
                    <Sparkles className="h-5 w-5 text-indigo-600" />
                    Recent Activity
                  </h2>
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
                    {history.slice(0, 5).map((item, idx) => (
                      <Link
                        key={idx}
                        to={item.resource_type === 'book' ? `/books/${item.resource_id}` : item.resource_type === 'paper' ? `/papers/${item.resource_id}` : `/videos/${item.resource_id}`}
                        className="flex items-center gap-4 p-4 hover:bg-zinc-50 transition"
                      >
                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${
                          item.resource_type === 'book' ? 'bg-indigo-100 text-indigo-600' :
                          item.resource_type === 'paper' ? 'bg-teal-100 text-teal-600' : 'bg-rose-100 text-rose-600'
                        }`}>
                          {item.resource_type === 'book' ? <BookOpen className="h-5 w-5" /> : item.resource_type === 'paper' ? <FileText className="h-5 w-5" /> : <Video className="h-5 w-5" />}
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="font-medium text-zinc-900 truncate">{item.title || 'Resource'}</p>
                          <p className="text-sm text-zinc-500">{item.resource_type?.toUpperCase()} • Recently viewed</p>
                        </div>
                        <ArrowRight className="h-5 w-5 text-zinc-400" />
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Quick Links Sidebar */}
            <div className="space-y-6">
              <div className="bg-white rounded-2xl p-6 border border-zinc-100">
                <h3 className="font-bold text-zinc-900 mb-4">Quick Links</h3>
                <div className="space-y-3 text-sm">
                  <Link to="/books" className="flex items-center justify-between text-zinc-600 hover:text-indigo-600 transition">
                    <span>Browse Books</span>
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                  <Link to="/papers" className="flex items-center justify-between text-zinc-600 hover:text-teal-600 transition">
                    <span>Research Papers</span>
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                  <Link to="/videos" className="flex items-center justify-between text-zinc-600 hover:text-rose-600 transition">
                    <span>Video Academy</span>
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                  <Link to="/community" className="flex items-center justify-between text-zinc-600 hover:text-indigo-600 transition">
                    <span>Community Feed</span>
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>
    </div>
  );
}

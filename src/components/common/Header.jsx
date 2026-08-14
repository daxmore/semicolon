import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useNotifications } from '../../hooks/useNotifications';
import Logo from './Logo';
import { 
  Search, 
  ChevronDown, 
  BookOpen, 
  FileText, 
  Video, 
  Award, 
  Trophy, 
  Flame, 
  User, 
  Bell, 
  History, 
  LayoutDashboard, 
  ShieldCheck, 
  LogOut, 
  Menu, 
  X,
  FileQuestion
} from 'lucide-react';

export default function Header() {
  const { user, profile, isAdmin, isPro, signOut } = useAuth();
  const { data: notifications } = useNotifications(user?.id);
  const unreadCount = notifications?.filter((n) => !n.is_read).length || 0;

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [resourcesOpen, setResourcesOpen] = useState(false);
  const [academyOpen, setAcademyOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = async () => {
    try {
      await signOut();
      navigate('/login');
    } catch (err) {
      console.error('Logout error', err);
    }
  };

  return (
    <header className="glass sticky top-0 z-40 border-b border-zinc-200/80 bg-white/80 backdrop-blur-md">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <div className="flex items-center">
            <Logo />
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-7 text-sm font-medium">
            <Link 
              to={user ? "/dashboard" : "/"} 
              className={`transition hover:text-indigo-600 ${location.pathname === '/' || location.pathname === '/dashboard' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
            >
              Home
            </Link>

            <Link 
              to="/community" 
              className={`transition hover:text-indigo-600 ${location.pathname.startsWith('/community') ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
            >
              Community
            </Link>

            {/* Resources Dropdown */}
            <div 
              className="relative"
              onMouseEnter={() => setResourcesOpen(true)}
              onMouseLeave={() => setResourcesOpen(false)}
            >
              <button className="flex items-center gap-1 text-zinc-600 hover:text-indigo-600 transition py-2">
                Resources
                <ChevronDown className="h-4 w-4 text-zinc-400 group-hover:text-indigo-600" />
              </button>

              {resourcesOpen && (
                <div className="absolute left-0 top-full pt-1 w-48 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                  <div className="flex flex-col rounded-xl bg-white shadow-xl ring-1 ring-zinc-200 overflow-hidden py-2 border border-zinc-100">
                    <Link to="/books" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-indigo-50/50 hover:text-indigo-600 transition">
                      <BookOpen className="h-4 w-4 text-indigo-500" />
                      Books
                    </Link>
                    <Link to="/papers" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-indigo-50/50 hover:text-indigo-600 transition">
                      <FileText className="h-4 w-4 text-indigo-500" />
                      Papers
                    </Link>
                    <Link to="/videos" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-indigo-50/50 hover:text-indigo-600 transition">
                      <Video className="h-4 w-4 text-indigo-500" />
                      Videos
                    </Link>
                  </div>
                </div>
              )}
            </div>

            {/* Academy Dropdown (Logged in only) */}
            {user && (
              <div 
                className="relative"
                onMouseEnter={() => setAcademyOpen(true)}
                onMouseLeave={() => setAcademyOpen(false)}
              >
                <button className="flex items-center gap-1 text-zinc-700 hover:text-amber-600 transition py-2 font-semibold">
                  <Flame className="h-4 w-4 text-amber-500 fill-amber-500" />
                  Academy
                  <ChevronDown className="h-4 w-4 text-zinc-400" />
                </button>

                {academyOpen && (
                  <div className="absolute left-0 top-full pt-1 w-48 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                    <div className="flex flex-col rounded-xl bg-white shadow-xl ring-1 ring-amber-500/20 overflow-hidden py-2 border border-amber-500/10">
                      <Link to="/academy" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                        <Flame className="h-4 w-4 text-amber-500" />
                        Dashboard
                      </Link>
                      <Link to="/leaderboard" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                        <Trophy className="h-4 w-4 text-amber-500" />
                        Leaderboard
                      </Link>
                      <Link to="/profile#badges" className="flex items-center gap-2.5 px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                        <Award className="h-4 w-4 text-amber-500" />
                        My Badges
                      </Link>
                    </div>
                  </div>
                )}
              </div>
            )}

            {user && (
              <Link 
                to="/request" 
                className={`transition hover:text-indigo-600 ${location.pathname === '/request' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
              >
                Request
              </Link>
            )}

            <Link 
              to="/pricing" 
              className={`transition hover:text-indigo-600 ${location.pathname === '/pricing' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
            >
              Pricing
            </Link>

            {!user && (
              <Link 
                to="/about" 
                className={`transition hover:text-indigo-600 ${location.pathname === '/about' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
              >
                About
              </Link>
            )}
          </nav>

          {/* Right Action Icons & Profile */}
          <div className="flex items-center gap-3">
            {/* Search Trigger */}
            <Link 
              to="/search" 
              className="w-9 h-9 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-600 hover:text-zinc-900"
              title="Search"
            >
              <Search className="h-4 w-4" />
            </Link>

            {/* Notification Bell with Badge */}
            {user && (
              <Link
                to="/notifications"
                className="w-9 h-9 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-600 hover:text-zinc-900 relative"
                title="Notifications"
              >
                <Bell className="h-4 w-4" />
                {unreadCount > 0 && (
                  <span className="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-600 text-white text-[9px] font-black rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                    {unreadCount > 9 ? '9+' : unreadCount}
                  </span>
                )}
              </Link>
            )}

            {user ? (
              <div 
                className="relative"
                onMouseEnter={() => setUserDropdownOpen(true)}
                onMouseLeave={() => setUserDropdownOpen(false)}
              >
                <button className="flex items-center gap-2 rounded-full bg-zinc-100 hover:bg-zinc-200 pl-1 pr-3 py-1 text-sm font-medium text-zinc-700 transition">
                  <div className="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white font-semibold text-xs overflow-hidden">
                    {profile?.avatar_url ? (
                      <img src={profile.avatar_url} alt="Avatar" className="w-full h-full object-cover" />
                    ) : (
                      <span>{(profile?.username || user?.email || 'U').charAt(0).toUpperCase()}</span>
                    )}
                  </div>
                  <span className="hidden sm:inline font-medium text-zinc-800 text-xs">
                    {profile?.username || user?.email?.split('@')[0]}
                  </span>
                  {isPro && (
                    <span className="bg-amber-100 text-amber-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-amber-300">PRO</span>
                  )}
                  <ChevronDown className="h-3.5 w-3.5 text-zinc-400" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 top-full pt-1 w-52 z-50 animate-in fade-in slide-in-from-top-1 duration-150">
                    <div className="rounded-xl bg-white shadow-xl ring-1 ring-zinc-200 overflow-hidden py-1 border border-zinc-100">
                      <div className="px-4 py-2 border-b border-zinc-100">
                        <p className="text-xs font-semibold text-zinc-900 truncate">{profile?.username || 'User'}</p>
                        <p className="text-[11px] text-zinc-500 truncate">{user?.email}</p>
                        <div className="mt-1 flex items-center gap-2">
                          <span className="text-[11px] font-semibold text-indigo-600">Level {profile?.level || 1}</span>
                          <span className="text-[11px] text-zinc-400">•</span>
                          <span className="text-[11px] text-zinc-600">{profile?.xp_total || 0} XP</span>
                        </div>
                      </div>

                      <Link to="/profile" className="flex items-center gap-3 px-4 py-2 text-xs text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                        <User className="h-4 w-4 text-zinc-400" />
                        Profile
                      </Link>
                      <Link to="/notifications" className="flex items-center gap-3 px-4 py-2 text-xs text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                        <Bell className="h-4 w-4 text-zinc-400" />
                        Notifications
                        {unreadCount > 0 && (
                          <span className="ml-auto bg-rose-600 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                            {unreadCount}
                          </span>
                        )}
                      </Link>
                      <Link to="/history" className="flex items-center gap-3 px-4 py-2 text-xs text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                        <History className="h-4 w-4 text-zinc-400" />
                        History
                      </Link>
                      
                      {isAdmin && (
                        <>
                          <div className="border-t border-zinc-100 my-1"></div>
                          <Link to="/admin" className="flex items-center gap-3 px-4 py-2 text-xs text-indigo-600 font-semibold hover:bg-indigo-50 transition">
                            <ShieldCheck className="h-4 w-4 text-indigo-600" />
                            Admin Dashboard
                          </Link>
                        </>
                      )}

                      <div className="border-t border-zinc-100 my-1"></div>
                      <button 
                        onClick={handleLogout}
                        className="w-full text-left flex items-center gap-3 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition"
                      >
                        <LogOut className="h-4 w-4 text-rose-600" />
                        Logout
                      </button>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex items-center gap-2">
                <Link to="/login" className="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                  Login
                </Link>
                <Link to="/signup" className="hidden sm:inline-flex rounded-lg border border-zinc-200 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition">
                  Register
                </Link>
              </div>
            )}

            {/* Mobile menu toggle */}
            <button 
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden w-9 h-9 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600"
            >
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Drawer */}
      {mobileMenuOpen && (
        <div className="md:hidden border-t border-zinc-100 bg-white px-4 py-6 shadow-xl space-y-4">
          <nav className="flex flex-col gap-3 text-sm font-medium">
            <Link to={user ? "/dashboard" : "/"} onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1">Home</Link>
            <Link to="/community" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1">Community</Link>
            <div className="border-t border-zinc-100 pt-2 pb-1 text-xs uppercase text-zinc-400 font-semibold tracking-wider">Resources</div>
            <Link to="/books" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1 flex items-center gap-2"><BookOpen className="h-4 w-4 text-indigo-500" /> Books</Link>
            <Link to="/papers" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1 flex items-center gap-2"><FileText className="h-4 w-4 text-indigo-500" /> Papers</Link>
            <Link to="/videos" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1 flex items-center gap-2"><Video className="h-4 w-4 text-indigo-500" /> Videos</Link>

            {user && (
              <>
                <div className="border-t border-zinc-100 pt-2 pb-1 text-xs uppercase text-zinc-400 font-semibold tracking-wider">Academy</div>
                <Link to="/academy" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1 flex items-center gap-2"><Flame className="h-4 w-4 text-amber-500" /> Dashboard</Link>
                <Link to="/leaderboard" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1 flex items-center gap-2"><Trophy className="h-4 w-4 text-amber-500" /> Leaderboard</Link>
                <Link to="/request" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1">Request Material</Link>
              </>
            )}
            
            <Link to="/pricing" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1">Pricing</Link>
            <Link to="/about" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1">About</Link>
          </nav>
        </div>
      )}
    </header>
  );
}

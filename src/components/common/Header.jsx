import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useNotifications } from '../../hooks/useNotifications';
import { useGlobalSearch } from '../../hooks/useGlobalSearch';
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
  ShieldCheck,
  LogOut,
  Menu,
  X,
  Sparkles,
  ArrowRight,
  Loader2
} from 'lucide-react';

export default function Header() {
  const { user, profile, isAdmin, isPro, signOut } = useAuth();
  const { data: notifications } = useNotifications(user?.id);
  const unreadCount = notifications?.filter((n) => !n.is_read).length || 0;

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [resourcesOpen, setResourcesOpen] = useState(false);
  const [academyOpen, setAcademyOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);

  // Fullscreen search modal state
  const [searchModalOpen, setSearchModalOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [debouncedQuery, setDebouncedQuery] = useState('');
  const searchInputRef = useRef(null);

  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedQuery(searchQuery);
    }, 300);
    return () => clearTimeout(timer);
  }, [searchQuery]);

  useEffect(() => {
    if (searchModalOpen) {
      setTimeout(() => searchInputRef.current?.focus(), 50);
    }
  }, [searchModalOpen]);

  // Keyboard shortcut Ctrl+K / Cmd+K
  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        setSearchModalOpen(true);
      }
      if (e.key === 'Escape') {
        setSearchModalOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const { data: searchResults, isLoading: isSearching } = useGlobalSearch(debouncedQuery);

  const handleLogout = async () => {
    try {
      await signOut();
      navigate('/login');
    } catch (err) {
      console.error('Logout error', err);
    }
  };

  return (
    <>
      <header className="glass sticky top-0 z-50 transition-all duration-300 border-b border-zinc-200/80 bg-[#FAFAFA]/85 backdrop-blur-md">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex h-16 items-center justify-between">

            {/* Section 1: Logo */}
            <div className="flex items-center">
              <Logo className="h-7 w-auto md:mr-12" />
            </div>

            {/* Section 2: Navigation (Center) */}
            <div className="hidden md:block">
              <nav aria-label="Global">
                <ul className="flex items-center gap-8 text-sm font-medium">
                  <li>
                    <Link
                      to={user ? "/dashboard" : "/"}
                      className={`transition hover:text-indigo-600 ${location.pathname === '/' || location.pathname === '/dashboard' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
                    >
                      Home
                    </Link>
                  </li>

                  <li>
                    <Link
                      to="/community"
                      className={`transition hover:text-indigo-600 ${location.pathname.startsWith('/community') ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
                    >
                      Community
                    </Link>
                  </li>

                  {/* Resources Dropdown */}
                  <li
                    className="relative"
                    onMouseEnter={() => setResourcesOpen(true)}
                    onMouseLeave={() => setResourcesOpen(false)}
                  >
                    <button className="flex items-center gap-1 text-zinc-600 hover:text-indigo-600 transition py-2 cursor-pointer">
                      Resources
                      <ChevronDown className="h-4 w-4 text-zinc-400" />
                    </button>

                    {resourcesOpen && (
                      <div className="absolute left-0 top-full pt-1 w-48 z-50">
                        <div className="flex flex-col rounded-xl bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden py-1.5 border border-zinc-100">
                          <Link to="/books" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                            <BookOpen className="h-4 w-4 text-indigo-500" />
                            Books
                          </Link>
                          <Link to="/papers" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                            <FileText className="h-4 w-4 text-teal-500" />
                            Papers
                          </Link>
                          <Link to="/videos" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">
                            <Video className="h-4 w-4 text-rose-500" />
                            Videos
                          </Link>
                        </div>
                      </div>
                    )}
                  </li>

                  {/* Academy Dropdown (Logged in only) */}
                  {user && (
                    <li
                      className="relative"
                      onMouseEnter={() => setAcademyOpen(true)}
                      onMouseLeave={() => setAcademyOpen(false)}
                    >
                      <button className="flex items-center gap-1 text-zinc-700 hover:text-amber-600 transition py-2 font-medium cursor-pointer">
                        Academy
                        <ChevronDown className="h-4 w-4 text-zinc-400" />
                      </button>

                      {academyOpen && (
                        <div className="absolute left-0 top-full pt-1 w-48 z-50">
                          <div className="flex flex-col rounded-xl bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden py-1.5 border border-zinc-100">
                            <Link to="/academy" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                              <Flame className="h-4 w-4 text-amber-500" />
                              Dashboard
                            </Link>
                            <Link to="/leaderboard" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                              <Trophy className="h-4 w-4 text-amber-500" />
                              Leaderboard
                            </Link>
                            <Link to="/profile#badges" className="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition">
                              <Award className="h-4 w-4 text-amber-500" />
                              My Badges
                            </Link>
                          </div>
                        </div>
                      )}
                    </li>
                  )}

                  {user && (
                    <li>
                      <Link
                        to="/request"
                        className={`transition hover:text-indigo-600 ${location.pathname === '/request' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
                      >
                        Request
                      </Link>
                    </li>
                  )}

                  <li>
                    <Link
                      to="/pricing"
                      className={`transition hover:text-indigo-600 ${location.pathname === '/pricing' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
                    >
                      Pricing
                    </Link>
                  </li>

                  {!user && (
                    <li>
                      <Link
                        to="/about"
                        className={`transition hover:text-indigo-600 ${location.pathname === '/about' ? 'text-indigo-600 font-semibold' : 'text-zinc-600'}`}
                      >
                        About
                      </Link>
                    </li>
                  )}
                </ul>
              </nav>
            </div>

            {/* Section 3: Actions (Right) */}
            <div className="flex items-center gap-3">
              {/* Search Trigger Button */}
              <button
                onClick={() => setSearchModalOpen(true)}
                className="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-500 hover:text-zinc-700 cursor-pointer"
                title="Search (Ctrl+K)"
              >
                <Search className="h-5 w-5" />
              </button>

              {/* Notification Bell */}
              {user && (
                <Link
                  to="/notifications"
                  className="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-500 hover:text-zinc-700 relative"
                  title="Notifications"
                >
                  <Bell className="h-5 w-5" />
                  {unreadCount > 0 && (
                    <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                  )}
                </Link>
              )}

              {user ? (
                /* User Dropdown */
                <div
                  className="relative"
                  onMouseEnter={() => setUserDropdownOpen(true)}
                  onMouseLeave={() => setUserDropdownOpen(false)}
                >
                  <button className="flex items-center gap-1.5 rounded-full bg-zinc-100 hover:bg-zinc-200 p-1 pr-2 text-sm font-medium text-zinc-700 transition cursor-pointer">
                    <div className="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm overflow-hidden flex-shrink-0">
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
                    <ChevronDown className="h-3.5 w-3.5 text-zinc-400" />
                  </button>

                  {userDropdownOpen && (
                    <div className="absolute right-0 top-full pt-1.5 w-48 z-50">
                      <div className="rounded-xl bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden py-1 border border-zinc-100">
                        <Link to="/profile" className="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                          <User className="h-4 w-4 text-zinc-400" />
                          Profile
                        </Link>
                        <Link to="/notifications" className="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                          <Bell className="h-4 w-4 text-zinc-400" />
                          Notifications
                          {unreadCount > 0 && (
                            <span className="ml-auto bg-rose-600 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                              {unreadCount}
                            </span>
                          )}
                        </Link>
                        <Link to="/history" className="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                          <History className="h-4 w-4 text-zinc-400" />
                          History
                        </Link>

                        {isAdmin && (
                          <>
                            <div className="border-t border-zinc-100 my-1"></div>
                            <Link to="/admin" className="flex items-center gap-3 px-4 py-2.5 text-sm text-indigo-600 font-semibold hover:bg-indigo-50 transition">
                              <ShieldCheck className="h-4 w-4 text-indigo-600" />
                              Admin Panel
                            </Link>
                          </>
                        )}

                        <div className="border-t border-zinc-100 my-1"></div>
                        <button
                          onClick={handleLogout}
                          className="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition cursor-pointer"
                        >
                          <LogOut className="h-4 w-4 text-red-600" />
                          Logout
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              ) : (
                <div className="flex items-center gap-2">
                  <Link
                    to="/login"
                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition"
                  >
                    Login
                  </Link>
                  <Link
                    to="/signup"
                    className="hidden sm:inline-flex rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition"
                  >
                    Register
                  </Link>
                </div>
              )}

              {/* Mobile menu toggle */}
              <div className="block md:hidden">
                <button
                  onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                  className="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 transition cursor-pointer"
                >
                  {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Mobile Drawer */}
        {mobileMenuOpen && (
          <div className="md:hidden border-t border-zinc-100 bg-white px-4 py-6 shadow-xl space-y-3">
            <nav className="flex flex-col gap-2 text-sm font-medium">
              <Link to={user ? "/dashboard" : "/"} onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1.5">Home</Link>
              <Link to="/community" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1.5">Community</Link>

              <div className="border-t border-zinc-100 pt-2 pb-1 text-xs uppercase text-zinc-400 font-semibold tracking-wider">Resources</div>
              <Link to="/books" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1.5 flex items-center gap-2"><BookOpen className="h-4 w-4 text-indigo-500" /> Books</Link>
              <Link to="/papers" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1.5 flex items-center gap-2"><FileText className="h-4 w-4 text-teal-500" /> Papers</Link>
              <Link to="/videos" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1.5 flex items-center gap-2"><Video className="h-4 w-4 text-rose-500" /> Videos</Link>

              {user && (
                <>
                  <div className="border-t border-zinc-100 pt-2 pb-1 text-xs uppercase text-zinc-400 font-semibold tracking-wider">Academy</div>
                  <Link to="/academy" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1.5 flex items-center gap-2"><Flame className="h-4 w-4 text-amber-500" /> Dashboard</Link>
                  <Link to="/leaderboard" onClick={() => setMobileMenuOpen(false)} className="pl-2 text-zinc-600 py-1.5 flex items-center gap-2"><Trophy className="h-4 w-4 text-amber-500" /> Leaderboard</Link>
                  <Link to="/request" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1.5">Request Material</Link>
                </>
              )}

              <Link to="/pricing" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1.5">Pricing</Link>
              <Link to="/about" onClick={() => setMobileMenuOpen(false)} className="text-zinc-700 py-1.5">About</Link>
            </nav>
          </div>
        )}
      </header>

      {/* Fullscreen Search Overlay Modal */}
      {searchModalOpen && (
        <div className="fixed inset-0 z-[100] bg-zinc-950/70 backdrop-blur-md flex flex-col items-center justify-start pt-20 p-4 sm:p-6 animate-in fade-in duration-150">
          <button
            onClick={() => setSearchModalOpen(false)}
            className="absolute top-6 right-6 w-10 h-10 rounded-full bg-zinc-800/80 hover:bg-zinc-700 text-zinc-400 hover:text-white flex items-center justify-center transition cursor-pointer"
          >
            <X className="h-5 w-5" />
          </button>

          <div className="w-full max-w-xl bg-zinc-900 border border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
            {/* Search Input Bar */}
            <div className="p-4 bg-zinc-900/90 border-b border-zinc-800 flex items-center gap-3">
              <Search className="h-5 w-5 text-zinc-400 flex-shrink-0" />
              <input
                ref={searchInputRef}
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search..."
                className="w-full text-base text-white placeholder-zinc-500 focus:outline-none bg-transparent"
              />
              {isSearching && <Loader2 className="h-4 w-4 text-indigo-500 animate-spin flex-shrink-0" />}
            </div>

            {/* Content & Trending section */}
            <div className="p-4 max-h-96 overflow-y-auto">
              {!debouncedQuery.trim() ? (
                <div className="space-y-3">
                  <p className="text-[11px] font-bold uppercase tracking-wider text-zinc-500 px-2">Trending</p>
                  <div className="space-y-1">
                    <Link
                      to="/books/1"
                      onClick={() => setSearchModalOpen(false)}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-zinc-800/60 text-zinc-300 hover:text-white transition group text-sm font-medium"
                    >
                      <div className="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center text-xs">📚</div>
                      <span>Clean Code</span>
                    </Link>
                    <Link
                      to="/books/2"
                      onClick={() => setSearchModalOpen(false)}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-zinc-800/60 text-zinc-300 hover:text-white transition group text-sm font-medium"
                    >
                      <div className="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center text-xs">📄</div>
                      <span>System Design Patterns</span>
                    </Link>
                    <Link
                      to="/videos/1"
                      onClick={() => setSearchModalOpen(false)}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-zinc-800/60 text-zinc-300 hover:text-white transition group text-sm font-medium"
                    >
                      <div className="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center text-xs">🎥</div>
                      <span>React Fundamentals</span>
                    </Link>
                    <Link
                      to="/books/3"
                      onClick={() => setSearchModalOpen(false)}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-zinc-800/60 text-zinc-300 hover:text-white transition group text-sm font-medium"
                    >
                      <div className="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center text-xs">📚</div>
                      <span>Machine Learning Basics</span>
                    </Link>
                  </div>
                </div>
              ) : searchResults && searchResults.length > 0 ? (
                <div className="space-y-1">
                  {searchResults.map((item) => (
                    <Link
                      key={`${item.type}-${item.id}`}
                      to={item.type === 'book' ? `/books/${item.id}` : item.type === 'paper' ? `/papers/${item.id}` : `/videos/${item.id}`}
                      onClick={() => setSearchModalOpen(false)}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-zinc-800/60 transition group text-zinc-200 hover:text-white"
                    >
                      <div className="w-8 h-8 rounded-lg bg-zinc-800 flex items-center justify-center flex-shrink-0 text-xs">
                        {item.type === 'book' ? '📚' : item.type === 'paper' ? '📄' : '🎥'}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">{item.title}</p>
                        <p className="text-[11px] text-zinc-500">{item.type?.toUpperCase()} • {item.author || item.category}</p>
                      </div>
                      <ArrowRight className="h-4 w-4 text-zinc-600 group-hover:text-zinc-300 transition" />
                    </Link>
                  ))}
                </div>
              ) : !isSearching ? (
                <div className="py-8 text-center text-zinc-500 text-xs">
                  No results found for "{debouncedQuery}"
                </div>
              ) : null}
            </div>

            {/* Modal footer shortcut guide */}
            <div className="p-3 bg-zinc-900 border-t border-zinc-800 flex items-center justify-between text-[11px] text-zinc-500 px-4">
              <span className="flex items-center gap-1.5">
                <kbd className="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-zinc-400 font-mono text-[10px]">↵</kbd>
                to search
              </span>
              <span className="flex items-center gap-1.5">
                <kbd className="px-1.5 py-0.5 bg-zinc-800 border border-zinc-700 rounded text-zinc-400 font-mono text-[10px]">esc</kbd>
                to close
              </span>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

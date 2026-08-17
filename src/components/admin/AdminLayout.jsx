import React from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { 
  LayoutDashboard, 
  BookOpen, 
  FileText, 
  Video, 
  MessageSquare, 
  AlertTriangle, 
  HelpCircle, 
  Users, 
  Award, 
  LogOut, 
  ArrowLeft,
  Layers
} from 'lucide-react';
import Logo from '../common/Logo';

export default function AdminLayout() {
  const { profile, signOut } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await signOut();
    navigate('/login');
  };

  const navItems = [
    { label: 'Dashboard', path: '/admin', icon: LayoutDashboard, section: 'Main' },
    { label: 'Books', path: '/admin/books', icon: BookOpen, section: 'Content' },
    { label: 'Papers', path: '/admin/papers', icon: FileText, section: 'Content' },
    { label: 'Videos', path: '/admin/videos', icon: Video, section: 'Content' },
    { label: 'Community', path: '/admin/community', icon: MessageSquare, section: 'Content' },
    { label: 'Reports', path: '/admin/reports', icon: AlertTriangle, section: 'Management' },
    { label: 'Requests', path: '/admin/requests', icon: HelpCircle, section: 'Management' },
    { label: 'Users', path: '/admin/users', icon: Users, section: 'Management' },
    { label: 'Badges', path: '/admin/badges', icon: Award, section: 'Gamification' },
    { label: 'Skills', path: '/admin/skills', icon: Layers, section: 'Gamification' },
    { label: 'Quizzes', path: '/admin/quizzes', icon: HelpCircle, section: 'Gamification' },
  ];

  return (
    <div className="flex min-h-screen bg-zinc-100 text-zinc-900">
      {/* Sidebar */}
      <aside className="w-64 bg-zinc-900 text-white flex flex-col fixed inset-y-0 left-0 z-40 border-r border-zinc-800">
        {/* Header */}
        <div className="p-5 border-b border-zinc-800 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
              ;
            </div>
            <div>
              <span className="font-bold text-sm text-white">Semicolon</span>
              <p className="text-[11px] text-zinc-400">Admin Control</p>
            </div>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 p-3 space-y-1 overflow-y-auto">
          {navItems.map((item, idx) => {
            const Icon = item.icon;
            const isActive = location.pathname === item.path;
            const prevItem = navItems[idx - 1];
            const isNewSection = !prevItem || prevItem.section !== item.section;

            return (
              <React.Fragment key={item.path}>
                {isNewSection && (
                  <p className="text-[10px] font-bold text-zinc-500 uppercase tracking-wider px-3 pt-4 pb-1.5">
                    {item.section}
                  </p>
                )}
                <Link
                  to={item.path}
                  className={`flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition ${
                    isActive
                      ? 'bg-indigo-600 text-white shadow-sm'
                      : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
                  }`}
                >
                  <Icon className="h-4 w-4" />
                  {item.label}
                </Link>
              </React.Fragment>
            );
          })}
        </nav>

        {/* Footer actions */}
        <div className="p-3 border-t border-zinc-800 space-y-1">
          <Link
            to="/dashboard"
            className="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-zinc-400 hover:bg-zinc-800 hover:text-white transition"
          >
            <ArrowLeft className="h-4 w-4" />
            Back to App
          </Link>
          <button
            onClick={handleLogout}
            className="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-rose-400 hover:bg-rose-950/40 transition text-left"
          >
            <LogOut className="h-4 w-4" />
            Logout
          </button>
        </div>
      </aside>

      {/* Main Admin Content */}
      <div className="pl-64 flex-1 flex flex-col">
        <header className="h-16 bg-white border-b border-zinc-200 px-8 flex items-center justify-between sticky top-0 z-30">
          <h1 className="text-base font-bold text-zinc-800">
            {navItems.find((i) => i.path === location.pathname)?.label || 'Admin'}
          </h1>
          <div className="flex items-center gap-3">
            <span className="text-xs text-zinc-500">Logged in as</span>
            <span className="text-xs font-semibold text-zinc-800 bg-zinc-100 px-2.5 py-1 rounded-full border border-zinc-200">
              {profile?.username || 'Admin'}
            </span>
          </div>
        </header>

        <main className="p-8 flex-1">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

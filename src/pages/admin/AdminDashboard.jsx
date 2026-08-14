import React from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { axiosClient } from '../../lib/axiosClient';
import { 
  Users, 
  BookOpen, 
  FileText, 
  Video, 
  MessageSquare, 
  AlertTriangle, 
  HelpCircle, 
  Award, 
  TrendingUp, 
  ShieldCheck,
  ArrowRight
} from 'lucide-react';

export default function AdminDashboard() {
  const { data: stats, isLoading } = useQuery({
    queryKey: ['admin_overview_stats'],
    queryFn: async () => {
      const [
        usersRes,
        booksRes,
        papersRes,
        videosRes,
        postsRes,
        reportsRes,
        requestsRes,
        subsRes,
      ] = await Promise.all([
        axiosClient.get('/rest/v1/profiles?select=id,is_pro'),
        axiosClient.get('/rest/v1/books?select=id'),
        axiosClient.get('/rest/v1/papers?select=id'),
        axiosClient.get('/rest/v1/videos?select=id'),
        axiosClient.get('/rest/v1/community_posts?select=id'),
        axiosClient.get('/rest/v1/community_reports?status=eq.pending&select=id'),
        axiosClient.get('/rest/v1/material_requests?status=eq.pending&select=id'),
        axiosClient.get('/rest/v1/subscriptions?select=amount'),
      ]);

      const users = usersRes.data || [];
      const proUsers = users.filter((u) => u.is_pro).length;
      const totalRevenue = (subsRes.data || []).reduce((acc, s) => acc + (s.amount || 0), 0);

      return {
        totalUsers: users.length,
        proUsers,
        totalBooks: booksRes.data?.length || 0,
        totalPapers: papersRes.data?.length || 0,
        totalVideos: videosRes.data?.length || 0,
        totalPosts: postsRes.data?.length || 0,
        pendingReports: reportsRes.data?.length || 0,
        pendingRequests: requestsRes.data?.length || 0,
        totalRevenue,
      };
    },
  });

  const cards = [
    { label: 'Total Developers', value: stats?.totalUsers || 0, icon: Users, color: 'text-indigo-600', bg: 'bg-indigo-50', link: '/admin/users' },
    { label: 'Pro Members', value: stats?.proUsers || 0, icon: ShieldCheck, color: 'text-amber-600', bg: 'bg-amber-50', link: '/admin/users' },
    { label: 'Total Books', value: stats?.totalBooks || 0, icon: BookOpen, color: 'text-indigo-600', bg: 'bg-indigo-50', link: '/admin/books' },
    { label: 'Research Papers', value: stats?.totalPapers || 0, icon: FileText, color: 'text-amber-600', bg: 'bg-amber-50', link: '/admin/papers' },
    { label: 'Video Deep Dives', value: stats?.totalVideos || 0, icon: Video, color: 'text-rose-600', bg: 'bg-rose-50', link: '/admin/videos' },
    { label: 'Discussions', value: stats?.totalPosts || 0, icon: MessageSquare, color: 'text-emerald-600', bg: 'bg-emerald-50', link: '/admin/community' },
    { label: 'Pending Reports', value: stats?.pendingReports || 0, icon: AlertTriangle, color: 'text-rose-600', bg: 'bg-rose-50', link: '/admin/reports' },
    { label: 'Pending Requests', value: stats?.pendingRequests || 0, icon: HelpCircle, color: 'text-amber-600', bg: 'bg-amber-50', link: '/admin/requests' },
  ];

  return (
    <div className="space-y-8">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-zinc-900">Admin Control Center</h1>
          <p className="text-xs text-zinc-500">Platform analytics, content overview, and moderation queues.</p>
        </div>
        <div className="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-zinc-200 shadow-sm text-xs font-semibold">
          <span className="text-zinc-500">Total Pro Revenue:</span>
          <span className="text-emerald-600 font-extrabold">₹{stats?.totalRevenue || 0}</span>
        </div>
      </div>

      {/* Grid Overview */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {cards.map((c, idx) => {
          const Icon = c.icon;
          return (
            <Link
              key={idx}
              to={c.link}
              className="bg-white p-6 rounded-2xl border border-zinc-200 hover:border-indigo-400 hover:shadow-lg transition flex flex-col justify-between space-y-4 group"
            >
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-zinc-500">{c.label}</span>
                <div className={`w-10 h-10 rounded-xl ${c.bg} ${c.color} flex items-center justify-center`}>
                  <Icon className="h-5 w-5" />
                </div>
              </div>

              <div className="flex items-baseline justify-between pt-2">
                <span className="text-3xl font-extrabold text-zinc-900">
                  {isLoading ? '...' : c.value}
                </span>
                <ArrowRight className="h-4 w-4 text-zinc-300 group-hover:translate-x-1 group-hover:text-indigo-600 transition" />
              </div>
            </Link>
          );
        })}
      </div>

      {/* Quick Action Hub */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
        <div className="bg-white p-6 rounded-2xl border border-zinc-200 space-y-3">
          <h3 className="font-bold text-sm text-zinc-900 flex items-center gap-2">
            <BookOpen className="h-4 w-4 text-indigo-600" /> Manage Content
          </h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            Upload new books, academic papers, and YouTube video tutorials to the library.
          </p>
          <div className="pt-2 flex flex-wrap gap-2 text-xs font-semibold">
            <Link to="/admin/books" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Books</Link>
            <Link to="/admin/papers" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Papers</Link>
            <Link to="/admin/videos" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Videos</Link>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-zinc-200 space-y-3">
          <h3 className="font-bold text-sm text-zinc-900 flex items-center gap-2">
            <Award className="h-4 w-4 text-amber-500" /> Gamification & Quizzes
          </h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            Configure skill MCQ questions, correct options, and create custom XP badges.
          </p>
          <div className="pt-2 flex flex-wrap gap-2 text-xs font-semibold">
            <Link to="/admin/quizzes" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Quizzes</Link>
            <Link to="/admin/badges" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Badges</Link>
          </div>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-zinc-200 space-y-3">
          <h3 className="font-bold text-sm text-zinc-900 flex items-center gap-2">
            <AlertTriangle className="h-4 w-4 text-rose-500" /> Moderation & Support
          </h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            Review flagged community reports and approve or reject user material requests.
          </p>
          <div className="pt-2 flex flex-wrap gap-2 text-xs font-semibold">
            <Link to="/admin/reports" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Reports</Link>
            <Link to="/admin/requests" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Requests</Link>
            <Link to="/admin/users" className="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 rounded-lg text-zinc-700">Users</Link>
          </div>
        </div>
      </div>
    </div>
  );
}

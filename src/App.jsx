import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from './contexts/AuthContext';
import { ToastProvider } from './contexts/ToastContext';

import Layout from './components/common/Layout';
import AdminLayout from './components/admin/AdminLayout';
import { ProtectedRoute, AdminRoute, GuestRoute } from './components/common/RouteGuards';

// Public & Static Pages
import Home from './pages/Home';
import About from './pages/About';
import GlobalSearch from './pages/GlobalSearch';

// Auth Pages
import Login from './pages/auth/Login';
import Signup from './pages/auth/Signup';
import ForgotPassword from './pages/auth/ForgotPassword';

// Core Content Pages
import BooksList from './pages/books/BooksList';
import BookDetail from './pages/books/BookDetail';
import PapersList from './pages/papers/PapersList';
import PaperDetail from './pages/papers/PaperDetail';
import VideosList from './pages/videos/VideosList';
import VideoDetail from './pages/videos/VideoDetail';

// Academy & Quizzes
import Academy from './pages/academy/Academy';
import SkillDetail from './pages/academy/SkillDetail';
import Quiz from './pages/academy/Quiz';
import Certificate from './pages/academy/Certificate';

// Community & Notifications
import CommunityFeed from './pages/community/CommunityFeed';
import CreatePost from './pages/community/CreatePost';
import PostDetail from './pages/community/PostDetail';
import Notifications from './pages/notifications/Notifications';

// User Pages
import Dashboard from './pages/user/Dashboard';
import Profile from './pages/user/Profile';
import Leaderboard from './pages/user/Leaderboard';
import UserHistory from './pages/user/UserHistory';

// Pricing & Payments
import Pricing from './pages/pricing/Pricing';
import CheckoutSuccess from './pages/pricing/CheckoutSuccess';
import CheckoutCancel from './pages/pricing/CheckoutCancel';

// Misc Pages (Viewer, Downloader, Requests)
import Request from './pages/misc/Request';
import MaterialViewer from './pages/misc/MaterialViewer';
import DownloadHandler from './pages/misc/DownloadHandler';

// Admin Views
import AdminDashboard from './pages/admin/AdminDashboard';
import AdminBooks from './pages/admin/AdminBooks';
import AdminPapers from './pages/admin/AdminPapers';
import AdminVideos from './pages/admin/AdminVideos';
import AdminQuizzes from './pages/admin/AdminQuizzes';
import AdminReports from './pages/admin/AdminReports';
import AdminRequests from './pages/admin/AdminRequests';
import AdminBadges from './pages/admin/AdminBadges';
import AdminUsers from './pages/admin/AdminUsers';
import AdminSkills from './pages/admin/AdminSkills';
import AdminCategories from './pages/admin/AdminCategories';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 0,
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <ToastProvider>
          <BrowserRouter>
            <Routes>
              {/* Standalone Auth Routes (Only for Guests / Not Logged In) */}
              <Route element={<GuestRoute />}>
                <Route path="/login" element={<Login />} />
                <Route path="/signup" element={<Signup />} />
                <Route path="/forgot-password" element={<ForgotPassword />} />
              </Route>

              {/* Public and App Layout */}
              <Route element={<Layout />}>
                <Route path="/" element={<Home />} />
                <Route path="/about" element={<About />} />
                <Route path="/search" element={<GlobalSearch />} />
                
                {/* Pricing & Checkout */}
                <Route path="/pricing" element={<Pricing />} />
                <Route path="/checkout/success" element={<CheckoutSuccess />} />
                <Route path="/checkout/cancel" element={<CheckoutCancel />} />

                {/* Community Feed & Leaderboard */}
                <Route path="/community" element={<CommunityFeed />} />
                <Route path="/community/post/:id" element={<PostDetail />} />
                <Route path="/leaderboard" element={<Leaderboard />} />

                {/* Protected User & Resource Routes (Login Required) */}
                <Route element={<ProtectedRoute />}>
                  {/* Resources (Books, Papers, Videos, Viewer, Downloader) */}
                  <Route path="/books" element={<BooksList />} />
                  <Route path="/books/:id" element={<BookDetail />} />
                  <Route path="/papers" element={<PapersList />} />
                  <Route path="/papers/:id" element={<PaperDetail />} />
                  <Route path="/videos" element={<VideosList />} />
                  <Route path="/videos/:id" element={<VideoDetail />} />
                  <Route path="/view" element={<MaterialViewer />} />
                  <Route path="/download" element={<DownloadHandler />} />

                  {/* User Dashboard & Settings */}
                  <Route path="/dashboard" element={<Dashboard />} />
                  <Route path="/profile" element={<Profile />} />
                  <Route path="/notifications" element={<Notifications />} />
                  <Route path="/history" element={<UserHistory />} />
                  
                  {/* Academy */}
                  <Route path="/academy" element={<Academy />} />
                  <Route path="/academy/skill/:id" element={<SkillDetail />} />
                  <Route path="/academy/quiz" element={<Quiz />} />
                  <Route path="/academy/certificate" element={<Certificate />} />
                  
                  {/* Community Create */}
                  <Route path="/community/new" element={<CreatePost />} />

                  {/* Material Request */}
                  <Route path="/request" element={<Request />} />
                </Route>
              </Route>

              {/* Protected Admin Routes */}
              <Route element={<AdminRoute />}>
                <Route path="/admin" element={<AdminLayout />}>
                  <Route index element={<AdminDashboard />} />
                  <Route path="books" element={<AdminBooks />} />
                  <Route path="papers" element={<AdminPapers />} />
                  <Route path="videos" element={<AdminVideos />} />
                  <Route path="categories" element={<AdminCategories />} />
                  <Route path="community" element={<CommunityFeed />} />
                  <Route path="reports" element={<AdminReports />} />
                  <Route path="requests" element={<AdminRequests />} />
                  <Route path="users" element={<AdminUsers />} />
                  <Route path="badges" element={<AdminBadges />} />
                  <Route path="quizzes" element={<AdminQuizzes />} />
                  <Route path="skills" element={<AdminSkills />} />
                </Route>
              </Route>

              {/* Fallback */}
              <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
          </BrowserRouter>
        </ToastProvider>
      </AuthProvider>
    </QueryClientProvider>
  );
}

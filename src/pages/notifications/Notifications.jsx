import React from 'react';
import { Link } from 'react-router-dom';
import { useNotifications, useNotificationMutations } from '../../hooks/useNotifications';
import { useAuth } from '../../contexts/AuthContext';
import { timeAgo } from '../../lib/utils';
import { Bell, CheckCheck, MessageSquare, Award, Sparkles, ExternalLink, ArrowLeft } from 'lucide-react';

export default function Notifications() {
  const { user } = useAuth();
  const { data: notifications, isLoading } = useNotifications(user?.id);
  const { markAsRead, markAllAsRead } = useNotificationMutations();

  const unreadCount = notifications?.filter((n) => !n.is_read).length || 0;

  const getIcon = (type) => {
    switch (type) {
      case 'new_comment':
        return <MessageSquare className="h-4 w-4 text-indigo-500" />;
      case 'badge':
        return <Award className="h-4 w-4 text-amber-500" />;
      default:
        return <Sparkles className="h-4 w-4 text-indigo-600" />;
    }
  };

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 py-10 space-y-6">
      <div className="flex items-center justify-between border-b border-zinc-200 pb-4">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <Bell className="h-5 w-5" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-zinc-900">Notifications</h1>
            <p className="text-xs text-zinc-500">
              {unreadCount > 0 ? `You have ${unreadCount} unread notification(s)` : 'All caught up!'}
            </p>
          </div>
        </div>

        {unreadCount > 0 && (
          <button
            onClick={() => markAllAsRead.mutate(user?.id)}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 text-xs font-semibold shadow-sm transition"
          >
            <CheckCheck className="h-4 w-4 text-indigo-600" />
            Mark all as read
          </button>
        )}
      </div>

      {/* Notifications list */}
      <div className="space-y-3">
        {isLoading ? (
          [1, 2, 3].map((n) => (
            <div key={n} className="bg-white p-4 rounded-xl border border-zinc-200 animate-pulse h-20"></div>
          ))
        ) : notifications && notifications.length > 0 ? (
          notifications.map((notif) => (
            <div
              key={notif.id}
              onClick={() => {
                if (!notif.is_read) markAsRead.mutate(notif.id);
              }}
              className={`p-4 rounded-2xl border transition flex items-start gap-4 ${
                notif.is_read
                  ? 'bg-white border-zinc-200/80 text-zinc-600'
                  : 'bg-indigo-50/40 border-indigo-200 shadow-sm'
              }`}
            >
              <div className="w-8 h-8 rounded-xl bg-white border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                {getIcon(notif.type)}
              </div>

              <div className="flex-1 space-y-1">
                <div className="flex items-center justify-between">
                  <h3 className="font-bold text-xs text-zinc-900">{notif.title}</h3>
                  <span className="text-[10px] text-zinc-400 font-medium">
                    {timeAgo(notif.created_at)}
                  </span>
                </div>

                <p className="text-xs text-zinc-600 leading-relaxed">{notif.message}</p>

                {notif.link && (
                  <Link
                    to={notif.link}
                    className="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:underline pt-1"
                  >
                    <span>View discussion</span>
                    <ExternalLink className="h-3 w-3" />
                  </Link>
                )}
              </div>
            </div>
          ))
        ) : (
          <div className="bg-white rounded-2xl border border-zinc-200 p-12 text-center space-y-2">
            <Bell className="h-8 w-8 text-zinc-300 mx-auto" />
            <h3 className="text-sm font-bold text-zinc-900">No notifications yet</h3>
            <p className="text-xs text-zinc-500">
              When people reply to your posts or you earn badges, updates will appear here.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

import React from 'react';
import { Link } from 'react-router-dom';
import { useUserHistory } from '../../hooks/useGamification';
import { useAuth } from '../../contexts/AuthContext';
import { timeAgo } from '../../lib/utils';
import { History, BookOpen, FileText, Video, MessageSquare, ExternalLink, ArrowLeft } from 'lucide-react';

export default function UserHistory() {
  const { user } = useAuth();
  const { data: historyList, isLoading } = useUserHistory(user?.id);

  const getResourceIcon = (type) => {
    switch (type) {
      case 'book':
        return <BookOpen className="h-4 w-4 text-indigo-600" />;
      case 'paper':
        return <FileText className="h-4 w-4 text-amber-600" />;
      case 'video':
        return <Video className="h-4 w-4 text-rose-600" />;
      default:
        return <MessageSquare className="h-4 w-4 text-indigo-500" />;
    }
  };

  const getResourceLink = (type, id) => {
    switch (type) {
      case 'book':
        return `/books/${id}`;
      case 'paper':
        return `/papers/${id}`;
      case 'video':
        return `/videos/${id}`;
      default:
        return `/community/post/${id}`;
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
      <div className="flex items-center gap-3 border-b border-zinc-200 pb-4">
        <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
          <History className="h-5 w-5" />
        </div>
        <div>
          <h1 className="text-xl font-bold text-zinc-900">Activity & History</h1>
          <p className="text-xs text-zinc-500">Recently viewed books, research papers, videos, and discussions.</p>
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-zinc-200/80 shadow-sm overflow-hidden">
        {isLoading ? (
          <div className="p-8 space-y-4">
            {[1, 2, 3].map((n) => (
              <div key={n} className="h-12 bg-zinc-100 rounded-xl animate-pulse"></div>
            ))}
          </div>
        ) : historyList && historyList.length > 0 ? (
          <div className="divide-y divide-zinc-100">
            {historyList.map((item) => (
              <div
                key={item.id}
                className="p-4 sm:px-6 hover:bg-zinc-50/80 transition flex items-center justify-between gap-4 text-xs"
              >
                <div className="flex items-center gap-3 min-w-0">
                  <div className="w-8 h-8 rounded-lg bg-zinc-50 border border-zinc-200 flex items-center justify-center shrink-0">
                    {getResourceIcon(item.resource_type)}
                  </div>
                  <div className="min-w-0">
                    <span className="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">
                      {item.resource_type}
                    </span>
                    <span className="font-semibold text-zinc-800 truncate block">
                      Resource #{item.resource_id}
                    </span>
                  </div>
                </div>

                <div className="flex items-center gap-4 shrink-0">
                  <span className="text-[11px] text-zinc-400 font-medium">{timeAgo(item.viewed_at)}</span>
                  <Link
                    to={getResourceLink(item.resource_type, item.resource_id)}
                    className="p-1.5 rounded-lg bg-zinc-100 hover:bg-indigo-50 hover:text-indigo-600 transition"
                    title="Open Resource"
                  >
                    <ExternalLink className="h-3.5 w-3.5" />
                  </Link>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="p-12 text-center space-y-2">
            <History className="h-8 w-8 text-zinc-300 mx-auto" />
            <h3 className="text-sm font-bold text-zinc-900">No activity yet</h3>
            <p className="text-xs text-zinc-500">
              When you read books, papers, or watch videos, they will appear here.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

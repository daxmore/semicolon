import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useUserHistory } from '../../hooks/useGamification';
import { useAuth } from '../../contexts/AuthContext';

export default function UserHistory() {
  const { user } = useAuth();
  const { data: historyList, isLoading } = useUserHistory(user?.id);
  const [filter, setFilter] = useState('all');

  const filteredHistory = (historyList || []).filter((item) => {
    if (filter === 'all') return true;
    return item.resource_type === filter;
  });

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      <section className="py-12">
        <div className="container mx-auto px-6 max-w-4xl">
          
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <h1 className="text-3xl font-bold text-zinc-900 flex items-center gap-3 font-heading">
              <div className="p-2 bg-indigo-50 rounded-xl text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              Activity History
            </h1>
          </div>

          {/* Enhanced Filters */}
          <div className="flex overflow-x-auto pb-4 mb-4 gap-2 no-scrollbar border-b border-zinc-200">
            <button
              onClick={() => setFilter('all')}
              className={`px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap cursor-pointer ${
                filter === 'all'
                  ? 'bg-zinc-900 text-white'
                  : 'bg-white text-zinc-600 border border-zinc-200 hover:bg-zinc-50'
              }`}
            >
              All Activity
            </button>
            <button
              onClick={() => setFilter('book')}
              className={`px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 cursor-pointer ${
                filter === 'book'
                  ? 'bg-indigo-600 text-white border border-indigo-600'
                  : 'bg-white text-indigo-700 border border-indigo-100 hover:bg-indigo-50'
              }`}
            >
              📚 Books
            </button>
            <button
              onClick={() => setFilter('paper')}
              className={`px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 cursor-pointer ${
                filter === 'paper'
                  ? 'bg-teal-600 text-white border border-teal-600'
                  : 'bg-white text-teal-700 border border-teal-100 hover:bg-teal-50'
              }`}
            >
              📄 Papers
            </button>
            <button
              onClick={() => setFilter('video')}
              className={`px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 cursor-pointer ${
                filter === 'video'
                  ? 'bg-rose-600 text-white border border-rose-600'
                  : 'bg-white text-rose-700 border border-rose-100 hover:bg-rose-50'
              }`}
            >
              🎥 Videos
            </button>
            <button
              onClick={() => setFilter('post')}
              className={`px-5 py-2.5 rounded-full text-sm font-medium transition whitespace-nowrap flex items-center gap-2 cursor-pointer ${
                filter === 'post'
                  ? 'bg-amber-500 text-white border border-amber-500'
                  : 'bg-white text-amber-600 border border-amber-100 hover:bg-amber-50'
              }`}
            >
              💬 Community Posts
            </button>
          </div>

          {/* History List */}
          <div className="bg-white border border-zinc-100 rounded-2xl shadow-sm overflow-hidden">
            {isLoading ? (
              <div className="divide-y divide-zinc-100">
                {[1, 2, 3].map((n) => (
                  <div key={n} className="p-5 animate-pulse flex items-center gap-4">
                    <div className="w-12 h-12 rounded-2xl bg-zinc-100"></div>
                    <div className="flex-1 space-y-2">
                      <div className="h-4 bg-zinc-100 rounded w-1/3"></div>
                      <div className="h-3 bg-zinc-100 rounded w-1/4"></div>
                    </div>
                  </div>
                ))}
              </div>
            ) : filteredHistory.length === 0 ? (
              <div className="p-16 text-center text-zinc-500">
                <div className="w-20 h-20 bg-zinc-50 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </div>
                <p className="text-xl font-medium text-zinc-900 mb-2 font-heading">No activity found</p>
                <p className="text-sm text-zinc-500">No history matches this filter.</p>
              </div>
            ) : (
              <div className="divide-y divide-zinc-100">
                {filteredHistory.map((item) => {
                  let iconBg = 'bg-zinc-100 text-zinc-600';
                  let iconSvg = null;
                  let url = '#';

                  if (item.resource_type === 'book') {
                    iconBg = 'bg-indigo-50 text-indigo-600';
                    iconSvg = (
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    );
                    url = `/books/${item.resource_id}`;
                  } else if (item.resource_type === 'paper') {
                    iconBg = 'bg-teal-50 text-teal-600';
                    iconSvg = (
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    );
                    url = `/papers/${item.resource_id}`;
                  } else if (item.resource_type === 'video') {
                    iconBg = 'bg-rose-50 text-rose-600';
                    iconSvg = (
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    );
                    url = `/videos/${item.resource_id}`;
                  } else if (item.resource_type === 'post') {
                    iconBg = 'bg-amber-50 text-amber-500';
                    iconSvg = (
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                      </svg>
                    );
                    url = `/community/post/${item.resource_id}`;
                  }

                  return (
                    <Link
                      key={item.id}
                      to={url}
                      className="flex items-center gap-4 p-5 hover:bg-zinc-50 transition group"
                    >
                      <div className={`w-12 h-12 rounded-2xl ${iconBg} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform`}>
                        {iconSvg}
                      </div>
                      
                      <div className="flex-1 min-w-0">
                        <h3 className="font-bold text-zinc-900 truncate text-sm">
                          {item.title || `${item.resource_type.toUpperCase()} #${item.resource_id}`}
                        </h3>
                        <p className="text-xs text-zinc-500 flex items-center gap-2 mt-1">
                          <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-600">
                            {item.resource_type}
                          </span>
                          •
                          <span>{new Date(item.viewed_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}</span>
                        </p>
                      </div>
                      
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-zinc-400 group-hover:text-zinc-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                      </svg>
                    </Link>
                  );
                })}
              </div>
            )}
          </div>

        </div>
      </section>
    </div>
  );
}

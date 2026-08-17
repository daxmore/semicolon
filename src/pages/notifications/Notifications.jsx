import React from 'react';
import { Link } from 'react-router-dom';
import { useNotifications, useNotificationMutations } from '../../hooks/useNotifications';
import { useAuth } from '../../contexts/AuthContext';

export default function Notifications() {
  const { user } = useAuth();
  const { data: notifications, isLoading } = useNotifications(user?.id);
  const { markAsRead, markAllAsRead } = useNotificationMutations();

  const unreadCount = (notifications || []).filter((n) => !n.is_read).length;

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      {/* Main Content */}
      <section className="py-12">
        <div className="container mx-auto px-6 max-w-3xl">
          
          <div className="flex items-center justify-between mb-8">
            <h1 className="text-3xl font-bold text-zinc-900 flex items-center gap-3 font-heading">
              <div className="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              Notifications
            </h1>
            
            {notifications && notifications.length > 0 && (
              <button 
                onClick={() => markAllAsRead.mutate(user?.id)}
                className="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition cursor-pointer"
              >
                Mark all as read
              </button>
            )}
          </div>

          {/* Notifications List */}
          <div className="bg-white border border-zinc-100 rounded-2xl shadow-sm overflow-hidden">
            {isLoading ? (
              <div className="divide-y divide-zinc-100">
                {[1, 2, 3].map((n) => (
                  <div key={n} className="p-6 animate-pulse flex gap-4">
                    <div className="w-12 h-12 rounded-full bg-zinc-100"></div>
                    <div className="flex-1 space-y-2">
                      <div className="h-4 bg-zinc-100 rounded w-1/3"></div>
                      <div className="h-3 bg-zinc-100 rounded w-2/3"></div>
                    </div>
                  </div>
                ))}
              </div>
            ) : !notifications || notifications.length === 0 ? (
              <div className="p-12 text-center text-zinc-500">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 mx-auto mb-4 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p className="text-lg font-medium text-zinc-800">You're all caught up!</p>
                <p className="mt-1">You don't have any notifications right now.</p>
              </div>
            ) : (
              <div className="divide-y divide-zinc-100">
                {notifications.map((notif) => {
                  const isRead = notif.is_read;
                  const type = notif.type || 'system';
                  
                  const isComment = type === 'new_comment';
                  const iconBg = isComment ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600';

                  return (
                    <div 
                      key={notif.id}
                      className={`p-6 transition hover:bg-zinc-50 flex gap-4 ${!isRead ? 'bg-indigo-50/20' : ''}`}
                    >
                      {/* Icon */}
                      <div className={`w-12 h-12 rounded-full ${iconBg} flex items-center justify-center flex-shrink-0`}>
                        {isComment ? (
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                          </svg>
                        ) : (
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        )}
                      </div>
                      
                      {/* Content */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <h3 className={`font-bold text-zinc-900 ${isRead ? 'text-zinc-600' : ''}`}>
                            {notif.title}
                            {!isRead && (
                              <span className="inline-block w-2.5 h-2.5 bg-indigo-500 rounded-full ml-2 align-middle"></span>
                            )}
                          </h3>
                          <span className="text-xs text-zinc-400 flex-shrink-0 whitespace-nowrap">
                            {new Date(notif.created_at).toLocaleDateString('en-US', {
                              month: 'short',
                              day: 'numeric',
                              hour: 'numeric',
                              minute: '2-digit',
                            })}
                          </span>
                        </div>
                        <p className="text-zinc-600 mt-1 line-clamp-2 text-sm">
                          {notif.message}
                        </p>
                        
                        {notif.link && (
                          <div className="mt-3 flex items-center gap-4 text-sm">
                            <Link to={notif.link} className="font-medium text-indigo-600 hover:text-indigo-700">
                              View Detail &rarr;
                            </Link>
                          </div>
                        )}
                      </div>
                      
                      {/* Mark as Read Action */}
                      {!isRead ? (
                        <div className="flex-shrink-0 flex items-start pt-1">
                          <button 
                            onClick={() => markAsRead.mutate(notif.id)}
                            className="text-zinc-300 hover:text-indigo-600 transition cursor-pointer"
                            title="Mark as read"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                              <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                          </button>
                        </div>
                      ) : (
                        <div className="flex-shrink-0 flex items-start pt-1 text-zinc-200" title="Read">
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fillRule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clipRule="evenodd" />
                          </svg>
                        </div>
                      )}
                    </div>
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

import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useVideos } from '../../hooks/useVideos';
import { SYSTEM_CATEGORIES, getYoutubeId } from '../../lib/utils';
import { Search, Video, Play, Sparkles, AlertCircle } from 'lucide-react';

export default function VideosList() {
  const [category, setCategory] = useState('All');
  const [searchTerm, setSearchTerm] = useState('');

  const { data: videos, isLoading, error } = useVideos({
    category: category === 'All' ? null : category,
    search: searchTerm,
  });

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header Banner */}
      <div className="mb-10 text-center sm:text-left flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-zinc-200 pb-8">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-semibold mb-3">
            <Sparkles className="h-3.5 w-3.5" />
            Video Academy
          </div>
          <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
            Curated Tech Tutorials & Deep Dives
          </h1>
          <p className="mt-2 text-xs text-zinc-500 max-w-xl">
            High quality video walkthroughs, system design sessions, and engineering deep dives.
          </p>
        </div>
      </div>

      {/* Filter & Search Bar */}
      <div className="bg-white p-4 rounded-2xl border border-zinc-200/80 shadow-sm mb-8 space-y-4">
        {/* Search Input */}
        <div className="relative">
          <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
          <input
            type="text"
            placeholder="Search video tutorials..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-10 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition"
          />
        </div>

        {/* Categories Pills */}
        <div className="flex items-center gap-2 overflow-x-auto pt-2 border-t border-zinc-100 no-scrollbar">
          <button
            onClick={() => setCategory('All')}
            className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
              category === 'All'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            All Categories
          </button>
          {SYSTEM_CATEGORIES.map((cat) => (
            <button
              key={cat}
              onClick={() => setCategory(cat)}
              className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
                category === cat
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Videos Grid */}
      {isLoading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3, 4, 5, 6].map((n) => (
            <div key={n} className="bg-white rounded-2xl border border-zinc-200 overflow-hidden animate-pulse">
              <div className="aspect-video bg-zinc-200"></div>
              <div className="p-4 space-y-3">
                <div className="h-4 bg-zinc-200 rounded w-3/4"></div>
                <div className="h-3 bg-zinc-200 rounded w-1/2"></div>
              </div>
            </div>
          ))}
        </div>
      ) : error ? (
        <div className="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-center text-xs text-rose-700 flex items-center justify-center gap-2">
          <AlertCircle className="h-4 w-4" />
          Failed to load videos.
        </div>
      ) : videos && videos.length > 0 ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {videos.map((vid) => {
            const ytId = getYoutubeId(vid.youtube_url);
            const thumbUrl = ytId
              ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg`
              : null;

            return (
              <Link
                key={vid.id}
                to={`/videos/${vid.id}`}
                className="group bg-white rounded-2xl border border-zinc-200/90 overflow-hidden hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-500/5 transition flex flex-col justify-between"
              >
                <div>
                  {/* Thumbnail / Video preview */}
                  <div className="aspect-video bg-zinc-900 relative overflow-hidden flex items-center justify-center">
                    {thumbUrl ? (
                      <img
                        src={thumbUrl}
                        alt={vid.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                      />
                    ) : (
                      <Video className="h-10 w-10 text-zinc-600" />
                    )}
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition flex items-center justify-center">
                      <div className="w-12 h-12 rounded-full bg-white/90 text-indigo-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                        <Play className="h-5 w-5 fill-indigo-600 ml-0.5" />
                      </div>
                    </div>
                  </div>

                  <div className="p-5 space-y-2">
                    <span className="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                      {vid.category}
                    </span>

                    <h3 className="font-bold text-sm text-zinc-900 group-hover:text-indigo-600 transition line-clamp-2 pt-1">
                      {vid.title}
                    </h3>

                    {vid.description && (
                      <p className="text-xs text-zinc-500 line-clamp-2 leading-relaxed">
                        {vid.description}
                      </p>
                    )}
                  </div>
                </div>

                <div className="px-5 pb-4 text-xs font-semibold text-indigo-600 flex items-center gap-1.5">
                  <Play className="h-3.5 w-3.5" /> Watch tutorial
                </div>
              </Link>
            );
          })}
        </div>
      ) : (
        <div className="text-center py-16 bg-white rounded-2xl border border-zinc-200 p-8 space-y-3">
          <Video className="h-8 w-8 text-zinc-400 mx-auto" />
          <h3 className="text-sm font-bold text-zinc-900">No videos found</h3>
          <p className="text-xs text-zinc-500 max-w-sm mx-auto">
            Try adjusting your search query or selected category.
          </p>
        </div>
      )}
    </div>
  );
}

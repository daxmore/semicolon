import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useVideos } from '../../hooks/useVideos';
import { useCommunityCategories } from '../../hooks/useCategories';
import { getYoutubeId } from '../../lib/utils';
import { Search, Video, Play } from 'lucide-react';

export default function VideosList() {
  const { data: categories } = useCommunityCategories();
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const { data: videos, isLoading } = useVideos({
    category: selectedCategory,
    search: searchTerm,
  });

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      {/* Hero Section */}
      <section className="relative pt-6 pb-4 bg-white border-b border-zinc-200">
        <div className="container mx-auto px-6 max-w-7xl">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <h1 className="text-2xl font-bold text-zinc-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Video Tutorials
              </h1>
              <p className="text-sm text-zinc-500 mt-1">Learn with our curated video tutorials</p>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <section className="py-8 bg-[#FAFAFA]">
        <div className="container mx-auto px-6 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {/* Left Sidebar (Category Filter) */}
            <div className="lg:col-span-3 hidden lg:block">
              <div className="sticky top-20">
                <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Filter by Category</h3>
                <div className="space-y-0.5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                  <button
                    onClick={() => setSelectedCategory(null)}
                    className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${
                      !selectedCategory
                        ? 'bg-rose-50 text-rose-700'
                        : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                    }`}
                  >
                    <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                      !selectedCategory ? 'bg-rose-100/50 text-rose-600' : 'text-zinc-400 bg-zinc-50 border border-zinc-100'
                    }`}>
                      <Video className="h-3.5 w-3.5" />
                    </div>
                    <span>All Videos</span>
                  </button>

                  {(categories || []).map((cat) => (
                    <button
                      key={cat}
                      onClick={() => setSelectedCategory(cat)}
                      className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group text-left ${
                        selectedCategory === cat
                          ? 'bg-rose-50 text-rose-700'
                          : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                      }`}
                    >
                      <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                        selectedCategory === cat ? 'bg-rose-100/50 text-rose-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 border border-zinc-100'
                      }`}>
                        <Video className="h-3.5 w-3.5" />
                      </div>
                      <span className="truncate">{cat}</span>
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Middle Content Column */}
            <div className="lg:col-span-9 flex flex-col gap-4">
              
              {/* Search Bar */}
              <div className="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
                <div className="flex-1 relative mb-0">
                  <Search className="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
                  <input
                    type="text"
                    placeholder="Search video tutorials..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-rose-500 transition"
                  />
                </div>
              </div>

              {/* Selected Category Header */}
              <div className="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                <span>{selectedCategory ? `${selectedCategory} Videos` : 'All Videos'}</span>
                <span className="font-medium text-zinc-900 bg-zinc-100 border border-zinc-200 px-2 py-0.5 rounded-md">
                  {videos?.length || 0} Results
                </span>
              </div>

              {/* Grid of Videos */}
              {isLoading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {[1, 2, 3, 4].map((n) => (
                    <div key={n} className="bg-white rounded-2xl border border-zinc-100 overflow-hidden animate-pulse">
                      <div className="h-44 bg-zinc-200"></div>
                      <div className="p-6 space-y-3">
                        <div className="h-5 bg-zinc-200 rounded w-3/4"></div>
                        <div className="h-4 bg-zinc-100 rounded w-1/2"></div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : !videos || videos.length === 0 ? (
                <div className="text-center py-20 bg-white rounded-2xl border border-zinc-100 shadow-sm">
                  <div className="w-20 h-20 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <Video className="h-10 w-10 text-zinc-400" />
                  </div>
                  <p className="text-xl text-zinc-500 mb-2">No videos found</p>
                  <p className="text-zinc-400 text-sm">Try selecting a different filter</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {videos.map((video, idx) => {
                    const ytId = getYoutubeId(video.youtube_url);
                    const thumbUrl = ytId
                      ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg`
                      : null;
                    return (
                      <Link
                        key={video.id}
                        to={`/videos/${video.id}`}
                        className="card-reveal bg-white rounded-2xl border border-zinc-100 hover:border-rose-300 hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col justify-between cursor-pointer"
                        style={{ animationDelay: `${idx * 60}ms` }}
                      >
                        {/* Video Thumbnail */}
                        <div className="relative aspect-video bg-zinc-900 overflow-hidden">
                          {thumbUrl ? (
                            <img
                              src={thumbUrl}
                              alt={video.title}
                              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                          ) : (
                            <div className="w-full h-full bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
                              <Video className="h-12 w-12 text-white" />
                            </div>
                          )}
                          <div className="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <div className="w-12 h-12 rounded-full bg-rose-600/90 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                              <Play className="h-5 w-5 fill-white ml-0.5" />
                            </div>
                          </div>
                          <span className="absolute top-3 left-3 px-3 py-1 bg-black/60 backdrop-blur rounded-full text-xs font-medium text-white">
                            {video.category || 'Tech'}
                          </span>
                        </div>
                        
                        <div className="p-6 flex-1 flex flex-col justify-between">
                          <div>
                            <h3 className="text-lg font-bold text-zinc-900 mb-2 line-clamp-2 group-hover:text-rose-600 transition">
                              {video.title}
                            </h3>
                            <p className="text-zinc-500 text-sm line-clamp-2 mb-6">
                              {video.description}
                            </p>
                          </div>
                          
                          <div className="pt-2">
                            <span
                              className="w-full block py-2.5 bg-rose-600 group-hover:bg-rose-700 text-white text-sm font-semibold rounded-xl text-center shadow-xs transition"
                            >
                              Watch Tutorial
                            </span>
                          </div>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

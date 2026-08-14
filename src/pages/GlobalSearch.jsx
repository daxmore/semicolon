import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';
import { Search, BookOpen, FileText, Video, MessageSquare, ArrowRight, Sparkles, X } from 'lucide-react';

export default function GlobalSearch() {
  const [query, setQuery] = useState('');
  const [activeFilter, setActiveFilter] = useState('all'); // 'all' | 'books' | 'papers' | 'videos' | 'community'

  const { data: results, isLoading } = useQuery({
    queryKey: ['global_search', query, activeFilter],
    queryFn: async () => {
      if (!query.trim()) return { books: [], papers: [], videos: [], community: [] };

      const encoded = encodeURIComponent(query.trim());

      const [booksRes, papersRes, videosRes, commRes] = await Promise.all([
        activeFilter === 'all' || activeFilter === 'books'
          ? axiosClient.get(`/rest/v1/books?or=(title.ilike.*${encoded}*,author.ilike.*${encoded}*,subject.ilike.*${encoded}*)&limit=5`)
          : Promise.resolve({ data: [] }),
        activeFilter === 'all' || activeFilter === 'papers'
          ? axiosClient.get(`/rest/v1/papers?or=(title.ilike.*${encoded}*,subject.ilike.*${encoded}*)&limit=5`)
          : Promise.resolve({ data: [] }),
        activeFilter === 'all' || activeFilter === 'videos'
          ? axiosClient.get(`/rest/v1/videos?or=(title.ilike.*${encoded}*,category.ilike.*${encoded}*)&limit=5`)
          : Promise.resolve({ data: [] }),
        activeFilter === 'all' || activeFilter === 'community'
          ? axiosClient.get(`/rest/v1/community_posts?or=(title.ilike.*${encoded}*,category.ilike.*${encoded}*)&limit=5`)
          : Promise.resolve({ data: [] }),
      ]);

      return {
        books: booksRes.data || [],
        papers: papersRes.data || [],
        videos: videosRes.data || [],
        community: commRes.data || [],
      };
    },
    enabled: query.trim().length > 1,
  });

  const totalResultsCount =
    (results?.books?.length || 0) +
    (results?.papers?.length || 0) +
    (results?.videos?.length || 0) +
    (results?.community?.length || 0);

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      {/* Search Input Box */}
      <div className="bg-white p-6 rounded-3xl border border-zinc-200/80 shadow-lg shadow-zinc-200/40 space-y-4">
        <div className="relative flex items-center">
          <Search className="absolute left-4 h-5 w-5 text-zinc-400" />
          <input
            type="text"
            autoFocus
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search across all books, research papers, videos, and discussions..."
            className="w-full pl-12 pr-10 py-3.5 bg-zinc-50 border border-zinc-200 rounded-2xl text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition"
          />
          {query && (
            <button
              onClick={() => setQuery('')}
              className="absolute right-3.5 p-1 text-zinc-400 hover:text-zinc-600 rounded-full"
            >
              <X className="h-4 w-4" />
            </button>
          )}
        </div>

        {/* Filter Pills */}
        <div className="flex items-center gap-2 overflow-x-auto pt-1">
          {[
            { id: 'all', label: 'All Resources' },
            { id: 'books', label: 'Books' },
            { id: 'papers', label: 'Papers' },
            { id: 'videos', label: 'Videos' },
            { id: 'community', label: 'Discussions' },
          ].map((f) => (
            <button
              key={f.id}
              onClick={() => setActiveFilter(f.id)}
              className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold shrink-0 transition ${
                activeFilter === f.id
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
              }`}
            >
              {f.label}
            </button>
          ))}
        </div>
      </div>

      {/* Results Container */}
      <div className="space-y-6">
        {isLoading && (
          <div className="space-y-3">
            {[1, 2, 3].map((n) => (
              <div key={n} className="bg-white p-5 rounded-2xl border border-zinc-200 animate-pulse h-20"></div>
            ))}
          </div>
        )}

        {query.trim().length > 1 && !isLoading && totalResultsCount === 0 && (
          <div className="bg-white rounded-2xl border border-zinc-200 p-12 text-center space-y-2">
            <Search className="h-8 w-8 text-zinc-300 mx-auto" />
            <h3 className="text-sm font-bold text-zinc-900">No results found for "{query}"</h3>
            <p className="text-xs text-zinc-500">Try searching for keywords, author names, or programming languages.</p>
          </div>
        )}

        {/* Books Results */}
        {results?.books?.length > 0 && (
          <div className="space-y-3">
            <h2 className="text-xs font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-1.5">
              <BookOpen className="h-4 w-4" /> Books ({results.books.length})
            </h2>
            <div className="bg-white rounded-2xl border border-zinc-200/80 divide-y divide-zinc-100 overflow-hidden shadow-sm">
              {results.books.map((b) => (
                <Link
                  key={b.id}
                  to={`/books/${b.id}`}
                  className="p-4 hover:bg-zinc-50 flex items-center justify-between transition text-xs group"
                >
                  <div>
                    <h3 className="font-bold text-zinc-900 group-hover:text-indigo-600 transition">
                      {b.title}
                    </h3>
                    <p className="text-[11px] text-zinc-500">By {b.author} • {b.subject}</p>
                  </div>
                  <ArrowRight className="h-4 w-4 text-zinc-400 group-hover:translate-x-1 group-hover:text-indigo-600 transition" />
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Papers Results */}
        {results?.papers?.length > 0 && (
          <div className="space-y-3">
            <h2 className="text-xs font-bold uppercase tracking-wider text-amber-600 flex items-center gap-1.5">
              <FileText className="h-4 w-4" /> Research Papers ({results.papers.length})
            </h2>
            <div className="bg-white rounded-2xl border border-zinc-200/80 divide-y divide-zinc-100 overflow-hidden shadow-sm">
              {results.papers.map((p) => (
                <Link
                  key={p.id}
                  to={`/papers/${p.id}`}
                  className="p-4 hover:bg-zinc-50 flex items-center justify-between transition text-xs group"
                >
                  <div>
                    <h3 className="font-bold text-zinc-900 group-hover:text-amber-600 transition">
                      {p.title}
                    </h3>
                    <p className="text-[11px] text-zinc-500">{p.subject} • Year {p.year}</p>
                  </div>
                  <ArrowRight className="h-4 w-4 text-zinc-400 group-hover:translate-x-1 group-hover:text-amber-600 transition" />
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Videos Results */}
        {results?.videos?.length > 0 && (
          <div className="space-y-3">
            <h2 className="text-xs font-bold uppercase tracking-wider text-rose-600 flex items-center gap-1.5">
              <Video className="h-4 w-4" /> Video Tutorials ({results.videos.length})
            </h2>
            <div className="bg-white rounded-2xl border border-zinc-200/80 divide-y divide-zinc-100 overflow-hidden shadow-sm">
              {results.videos.map((v) => (
                <Link
                  key={v.id}
                  to={`/videos/${v.id}`}
                  className="p-4 hover:bg-zinc-50 flex items-center justify-between transition text-xs group"
                >
                  <div>
                    <h3 className="font-bold text-zinc-900 group-hover:text-rose-600 transition">
                      {v.title}
                    </h3>
                    <p className="text-[11px] text-zinc-500">{v.category}</p>
                  </div>
                  <ArrowRight className="h-4 w-4 text-zinc-400 group-hover:translate-x-1 group-hover:text-rose-600 transition" />
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Community Results */}
        {results?.community?.length > 0 && (
          <div className="space-y-3">
            <h2 className="text-xs font-bold uppercase tracking-wider text-emerald-600 flex items-center gap-1.5">
              <MessageSquare className="h-4 w-4" /> Discussions ({results.community.length})
            </h2>
            <div className="bg-white rounded-2xl border border-zinc-200/80 divide-y divide-zinc-100 overflow-hidden shadow-sm">
              {results.community.map((c) => (
                <Link
                  key={c.id}
                  to={`/community/post/${c.id}`}
                  className="p-4 hover:bg-zinc-50 flex items-center justify-between transition text-xs group"
                >
                  <div>
                    <h3 className="font-bold text-zinc-900 group-hover:text-emerald-600 transition">
                      {c.title}
                    </h3>
                    <p className="text-[11px] text-zinc-500">{c.category}</p>
                  </div>
                  <ArrowRight className="h-4 w-4 text-zinc-400 group-hover:translate-x-1 group-hover:text-emerald-600 transition" />
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

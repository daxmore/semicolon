import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useBooks } from '../../hooks/useBooks';
import { SYSTEM_CATEGORIES } from '../../lib/utils';
import { Search, BookOpen, ChevronRight, Sparkles, Filter, AlertCircle } from 'lucide-react';

const DIFFICULTIES = ['All', 'Easy', 'Medium', 'Hard'];

export default function BooksList() {
  const [subject, setSubject] = useState('All');
  const [difficulty, setDifficulty] = useState('All');
  const [searchTerm, setSearchTerm] = useState('');

  const { data: books, isLoading, error } = useBooks({
    subject: subject === 'All' ? null : subject,
    difficulty: difficulty === 'All' ? null : difficulty,
    search: searchTerm,
  });

  const getDifficultyBadge = (diff) => {
    switch (diff?.toLowerCase()) {
      case 'easy':
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
      case 'hard':
        return 'bg-rose-50 text-rose-700 border-rose-200';
      default:
        return 'bg-amber-50 text-amber-700 border-amber-200';
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header Banner */}
      <div className="mb-10 text-center sm:text-left flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-zinc-200 pb-8">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-semibold mb-3">
            <Sparkles className="h-3.5 w-3.5" />
            Curated Library
          </div>
          <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
            Developer Books & Guides
          </h1>
          <p className="mt-2 text-xs text-zinc-500 max-w-xl">
            In-depth guides, clean architecture references, and software engineering handbooks.
          </p>
        </div>
      </div>

      {/* Filter & Search Bar */}
      <div className="bg-white p-4 rounded-2xl border border-zinc-200/80 shadow-sm mb-8 space-y-4">
        <div className="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
          {/* Search Input */}
          <div className="relative flex-1">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
            <input
              type="text"
              placeholder="Search books by title, author, or keywords..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition"
            />
          </div>

          {/* Difficulty Filter */}
          <div className="flex items-center gap-2">
            <span className="text-xs font-semibold text-zinc-600 shrink-0 flex items-center gap-1">
              <Filter className="h-3.5 w-3.5" /> Level:
            </span>
            <div className="flex gap-1 overflow-x-auto pb-1 sm:pb-0">
              {DIFFICULTIES.map((d) => (
                <button
                  key={d}
                  onClick={() => setDifficulty(d)}
                  className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                    difficulty === d
                      ? 'bg-zinc-900 text-white shadow-sm'
                      : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
                  }`}
                >
                  {d}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Categories Pills */}
        <div className="flex items-center gap-2 overflow-x-auto pt-2 border-t border-zinc-100 no-scrollbar">
          <button
            onClick={() => setSubject('All')}
            className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
              subject === 'All'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            All Categories
          </button>
          {SYSTEM_CATEGORIES.map((cat) => (
            <button
              key={cat}
              onClick={() => setSubject(cat)}
              className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
                subject === cat
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Books Grid */}
      {isLoading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3, 4, 5, 6].map((n) => (
            <div key={n} className="bg-white p-6 rounded-2xl border border-zinc-200 animate-pulse space-y-4">
              <div className="h-5 bg-zinc-200 rounded w-3/4"></div>
              <div className="h-3 bg-zinc-200 rounded w-1/2"></div>
              <div className="h-16 bg-zinc-100 rounded"></div>
              <div className="h-4 bg-zinc-200 rounded w-1/4"></div>
            </div>
          ))}
        </div>
      ) : error ? (
        <div className="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-center text-xs text-rose-700 flex items-center justify-center gap-2">
          <AlertCircle className="h-4 w-4" />
          Failed to load books. Please verify database connection.
        </div>
      ) : books && books.length > 0 ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {books.map((book) => (
            <Link
              key={book.id}
              to={`/books/${book.id}`}
              className="group bg-white p-6 rounded-2xl border border-zinc-200/90 hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-500/5 transition flex flex-col justify-between"
            >
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <span className="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                    {book.subject}
                  </span>
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-md border uppercase tracking-wider ${getDifficultyBadge(
                      book.difficulty
                    )}`}
                  >
                    {book.difficulty}
                  </span>
                </div>

                <h3 className="font-bold text-base text-zinc-900 group-hover:text-indigo-600 transition line-clamp-2">
                  {book.title}
                </h3>

                <p className="text-xs text-zinc-500 font-medium">By {book.author}</p>

                <p className="text-xs text-zinc-600 line-clamp-3 leading-relaxed">
                  {book.description}
                </p>
              </div>

              <div className="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
                <span className="flex items-center gap-1.5">
                  <BookOpen className="h-3.5 w-3.5" /> Read book
                </span>
                <ChevronRight className="h-4 w-4 transform group-hover:translate-x-1 transition" />
              </div>
            </Link>
          ))}
        </div>
      ) : (
        <div className="text-center py-16 bg-white rounded-2xl border border-zinc-200 p-8 space-y-3">
          <BookOpen className="h-8 w-8 text-zinc-400 mx-auto" />
          <h3 className="text-sm font-bold text-zinc-900">No books found</h3>
          <p className="text-xs text-zinc-500 max-w-sm mx-auto">
            Try adjusting your search terms or category filters.
          </p>
        </div>
      )}
    </div>
  );
}

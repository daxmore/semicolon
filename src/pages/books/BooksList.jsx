import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useBooks } from '../../hooks/useBooks';
import { useAuth } from '../../contexts/AuthContext';
import { SYSTEM_CATEGORIES } from '../../lib/utils';
import { Search, BookOpen, Download } from 'lucide-react';

export default function BooksList() {
  const { isPro } = useAuth();
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const { data: books, isLoading } = useBooks({
    subject: selectedCategory,
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
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Library Books
              </h1>
              <p className="text-sm text-zinc-500 mt-1">Browse our collection of digital textbooks and references</p>
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
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                    }`}
                  >
                    <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                      !selectedCategory ? 'bg-indigo-100/50 text-indigo-600' : 'text-zinc-400 bg-zinc-50 border border-zinc-100'
                    }`}>
                      <BookOpen className="h-3.5 w-3.5" />
                    </div>
                    <span>All Books</span>
                  </button>

                  {SYSTEM_CATEGORIES.map((cat) => (
                    <button
                      key={cat}
                      onClick={() => setSelectedCategory(cat)}
                      className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group text-left ${
                        selectedCategory === cat
                          ? 'bg-indigo-50 text-indigo-700'
                          : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                      }`}
                    >
                      <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                        selectedCategory === cat ? 'bg-indigo-100/50 text-indigo-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 border border-zinc-100'
                      }`}>
                        <BookOpen className="h-3.5 w-3.5" />
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
                    placeholder="Search books by title, author, or description..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-indigo-500 transition"
                  />
                </div>
              </div>

              {/* Selected Category Header */}
              <div className="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                <span>{selectedCategory ? `${selectedCategory} Books` : 'All Books'}</span>
                <span className="font-medium text-zinc-900 bg-zinc-100 border border-zinc-200 px-2 py-0.5 rounded-md">
                  {books?.length || 0} Results
                </span>
              </div>

              {/* Grid of Books */}
              {isLoading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {[1, 2, 3, 4].map((n) => (
                    <div key={n} className="bg-white rounded-2xl border border-zinc-100 p-6 animate-pulse space-y-4">
                      <div className="h-32 bg-zinc-100 rounded-xl"></div>
                      <div className="h-5 bg-zinc-200 rounded w-3/4"></div>
                      <div className="h-4 bg-zinc-100 rounded w-1/2"></div>
                    </div>
                  ))}
                </div>
              ) : !books || books.length === 0 ? (
                <div className="text-center py-20 bg-white rounded-2xl border border-zinc-100 shadow-sm">
                  <div className="w-20 h-20 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <BookOpen className="h-10 w-10 text-zinc-400" />
                  </div>
                  <p className="text-xl text-zinc-500 mb-2">No books found</p>
                  <p className="text-zinc-400 text-sm">Try selecting a different subject filter</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {books.map((book, idx) => {
                    const dlLink = isPro && book.pdf_url ? book.pdf_url : '/pricing';
                    return (
                      <div
                        key={book.id}
                        onClick={(e) => {
                          // If download button was not clicked, navigate to book detail
                          if (!e.target.closest('.download-btn')) {
                            navigate(`/books/${book.id}`);
                          }
                        }}
                        className="card-reveal bg-white rounded-2xl border border-zinc-100 hover:border-indigo-300 hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col justify-between cursor-pointer"
                        style={{ animationDelay: `${idx * 60}ms` }}
                      >
                        {/* Cover/Header */}
                        <div className="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 p-6 relative overflow-hidden">
                          <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                          <div className="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                          <span className="relative z-10 inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                            {book.subject || 'Programming'}
                          </span>
                        </div>
                        
                        <div className="p-6 flex-1 flex flex-col justify-between">
                          <div>
                            <div className="flex items-center justify-between mb-3">
                              <span className="text-xs font-medium text-zinc-400 uppercase tracking-wider">
                                {book.difficulty || 'General'}
                              </span>
                            </div>
                            
                            <h3 className="text-lg font-bold text-zinc-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition">
                              {book.title}
                            </h3>
                            <p className="text-sm text-indigo-600 font-medium mb-3">
                              by {book.author || 'Anonymous'}
                            </p>
                            <p className="text-zinc-500 text-sm line-clamp-2 mb-6">
                              {book.description}
                            </p>
                          </div>
                          
                          <div className="flex gap-3 pt-2">
                            <span
                              className="flex-1 py-2.5 bg-indigo-600 group-hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl text-center shadow-xs transition"
                            >
                              Read Now
                            </span>
                            <Link
                              to={dlLink}
                              onClick={(e) => e.stopPropagation()}
                              target={isPro && book.pdf_url ? "_blank" : "_self"}
                              className="download-btn py-2.5 px-4 border border-zinc-200 hover:border-zinc-300 text-zinc-700 text-sm font-medium rounded-xl transition flex items-center gap-2 z-10"
                              title={isPro ? 'Download PDF' : 'Upgrade to Pro to Download'}
                            >
                              <Download className="h-4 w-4" />
                            </Link>
                          </div>
                        </div>
                      </div>
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

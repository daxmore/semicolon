import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { usePapers } from '../../hooks/usePapers';
import { useCommunityCategories } from '../../hooks/useCategories';
import { useAuth } from '../../contexts/AuthContext';
import { Search, FileText, Download } from 'lucide-react';

const YEARS = [2026, 2025, 2024, 2023, 2022, 2021, 2020];

export default function PapersList() {
  const navigate = useNavigate();
  const { isPro } = useAuth();
  const { data: categories } = useCommunityCategories();
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [selectedYear, setSelectedYear] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const { data: papers, isLoading } = usePapers({
    subject: selectedCategory,
    year: selectedYear,
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
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Research Papers
              </h1>
              <p className="text-sm text-zinc-500 mt-1">Access exam papers and research documents</p>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <section className="py-8 bg-[#FAFAFA]">
        <div className="container mx-auto px-6 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {/* Left Sidebar (Topics & Filters) */}
            <div className="lg:col-span-3 hidden lg:block">
              <div className="sticky top-20">
                <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Filter by Category</h3>
                <div className="space-y-0.5 max-h-[50vh] overflow-y-auto custom-scrollbar pr-2 mb-6">
                  <button
                    onClick={() => setSelectedCategory(null)}
                    className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${
                      !selectedCategory
                        ? 'bg-teal-50 text-teal-700'
                        : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                    }`}
                  >
                    <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                      !selectedCategory ? 'bg-teal-100/50 text-teal-600' : 'text-zinc-400 bg-zinc-50 border border-zinc-100'
                    }`}>
                      <FileText className="h-3.5 w-3.5" />
                    </div>
                    <span>All Papers</span>
                  </button>

                  {(categories || []).map((cat) => (
                    <button
                      key={cat}
                      onClick={() => setSelectedCategory(cat)}
                      className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group text-left ${
                        selectedCategory === cat
                          ? 'bg-teal-50 text-teal-700'
                          : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                      }`}
                    >
                      <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                        selectedCategory === cat ? 'bg-teal-100/50 text-teal-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 border border-zinc-100'
                      }`}>
                        <FileText className="h-3.5 w-3.5" />
                      </div>
                      <span className="truncate">{cat}</span>
                    </button>
                  ))}
                </div>

                {/* Year Filter (Inside Sidebar) */}
                <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Filter by Year</h3>
                <div className="space-y-0.5 pr-2">
                  <button
                    onClick={() => setSelectedYear(null)}
                    className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition ${
                      !selectedYear ? 'bg-teal-50 text-teal-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                    }`}
                  >
                    <span>All Years</span>
                  </button>
                  {YEARS.map((y) => (
                    <button
                      key={y}
                      onClick={() => setSelectedYear(y)}
                      className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition ${
                        selectedYear === y ? 'bg-teal-50 text-teal-700' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                      }`}
                    >
                      <span>{y}</span>
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
                    placeholder="Search papers by title or topic..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-teal-500 transition"
                  />
                </div>
              </div>

              {/* Selected Category Header */}
              <div className="flex items-center justify-between text-sm text-zinc-500 mb-2 px-1">
                <span>{selectedCategory ? `${selectedCategory} Papers` : 'All Papers'}</span>
                <span className="font-medium text-zinc-900 bg-zinc-100 border border-zinc-200 px-2 py-0.5 rounded-md">
                  {papers?.length || 0} Results
                </span>
              </div>

              {/* Grid of Papers */}
              {isLoading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {[1, 2, 3, 4].map((n) => (
                    <div key={n} className="bg-white rounded-2xl border border-zinc-100 p-6 animate-pulse space-y-4">
                      <div className="h-32 bg-zinc-100 rounded-xl"></div>
                      <div className="h-5 bg-zinc-200 rounded w-3/4"></div>
                    </div>
                  ))}
                </div>
              ) : !papers || papers.length === 0 ? (
                <div className="text-center py-20 bg-white rounded-2xl border border-zinc-100 shadow-sm">
                  <div className="w-20 h-20 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <FileText className="h-10 w-10 text-zinc-400" />
                  </div>
                  <p className="text-xl text-zinc-500 mb-2">No papers found</p>
                  <p className="text-zinc-400 text-sm">Try selecting a different filter</p>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {papers.map((paper, idx) => {
                    const dlLink = isPro && paper.pdf_url ? paper.pdf_url : '/pricing';
                    return (
                      <div
                        key={paper.id}
                        onClick={(e) => {
                          // If download button was not clicked, navigate to paper detail
                          if (!e.target.closest('.download-btn')) {
                            navigate(`/papers/${paper.id}`);
                          }
                        }}
                        className="card-reveal bg-white rounded-2xl border border-zinc-100 hover:border-teal-300 hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col justify-between cursor-pointer"
                        style={{ animationDelay: `${idx * 60}ms` }}
                      >
                        {/* Cover/Header */}
                        <div className="h-32 bg-gradient-to-br from-teal-500 to-emerald-600 p-6 relative overflow-hidden">
                          <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                          <div className="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full"></div>
                          <span className="relative z-10 inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-medium text-white">
                            {paper.subject || 'Computer Science'}
                          </span>
                        </div>
                        
                        <div className="p-6 flex-1 flex flex-col justify-between">
                          <div>
                            <div className="flex items-center justify-between mb-3">
                              <span className="text-xs font-medium text-zinc-400 uppercase tracking-wider">
                                {paper.year || 'Research'}
                              </span>
                            </div>
                            
                            <h3 className="text-lg font-bold text-zinc-900 mb-2 line-clamp-2 group-hover:text-teal-600 transition">
                              {paper.title}
                            </h3>
                            <p className="text-zinc-500 text-sm line-clamp-2 mb-6">
                              {paper.description || 'Seminal whitepaper on distributed systems and computer science fundamentals.'}
                            </p>
                          </div>
                          
                          <div className="flex gap-3 pt-2">
                            <span
                              className="flex-1 py-2.5 bg-teal-600 group-hover:bg-teal-700 text-white text-sm font-semibold rounded-xl text-center shadow-xs transition"
                            >
                              Read Paper
                            </span>
                            <Link
                              to={dlLink}
                              onClick={(e) => e.stopPropagation()}
                              target={isPro && paper.pdf_url ? "_blank" : "_self"}
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

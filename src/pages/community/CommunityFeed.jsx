import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCommunityPosts, useUserReactions, useCommunityMutations } from '../../hooks/useCommunity';
import { useAuth } from '../../contexts/AuthContext';
import { SYSTEM_CATEGORIES } from '../../lib/utils';
import { 
  Flame, 
  Sparkles, 
  Clock, 
  Search, 
  MessageSquare, 
  Share2, 
  ArrowUp, 
  ArrowDown, 
  User, 
  Check, 
  Plus,
  BookOpen
} from 'lucide-react';

export default function CommunityFeed() {
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [sort, setSort] = useState('new'); // 'hot' | 'top' | 'new'
  const [searchTerm, setSearchTerm] = useState('');
  const [copiedPostId, setCopiedPostId] = useState(null);

  const { user, profile } = useAuth();
  const navigate = useNavigate();

  const { data: posts, isLoading } = useCommunityPosts({
    category: selectedCategory,
    sort,
    search: searchTerm,
  });

  const postIds = posts?.map((p) => p.id) || [];
  const { data: userReactions } = useUserReactions(user?.id, postIds);
  const { votePost } = useCommunityMutations();

  const handleVote = (e, post, type) => {
    e.preventDefault();
    e.stopPropagation();

    if (!user) {
      navigate('/login');
      return;
    }

    const currentReaction = userReactions?.[post.id] || null;

    votePost.mutate({
      postId: post.id,
      userId: user.id,
      type,
      currentReaction,
      currentUpvotes: post.upvotes || 0,
      currentDownvotes: post.downvotes || 0,
    });
  };

  const handleShare = (e, post) => {
    e.preventDefault();
    e.stopPropagation();
    const url = `${window.location.origin}/community/post/${post.id}`;
    if (navigator.share) {
      navigator.share({ title: post.title, url }).catch(() => {});
    } else {
      navigator.clipboard.writeText(url).then(() => {
        setCopiedPostId(post.id);
        setTimeout(() => setCopiedPostId(null), 2000);
      });
    }
  };

  return (
    <div className="antialiased bg-[#FAFAFA] min-h-screen">
      {/* Adjusted Hero Section */}
      <section className="relative pt-6 pb-4 bg-white border-b border-zinc-200">
        <div className="container mx-auto px-6 max-w-7xl">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <h1 className="text-2xl font-bold text-zinc-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                Community
              </h1>
              <p className="text-sm text-zinc-500 mt-1">Connect and share ideas with fellow developers</p>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <section className="py-8 bg-[#FAFAFA]">
        <div className="container mx-auto px-6 max-w-7xl">
          {/* 3 Column Grid */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {/* Left Sidebar (Topics) */}
            <div className="lg:col-span-3 hidden lg:block">
              <div className="sticky top-20">
                <h3 className="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3 px-3">Discover Topics</h3>
                <div className="space-y-0.5 max-h-[75vh] overflow-y-auto custom-scrollbar pr-2">
                  <button
                    onClick={() => setSelectedCategory(null)}
                    className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${
                      !selectedCategory
                        ? 'bg-amber-50 text-amber-700'
                        : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                    }`}
                  >
                    <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                      !selectedCategory ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400 bg-zinc-50 border border-zinc-100'
                    }`}>
                      <BookOpen className="h-3.5 w-3.5" />
                    </div>
                    <span>All Posts</span>
                  </button>

                  {SYSTEM_CATEGORIES.map((cat) => (
                    <button
                      key={cat}
                      onClick={() => setSelectedCategory(cat)}
                      className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group text-left ${
                        selectedCategory === cat
                          ? 'bg-amber-50 text-amber-700'
                          : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900'
                      }`}
                    >
                      <div className={`w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 ${
                        selectedCategory === cat ? 'bg-amber-100/50 text-amber-600' : 'text-zinc-400 group-hover:text-zinc-500 bg-zinc-50 border border-zinc-100'
                      }`}>
                        <BookOpen className="h-3.5 w-3.5" />
                      </div>
                      <span className="truncate">{cat}</span>
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Middle Feed Column */}
            <div className="lg:col-span-6 flex flex-col gap-4">
              
              {/* Create Post Box (Reddit/Quora style) */}
              <div className="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
                <div className="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 overflow-hidden">
                  {profile?.username ? profile.username.charAt(0).toUpperCase() : 'U'}
                </div>
                
                <div className="flex-1 relative mb-0">
                  <Search className="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
                  <input
                    type="text"
                    placeholder="Search community posts..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-full pl-10 pr-4 py-2.5 text-sm text-zinc-900 focus:outline-none focus:border-indigo-500 transition"
                  />
                </div>
              </div>
              
              {/* Feed Sorting Options */}
              <div className="flex items-center gap-2 px-1">
                <button
                  onClick={() => setSort('hot')}
                  className={`flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition ${
                    sort === 'hot' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'
                  }`}
                >
                  <Flame className="h-4 w-4 text-orange-500" />
                  Hot
                </button>
                <button
                  onClick={() => setSort('top')}
                  className={`flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition ${
                    sort === 'top' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'
                  }`}
                >
                  <Sparkles className="h-4 w-4 text-indigo-500" />
                  Top
                </button>
                <button
                  onClick={() => setSort('new')}
                  className={`flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-full transition ${
                    sort === 'new' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-semibold text-zinc-500 hover:bg-zinc-100/80 hover:text-zinc-700'
                  }`}
                >
                  <Clock className="h-4 w-4 text-zinc-500" />
                  New
                </button>
              </div>

              {/* Post List */}
              {isLoading ? (
                <div className="space-y-4">
                  {[1, 2, 3].map((n) => (
                    <div key={n} className="bg-white border border-zinc-200 rounded-xl p-4 animate-pulse space-y-3">
                      <div className="h-4 bg-zinc-200 rounded w-1/3"></div>
                      <div className="h-6 bg-zinc-200 rounded w-3/4"></div>
                      <div className="h-16 bg-zinc-100 rounded"></div>
                    </div>
                  ))}
                </div>
              ) : !posts || posts.length === 0 ? (
                <div className="text-center py-16 bg-white border border-zinc-200 rounded-xl shadow-sm">
                  <div className="w-16 h-16 bg-zinc-50 border border-zinc-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <MessageSquare className="h-8 w-8 text-zinc-300" />
                  </div>
                  <p className="text-lg font-bold text-zinc-900 mb-1">No posts found</p>
                  <p className="text-sm text-zinc-500 mb-6">Looks like it's quiet here. Be the first to post!</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {posts.map((post) => {
                    const reaction = userReactions?.[post.id];
                    const hasUpvoted = reaction === 'upvote';
                    const hasDownvoted = reaction === 'downvote';

                    return (
                      <article
                        key={post.id}
                        className="bg-white border border-zinc-200 rounded-xl hover:border-zinc-300 transition-colors duration-200 overflow-hidden shadow-sm flex"
                      >
                        {/* Desktop Upvote Sidebar */}
                        <div className="w-12 bg-zinc-50/50 flex flex-col items-center pt-3 pb-2 flex-shrink-0 border-r border-zinc-100 hidden sm:flex">
                          <button
                            onClick={(e) => handleVote(e, post, 'upvote')}
                            className={`p-1 rounded transition group ${
                              hasUpvoted ? 'text-amber-500' : 'text-zinc-400 hover:bg-zinc-200'
                            }`}
                          >
                            <ArrowUp className={`h-6 w-6 ${hasUpvoted ? 'fill-amber-500' : 'group-hover:text-amber-500'}`} />
                          </button>
                          <span className="text-sm font-bold text-zinc-900 my-1">
                            {post.upvotes || 0}
                          </span>
                          <button
                            onClick={(e) => handleVote(e, post, 'downvote')}
                            className={`p-1 rounded transition group ${
                              hasDownvoted ? 'text-blue-500' : 'text-zinc-400 hover:bg-zinc-200'
                            }`}
                          >
                            <ArrowDown className={`h-6 w-6 ${hasDownvoted ? 'fill-blue-500' : 'group-hover:text-blue-500'}`} />
                          </button>
                        </div>

                        {/* Main Post Content */}
                        <div className="flex-1 p-2 sm:p-4 min-w-0">
                          {/* Header Row (Category, User, Date) */}
                          <div className="flex items-center gap-1.5 text-[13px] mb-2 px-2 sm:px-0">
                            <span className="font-bold text-zinc-900">{post.category || 'General'}</span>
                            <span className="text-zinc-400">•</span>
                            <span className="text-zinc-500">Posted by</span>
                            <span className="text-zinc-500 font-medium hover:underline hover:text-zinc-900 cursor-pointer flex items-center gap-1">
                              {post.profiles?.username || 'Anonymous'}
                              {post.profiles?.is_pro && (
                                <span className="inline-flex items-center bg-indigo-100 text-indigo-700 text-[10px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-tight" title="Semicolon Pro Member">
                                  Pro
                                </span>
                              )}
                            </span>
                            <span className="text-zinc-400">•</span>
                            <span className="text-zinc-500 text-xs">
                              {new Date(post.created_at).toLocaleDateString()}
                            </span>
                          </div>
                          
                          {/* Post Title */}
                          <h3 className="text-lg font-semibold text-zinc-900 mb-1 leading-snug px-2 sm:px-0">
                            <Link to={`/community/post/${post.id}`} className="hover:underline">
                              {post.title}
                            </Link>
                          </h3>
                          
                          {/* Text Snippet */}
                          <p className="text-zinc-600 text-sm mb-3 line-clamp-3 px-2 sm:px-0 leading-relaxed">
                            {post.description}
                          </p>

                          {/* Attached Image (if any) */}
                          {post.image_url && (
                            <Link to={`/community/post/${post.id}`} className="block mb-3 bg-zinc-50 overflow-hidden sm:rounded-lg border-y sm:border border-zinc-200/60 max-h-[500px] flex items-center justify-center -mx-2 sm:mx-0">
                              <img
                                src={post.image_url}
                                alt="Post media"
                                className="w-full h-full object-contain max-h-[500px]"
                              />
                            </Link>
                          )}
                          
                          {/* Action Row */}
                          <div className="flex items-center gap-1 mt-1 px-1 sm:px-0">
                            {/* Mobile Upvote Button */}
                            <div className="flex sm:hidden items-center text-zinc-500 border border-zinc-200 rounded-full px-2 py-1 gap-1 mr-2">
                              <button onClick={(e) => handleVote(e, post, 'upvote')}>
                                <ArrowUp className={`h-4 w-4 ${hasUpvoted ? 'text-amber-500' : ''}`} />
                              </button>
                              <span className="text-xs font-bold">{post.upvotes || 0}</span>
                              <button onClick={(e) => handleVote(e, post, 'downvote')}>
                                <ArrowDown className={`h-4 w-4 ${hasDownvoted ? 'text-blue-500' : ''}`} />
                              </button>
                            </div>

                            <Link
                              to={`/community/post/${post.id}`}
                              className="flex items-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 rounded-md transition"
                            >
                              <MessageSquare className="h-4 w-4" />
                              <span>Comments</span>
                            </Link>
                            
                            <button
                              onClick={(e) => handleShare(e, post)}
                              className="flex items-center gap-1.5 px-2 py-1.5 text-xs font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 rounded-md transition"
                            >
                              {copiedPostId === post.id ? (
                                <>
                                  <Check className="h-4 w-4 text-emerald-500" />
                                  <span className="text-emerald-600">Copied!</span>
                                </>
                              ) : (
                                <>
                                  <Share2 className="h-4 w-4" />
                                  <span>Share</span>
                                </>
                              )}
                            </button>
                          </div>
                        </div>
                      </article>
                    );
                  })}
                </div>
              )}
            </div>

            {/* Right Sidebar (Stats / Trending) */}
            <div className="lg:col-span-3 hidden lg:block">
              <div className="sticky top-20">
                <div className="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm mb-4">
                  <div className="flex items-center gap-2 mb-3 px-1">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span className="font-bold text-zinc-900 text-sm">About Community</span>
                  </div>
                  <p className="text-[13px] text-zinc-600 mb-4 px-1 leading-relaxed">
                    Welcome to Semicolon Community! The best place to share code, ask questions, and collaborate with your peers on everything from Frontend to Data Science.
                  </p>
                  <div className="flex items-center gap-4 text-center px-2 py-3 border-y border-zinc-100 mb-4">
                    <div className="flex-1">
                      <span className="block text-lg font-bold text-zinc-900">
                        {posts?.length || 0}
                      </span>
                      <span className="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Posts</span>
                    </div>
                    <div className="w-px h-8 bg-zinc-200"></div>
                    <div className="flex-1">
                      <span className="block text-lg font-bold text-zinc-900">
                        5,000+
                      </span>
                      <span className="block text-[11px] font-medium text-zinc-500 uppercase tracking-wide mt-0.5">Members</span>
                    </div>
                  </div>
                  <Link
                    to="/community/new"
                    className="block w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-white font-medium text-sm text-center rounded-full transition shadow-sm"
                  >
                    Create Post
                  </Link>
                </div>
                
                {/* Mini Links Footer */}
                <div className="flex flex-wrap gap-x-3 gap-y-1.5 px-2 text-xs text-zinc-500 font-medium pt-2">
                  <a href="#" className="hover:underline">User Agreement</a>
                  <a href="#" className="hover:underline">Privacy Policy</a>
                  <a href="#" className="hover:underline">Moderator Code Of Conduct</a>
                  <span className="w-full mt-1 text-zinc-400">Semicolon © 2026</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </section>
    </div>
  );
}

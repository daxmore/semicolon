import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCommunityPosts, useUserReactions, useCommunityMutations } from '../../hooks/useCommunity';
import { useAuth } from '../../contexts/AuthContext';
import { SYSTEM_CATEGORIES, timeAgo } from '../../lib/utils';
import { 
  MessageSquare, 
  Plus, 
  ArrowUp, 
  ArrowDown, 
  Search, 
  Flame, 
  Sparkles, 
  Clock, 
  TrendingUp, 
  CheckCircle2, 
  AlertCircle 
} from 'lucide-react';

export default function CommunityFeed() {
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [sort, setSort] = useState('new'); // 'new' | 'top'
  const [searchTerm, setSearchTerm] = useState('');

  const { user } = useAuth();
  const navigate = useNavigate();

  const { data: posts, isLoading, error } = useCommunityPosts({
    category: selectedCategory === 'All' ? null : selectedCategory,
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

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
      {/* Header Banner */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 pb-6">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-700 text-xs font-semibold mb-2">
            <MessageSquare className="h-3.5 w-3.5 text-amber-600" />
            Developer Discussions
          </div>
          <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
            Community Discussions
          </h1>
          <p className="mt-1 text-xs text-zinc-500">
            Share engineering thoughts, ask architecture questions, and collaborate with peers.
          </p>
        </div>

        <Link
          to="/community/new"
          className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-amber-600/20 transition shrink-0"
        >
          <Plus className="h-4 w-4" /> New Discussion (+10 XP)
        </Link>
      </div>

      {/* Filter and Sort Toolbar */}
      <div className="bg-white p-4 rounded-2xl border border-zinc-200/80 shadow-sm space-y-4">
        <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
          {/* Search */}
          <div className="relative flex-1">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
            <input
              type="text"
              placeholder="Search discussions by title or content..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition"
            />
          </div>

          {/* Sort Buttons */}
          <div className="flex items-center gap-1 shrink-0 bg-zinc-100 p-1 rounded-xl">
            <button
              onClick={() => setSort('new')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 ${
                sort === 'new' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-800'
              }`}
            >
              <Clock className="h-3.5 w-3.5" /> Latest
            </button>
            <button
              onClick={() => setSort('top')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 ${
                sort === 'top' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-800'
              }`}
            >
              <TrendingUp className="h-3.5 w-3.5" /> Top Voted
            </button>
          </div>
        </div>

        {/* Categories Pills */}
        <div className="flex items-center gap-2 overflow-x-auto pt-2 border-t border-zinc-100 no-scrollbar">
          <button
            onClick={() => setSelectedCategory('All')}
            className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
              selectedCategory === 'All'
                ? 'bg-amber-600 text-white shadow-sm'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            All Categories
          </button>
          {SYSTEM_CATEGORIES.map((cat) => (
            <button
              key={cat}
              onClick={() => setSelectedCategory(cat)}
              className={`px-3 py-1 rounded-full text-xs font-medium shrink-0 transition ${
                selectedCategory === cat
                  ? 'bg-amber-600 text-white shadow-sm'
                  : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Posts List */}
      {isLoading ? (
        <div className="space-y-4">
          {[1, 2, 3, 4].map((n) => (
            <div key={n} className="bg-white p-6 rounded-2xl border border-zinc-200 animate-pulse space-y-3">
              <div className="h-4 bg-zinc-200 rounded w-1/4"></div>
              <div className="h-6 bg-zinc-200 rounded w-3/4"></div>
              <div className="h-10 bg-zinc-100 rounded"></div>
            </div>
          ))}
        </div>
      ) : error ? (
        <div className="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-center text-xs text-rose-700 flex items-center justify-center gap-2">
          <AlertCircle className="h-4 w-4" />
          Failed to load community discussions.
        </div>
      ) : posts && posts.length > 0 ? (
        <div className="space-y-4">
          {posts.map((post) => {
            const currentReaction = userReactions?.[post.id];
            const author = post.profiles;
            const netVotes = (post.upvotes || 0) - (post.downvotes || 0);

            return (
              <Link
                key={post.id}
                to={`/community/post/${post.id}`}
                className="group block bg-white p-6 rounded-2xl border border-zinc-200/90 hover:border-amber-400 hover:shadow-xl hover:shadow-amber-500/5 transition"
              >
                <div className="flex items-start gap-4">
                  {/* Voting Column */}
                  <div className="flex flex-col items-center gap-1 bg-zinc-50 border border-zinc-200/80 p-1.5 rounded-xl shrink-0">
                    <button
                      onClick={(e) => handleVote(e, post, 'upvote')}
                      className={`p-1.5 rounded-lg transition ${
                        currentReaction === 'upvote'
                          ? 'bg-amber-600 text-white shadow-sm'
                          : 'text-zinc-400 hover:text-amber-600 hover:bg-zinc-200'
                      }`}
                      title="Upvote"
                    >
                      <ArrowUp className="h-4 w-4" />
                    </button>
                    <span
                      className={`text-xs font-bold ${
                        currentReaction === 'upvote'
                          ? 'text-amber-600'
                          : currentReaction === 'downvote'
                          ? 'text-rose-600'
                          : 'text-zinc-700'
                      }`}
                    >
                      {netVotes}
                    </span>
                    <button
                      onClick={(e) => handleVote(e, post, 'downvote')}
                      className={`p-1.5 rounded-lg transition ${
                        currentReaction === 'downvote'
                          ? 'bg-rose-600 text-white shadow-sm'
                          : 'text-zinc-400 hover:text-rose-600 hover:bg-zinc-200'
                      }`}
                      title="Downvote"
                    >
                      <ArrowDown className="h-4 w-4" />
                    </button>
                  </div>

                  {/* Post Main Body */}
                  <div className="flex-1 min-w-0 space-y-2.5">
                    {/* Meta info */}
                    <div className="flex flex-wrap items-center gap-2 text-xs">
                      <div className="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-[10px] overflow-hidden shrink-0">
                        {author?.avatar_url ? (
                          <img src={author.avatar_url} alt="" className="w-full h-full object-cover" />
                        ) : (
                          <span>{(author?.username || 'U').charAt(0).toUpperCase()}</span>
                        )}
                      </div>

                      <span className="font-semibold text-zinc-900">{author?.username || 'Anonymous'}</span>

                      {author?.is_pro && (
                        <span className="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded border border-amber-300">
                          PRO
                        </span>
                      )}

                      <span className="text-zinc-400">•</span>
                      <span className="text-zinc-400">{timeAgo(post.created_at)}</span>

                      <span className="ml-auto text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                        {post.category}
                      </span>
                    </div>

                    {/* Title */}
                    <h2 className="font-bold text-base text-zinc-900 group-hover:text-amber-600 transition leading-snug">
                      {post.title}
                    </h2>

                    {/* Description preview */}
                    <p className="text-xs text-zinc-600 line-clamp-2 leading-relaxed">
                      {post.description}
                    </p>

                    {/* Post image thumbnail if present */}
                    {post.image_url && (
                      <div className="h-36 max-w-sm rounded-xl overflow-hidden border border-zinc-200 bg-zinc-100">
                        <img src={post.image_url} alt="" className="w-full h-full object-cover" />
                      </div>
                    )}
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      ) : (
        <div className="bg-white rounded-2xl border border-zinc-200 p-12 text-center space-y-3">
          <MessageSquare className="h-8 w-8 text-zinc-400 mx-auto" />
          <h3 className="text-sm font-bold text-zinc-900">No discussions found</h3>
          <p className="text-xs text-zinc-500 max-w-sm mx-auto">
            Be the first to start a conversation in this topic!
          </p>
          <Link
            to="/community/new"
            className="inline-block mt-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold"
          >
            Create Discussion
          </Link>
        </div>
      )}
    </div>
  );
}

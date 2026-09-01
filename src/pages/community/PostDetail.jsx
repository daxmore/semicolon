import React, { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { 
  useCommunityPost, 
  useCommunityComments, 
  useUserReactions, 
  useCommunityMutations 
} from '../../hooks/useCommunity';
import { useAuth } from '../../contexts/AuthContext';
import { useToast } from '../../contexts/ToastContext';
import { timeAgo, calculateLevel } from '../../lib/utils';
import { axiosClient } from '../../lib/axiosClient';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';
import { 
  ArrowLeft, 
  ArrowUp, 
  ArrowDown, 
  MessageSquare, 
  Check, 
  CheckCircle2, 
  Trash2, 
  Flag, 
  Send, 
  CornerDownRight, 
  Sparkles, 
  AlertCircle 
} from 'lucide-react';

export default function PostDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, profile, isAdmin, refreshProfile } = useAuth();
  const { showToast } = useToast();

  const { data: post, isLoading: postLoading, error: postError } = useCommunityPost(id);
  const { data: comments, isLoading: commentsLoading } = useCommunityComments(id);
  const { data: userReactions } = useUserReactions(user?.id, [parseInt(id, 10)]);

  const { 
    votePost, 
    addComment, 
    toggleAcceptAnswer, 
    deleteComment, 
    deletePost, 
    submitReport 
  } = useCommunityMutations();

  const [commentText, setCommentText] = useState('');
  const [replyParentId, setReplyParentId] = useState(null);
  const [replyText, setReplyText] = useState('');
  const [reportReason, setReportReason] = useState('');
  const [showReportModal, setShowReportModal] = useState(false);
  const [reportTarget, setReportTarget] = useState(null); // { type: 'post'|'comment', id: number }
  
  // Deletion modals
  const [showDeletePostModal, setShowDeletePostModal] = useState(false);
  const [commentToDelete, setCommentToDelete] = useState(null);

  // Handle post vote
  const handleVote = (type) => {
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

  // Submit top-level comment (+10 XP)
  const handleAddComment = async (e) => {
    e.preventDefault();
    if (!user) {
      navigate('/login');
      return;
    }
    if (!commentText.trim()) return;

    try {
      await addComment.mutateAsync({
        post_id: post.id,
        user_id: user.id,
        parent_id: null,
        content: commentText.trim(),
      });

      // Award 10 XP
      const updatedXp = (profile?.xp_total || 0) + 10;
      const updatedWeeklyXp = (profile?.xp_weekly || 0) + 10;
      const newLevel = calculateLevel(updatedXp);

      await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, {
        xp_total: updatedXp,
        xp_weekly: updatedWeeklyXp,
        level: newLevel,
      });

      // Send notification to post author if not self
      if (post.user_id !== user.id) {
        await axiosClient.post('/rest/v1/notifications', {
          user_id: post.user_id,
          type: 'new_comment',
          title: 'New comment on your discussion',
          message: `${profile?.username || 'Someone'} replied: "${commentText.slice(0, 50)}..."`,
          link: `/community/post/${post.id}`,
        });
      }

      await refreshProfile();
      setCommentText('');
      showToast('Comment posted! +10 XP');
    } catch (err) {
      console.error(err);
      showToast('Failed to post comment', 'error');
    }
  };

  // Submit nested reply (+10 XP)
  const handleAddReply = async (parentId) => {
    if (!user) {
      navigate('/login');
      return;
    }
    if (!replyText.trim()) return;

    try {
      await addComment.mutateAsync({
        post_id: post.id,
        user_id: user.id,
        parent_id: parentId,
        content: replyText.trim(),
      });

      setReplyText('');
      setReplyParentId(null);
      showToast('Reply posted! +10 XP');
    } catch (err) {
      console.error(err);
      showToast('Failed to post reply', 'error');
    }
  };

  const confirmDeletePost = async () => {
    try {
      await deletePost.mutateAsync(post.id);
      setShowDeletePostModal(false);
      showToast('Discussion post deleted.');
      navigate('/community');
    } catch (err) {
      console.error('Failed to delete post:', err);
      showToast('Failed to delete post', 'error');
    }
  };

  const confirmDeleteComment = async () => {
    if (!commentToDelete) return;
    try {
      await deleteComment.mutateAsync({ commentId: commentToDelete.id, postId: post.id });
      setCommentToDelete(null);
      showToast('Comment deleted.');
    } catch (err) {
      console.error('Failed to delete comment:', err);
      showToast('Failed to delete comment', 'error');
    }
  };

  const handleReportSubmit = async (e) => {
    e.preventDefault();
    if (!user || !reportTarget) return;

    try {
      await submitReport.mutateAsync({
        target_type: reportTarget.type,
        target_id: reportTarget.id,
        user_id: user.id,
        reason: reportReason,
      });
      setShowReportModal(false);
      setReportReason('');
      showToast('Report submitted for moderation.');
    } catch (err) {
      console.error(err);
      showToast('Failed to submit report', 'error');
    }
  };

  if (postLoading) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-16 animate-pulse space-y-6">
        <div className="h-6 bg-zinc-200 rounded w-1/4"></div>
        <div className="h-48 bg-zinc-100 rounded-2xl"></div>
        <div className="h-32 bg-zinc-100 rounded-2xl"></div>
      </div>
    );
  }

  if (postError || !post) {
    return (
      <div className="max-w-md mx-auto px-4 py-20 text-center space-y-4">
        <div className="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
          <AlertCircle className="h-6 w-6" />
        </div>
        <h2 className="text-base font-bold text-zinc-900">Discussion Not Found</h2>
        <p className="text-xs text-zinc-500">The discussion post you are looking for has been removed or does not exist.</p>
        <Link
          to="/community"
          className="inline-flex items-center gap-1.5 px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Discussions
        </Link>
      </div>
    );
  }

  const currentReaction = userReactions?.[post.id] || null;
  const netVotes = (post.upvotes || 0) - (post.downvotes || 0);
  const author = post.profiles || null;
  const canManagePost = user && (user.id === post.user_id || isAdmin);

  // Group threaded comments by parent ID
  const commentsByParent = {};
  const rootComments = [];

  (comments || []).forEach((c) => {
    if (!c.parent_id) {
      rootComments.push(c);
    } else {
      if (!commentsByParent[c.parent_id]) {
        commentsByParent[c.parent_id] = [];
      }
      commentsByParent[c.parent_id].push(c);
    }
  });

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-6">
      {/* Back Link */}
      <Link
        to="/community"
        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-amber-600 transition"
      >
        <ArrowLeft className="h-3.5 w-3.5" /> Back to Discussions
      </Link>

      {/* Main Post Card */}
      <div className="bg-white rounded-2xl border border-zinc-200/80 p-6 sm:p-8 shadow-sm space-y-6">
        <div className="flex items-start gap-4">
          {/* Voting */}
          <div className="flex flex-col items-center gap-1 bg-zinc-50 border border-zinc-200/80 p-2 rounded-xl shrink-0">
            <button
              onClick={() => handleVote('upvote')}
              className={`p-1.5 rounded-lg transition ${
                currentReaction === 'upvote'
                  ? 'bg-amber-600 text-white shadow-sm'
                  : 'text-zinc-400 hover:text-amber-600 hover:bg-zinc-200'
              }`}
            >
              <ArrowUp className="h-5 w-5" />
            </button>
            <span className="text-xs font-bold text-zinc-800">{netVotes}</span>
            <button
              onClick={() => handleVote('downvote')}
              className={`p-1.5 rounded-lg transition ${
                currentReaction === 'downvote'
                  ? 'bg-rose-600 text-white shadow-sm'
                  : 'text-zinc-400 hover:text-rose-600 hover:bg-zinc-200'
              }`}
            >
              <ArrowDown className="h-5 w-5" />
            </button>
          </div>

          {/* Post Content */}
          <div className="flex-1 space-y-4">
            {/* Header info */}
            <div className="flex flex-wrap items-center justify-between gap-2 text-xs">
              <div className="flex items-center gap-2">
                <div className="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs overflow-hidden shrink-0">
                  {author?.avatar_url ? (
                    <img src={author.avatar_url} alt="" className="w-full h-full object-cover" />
                  ) : (
                    <span>{(author?.username || 'U').charAt(0).toUpperCase()}</span>
                  )}
                </div>
                <span className="font-semibold text-zinc-900">{author?.username}</span>
                {author?.is_pro && (
                  <span className="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded border border-amber-300">
                    PRO
                  </span>
                )}
                <span className="text-zinc-400">•</span>
                <span className="text-zinc-400">{timeAgo(post.created_at)}</span>
              </div>

              <span className="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                {post.category}
              </span>
            </div>

            {/* Title */}
            <h1 className="text-2xl font-bold font-heading text-zinc-900 tracking-tight">
              {post.title}
            </h1>

            {/* Body */}
            <div className="text-xs text-zinc-700 leading-relaxed whitespace-pre-line font-sans">
              {post.description}
            </div>

            {/* Image */}
            {post.image_url && (
              <div className="rounded-xl border border-zinc-200 overflow-hidden max-h-80 bg-zinc-900 flex items-center justify-center">
                <img src={post.image_url} alt="" className="w-full h-full max-h-80 object-contain" />
              </div>
            )}

            {/* Post actions */}
            <div className="flex items-center justify-between pt-4 border-t border-zinc-100 text-xs text-zinc-500">
              <span className="flex items-center gap-1.5 font-semibold">
                <MessageSquare className="h-4 w-4" />
                {comments?.length || 0} Answers / Replies
              </span>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => {
                    if (!user) {
                      navigate('/login');
                      return;
                    }
                    setReportTarget({ type: 'post', id: post.id });
                    setShowReportModal(true);
                  }}
                  className="hover:text-rose-600 p-1.5 rounded-lg hover:bg-zinc-100 transition flex items-center gap-1 cursor-pointer"
                  title="Report Post"
                >
                  <Flag className="h-3.5 w-3.5" />
                  <span>Report</span>
                </button>

                {canManagePost && (
                  <button
                    onClick={() => setShowDeletePostModal(true)}
                    className="text-rose-600 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition flex items-center gap-1 font-semibold cursor-pointer"
                  >
                    <Trash2 className="h-3.5 w-3.5" />
                    <span>Delete</span>
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Write Comment Box / Guest Login Prompt */}
      {user ? (
        <div className="bg-white rounded-2xl border border-zinc-200/80 p-6 shadow-sm space-y-4">
          <h3 className="text-xs font-bold uppercase tracking-wider text-zinc-900">
            Your Answer or Solution (+10 XP)
          </h3>
          <form onSubmit={handleAddComment} className="space-y-3">
            <textarea
              rows="3"
              required
              placeholder="Write a helpful, technical answer..."
              value={commentText}
              onChange={(e) => setCommentText(e.target.value)}
              className="w-full p-3 bg-zinc-50 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition"
            />
            <div className="flex justify-end">
              <button
                type="submit"
                disabled={!commentText.trim() || addComment.isPending}
                className="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-md shadow-amber-600/20 transition flex items-center gap-1.5 cursor-pointer"
              >
                <Send className="h-3.5 w-3.5" />
                {addComment.isPending ? 'Posting...' : 'Post Answer'}
              </button>
            </div>
          </form>
        </div>
      ) : (
        <div className="bg-amber-50/50 border border-amber-200/80 rounded-2xl p-6 text-center space-y-3 shadow-xs">
          <h3 className="text-sm font-bold text-zinc-900">Join the Discussion</h3>
          <p className="text-xs text-zinc-600 max-w-md mx-auto">
            Log in or create a free developer account to answer questions, reply in thread chains, vote, and earn XP.
          </p>
          <div className="flex items-center justify-center gap-3 pt-1">
            <Link
              to="/login"
              className="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-xs transition"
            >
              Sign In to Answer
            </Link>
            <Link
              to="/signup"
              className="px-4 py-2 bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-700 rounded-xl text-xs font-semibold transition"
            >
              Create Account
            </Link>
          </div>
        </div>
      )}

      {/* Threaded Comments Section */}
      <div className="space-y-4">
        <h2 className="text-sm font-bold text-zinc-900 flex items-center gap-2">
          <span>Discussion Thread Chain</span>
          <span className="text-xs text-zinc-400 font-normal">({comments?.length || 0})</span>
        </h2>

        {commentsLoading ? (
          <div className="space-y-3">
            {[1, 2].map((n) => (
              <div key={n} className="bg-white p-5 rounded-2xl border border-zinc-200 animate-pulse h-28"></div>
            ))}
          </div>
        ) : rootComments.length > 0 ? (
          <div className="space-y-4">
            {rootComments.map((comment) => (
              <CommentCard
                key={comment.id}
                comment={comment}
                postAuthorId={post.user_id}
                currentUserId={user?.id}
                isAdmin={isAdmin}
                commentsByParent={commentsByParent}
                replyParentId={replyParentId}
                setReplyParentId={setReplyParentId}
                replyText={replyText}
                setReplyText={setReplyText}
                onReplySubmit={handleAddReply}
                onAcceptAnswer={(commentId, currentStatus) =>
                  toggleAcceptAnswer.mutate({ commentId, postId: post.id, currentStatus })
                }
                onDeleteComment={(commentItem) => setCommentToDelete(commentItem)}
                onReport={(commentId, isGuestTrigger) => {
                  if (!user || isGuestTrigger) {
                    navigate('/login');
                    return;
                  }
                  setReportTarget({ type: 'comment', id: commentId });
                  setShowReportModal(true);
                }}
              />
            ))}
          </div>
        ) : (
          <div className="bg-white p-8 rounded-2xl border border-zinc-200 text-center space-y-2">
            <p className="text-xs text-zinc-500">No replies yet. Be the first to share an answer!</p>
          </div>
        )}
      </div>

      {/* Report Modal */}
      {showReportModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <h3 className="font-bold text-sm text-zinc-900">Report Inappropriate Content</h3>
            <form onSubmit={handleReportSubmit} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Reason for reporting</label>
                <textarea
                  rows="3"
                  required
                  placeholder="Explain why this content violates community guidelines..."
                  value={reportReason}
                  onChange={(e) => setReportReason(e.target.value)}
                  className="w-full p-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                />
              </div>
              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setShowReportModal(false)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 font-semibold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold"
                >
                  Submit Report
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Post Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={showDeletePostModal}
        onClose={() => setShowDeletePostModal(false)}
        onConfirm={confirmDeletePost}
        title="Delete Discussion Post"
        itemName={post?.title || ''}
        message="Are you sure you want to delete this discussion? All comments and replies will be permanently removed."
        isLoading={deletePost.isPending}
      />

      {/* Delete Comment Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!commentToDelete}
        onClose={() => setCommentToDelete(null)}
        onConfirm={confirmDeleteComment}
        title="Delete Comment / Reply"
        itemName={commentToDelete ? `"${commentToDelete.content.slice(0, 40)}..."` : ''}
        message="Are you sure you want to delete this comment? Nested replies underneath will also be deleted."
        isLoading={deleteComment.isPending}
      />
    </div>
  );
}

// Full Recursive Threaded Comment Card Component Supporting Multi-Level Chains
function CommentCard({
  comment,
  postAuthorId,
  currentUserId,
  isAdmin,
  commentsByParent = {},
  replyParentId,
  setReplyParentId,
  replyText,
  setReplyText,
  onReplySubmit,
  onAcceptAnswer,
  onDeleteComment,
  onReport,
  depth = 0,
}) {
  const isPostAuthor = currentUserId === postAuthorId;
  const isCommentAuthor = currentUserId === comment.user_id;
  const canDelete = isCommentAuthor || isAdmin;
  const author = comment.profiles;
  const childList = commentsByParent[comment.id] || [];

  return (
    <div
      id={`comment-${comment.id}`}
      className={`p-4 sm:p-5 rounded-2xl border transition space-y-3 ${
        comment.is_accepted
          ? 'bg-emerald-50/40 border-emerald-300 shadow-sm'
          : 'bg-white border-zinc-200/80 shadow-xs'
      }`}
    >
      {/* Accepted Answer Badge */}
      {comment.is_accepted && (
        <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold border border-emerald-300">
          <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600" />
          Accepted Solution
        </div>
      )}

      {/* Comment Header */}
      <div className="flex items-center justify-between text-xs">
        <div className="flex items-center gap-2">
          <div className="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-[10px] shrink-0 overflow-hidden">
            {author?.avatar_url ? (
              <img src={author.avatar_url} alt="" className="w-full h-full object-cover" />
            ) : (
              <span>{(author?.username || 'U').charAt(0).toUpperCase()}</span>
            )}
          </div>
          <span className="font-semibold text-zinc-900">{author?.username}</span>
          {author?.is_pro && (
            <span className="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded border border-amber-200">
              PRO
            </span>
          )}
          <span className="text-zinc-400">•</span>
          <span className="text-zinc-400">{timeAgo(comment.created_at)}</span>
        </div>

        {/* Accept Answer button for Post Author */}
        {isPostAuthor && (
          <button
            onClick={() => onAcceptAnswer(comment.id, comment.is_accepted)}
            className={`text-xs font-semibold px-2.5 py-1 rounded-lg border transition flex items-center gap-1 cursor-pointer ${
              comment.is_accepted
                ? 'bg-emerald-600 text-white border-emerald-600'
                : 'bg-zinc-50 border-zinc-200 text-zinc-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300'
            }`}
          >
            <Check className="h-3.5 w-3.5" />
            {comment.is_accepted ? 'Accepted' : 'Mark as Accepted'}
          </button>
        )}
      </div>

      {/* Content */}
      <p className="text-xs text-zinc-700 leading-relaxed whitespace-pre-line">{comment.content}</p>

      {/* Action Footer */}
      <div className="flex items-center gap-4 text-xs text-zinc-500 pt-2 border-t border-zinc-100">
        <button
          onClick={() => {
            if (!currentUserId) {
              onReport(null, true); // trigger guest login
              return;
            }
            setReplyParentId(replyParentId === comment.id ? null : comment.id);
            setReplyText('');
          }}
          className="font-semibold hover:text-amber-600 flex items-center gap-1 cursor-pointer"
        >
          <CornerDownRight className="h-3.5 w-3.5" /> Reply
        </button>

        <button
          onClick={() => {
            if (!currentUserId) {
              onReport(null, true); // trigger guest login
              return;
            }
            onReport(comment.id);
          }}
          className="hover:text-rose-600 cursor-pointer"
        >
          Report
        </button>

        {canDelete && (
          <button
            onClick={() => onDeleteComment(comment)}
            className="text-rose-600 hover:text-rose-700 ml-auto font-semibold cursor-pointer"
          >
            Delete
          </button>
        )}
      </div>

      {/* Reply input form */}
      {replyParentId === comment.id && (
        <div className="pt-2 pl-3 sm:pl-4 border-l-2 border-amber-400 space-y-2 animate-in fade-in">
          <textarea
            rows="2"
            value={replyText}
            onChange={(e) => setReplyText(e.target.value)}
            placeholder={`Replying to @${author?.username || 'user'}...`}
            className="w-full p-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600"
          />
          <div className="flex justify-end gap-2">
            <button
              onClick={() => {
                setReplyParentId(null);
                setReplyText('');
              }}
              className="px-3 py-1.5 text-xs text-zinc-500 hover:bg-zinc-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              onClick={() => onReplySubmit(comment.id)}
              disabled={!replyText.trim()}
              className="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg text-xs font-semibold shadow-sm transition cursor-pointer"
            >
              Reply
            </button>
          </div>
        </div>
      )}

      {/* Recursive Nested Child Replies (Chain Threading) */}
      {childList.length > 0 && (
        <div className="mt-3 pl-3 sm:pl-5 border-l-2 border-indigo-200 space-y-3 pt-2">
          {childList.map((child) => (
            <CommentCard
              key={child.id}
              comment={child}
              postAuthorId={postAuthorId}
              currentUserId={currentUserId}
              isAdmin={isAdmin}
              commentsByParent={commentsByParent}
              replyParentId={replyParentId}
              setReplyParentId={setReplyParentId}
              replyText={replyText}
              setReplyText={setReplyText}
              onReplySubmit={onReplySubmit}
              onAcceptAnswer={onAcceptAnswer}
              onDeleteComment={onDeleteComment}
              onReport={onReport}
              depth={depth + 1}
            />
          ))}
        </div>
      )}
    </div>
  );
}


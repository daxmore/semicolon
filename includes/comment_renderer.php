<?php
// includes/comment_renderer.php

if (!function_exists('render_comments')) {
    function render_comments($parent_id, $comments_by_parent, $post_user_id, $depth = 0, $comment_reactions, $is_admin, $post_id, $logged_in_user_id) {
        if (!isset($comments_by_parent[$parent_id])) {
            return;
        }

        foreach ($comments_by_parent[$parent_id] as $comment) {
            $is_accepted = !empty($comment['is_accepted']);
            $margin_class = $depth > 0 ? 'ml-8 md:ml-12 border-l-2 border-zinc-100 pl-4 mt-4' : 'bg-white border text-zinc-900 border-zinc-100 rounded-2xl p-6 shadow-sm mt-6';
            if ($is_accepted && $depth == 0) {
                $margin_class = 'bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-6 shadow-sm mt-6';
            }
            
            $has_upvoted = isset($comment_reactions[$comment['id']]) && $comment_reactions[$comment['id']] === 'upvote';
            $has_downvoted = isset($comment_reactions[$comment['id']]) && $comment_reactions[$comment['id']] === 'downvote';
            
            $can_edit_comment = $is_admin || ($comment['user_id'] == $logged_in_user_id);
            ?>
            <div class="<?php echo $margin_class; ?> relative group" id="comment-<?php echo $comment['id']; ?>">
                
                <?php if ($can_edit_comment): ?>
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-10">
                        <button onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)" class="text-xs text-amber-600 hover:text-amber-700 font-bold bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded">Edit</button>
                        <form action="community_post_detail.php?id=<?php echo $post_id; ?>" method="POST" class="inline">
                            <input type="hidden" name="action" value="delete_comment">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <button type="submit" onclick="return confirm('Delete this comment permanently?')" class="text-xs text-red-500 hover:text-red-700 font-bold bg-red-50 hover:bg-red-100 px-2 py-1 rounded">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="flex gap-4">
                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm md:text-lg flex-shrink-0 overflow-hidden">
                        <?php if (!empty($comment['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="font-bold text-zinc-900"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <?php if ($is_accepted): ?>
                                <span class="inline-flex items-center gap-1 text-[10px] md:text-xs font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    Accepted Answer
                                </span>
                            <?php endif; ?>
                            <?php if ($comment['user_id'] === $post_user_id): ?>
                                <span class="text-[10px] md:text-xs font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-md">OP</span>
                            <?php endif; ?>
                            <span class="text-xs text-zinc-400"><?php echo date('M j, Y, g:i a', strtotime($comment['created_at'])); ?></span>
                        </div>
                        
                        <div id="comment-display-<?php echo $comment['id']; ?>">
                            <div class="markdown-render text-zinc-700 text-sm md:text-base leading-relaxed mb-3" data-raw="<?php echo htmlspecialchars($comment['content']); ?>">
                                <?php echo htmlspecialchars($comment['content']); ?>
                            </div>
                        </div>

                        <?php if ($can_edit_comment): ?>
                        <!-- Edit Comment Form -->
                        <div id="comment-edit-form-<?php echo $comment['id']; ?>" class="hidden mb-4">
                            <form action="community_post_detail.php?id=<?php echo $post_id; ?>" method="POST">
                                <input type="hidden" name="action" value="edit_comment">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                <textarea name="content" rows="3" required class="w-full px-4 py-2 text-sm bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 outline-none mb-2"><?php echo htmlspecialchars($comment['content']); ?></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="toggleCommentEdit(<?php echo $comment['id']; ?>)" class="px-3 py-1 text-xs text-zinc-500 hover:text-zinc-900">Cancel</button>
                                    <button type="submit" class="px-3 py-1 bg-amber-500 text-white rounded text-xs font-bold transition">Save Changes</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Comment Actions (Vote & Reply) -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center bg-zinc-50 border border-zinc-200 rounded-full overflow-hidden">
                                <button onclick="handleCommentVote(<?php echo $comment['id']; ?>, 'upvote')" id="cmt-upvote-<?php echo $comment['id']; ?>" class="px-2 py-1 hover:bg-zinc-200 transition <?php echo $has_upvoted ? 'text-amber-500 bg-amber-50' : 'text-zinc-500'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?php echo !$has_upvoted ? 'group-hover:text-amber-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                </button>
                                <span id="cmt-count-<?php echo $comment['id']; ?>" class="text-xs font-bold text-zinc-700 px-2 border-x border-zinc-200"><?php echo number_format($comment['upvotes'] - $comment['downvotes']); ?></span>
                                <button onclick="handleCommentVote(<?php echo $comment['id']; ?>, 'downvote')" id="cmt-downvote-<?php echo $comment['id']; ?>" class="px-2 py-1 hover:bg-zinc-200 transition <?php echo $has_downvoted ? 'text-blue-500 bg-blue-50' : 'text-zinc-500'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-180 <?php echo !$has_downvoted ? 'group-hover:text-blue-500 transition-colors' : ''; ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                </button>
                            </div>
                            <button onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="text-xs font-bold text-zinc-500 hover:text-zinc-900 transition flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Reply
                            </button>
                            <button onclick="openReportModal('comment', <?php echo $comment['id']; ?>)" class="text-xs font-bold text-zinc-400 hover:text-red-500 transition px-2 ml-2 border-l border-zinc-200" title="Report Comment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                            </button>
                            
                            <?php if ($logged_in_user_id === $post_user_id && $depth == 0): ?>
                                <button onclick="handleAcceptAnswer(<?php echo $comment['id']; ?>, <?php echo $post_id; ?>)" class="ml-auto text-xs font-bold transition flex items-center gap-1 <?php echo $is_accepted ? 'text-emerald-600 hover:text-emerald-700' : 'text-zinc-400 hover:text-emerald-600'; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <?php echo $is_accepted ? 'Unmark Accepted' : 'Mark as Accepted'; ?>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Hidden Reply Form -->
                        <div id="reply-form-<?php echo $comment['id']; ?>" class="hidden mt-4">
                            <form action="community_post_detail.php?id=<?php echo $post_id; ?>" method="POST">
                                <input type="hidden" name="action" value="post_comment">
                                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                <textarea name="comment_content" rows="3" required placeholder="Write a reply..." class="w-full px-4 py-2 text-sm bg-white border border-zinc-200 rounded-xl text-zinc-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-y mb-2"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="px-4 py-1.5 text-xs text-zinc-500 hover:text-zinc-900 font-medium transition">Cancel</button>
                                    <button type="submit" class="px-4 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-medium rounded-lg text-xs transition shadow-sm">Post Reply</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Recursively render children -->
                <?php render_comments($comment['id'], $comments_by_parent, $post_user_id, $depth + 1, $comment_reactions, $is_admin, $post_id, $logged_in_user_id); ?>
                
            </div>
            <?php
        }
    }
}
?>

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch community posts with category and sort ('new', 'top', 'hot')
 */
export function useCommunityPosts({ category, sort = 'new', search } = {}) {
  return useQuery({
    queryKey: ['community_posts', { category, sort, search }],
    queryFn: async () => {
      let order = 'created_at.desc';
      if (sort === 'top') order = 'upvotes.desc,created_at.desc';

      let query = `/rest/v1/community_posts?select=*,profiles:user_id(id,username,avatar_url,is_pro,level)&order=${order}`;

      if (category && category !== 'All') {
        query += `&category=eq.${encodeURIComponent(category)}`;
      }

      if (search) {
        query += `&or=(title.ilike.*${encodeURIComponent(search)}*,description.ilike.*${encodeURIComponent(search)}*)`;
      }

      const { data } = await axiosClient.get(query);
      return data || [];
    },
  });
}

/**
 * Fetch single post with author profile and comments count
 */
export function useCommunityPost(postId) {
  return useQuery({
    queryKey: ['community_post', String(postId)],
    queryFn: async () => {
      if (!postId) return null;

      const { data } = await axiosClient.get(
        `/rest/v1/community_posts?id=eq.${postId}&select=*,profiles:user_id(id,username,avatar_url,is_pro,level)`,
        { headers: { Accept: 'application/vnd.pgrst.object+json' } }
      );
      return data;
    },
    enabled: !!postId,
  });
}

/**
 * Fetch threaded comments for a post
 */
export function useCommunityComments(postId) {
  return useQuery({
    queryKey: ['community_comments', String(postId)],
    queryFn: async () => {
      if (!postId) return [];

      const { data } = await axiosClient.get(
        `/rest/v1/community_comments?post_id=eq.${postId}&select=*,profiles:user_id(id,username,avatar_url,is_pro,level)&order=is_accepted.desc,upvotes.desc,created_at.asc`
      );
      return data || [];
    },
    enabled: !!postId,
  });
}

/**
 * Fetch current user's reaction for a post or list of posts
 */
export function useUserReactions(userId, postIds = []) {
  const idsKey = Array.isArray(postIds) ? postIds.map(String).sort().join(',') : String(postIds);
  return useQuery({
    queryKey: ['user_reactions', userId, idsKey],
    queryFn: async () => {
      if (!userId || !postIds || (Array.isArray(postIds) && postIds.length === 0)) return {};

      const list = Array.isArray(postIds) ? postIds : [postIds];
      const cleanIds = list.map(Number).filter(Boolean);
      if (cleanIds.length === 0) return {};

      const { data } = await axiosClient.get(
        `/rest/v1/community_reactions?user_id=eq.${userId}&post_id=in.(${cleanIds.join(',')})&select=post_id,reaction_type`
      );

      const map = {};
      data?.forEach((r) => {
        map[r.post_id] = r.reaction_type;
        map[String(r.post_id)] = r.reaction_type;
      });
      return map;
    },
    enabled: !!userId && (Array.isArray(postIds) ? postIds.length > 0 : !!postIds),
  });
}

/**
 * Community Action Mutations
 */
export function useCommunityMutations() {
  const queryClient = useQueryClient();

  // Create Post (+10 XP)
  const createPost = useMutation({
    mutationFn: async ({ title, description, image_url, category, user_id }) => {
      const { data } = await axiosClient.post(
        '/rest/v1/community_posts',
        { title, description, image_url, category, user_id },
        { headers: { Prefer: 'return=representation' } }
      );
      return data[0];
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['community_posts'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  // Delete Post
  const deletePost = useMutation({
    mutationFn: async (postId) => {
      await axiosClient.delete(`/rest/v1/community_posts?id=eq.${postId}`);
      return postId;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['community_posts'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  // Post or Reply Comment (+10 XP)
  const addComment = useMutation({
    mutationFn: async ({ post_id, user_id, parent_id, content }) => {
      const { data } = await axiosClient.post(
        '/rest/v1/community_comments',
        { post_id, user_id, parent_id: parent_id || null, content },
        { headers: { Prefer: 'return=representation' } }
      );
      return data[0];
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['community_comments'] });
      queryClient.invalidateQueries({ queryKey: ['community_comments', String(variables.post_id)] });
      queryClient.invalidateQueries({ queryKey: ['community_post', String(variables.post_id)] });
    },
  });

  // Accept Answer Toggle
  const toggleAcceptAnswer = useMutation({
    mutationFn: async ({ commentId, postId, currentStatus }) => {
      const newStatus = !currentStatus;
      await axiosClient.patch(`/rest/v1/community_comments?id=eq.${commentId}`, {
        is_accepted: newStatus,
      });
      return { commentId, postId, newStatus };
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['community_comments'] });
      queryClient.invalidateQueries({ queryKey: ['community_comments', String(variables.postId)] });
    },
  });

  // Delete Comment
  const deleteComment = useMutation({
    mutationFn: async ({ commentId, postId }) => {
      await axiosClient.delete(`/rest/v1/community_comments?id=eq.${commentId}`);
      return { commentId, postId };
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['community_comments'] });
      queryClient.invalidateQueries({ queryKey: ['community_comments', String(variables.postId)] });
    },
  });

  // Vote Post (with instant optimistic update)
  const votePost = useMutation({
    mutationFn: async ({ postId, userId, type, currentReaction, currentUpvotes, currentDownvotes }) => {
      let newUpvotes = currentUpvotes;
      let newDownvotes = currentDownvotes;

      if (currentReaction === type) {
        // Toggle OFF (unvote)
        await axiosClient.delete(
          `/rest/v1/community_reactions?user_id=eq.${userId}&post_id=eq.${postId}`
        );
        if (type === 'upvote') newUpvotes = Math.max(0, newUpvotes - 1);
        if (type === 'downvote') newDownvotes = Math.max(0, newDownvotes - 1);
      } else if (currentReaction) {
        // Switch Vote
        await axiosClient.patch(
          `/rest/v1/community_reactions?user_id=eq.${userId}&post_id=eq.${postId}`,
          { reaction_type: type }
        );
        if (type === 'upvote') {
          newUpvotes += 1;
          newDownvotes = Math.max(0, newDownvotes - 1);
        } else {
          newDownvotes += 1;
          newUpvotes = Math.max(0, newUpvotes - 1);
        }
      } else {
        // New Vote
        await axiosClient.post('/rest/v1/community_reactions', {
          user_id: userId,
          post_id: postId,
          reaction_type: type,
        });
        if (type === 'upvote') newUpvotes += 1;
        if (type === 'downvote') newDownvotes += 1;
      }

      // Update count on community_posts table
      await axiosClient.patch(`/rest/v1/community_posts?id=eq.${postId}`, {
        upvotes: newUpvotes,
        downvotes: newDownvotes,
      });

      return { postId, newUpvotes, newDownvotes };
    },
    onMutate: async ({ postId, type, currentReaction, currentUpvotes, currentDownvotes }) => {
      let newUpvotes = currentUpvotes;
      let newDownvotes = currentDownvotes;
      let nextReaction = type;

      if (currentReaction === type) {
        nextReaction = null;
        if (type === 'upvote') newUpvotes = Math.max(0, newUpvotes - 1);
        if (type === 'downvote') newDownvotes = Math.max(0, newDownvotes - 1);
      } else if (currentReaction) {
        if (type === 'upvote') {
          newUpvotes += 1;
          newDownvotes = Math.max(0, newDownvotes - 1);
        } else {
          newDownvotes += 1;
          newUpvotes = Math.max(0, newUpvotes - 1);
        }
      } else {
        if (type === 'upvote') newUpvotes += 1;
        if (type === 'downvote') newDownvotes += 1;
      }

      // Optimistically update single post view
      queryClient.setQueryData(['community_post', String(postId)], (old) => {
        if (!old) return old;
        return { ...old, upvotes: newUpvotes, downvotes: newDownvotes };
      });

      // Optimistically update community feed posts list
      queryClient.setQueriesData({ queryKey: ['community_posts'] }, (old) => {
        if (!Array.isArray(old)) return old;
        return old.map((p) =>
          String(p.id) === String(postId)
            ? { ...p, upvotes: newUpvotes, downvotes: newDownvotes }
            : p
        );
      });

      // Optimistically update reaction status
      queryClient.setQueriesData({ queryKey: ['user_reactions'] }, (old) => {
        if (!old) return { [postId]: nextReaction, [String(postId)]: nextReaction };
        return { ...old, [postId]: nextReaction, [String(postId)]: nextReaction };
      });
    },
    onSettled: (_, __, variables) => {
      queryClient.invalidateQueries({ queryKey: ['community_posts'] });
      queryClient.invalidateQueries({ queryKey: ['community_post'] });
      queryClient.invalidateQueries({ queryKey: ['community_post', String(variables.postId)] });
      queryClient.invalidateQueries({ queryKey: ['user_reactions'] });
    },
  });

  // Submit Report
  const submitReport = useMutation({
    mutationFn: async ({ target_type, target_id, user_id, reason }) => {
      const { data } = await axiosClient.post('/rest/v1/community_reports', {
        target_type,
        target_id,
        user_id,
        reason,
      });
      return data;
    },
  });

  return {
    createPost,
    deletePost,
    addComment,
    toggleAcceptAnswer,
    deleteComment,
    votePost,
    submitReport,
  };
}

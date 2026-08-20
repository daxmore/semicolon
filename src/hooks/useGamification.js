import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch all available badges
 */
export function useBadges() {
  return useQuery({
    queryKey: ['badges'],
    queryFn: async () => {
      const { data } = await axiosClient.get('/rest/v1/badges?select=*&order=required_xp.asc,id.asc');
      return data || [];
    },
  });
}

/**
 * Fetch unlocked badges for a user
 */
export function useUserBadges(userId) {
  return useQuery({
    queryKey: ['user_badges', userId],
    queryFn: async () => {
      if (!userId) return [];
      const { data } = await axiosClient.get(
        `/rest/v1/user_badges?user_id=eq.${userId}&select=*,badges(*)`
      );
      return data || [];
    },
    enabled: !!userId,
  });
}

/**
 * Fetch Leaderboard: weekly top 10 & lifetime top 10
 */
export function useLeaderboard(tab = 'weekly') {
  return useQuery({
    queryKey: ['leaderboard', tab],
    queryFn: async () => {
      const orderColumn = tab === 'weekly' ? 'xp_weekly.desc' : 'xp_total.desc';
      const { data } = await axiosClient.get(
        `/rest/v1/profiles?role=neq.admin&select=id,username,avatar_url,xp_weekly,xp_total,level,is_pro,daily_streak&order=${orderColumn}&limit=20`
      );
      return data || [];
    },
  });
}

/**
 * Fetch reading & viewing history
 */
export function useUserHistory(userId) {
  return useQuery({
    queryKey: ['user_history', userId],
    queryFn: async () => {
      if (!userId) return [];
      const { data } = await axiosClient.get(
        `/rest/v1/user_history?user_id=eq.${userId}&select=*&order=viewed_at.desc&limit=30`
      );
      return data || [];
    },
    enabled: !!userId,
  });
}

/**
 * Admin: Fetch all users list
 */
export function useAdminUsers() {
  return useQuery({
    queryKey: ['admin_users'],
    queryFn: async () => {
      const { data } = await axiosClient.get('/rest/v1/profiles?role=neq.admin&select=*&order=created_at.desc');
      return data || [];
    },
  });
}

/**
 * Badge & User Admin Mutations
 */
export function useGamificationMutations() {
  const queryClient = useQueryClient();

  const toggleEquipBadge = useMutation({
    mutationFn: async ({ userBadgeId, isEquipped, userId }) => {
      // Un-equip other badges if equipping this one
      if (!isEquipped) {
        await axiosClient.patch(`/rest/v1/user_badges?user_id=eq.${userId}`, {
          is_equipped: false,
        });
      }

      await axiosClient.patch(`/rest/v1/user_badges?id=eq.${userBadgeId}`, {
        is_equipped: !isEquipped,
      });

      return { userBadgeId, userId };
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['user_badges', variables.userId] });
    },
  });

  const createBadge = useMutation({
    mutationFn: async (badgeData) => {
      const { data } = await axiosClient.post('/rest/v1/badges', badgeData, {
        headers: { Prefer: 'return=representation' },
      });
      return data[0];
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['badges'] });
    },
  });

  const updateBadge = useMutation({
    mutationFn: async ({ id, ...badgeData }) => {
      const { data } = await axiosClient.patch(`/rest/v1/badges?id=eq.${id}`, badgeData, {
        headers: { Prefer: 'return=representation' },
      });
      return data[0];
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['badges'] });
    },
  });

  const deleteBadge = useMutation({
    mutationFn: async (id) => {
      await axiosClient.delete(`/rest/v1/badges?id=eq.${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['badges'] });
    },
  });

  const toggleUserProStatus = useMutation({
    mutationFn: async ({ userId, isPro }) => {
      await axiosClient.patch(`/rest/v1/profiles?id=eq.${userId}`, {
        is_pro: !isPro,
      });
      return { userId, isPro: !isPro };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_users'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  const changeUserRole = useMutation({
    mutationFn: async ({ userId, role }) => {
      await axiosClient.patch(`/rest/v1/profiles?id=eq.${userId}`, {
        role,
      });
      return { userId, role };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_users'] });
    },
  });

  const banUser = useMutation({
    mutationFn: async ({ userId, isBanned }) => {
      await axiosClient.patch(`/rest/v1/profiles?id=eq.${userId}`, {
        is_banned: !isBanned,
      });
      return { userId, isBanned: !isBanned };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_users'] });
    },
  });

  const deleteUser = useMutation({
    mutationFn: async (userId) => {
      await axiosClient.delete(`/rest/v1/profiles?id=eq.${userId}`);
      return userId;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_users'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  return {
    toggleEquipBadge,
    createBadge,
    updateBadge,
    deleteBadge,
    toggleUserProStatus,
    changeUserRole,
    banUser,
    deleteUser,
  };
}

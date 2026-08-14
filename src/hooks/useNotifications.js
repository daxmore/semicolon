import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch current user's notifications
 */
export function useNotifications(userId) {
  return useQuery({
    queryKey: ['notifications', userId],
    queryFn: async () => {
      if (!userId) return [];
      const { data } = await axiosClient.get(
        `/rest/v1/notifications?user_id=eq.${userId}&select=*&order=created_at.desc`
      );
      return data || [];
    },
    enabled: !!userId,
  });
}

/**
 * Notifications actions
 */
export function useNotificationMutations() {
  const queryClient = useQueryClient();

  const markAsRead = useMutation({
    mutationFn: async (notifId) => {
      await axiosClient.patch(`/rest/v1/notifications?id=eq.${notifId}`, {
        is_read: true,
      });
      return notifId;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });

  const markAllAsRead = useMutation({
    mutationFn: async (userId) => {
      await axiosClient.patch(`/rest/v1/notifications?user_id=eq.${userId}&is_read=eq.false`, {
        is_read: true,
      });
      return userId;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });

  return { markAsRead, markAllAsRead };
}

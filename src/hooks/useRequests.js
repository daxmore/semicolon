import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch current user's submitted material requests
 */
export function useUserRequests(userId) {
  return useQuery({
    queryKey: ['user_requests', userId],
    queryFn: async () => {
      if (!userId) return [];
      const { data } = await axiosClient.get(
        `/rest/v1/material_requests?user_id=eq.${userId}&select=*&order=created_at.desc`
      );
      return data || [];
    },
    enabled: !!userId,
  });
}

/**
 * Admin: Fetch all material requests
 */
export function useAdminRequests(statusFilter = 'all') {
  return useQuery({
    queryKey: ['admin_requests', statusFilter],
    queryFn: async () => {
      let query = '/rest/v1/material_requests?select=*,profiles:user_id(username,email)&order=created_at.desc';
      if (statusFilter !== 'all') {
        query += `&status=eq.${statusFilter}`;
      }
      const { data } = await axiosClient.get(query);
      return data || [];
    },
  });
}

/**
 * Material Request Mutations
 */
export function useRequestMutations() {
  const queryClient = useQueryClient();

  const createRequest = useMutation({
    mutationFn: async (requestData) => {
      const { data } = await axiosClient.post(
        '/rest/v1/material_requests',
        requestData,
        { headers: { Prefer: 'return=representation' } }
      );
      return data[0];
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['user_requests', variables.user_id] });
      queryClient.invalidateQueries({ queryKey: ['admin_requests'] });
    },
  });

  const updateRequestStatus = useMutation({
    mutationFn: async ({ id, status }) => {
      await axiosClient.patch(`/rest/v1/material_requests?id=eq.${id}`, { status });
      return { id, status };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_requests'] });
      queryClient.invalidateQueries({ queryKey: ['user_requests'] });
    },
  });

  const deleteRequest = useMutation({
    mutationFn: async (id) => {
      await axiosClient.delete(`/rest/v1/material_requests?id=eq.${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_requests'] });
      queryClient.invalidateQueries({ queryKey: ['user_requests'] });
    },
  });

  return { createRequest, updateRequestStatus, deleteRequest };
}

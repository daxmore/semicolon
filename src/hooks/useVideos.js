import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch list of videos with optional filters (category, search)
 */
export function useVideos({ category, search } = {}) {
  return useQuery({
    queryKey: ['videos', { category, search }],
    queryFn: async () => {
      let query = '/rest/v1/videos?select=*&order=created_at.desc';

      if (category && category !== 'All') {
        query += `&category=eq.${encodeURIComponent(category)}`;
      }
      if (search) {
        query += `&or=(title.ilike.*${encodeURIComponent(search)}*,description.ilike.*${encodeURIComponent(search)}*)`;
      }

      const { data } = await axiosClient.get(query);
      return data;
    },
  });
}

/**
 * Fetch single video by ID or token
 */
export function useVideo(idOrToken) {
  return useQuery({
    queryKey: ['video', idOrToken],
    queryFn: async () => {
      if (!idOrToken) return null;
      
      const isNumeric = /^\d+$/.test(idOrToken);
      const query = isNumeric
        ? `/rest/v1/videos?id=eq.${idOrToken}&select=*`
        : `/rest/v1/videos?token=eq.${encodeURIComponent(idOrToken)}&select=*`;

      const { data } = await axiosClient.get(query, {
        headers: { Accept: 'application/vnd.pgrst.object+json' },
      });
      return data;
    },
    enabled: !!idOrToken,
  });
}

/**
 * Admin Mutations for Videos
 */
export function useVideoMutations() {
  const queryClient = useQueryClient();

  const createVideo = useMutation({
    mutationFn: async (newVideo) => {
      const { data } = await axiosClient.post('/rest/v1/videos', newVideo, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['videos'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  const updateVideo = useMutation({
    mutationFn: async ({ id, ...updates }) => {
      const { data } = await axiosClient.patch(`/rest/v1/videos?id=eq.${id}`, updates, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['videos'] });
      queryClient.invalidateQueries({ queryKey: ['video', variables.id] });
    },
  });

  const deleteVideo = useMutation({
    mutationFn: async (id) => {
      await axiosClient.delete(`/rest/v1/videos?id=eq.${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['videos'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  return { createVideo, updateVideo, deleteVideo };
}

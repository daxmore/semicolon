import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch list of papers with optional filters (subject, year, search)
 */
export function usePapers({ subject, year, search } = {}) {
  return useQuery({
    queryKey: ['papers', { subject, year, search }],
    queryFn: async () => {
      let query = '/rest/v1/papers?select=*&order=year.desc,created_at.desc';

      if (subject && subject !== 'All') {
        query += `&subject=eq.${encodeURIComponent(subject)}`;
      }
      if (year && year !== 'All') {
        query += `&year=eq.${year}`;
      }
      if (search) {
        query += `&or=(title.ilike.*${encodeURIComponent(search)}*,subject.ilike.*${encodeURIComponent(search)}*)`;
      }

      const { data } = await axiosClient.get(query);
      return data;
    },
  });
}

/**
 * Fetch single paper by ID or token
 */
export function usePaper(idOrToken) {
  return useQuery({
    queryKey: ['paper', idOrToken],
    queryFn: async () => {
      if (!idOrToken) return null;
      
      const isNumeric = /^\d+$/.test(idOrToken);
      const query = isNumeric
        ? `/rest/v1/papers?id=eq.${idOrToken}&select=*`
        : `/rest/v1/papers?token=eq.${encodeURIComponent(idOrToken)}&select=*`;

      const { data } = await axiosClient.get(query, {
        headers: { Accept: 'application/vnd.pgrst.object+json' },
      });
      return data;
    },
    enabled: !!idOrToken,
  });
}

/**
 * Admin Mutations for Papers
 */
export function usePaperMutations() {
  const queryClient = useQueryClient();

  const createPaper = useMutation({
    mutationFn: async (newPaper) => {
      const { data } = await axiosClient.post('/rest/v1/papers', newPaper, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['papers'] });
    },
  });

  const updatePaper = useMutation({
    mutationFn: async ({ id, ...updates }) => {
      const { data } = await axiosClient.patch(`/rest/v1/papers?id=eq.${id}`, updates, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['papers'] });
      queryClient.invalidateQueries({ queryKey: ['paper', variables.id] });
    },
  });

  const deletePaper = useMutation({
    mutationFn: async (id) => {
      await axiosClient.delete(`/rest/v1/papers?id=eq.${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['papers'] });
    },
  });

  return { createPaper, updatePaper, deletePaper };
}

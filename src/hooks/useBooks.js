import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch list of books with optional filters (subject, difficulty, search)
 */
export function useBooks({ subject, difficulty, search } = {}) {
  return useQuery({
    queryKey: ['books', { subject, difficulty, search }],
    queryFn: async () => {
      let query = '/rest/v1/books?select=*&order=created_at.desc';

      if (subject && subject !== 'All') {
        query += `&subject=eq.${encodeURIComponent(subject)}`;
      }
      if (difficulty && difficulty !== 'All') {
        query += `&difficulty=eq.${encodeURIComponent(difficulty)}`;
      }
      if (search) {
        // Full-text search or ILIKE fallback
        query += `&or=(title.ilike.*${encodeURIComponent(search)}*,author.ilike.*${encodeURIComponent(search)}*,description.ilike.*${encodeURIComponent(search)}*)`;
      }

      const { data } = await axiosClient.get(query);
      return data;
    },
  });
}

/**
 * Fetch a single book by ID or token
 */
export function useBook(idOrToken) {
  return useQuery({
    queryKey: ['book', idOrToken],
    queryFn: async () => {
      if (!idOrToken) return null;
      
      const isNumeric = /^\d+$/.test(idOrToken);
      const query = isNumeric
        ? `/rest/v1/books?id=eq.${idOrToken}&select=*`
        : `/rest/v1/books?token=eq.${encodeURIComponent(idOrToken)}&select=*`;

      const { data } = await axiosClient.get(query, {
        headers: { Accept: 'application/vnd.pgrst.object+json' },
      });
      return data;
    },
    enabled: !!idOrToken,
  });
}

/**
 * Admin Mutations for Books
 */
export function useBookMutations() {
  const queryClient = useQueryClient();

  const createBook = useMutation({
    mutationFn: async (newBook) => {
      const { data } = await axiosClient.post('/rest/v1/books', newBook, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['books'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  const updateBook = useMutation({
    mutationFn: async ({ id, ...updates }) => {
      const { data } = await axiosClient.patch(`/rest/v1/books?id=eq.${id}`, updates, {
        headers: { Prefer: 'return=representation' },
      });
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['books'] });
      queryClient.invalidateQueries({ queryKey: ['book', variables.id] });
    },
  });

  const deleteBook = useMutation({
    mutationFn: async (id) => {
      await axiosClient.delete(`/rest/v1/books?id=eq.${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['books'] });
      queryClient.invalidateQueries({ queryKey: ['admin_overview_stats'] });
    },
  });

  return { createBook, updateBook, deleteBook };
}

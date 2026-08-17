import { useQuery } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

export function useGlobalSearch(query) {
  return useQuery({
    queryKey: ['global_search_modal', query],
    queryFn: async () => {
      if (!query || !query.trim() || query.trim().length < 2) {
        return [];
      }

      const encoded = encodeURIComponent(query.trim());

      const [booksRes, papersRes, videosRes] = await Promise.all([
        axiosClient
          .get(`/rest/v1/books?or=(title.ilike.*${encoded}*,author.ilike.*${encoded}*,subject.ilike.*${encoded}*)&limit=4`)
          .catch(() => ({ data: [] })),
        axiosClient
          .get(`/rest/v1/papers?or=(title.ilike.*${encoded}*,subject.ilike.*${encoded}*)&limit=4`)
          .catch(() => ({ data: [] })),
        axiosClient
          .get(`/rest/v1/videos?or=(title.ilike.*${encoded}*,category.ilike.*${encoded}*)&limit=4`)
          .catch(() => ({ data: [] })),
      ]);

      const books = (booksRes.data || []).map((b) => ({ ...b, type: 'book' }));
      const papers = (papersRes.data || []).map((p) => ({ ...p, type: 'paper' }));
      const videos = (videosRes.data || []).map((v) => ({ ...v, type: 'video' }));

      return [...books, ...papers, ...videos];
    },
    enabled: Boolean(query && query.trim().length >= 2),
    staleTime: 1000 * 60 * 2, // 2 mins
  });
}

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';
import { SYSTEM_CATEGORIES } from '../lib/utils';

const CATEGORIES_STORAGE_KEY = 'semicolon_custom_categories';
const TOPIC_REQUESTS_KEY = 'semicolon_topic_requests';

// Initial default topic requests for demo/testing
const DEFAULT_TOPIC_REQUESTS = [
  {
    id: 1,
    topic_name: 'Rust Systems Programming',
    reason: 'Many students are learning low-level memory safety and want dedicated resources and discussions for Rust.',
    purpose: 'Share crates, memory safety guides, and embedded development tutorials.',
    user_id: null,
    username: 'vansh',
    status: 'pending',
    created_at: new Date(Date.now() - 3600000 * 24).toISOString(),
  },
  {
    id: 2,
    topic_name: 'Quantum Computing',
    reason: 'Quantum algorithms and Qiskit tutorials are emerging and need a dedicated place.',
    purpose: 'Study groups for IBM Qiskit and quantum circuits.',
    user_id: null,
    username: 'dax',
    status: 'pending',
    created_at: new Date(Date.now() - 3600000 * 48).toISOString(),
  },
];

// Helper to get local stored categories
export function getStoredCategories() {
  try {
    const custom = localStorage.getItem(CATEGORIES_STORAGE_KEY);
    if (custom) {
      const parsed = JSON.parse(custom);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
    localStorage.setItem(CATEGORIES_STORAGE_KEY, JSON.stringify(SYSTEM_CATEGORIES));
    return SYSTEM_CATEGORIES;
  } catch {
    return SYSTEM_CATEGORIES;
  }
}

// Helper to get topic requests
export function getStoredTopicRequests() {
  try {
    const list = JSON.parse(localStorage.getItem(TOPIC_REQUESTS_KEY));
    if (Array.isArray(list) && list.length > 0) return list;
    localStorage.setItem(TOPIC_REQUESTS_KEY, JSON.stringify(DEFAULT_TOPIC_REQUESTS));
    return DEFAULT_TOPIC_REQUESTS;
  } catch {
    return DEFAULT_TOPIC_REQUESTS;
  }
}

/**
 * Hook to fetch all categories (defaults + admin custom added)
 */
export function useCommunityCategories() {
  return useQuery({
    queryKey: ['community_categories'],
    queryFn: async () => {
      // 1. Try fetching from remote categories table if exists
      try {
        const { data } = await axiosClient.get('/rest/v1/categories?select=*&order=name.asc');
        if (data && data.length > 0) {
          const names = data.map((c) => c.name);
          return names;
        }
      } catch (err) {
        // Fallback to local persisted store
      }
      return getStoredCategories();
    },
    staleTime: 0,
  });
}

/**
 * Hook to fetch all user topic/community requests for Admin
 */
export function useTopicRequests() {
  return useQuery({
    queryKey: ['topic_requests'],
    queryFn: async () => {
      try {
        const { data } = await axiosClient.get(
          '/rest/v1/topic_requests?select=*,profiles(username,email)&order=created_at.desc'
        );
        if (data && data.length > 0) return data;
      } catch (err) {
        // Fallback to local persisted store
      }
      return getStoredTopicRequests();
    },
  });
}

/**
 * Categories & Topic Requests Mutations
 */
export function useCategoryMutations() {
  const queryClient = useQueryClient();

  return {
    addCategory: useMutation({
      mutationFn: async (categoryName) => {
        const trimmed = categoryName.trim();
        if (!trimmed) throw new Error('Category name cannot be empty');

        try {
          await axiosClient.post('/rest/v1/categories', { name: trimmed });
        } catch {
          // Fallback
        }

        const current = getStoredCategories();
        if (!current.includes(trimmed)) {
          const updated = [...current, trimmed];
          localStorage.setItem(CATEGORIES_STORAGE_KEY, JSON.stringify(updated));
        }
        return trimmed;
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['community_categories'] });
      },
    }),

    updateCategory: useMutation({
      mutationFn: async ({ oldName, newName }) => {
        const trimmed = newName.trim();
        if (!trimmed) throw new Error('Category name cannot be empty');

        try {
          await axiosClient.patch(`/rest/v1/categories?name=eq.${oldName}`, { name: trimmed });
        } catch {
          // Fallback
        }

        const current = getStoredCategories();
        const updated = current.map((c) => (c === oldName ? trimmed : c));
        localStorage.setItem(CATEGORIES_STORAGE_KEY, JSON.stringify(updated));
        return { oldName, newName: trimmed };
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['community_categories'] });
      },
    }),

    deleteCategory: useMutation({
      mutationFn: async (categoryName) => {
        try {
          await axiosClient.delete(`/rest/v1/categories?name=eq.${categoryName}`);
        } catch {
          // Fallback
        }

        const current = getStoredCategories();
        const updated = current.filter((c) => c !== categoryName);
        localStorage.setItem(CATEGORIES_STORAGE_KEY, JSON.stringify(updated));
        return categoryName;
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['community_categories'] });
      },
    }),

    submitTopicRequest: useMutation({
      mutationFn: async ({ topic_name, reason, purpose, user_id, username }) => {
        const newReq = {
          id: Date.now(),
          topic_name: topic_name.trim(),
          reason: reason.trim(),
          purpose: purpose.trim(),
          user_id: user_id || null,
          username: username || 'User',
          status: 'pending',
          created_at: new Date().toISOString(),
        };

        try {
          await axiosClient.post('/rest/v1/topic_requests', newReq);
        } catch {
          const existing = getStoredTopicRequests();
          const updated = [newReq, ...existing];
          localStorage.setItem(TOPIC_REQUESTS_KEY, JSON.stringify(updated));
        }

        return newReq;
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['topic_requests'] });
      },
    }),

    updateTopicRequestStatus: useMutation({
      mutationFn: async ({ id, status, note, user_id, topic_name }) => {
        // 1. Update topic request status
        try {
          await axiosClient.patch(`/rest/v1/topic_requests?id=eq.${id}`, { status, admin_note: note });
        } catch {
          // Fallback
        }

        const existing = getStoredTopicRequests();
        const updated = existing.map((r) =>
          String(r.id) === String(id) ? { ...r, status, admin_note: note } : r
        );
        localStorage.setItem(TOPIC_REQUESTS_KEY, JSON.stringify(updated));

        // 2. If approved, automatically add to categories list
        if (status === 'approved' && topic_name) {
          const current = getStoredCategories();
          if (!current.includes(topic_name)) {
            const newCats = [...current, topic_name];
            localStorage.setItem(CATEGORIES_STORAGE_KEY, JSON.stringify(newCats));
          }
        }

        // 3. Send Notification to User
        if (user_id) {
          const defaultTitle =
            status === 'approved'
              ? `Community Request Approved: "${topic_name}"`
              : `Community Request Declined: "${topic_name}"`;

          const defaultMessage =
            note?.trim() ||
            (status === 'approved'
              ? `Great news! Your request to add the "${topic_name}" community topic has been approved and is now active.`
              : `Thank you for your suggestion. After review, the request for "${topic_name}" was not approved at this time.`);

          try {
            await axiosClient.post('/rest/v1/notifications', {
              user_id: user_id,
              type: 'system',
              title: defaultTitle,
              message: defaultMessage,
              link: status === 'approved' ? `/community?category=${encodeURIComponent(topic_name)}` : '/community',
              is_read: false,
            });
          } catch (notifErr) {
            console.warn('Failed to send notification via Supabase:', notifErr);
          }
        }

        return { id, status };
      },
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['topic_requests'] });
        queryClient.invalidateQueries({ queryKey: ['community_categories'] });
        queryClient.invalidateQueries({ queryKey: ['notifications'] });
      },
    }),
  };
}

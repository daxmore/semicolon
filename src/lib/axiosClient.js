import axios from 'axios';
import { supabase } from './supabaseClient';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;

/**
 * Axios instance pre-configured for Supabase REST API and Edge Functions.
 *
 * - Base URL points to the Supabase REST endpoint (PostgREST).
 * - An interceptor attaches the current session's access token to
 *   the Authorization header on every request so RLS policies apply.
 * - The apikey header is always sent for Supabase gateway auth.
 *
 * Usage:
 *   import { axiosClient } from '@/lib/axiosClient';
 *   const { data } = await axiosClient.get('/rest/v1/books?select=*');
 */
const axiosClient = axios.create({
  baseURL: supabaseUrl,
  headers: {
    'Content-Type': 'application/json',
    apikey: import.meta.env.VITE_SUPABASE_ANON_KEY,
  },
});

// Request interceptor: attach the current access token
axiosClient.interceptors.request.use(
  async (config) => {
    const {
      data: { session },
    } = await supabase.auth.getSession();

    if (session?.access_token) {
      config.headers.Authorization = `Bearer ${session.access_token}`;
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: normalise errors
axiosClient.interceptors.response.use(
  (response) => response,
  (error) => {
    // Supabase REST returns errors in a specific shape
    const message =
      error.response?.data?.message ||
      error.response?.data?.error_description ||
      error.message ||
      'An unexpected error occurred';

    console.error('[axiosClient]', message);
    return Promise.reject(error);
  }
);

export { axiosClient };

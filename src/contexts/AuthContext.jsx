import { createContext, useContext, useEffect, useState } from 'react';
import { supabase } from '../lib/supabaseClient';
import { axiosClient } from '../lib/axiosClient';

const AuthContext = createContext(null);

/**
 * AuthProvider wraps the app and provides:
 * - user: the current Supabase auth user (from auth.users)
 * - profile: the app-specific profile (from public.profiles)
 * - loading: whether the initial session check is in progress
 * - signIn / signUp / signOut helpers
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);

  // Fetch the public profile for a given auth user
  const fetchProfile = async (authUser) => {
    if (!authUser) {
      setProfile(null);
      return null;
    }

    try {
      const { data } = await axiosClient.get(
        `/rest/v1/profiles?id=eq.${authUser.id}&select=*`,
        { headers: { Accept: 'application/vnd.pgrst.object+json' } }
      );
      setProfile(data);
      return data;
    } catch (err) {
      console.error('Failed to fetch profile:', err);
      setProfile(null);
      return null;
    }
  };

  useEffect(() => {
    // 1. Check for existing session on mount
    const initSession = async () => {
      const {
        data: { session },
      } = await supabase.auth.getSession();

      setUser(session?.user ?? null);

      if (session?.user) {
        await fetchProfile(session.user);
      }

      setLoading(false);
    };

    initSession();

    // 2. Listen for auth state changes (login, logout, token refresh)
    const {
      data: { subscription },
    } = supabase.auth.onAuthStateChange(async (event, session) => {
      const currentUser = session?.user ?? null;
      setUser(currentUser);

      if (event === 'SIGNED_IN' || event === 'TOKEN_REFRESHED') {
        await fetchProfile(currentUser);
      }

      if (event === 'SIGNED_OUT') {
        setProfile(null);
      }
    });

    return () => subscription.unsubscribe();
  }, []);

  // Auth helpers
  const signIn = async (email, password) => {
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });
    if (error) throw error;
    return data;
  };

  const signUp = async (email, password, username) => {
    const { data, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: { username },
      },
    });
    if (error) throw error;
    return data;
  };

  const signOut = async () => {
    const { error } = await supabase.auth.signOut();
    if (error) throw error;
  };

  const value = {
    user,
    profile,
    loading,
    signIn,
    signUp,
    signOut,
    refreshProfile: () => fetchProfile(user),
    isAdmin: profile?.role === 'admin',
    isPro: profile?.is_pro === true,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/**
 * Hook to access the auth context.
 * Must be used inside <AuthProvider>.
 */
export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

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
  const [bannedNotice, setBannedNotice] = useState(
    () => sessionStorage.getItem('banned_notice') || ''
  );

  const clearBannedNotice = () => {
    sessionStorage.removeItem('banned_notice');
    setBannedNotice('');
  };

  // Completely purge all in-memory, localStorage, and sessionStorage auth tokens
  const purgeSession = async (noticeMsg) => {
    try {
      if (noticeMsg) {
        sessionStorage.setItem('banned_notice', noticeMsg);
        setBannedNotice(noticeMsg);
      }
      setUser(null);
      setProfile(null);
      await supabase.auth.signOut({ scope: 'local' }).catch(() => {});
      
      // Wipe all Supabase auth storage tokens
      Object.keys(localStorage).forEach((key) => {
        if (key.startsWith('sb-') || key.includes('auth-token') || key.includes('supabase')) {
          localStorage.removeItem(key);
        }
      });
      Object.keys(sessionStorage).forEach((key) => {
        if (key !== 'banned_notice' && (key.startsWith('sb-') || key.includes('auth-token') || key.includes('supabase'))) {
          sessionStorage.removeItem(key);
        }
      });
    } catch (e) {
      console.error('Error purging session:', e);
    }
  };

  // Fetch the public profile for a given auth user
  const fetchProfile = async (authUser) => {
    if (!authUser) {
      setUser(null);
      setProfile(null);
      return null;
    }

    try {
      const { data } = await axiosClient.get(
        `/rest/v1/profiles?id=eq.${authUser.id}&select=*`,
        { headers: { Accept: 'application/vnd.pgrst.object+json' } }
      );

      // Check if user is banned
      if (data?.is_banned) {
        console.warn('User account is banned. Completely purging session.');
        await purgeSession('Your account has been suspended by an administrator. Access is revoked.');
        return null;
      }

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

      if (session?.user) {
        const prof = await fetchProfile(session.user);
        if (prof && !prof.is_banned) {
          setUser(session.user);
        } else {
          await purgeSession('Your account has been suspended by an administrator. Access is revoked.');
        }
      } else {
        setUser(null);
      }

      setLoading(false);
    };

    initSession();

    // 2. Listen for auth state changes (login, logout, token refresh)
    const {
      data: { subscription },
    } = supabase.auth.onAuthStateChange(async (event, session) => {
      const currentUser = session?.user ?? null;

      if (event === 'SIGNED_IN' || event === 'TOKEN_REFRESHED') {
        if (currentUser) {
          const prof = await fetchProfile(currentUser);
          if (prof && !prof.is_banned) {
            setUser(currentUser);
          } else {
            await purgeSession('Your account has been suspended by an administrator. Access is revoked.');
          }
        }
      } else if (event === 'SIGNED_OUT') {
        setUser(null);
        setProfile(null);
      } else {
        setUser(currentUser);
      }
    });

    return () => subscription.unsubscribe();
  }, []);

  // 3. Realtime subscription to profile updates (instant ban / role / pro updates)
  useEffect(() => {
    if (!user?.id) return;

    const channel = supabase
      .channel(`profile-sync-${user.id}`)
      .on(
        'postgres_changes',
        {
          event: 'UPDATE',
          schema: 'public',
          table: 'profiles',
          filter: `id=eq.${user.id}`,
        },
        async (payload) => {
          if (payload.new?.is_banned) {
            console.warn('Realtime: User banned. Forcefully purging session.');
            await purgeSession('Your account has been suspended by an administrator. Access is revoked.');
          } else {
            setProfile(payload.new);
          }
        }
      )
      .subscribe();

    // Check profile on tab focus / visibility change
    const onVisibilityChange = async () => {
      if (document.visibilityState === 'visible' && user?.id) {
        await fetchProfile(user);
      }
    };
    document.addEventListener('visibilitychange', onVisibilityChange);

    return () => {
      supabase.removeChannel(channel);
      document.removeEventListener('visibilitychange', onVisibilityChange);
    };
  }, [user?.id]);

  // Auth helpers
  const signIn = async (email, password) => {
    clearBannedNotice();
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });
    if (error) throw error;

    if (data?.user) {
      const prof = await fetchProfile(data.user);
      if (prof?.is_banned || !prof) {
        await purgeSession('Your account has been banned/suspended by an administrator. Please contact support.');
        throw new Error('Your account has been banned/suspended by an administrator. Please contact support.');
      }
      setUser(data.user);
    }

    return data;
  };

  const signUp = async (email, password, username) => {
    clearBannedNotice();
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
    clearBannedNotice();
    const { error } = await supabase.auth.signOut();
    if (error) throw error;
  };

  const value = {
    user,
    profile,
    loading,
    bannedNotice,
    clearBannedNotice,
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

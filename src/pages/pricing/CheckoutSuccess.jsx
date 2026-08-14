import React, { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { axiosClient } from '../../lib/axiosClient';
import { CheckCircle2, ArrowRight, Sparkles, ShieldCheck } from 'lucide-react';

export default function CheckoutSuccess() {
  const [searchParams] = useSearchParams();
  const sessionId = searchParams.get('session_id');
  const { user, refreshProfile } = useAuth();
  const [upgraded, setUpgraded] = useState(false);

  useEffect(() => {
    // If webhook takes a second, also ensure user's profile is refreshed
    const sync = async () => {
      if (user) {
        // Fallback update in case client returned before webhook processed in local dev
        await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, {
          is_pro: true,
        });
        await refreshProfile();
        setUpgraded(true);
      }
    };
    sync();
  }, [user]);

  return (
    <div className="max-w-xl mx-auto px-4 py-16 text-center space-y-6">
      <div className="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto border border-emerald-200/80 shadow-lg shadow-emerald-600/10 animate-in zoom-in-50 duration-300">
        <ShieldCheck className="h-8 w-8 text-emerald-600" />
      </div>

      <div className="space-y-2">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase tracking-wider">
          PRO STATUS ACTIVATED
        </div>
        <h1 className="text-3xl font-extrabold font-heading text-zinc-900 tracking-tight">
          Welcome to Semicolon Pro!
        </h1>
        <p className="text-xs text-zinc-600 leading-relaxed max-w-md mx-auto">
          You paid ₹499, so you’re definitely finishing that course now. We’ve unlocked all book and paper downloads. Congratulations, you’ve officially paid to suffer less. 😌
        </p>
      </div>

      <div className="p-4 bg-zinc-50 border border-zinc-200 rounded-2xl text-xs text-zinc-500 font-mono">
        Transaction Reference: {sessionId ? sessionId.slice(0, 24) + '...' : 'Verified by Stripe'}
      </div>

      <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
        <Link
          to="/dashboard"
          className="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition flex items-center justify-center gap-2"
        >
          <span>Go to Dashboard</span>
          <ArrowRight className="h-3.5 w-3.5" />
        </Link>
        <Link
          to="/books"
          className="w-full sm:w-auto px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 rounded-xl text-xs font-semibold transition"
        >
          Explore Pro Library
        </Link>
      </div>
    </div>
  );
}

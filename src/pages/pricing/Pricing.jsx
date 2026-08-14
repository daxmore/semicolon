import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useStripeCheckout } from '../../hooks/useStripe';
import { Check, Sparkles, Zap, Lock, ShieldCheck, ArrowRight, AlertCircle } from 'lucide-react';

export default function Pricing() {
  const { user, isPro } = useAuth();
  const navigate = useNavigate();
  const { mutate: checkout, isPending, error } = useStripeCheckout();

  const handleUpgrade = () => {
    if (!user) {
      navigate('/login');
      return;
    }

    checkout({
      userId: user.id,
      email: user.email,
    });
  };

  const freeFeatures = [
    'Access to all public developer books online',
    'Access to research papers & video tutorials',
    'Interactive skill quizzes (Easy & Medium tiers)',
    'Community discussions & voting',
    'Level progression & XP tracking',
  ];

  const proFeatures = [
    'Everything in Free tier',
    'Unlimited high-speed PDF downloads for all books',
    'Download academic & technical research papers',
    'Interview Tier challenges & verified certificates',
    'Pro badge on community feed and profile',
    'Priority material request submissions',
    'Completely ad-free experience',
  ];

  return (
    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
      {/* Header */}
      <div className="text-center space-y-3 max-w-2xl mx-auto">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200/60 text-amber-700 text-xs font-semibold">
          <Sparkles className="h-3.5 w-3.5" />
          Simple, Transparent Pricing
        </div>
        <h1 className="text-4xl font-extrabold font-heading text-zinc-900 tracking-tight">
          Invest in Your Engineering Career
        </h1>
        <p className="text-xs text-zinc-500 leading-relaxed">
          Level up your architectural skills, download exclusive books and papers, and earn verified mastery certificates.
        </p>
      </div>

      {error && (
        <div className="max-w-md mx-auto p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle className="h-4 w-4 shrink-0" />
          <span>{error.message || 'Failed to initiate checkout. Please check Edge Function setup.'}</span>
        </div>
      )}

      {/* Pricing Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto items-stretch">
        {/* Free Plan */}
        <div className="bg-white rounded-3xl border border-zinc-200/80 p-8 flex flex-col justify-between shadow-sm hover:border-zinc-300 transition space-y-6">
          <div className="space-y-4">
            <div className="space-y-1">
              <span className="text-xs font-bold uppercase tracking-wider text-zinc-400">Starter</span>
              <h2 className="text-2xl font-bold text-zinc-900">Free Tier</h2>
              <p className="text-xs text-zinc-500">Perfect for exploring library resources and participating in community.</p>
            </div>

            <div className="py-2">
              <span className="text-4xl font-extrabold text-zinc-900">₹0</span>
              <span className="text-xs text-zinc-400 font-medium ml-1">/ forever</span>
            </div>

            <div className="pt-4 border-t border-zinc-100 space-y-3 text-xs">
              <span className="font-semibold text-zinc-800 block">What is included:</span>
              {freeFeatures.map((feat, idx) => (
                <div key={idx} className="flex items-center gap-2.5 text-zinc-600">
                  <Check className="h-4 w-4 text-emerald-500 shrink-0" />
                  <span>{feat}</span>
                </div>
              ))}
            </div>
          </div>

          <div className="pt-6">
            <button
              disabled
              className="w-full py-3 px-4 bg-zinc-100 text-zinc-500 rounded-xl text-xs font-semibold cursor-default"
            >
              Current Base Plan
            </button>
          </div>
        </div>

        {/* Pro Plan */}
        <div className="bg-gradient-to-b from-zinc-950 to-zinc-900 text-white rounded-3xl p-8 flex flex-col justify-between shadow-2xl relative border border-amber-500/30 space-y-6">
          {/* Top highlight pill */}
          <div className="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">
            Recommended For Engineers
          </div>

          <div className="space-y-4">
            <div className="space-y-1">
              <span className="text-xs font-bold uppercase tracking-wider text-amber-400">Unlimited Access</span>
              <h2 className="text-2xl font-bold text-white">Semicolon Pro</h2>
              <p className="text-xs text-zinc-400">One-time payment for full library downloads, interview tiers, and certificates.</p>
            </div>

            <div className="py-2 flex items-baseline gap-2">
              <span className="text-4xl font-extrabold text-white">₹499</span>
              <span className="text-xs text-zinc-400 font-medium">/ one-time payment</span>
            </div>

            <div className="pt-4 border-t border-white/10 space-y-3 text-xs">
              <span className="font-semibold text-amber-300 block">Everything you unlock:</span>
              {proFeatures.map((feat, idx) => (
                <div key={idx} className="flex items-center gap-2.5 text-zinc-300">
                  <Check className="h-4 w-4 text-amber-400 shrink-0" />
                  <span>{feat}</span>
                </div>
              ))}
            </div>
          </div>

          <div className="pt-6">
            {isPro ? (
              <div className="w-full py-3 px-4 bg-amber-500/20 border border-amber-400/30 text-amber-300 rounded-xl text-xs font-bold text-center flex items-center justify-center gap-2">
                <ShieldCheck className="h-4 w-4 text-amber-400" />
                Active Pro Member
              </div>
            ) : (
              <button
                onClick={handleUpgrade}
                disabled={isPending}
                className="w-full py-3 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-zinc-950 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/20 transition flex items-center justify-center gap-2 disabled:opacity-50"
              >
                {isPending ? (
                  <div className="w-4 h-4 border-2 border-zinc-950 border-t-transparent rounded-full animate-spin" />
                ) : (
                  <>
                    <span>Upgrade to Pro Now</span>
                    <ArrowRight className="h-4 w-4" />
                  </>
                )}
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { useStripeCheckout } from '../../hooks/useStripe';
import { Check, AlertCircle } from 'lucide-react';

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

  return (
    <div className="antialiased bg-[#FAFAFA]">
      {/* Hero Section */}
      <section className="relative pt-10 pb-24 overflow-hidden">
        <div className="absolute inset-0 -z-10">
          <div className="absolute top-0 left-1/3 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 right-1/3 w-80 h-80 bg-teal-200/30 rounded-full blur-3xl"></div>
        </div>
        
        <div className="container mx-auto px-6 text-center">
          <span className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-full text-sm font-medium text-indigo-600 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Simple Pricing
          </span>
          <h1 className="text-5xl md:text-6xl font-bold text-zinc-900 mb-6 tracking-tight">
            Unlock <span className="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Full Access</span>
          </h1>
          <p className="text-xl text-zinc-500 max-w-2xl mx-auto">
            Choose the plan that fits your learning needs. Upgrade to Pro for exclusive features and priority support.
          </p>
        </div>
      </section>

      {/* Error Message */}
      {error && (
        <div className="max-w-md mx-auto mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle className="h-4 w-4 shrink-0" />
          <span>{error.message || 'Failed to initiate checkout.'}</span>
        </div>
      )}

      {/* Pricing Cards */}
      <section className="pb-24">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            
            {/* Free Plan */}
            <div className="bg-white rounded-3xl p-8 border border-zinc-200 hover:border-zinc-300 hover:shadow-xl transition-all duration-300 flex flex-col">
              <div className="mb-8">
                <h2 className="text-2xl font-bold text-zinc-900 mb-2">Free</h2>
                <div className="flex items-baseline gap-1">
                  <span className="text-5xl font-bold text-zinc-900">₹0</span>
                  <span className="text-zinc-500">/month</span>
                </div>
                <p className="text-zinc-500 mt-2">Perfect for getting started</p>
              </div>
              
              <ul className="space-y-4 mb-8 flex-grow">
                <li className="flex items-center gap-3 text-zinc-700">
                  <div className="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-green-600 stroke-[3]" />
                  </div>
                  View Books & Papers
                </li>
                <li className="flex items-center gap-3 text-zinc-700">
                  <div className="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-green-600 stroke-[3]" />
                  </div>
                  Watch Videos
                </li>
                <li className="flex items-center gap-3 text-zinc-700">
                  <div className="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-green-600 stroke-[3]" />
                  </div>
                  Basic Search
                </li>
                <li className="flex items-center gap-3 text-zinc-700">
                  <div className="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-green-600 stroke-[3]" />
                  </div>
                  Request Materials
                </li>
              </ul>
              
              <button className="w-full py-4 bg-zinc-100 text-zinc-600 font-semibold rounded-xl cursor-default">
                {isPro ? 'Legacy Plan' : 'Current Plan'}
              </button>
            </div>

            {/* Pro Plan */}
            <div className="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-3xl p-8 relative overflow-hidden flex flex-col shadow-xl shadow-indigo-500/20">
              {/* Background pattern */}
              <div className="absolute inset-0 opacity-10">
                <div className="absolute inset-0 bg-[linear-gradient(to_right,#ffffff12_1px,transparent_1px),linear-gradient(to_bottom,#ffffff12_1px,transparent_1px)] bg-[size:24px_24px]"></div>
              </div>
              
              {/* Badge */}
              <div className="absolute top-6 right-6">
                <span className="px-3 py-1 bg-white/20 backdrop-blur text-white text-xs font-bold rounded-full">
                  RECOMMENDED
                </span>
              </div>
              
              <div className="relative z-10 mb-8">
                <h2 className="text-2xl font-bold text-white mb-2">Pro</h2>
                <div className="flex items-baseline gap-1">
                  <span className="text-5xl font-bold text-white">₹499</span>
                  <span className="text-indigo-200">/month</span>
                </div>
                <p className="text-indigo-200 mt-2">For serious learners</p>
              </div>
              
              <ul className="space-y-4 mb-8 flex-grow relative z-10">
                <li className="flex items-center gap-3 text-white">
                  <div className="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-white stroke-[3]" />
                  </div>
                  <strong>Everything in Free</strong>
                </li>
                <li className="flex items-center gap-3 text-indigo-100">
                  <div className="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-white stroke-[3]" />
                  </div>
                  Download Access
                </li>
                <li className="flex items-center gap-3 text-indigo-100">
                  <div className="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-white stroke-[3]" />
                  </div>
                  Priority Request Handling
                </li>
                <li className="flex items-center gap-3 text-indigo-100">
                  <div className="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-white stroke-[3]" />
                  </div>
                  Advanced Search & Analytics
                </li>
                <li className="flex items-center gap-3 text-indigo-100">
                  <div className="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <Check className="h-3 w-3 text-white stroke-[3]" />
                  </div>
                  Ad-Free Experience
                </li>
              </ul>
              
              {isPro ? (
                <button className="relative z-10 w-full py-4 bg-white/20 text-white font-semibold rounded-xl cursor-default backdrop-blur">
                  Active Plan
                </button>
              ) : (
                <button 
                  onClick={handleUpgrade}
                  disabled={isPending}
                  className="relative z-10 w-full py-4 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition-colors shadow-lg disabled:opacity-50"
                >
                  {isPending ? 'Connecting...' : 'Upgrade to Pro'}
                </button>
              )}
            </div>

          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-20 bg-white border-t border-zinc-100">
        <div className="container mx-auto px-6">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-zinc-900 mb-4">Frequently Asked Questions</h2>
            <p className="text-zinc-500">Got questions? We've got answers.</p>
          </div>
          
          <div className="max-w-2xl mx-auto space-y-4">
            <div className="bg-zinc-50 rounded-2xl p-6">
              <h3 className="font-semibold text-zinc-900 mb-2">Can I cancel anytime?</h3>
              <p className="text-zinc-600">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
            </div>
            <div className="bg-zinc-50 rounded-2xl p-6">
              <h3 className="font-semibold text-zinc-900 mb-2">What payment methods do you accept?</h3>
              <p className="text-zinc-600">We accept all major credit cards, debit cards, and UPI payments for Indian users.</p>
            </div>
            <div className="bg-zinc-50 rounded-2xl p-6">
              <h3 className="font-semibold text-zinc-900 mb-2">Is there a student discount?</h3>
              <p className="text-zinc-600">Yes! Students with a valid .edu email can get 50% off Pro subscriptions. Contact us to verify.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

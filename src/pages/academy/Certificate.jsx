import React, { useRef } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { Award, Download, ArrowLeft, ShieldCheck, Share2 } from 'lucide-react';
import Logo from '../../components/common/Logo';

export default function Certificate() {
  const [searchParams] = useSearchParams();
  const skillName = searchParams.get('skill') || 'Full Stack Architecture';
  const { profile } = useAuth();
  const certRef = useRef();

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-12 space-y-8">
      <div className="flex items-center justify-between">
        <Link
          to="/academy"
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition"
        >
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Academy
        </Link>
        <button
          onClick={handlePrint}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <Download className="h-4 w-4" /> Export Certificate
        </button>
      </div>

      {/* Certificate Frame */}
      <div
        ref={certRef}
        className="bg-white p-10 sm:p-16 rounded-3xl border-8 border-indigo-950 shadow-2xl space-y-8 text-center relative overflow-hidden print:border-none print:shadow-none"
      >
        {/* Subtle Watermark */}
        <div className="absolute inset-0 opacity-[0.03] pointer-events-none flex items-center justify-center">
          <Award className="w-96 h-96" />
        </div>

        <div className="flex justify-center">
          <Logo />
        </div>

        <div className="space-y-2">
          <span className="text-xs uppercase tracking-[0.25em] font-bold text-amber-600">
            Certificate of Engineering Mastery
          </span>
          <h1 className="text-3xl sm:text-4xl font-extrabold font-heading text-zinc-950 tracking-tight">
            Verified Competency
          </h1>
        </div>

        <div className="py-4 space-y-2">
          <p className="text-xs text-zinc-500 font-medium">This is proudly presented to</p>
          <h2 className="text-2xl sm:text-3xl font-bold font-heading text-indigo-700 underline decoration-amber-400 decoration-2 underline-offset-8">
            {profile?.username || 'Verified Developer'}
          </h2>
          <p className="text-xs text-zinc-600 max-w-md mx-auto pt-4 leading-relaxed">
            For successfully completing all four progressive challenge tiers and demonstrating practical mastery in{' '}
            <strong className="text-zinc-900">{skillName}</strong>.
          </p>
        </div>

        {/* Footer Details */}
        <div className="grid grid-cols-2 gap-8 pt-8 border-t border-zinc-200 text-xs">
          <div className="text-left space-y-1">
            <span className="text-zinc-400 block text-[10px] uppercase font-semibold">Date of Issuance</span>
            <span className="font-semibold text-zinc-800">{new Date().toLocaleDateString()}</span>
          </div>
          <div className="text-right space-y-1">
            <span className="text-zinc-400 block text-[10px] uppercase font-semibold">Verification</span>
            <span className="font-mono text-emerald-600 font-bold flex items-center justify-end gap-1">
              <ShieldCheck className="h-4 w-4" /> SEMI-VERIFIED
            </span>
          </div>
        </div>
      </div>
    </div>
  );
}

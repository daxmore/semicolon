import React from 'react';
import { Link } from 'react-router-dom';
import { Code, Terminal, BookOpen, Users, Shield, Sparkles, Heart } from 'lucide-react';
import Logo from '../components/common/Logo';

export default function About() {
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
      {/* Header */}
      <div className="text-center space-y-4">
        <div className="flex justify-center mb-2">
          <Logo />
        </div>
        <h1 className="text-4xl font-extrabold font-heading text-zinc-900 tracking-tight">
          About Semicolon
        </h1>
        <p className="text-sm text-zinc-500 max-w-xl mx-auto leading-relaxed">
          The curated engineering hub built by developers, for developers. Designed to bridge the gap between academic research and modern production software engineering.
        </p>
      </div>

      {/* Philosophy */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-2xl border border-zinc-200/80 shadow-sm space-y-3">
          <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <BookOpen className="h-5 w-5" />
          </div>
          <h3 className="font-bold text-sm text-zinc-900">Curated Knowledge</h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            We cut through the noise. Only verified, high-yield architectural books, seminal research papers, and deep-dive videos make it onto the platform.
          </p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-zinc-200/80 shadow-sm space-y-3">
          <div className="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <Sparkles className="h-5 w-5" />
          </div>
          <h3 className="font-bold text-sm text-zinc-900">Gamified Growth</h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            Continuous learning should be rewarding. Track daily streaks, earn XP, unlock progressive tiers, and showcase verified mastery certificates.
          </p>
        </div>

        <div className="bg-white p-6 rounded-2xl border border-zinc-200/80 shadow-sm space-y-3">
          <div className="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <Users className="h-5 w-5" />
          </div>
          <h3 className="font-bold text-sm text-zinc-900">Peer Collaboration</h3>
          <p className="text-xs text-zinc-500 leading-relaxed">
            Discuss challenging architectural patterns, receive helpful answers from other developers, and earn reputation for accepted solutions.
          </p>
        </div>
      </div>

      {/* Origin Story */}
      <div className="bg-gradient-to-br from-zinc-950 to-zinc-900 text-white rounded-3xl p-8 sm:p-12 shadow-xl space-y-4">
        <span className="text-xs font-bold uppercase tracking-wider text-indigo-400">Our Mission</span>
        <h2 className="text-2xl sm:text-3xl font-bold font-heading">
          Why "Semicolon"?
        </h2>
        <p className="text-xs text-zinc-300 leading-relaxed">
          In literature, an author uses a semicolon when they could have chosen to end their sentence, but decided to continue. In software engineering, the semicolon terminates a command, preparing the compiler for the next instruction.
        </p>
        <p className="text-xs text-zinc-300 leading-relaxed">
          Semicolon represents your relentless journey as an engineer—never stopping at "good enough," continuously learning the foundations and evolving into a master of the craft.
        </p>
      </div>

      {/* CTA */}
      <div className="text-center pt-4">
        <Link
          to="/signup"
          className="inline-flex items-center gap-2 px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-bold shadow-lg shadow-indigo-600/20 transition"
        >
          <span>Join Semicolon Today</span>
        </Link>
      </div>
    </div>
  );
}

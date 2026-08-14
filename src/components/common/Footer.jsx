import React from 'react';
import { Link } from 'react-router-dom';
import Logo from './Logo';

export default function Footer() {
  return (
    <footer className="bg-white border-t border-zinc-200/80 mt-auto">
      <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Col 1 */}
          <div className="space-y-4 md:col-span-1">
            <Logo />
            <p className="text-xs text-zinc-500 leading-relaxed">
              Curated books, research papers, video tutorials, and interactive coding challenges for modern software engineers.
            </p>
            <p className="text-xs text-zinc-400">
              © {new Date().getFullYear()} Semicolon. All rights reserved.
            </p>
          </div>

          {/* Col 2 */}
          <div>
            <h3 className="text-xs font-bold uppercase tracking-wider text-zinc-900 mb-3">Resources</h3>
            <ul className="space-y-2 text-xs">
              <li><Link to="/books" className="text-zinc-600 hover:text-indigo-600 transition">Books & Guides</Link></li>
              <li><Link to="/papers" className="text-zinc-600 hover:text-indigo-600 transition">Research Papers</Link></li>
              <li><Link to="/videos" className="text-zinc-600 hover:text-indigo-600 transition">Video Tutorials</Link></li>
              <li><Link to="/community" className="text-zinc-600 hover:text-indigo-600 transition">Developer Feed</Link></li>
            </ul>
          </div>

          {/* Col 3 */}
          <div>
            <h3 className="text-xs font-bold uppercase tracking-wider text-zinc-900 mb-3">Academy</h3>
            <ul className="space-y-2 text-xs">
              <li><Link to="/academy" className="text-zinc-600 hover:text-indigo-600 transition">Skill Paths</Link></li>
              <li><Link to="/leaderboard" className="text-zinc-600 hover:text-indigo-600 transition">Hall of Fame</Link></li>
              <li><Link to="/pricing" className="text-zinc-600 hover:text-indigo-600 transition">Pro Membership</Link></li>
              <li><Link to="/request" className="text-zinc-600 hover:text-indigo-600 transition">Request Content</Link></li>
            </ul>
          </div>

          {/* Col 4 */}
          <div>
            <h3 className="text-xs font-bold uppercase tracking-wider text-zinc-900 mb-3">Company</h3>
            <ul className="space-y-2 text-xs">
              <li><Link to="/about" className="text-zinc-600 hover:text-indigo-600 transition">About Us</Link></li>
              <li><a href="mailto:support@semicolon.dev" className="text-zinc-600 hover:text-indigo-600 transition">Support</a></li>
              <li><span className="text-zinc-400">Terms of Service</span></li>
              <li><span className="text-zinc-400">Privacy Policy</span></li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  );
}

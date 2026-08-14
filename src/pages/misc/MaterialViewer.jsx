import React, { useEffect, useState } from 'react';
import { useSearchParams, Link, useNavigate } from 'react-router-dom';
import { axiosClient } from '../../lib/axiosClient';
import { useAuth } from '../../contexts/AuthContext';
import { BookOpen, FileText, ArrowLeft, Download, ShieldCheck, AlertCircle } from 'lucide-react';

export default function MaterialViewer() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token');
  const type = searchParams.get('type') || 'book'; // 'book' | 'paper'
  const { user } = useAuth();
  const navigate = useNavigate();

  const [material, setMaterial] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchByToken = async () => {
      if (!token) {
        setError('Missing resource token.');
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        const endpoint = type === 'paper' ? 'papers' : 'books';
        const { data } = await axiosClient.get(
          `/rest/v1/${endpoint}?token=eq.${encodeURIComponent(token)}&select=*`,
          { headers: { Accept: 'application/vnd.pgrst.object+json' } }
        );

        setMaterial(data);

        // Record viewing history in user_history if user logged in
        if (user && data?.id) {
          await axiosClient.post('/rest/v1/user_history', {
            user_id: user.id,
            resource_type: type,
            resource_id: data.id,
          });
        }
      } catch (err) {
        console.error(err);
        setError('Invalid or expired resource token.');
      } finally {
        setLoading(false);
      }
    };

    fetchByToken();
  }, [token, type, user]);

  if (loading) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 text-center space-y-4">
        <div className="w-10 h-10 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto" />
        <p className="text-xs text-zinc-500">Loading secure material viewer...</p>
      </div>
    );
  }

  if (error || !material) {
    return (
      <div className="max-w-md mx-auto px-4 py-16 text-center space-y-4 bg-white p-8 rounded-3xl border border-zinc-200 shadow-sm mt-10">
        <AlertCircle className="h-8 w-8 text-rose-500 mx-auto" />
        <h2 className="text-base font-bold text-zinc-900">Access Restricted</h2>
        <p className="text-xs text-zinc-500">{error || 'Resource not found.'}</p>
        <Link to="/books" className="inline-block text-xs font-semibold text-indigo-600">
          &larr; Back to Library
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
      <div className="flex items-center justify-between">
        <Link
          to={type === 'paper' ? '/papers' : '/books'}
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition"
        >
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Library
        </Link>
        <span className="text-xs font-mono bg-zinc-100 text-zinc-600 px-3 py-1 rounded-full border border-zinc-200">
          Token: {token.slice(0, 8)}...
        </span>
      </div>

      <div className="bg-white rounded-3xl border border-zinc-200/80 p-8 shadow-sm space-y-6">
        <div className="border-b border-zinc-100 pb-4 space-y-2">
          <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full uppercase">
            {material.subject}
          </span>
          <h1 className="text-2xl font-bold font-heading text-zinc-900">{material.title}</h1>
          {material.author && (
            <p className="text-xs text-zinc-500 font-medium">By {material.author}</p>
          )}
        </div>

        {/* Embedded Iframe / PDF Viewer */}
        <div className="aspect-[4/3] w-full bg-zinc-900 rounded-2xl overflow-hidden border border-zinc-200 shadow-inner">
          <iframe
            src={material.private_path}
            title={material.title}
            className="w-full h-full border-0"
          />
        </div>
      </div>
    </div>
  );
}

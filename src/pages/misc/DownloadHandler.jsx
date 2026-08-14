import React, { useEffect, useState } from 'react';
import { useSearchParams, Link, useNavigate } from 'react-router-dom';
import { axiosClient } from '../../lib/axiosClient';
import { useAuth } from '../../contexts/AuthContext';
import { Download, Lock, CheckCircle2, AlertCircle, ArrowLeft } from 'lucide-react';

export default function DownloadHandler() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token');
  const type = searchParams.get('type') || 'book';
  const { user, isPro } = useAuth();
  const navigate = useNavigate();

  const [loading, setLoading] = useState(true);
  const [material, setMaterial] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const handleDownloadAuth = async () => {
      if (!user) {
        navigate('/login');
        return;
      }

      if (!isPro) {
        navigate('/pricing');
        return;
      }

      if (!token) {
        setError('Missing download token.');
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

        // Record download in downloads table
        await axiosClient.post('/rest/v1/downloads', {
          user_id: user.id,
          resource_type: type,
          resource_id: data.id,
        });

        // Trigger file download
        if (data?.private_path) {
          window.location.href = data.private_path;
        }
      } catch (err) {
        console.error(err);
        setError('Failed to authorize secure download.');
      } finally {
        setLoading(false);
      }
    };

    handleDownloadAuth();
  }, [token, type, user, isPro, navigate]);

  if (loading) {
    return (
      <div className="max-w-md mx-auto px-4 py-20 text-center space-y-4">
        <div className="w-10 h-10 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto" />
        <p className="text-xs text-zinc-500 font-semibold">Authorizing secure Pro download...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-md mx-auto px-4 py-16 text-center space-y-4 bg-white p-8 rounded-3xl border border-zinc-200 shadow-sm mt-10">
        <AlertCircle className="h-8 w-8 text-rose-500 mx-auto" />
        <h2 className="text-base font-bold text-zinc-900">Download Failed</h2>
        <p className="text-xs text-zinc-500">{error}</p>
        <Link to="/books" className="inline-block text-xs font-semibold text-indigo-600">
          &larr; Back to Library
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-md mx-auto px-4 py-20 text-center space-y-6">
      <div className="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto border border-emerald-200">
        <CheckCircle2 className="h-8 w-8 text-emerald-600" />
      </div>
      <div className="space-y-2">
        <h1 className="text-2xl font-bold text-zinc-900">Download Started</h1>
        <p className="text-xs text-zinc-500">
          Your file for <strong className="text-zinc-800">{material?.title}</strong> should begin downloading automatically.
        </p>
      </div>
      <div className="pt-2">
        <Link
          to={type === 'paper' ? '/papers' : '/books'}
          className="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:underline"
        >
          <ArrowLeft className="h-3.5 w-3.5" /> Back to Library
        </Link>
      </div>
    </div>
  );
}

import React, { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { usePaper } from '../../hooks/usePapers';
import { useAuth } from '../../contexts/AuthContext';
import { axiosClient } from '../../lib/axiosClient';
import { 
  FileText, 
  Download, 
  ArrowLeft, 
  ThumbsUp, 
  ThumbsDown, 
  Lock, 
  ExternalLink,
  CheckCircle2,
  AlertCircle
} from 'lucide-react';

export default function PaperDetail() {
  const { id } = useParams();
  const { user, isPro } = useAuth();
  const { data: paper, isLoading, error } = usePaper(id);
  const navigate = useNavigate();
  const [reaction, setReaction] = useState(null);
  const [toast, setToast] = useState('');

  const handleReaction = async (isHelpful) => {
    if (!user) {
      navigate('/login');
      return;
    }

    try {
      const { data: existing } = await axiosClient.get(
        `/rest/v1/reactions?user_id=eq.${user.id}&resource_type=eq.paper&resource_id=eq.${paper.id}&select=id,is_helpful`
      );

      if (existing && existing.length > 0) {
        const current = existing[0];
        if (current.is_helpful === isHelpful) {
          await axiosClient.delete(`/rest/v1/reactions?id=eq.${current.id}`);
          setReaction(null);
          setToast('Feedback removed.');
        } else {
          await axiosClient.patch(`/rest/v1/reactions?id=eq.${current.id}`, { is_helpful: isHelpful });
          setReaction(isHelpful ? 'helpful' : 'not_helpful');
          setToast('Feedback updated!');
        }
      } else {
        await axiosClient.post('/rest/v1/reactions', {
          user_id: user.id,
          resource_type: 'paper',
          resource_id: paper.id,
          is_helpful: isHelpful,
        });
        setReaction(isHelpful ? 'helpful' : 'not_helpful');
        setToast('Thank you for your feedback!');
      }

      setTimeout(() => setToast(''), 3000);
    } catch (err) {
      console.error(err);
      setToast('Something went wrong.');
      setTimeout(() => setToast(''), 3000);
    }
  };

  const handleDownload = () => {
    if (!user) {
      navigate('/login');
      return;
    }
    if (!isPro) {
      navigate('/pricing');
      return;
    }
    window.open(paper.private_path, '_blank');
  };

  if (isLoading) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-16 animate-pulse space-y-6">
        <div className="h-6 bg-zinc-200 rounded w-1/4"></div>
        <div className="h-10 bg-zinc-200 rounded w-3/4"></div>
      </div>
    );
  }

  if (error || !paper) {
    return (
      <div className="max-w-xl mx-auto px-4 py-16 text-center space-y-4">
        <AlertCircle className="h-10 w-10 text-rose-500 mx-auto" />
        <h2 className="text-lg font-bold text-zinc-900">Paper Not Found</h2>
        <p className="text-xs text-zinc-500">The research paper you requested does not exist.</p>
        <Link to="/papers" className="inline-flex items-center gap-2 text-xs font-semibold text-indigo-600">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to papers
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <Link
        to="/papers"
        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition mb-6"
      >
        <ArrowLeft className="h-3.5 w-3.5" />
        Back to Papers Repository
      </Link>

      {toast && (
        <div className="toast-enter fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl shadow-lg text-xs font-medium text-zinc-800">
          <span className="w-2 h-2 rounded-full bg-emerald-500 shrink-0" />
          {toast}
        </div>
      )}

      <div className="bg-white rounded-2xl border border-zinc-200/80 p-8 sm:p-10 shadow-sm space-y-8">
        <div className="space-y-4 border-b border-zinc-100 pb-6">
          <div className="flex items-center gap-2">
            <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
              {paper.subject}
            </span>
            <span className="text-xs font-bold text-zinc-700 bg-zinc-100 px-3 py-1 rounded-full border border-zinc-200">
              Published: {paper.year}
            </span>
          </div>

          <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
            {paper.title}
          </h1>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
          <a
            href={paper.private_path}
            target="_blank"
            rel="noopener noreferrer"
            className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white font-semibold text-xs shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition"
          >
            <FileText className="h-4 w-4" />
            Read Paper
            <ExternalLink className="h-3.5 w-3.5 opacity-70" />
          </a>

          <button
            onClick={handleDownload}
            className={`flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-xs border transition ${
              isPro
                ? 'bg-amber-500 text-white border-amber-600 shadow-md shadow-amber-500/20 hover:bg-amber-600'
                : 'bg-zinc-50 border-zinc-200 text-zinc-700 hover:bg-zinc-100'
            }`}
          >
            {isPro ? (
              <>
                <Download className="h-4 w-4" />
                Download Paper (Pro)
              </>
            ) : (
              <>
                <Lock className="h-4 w-4 text-amber-500" />
                <span>Unlock PDF Download</span>
                <span className="text-[10px] font-bold text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">PRO</span>
              </>
            )}
          </button>
        </div>

        {/* Reactions */}
        <div className="pt-6 border-t border-zinc-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <span className="text-xs font-medium text-zinc-500">Was this paper helpful?</span>
          <div className="flex items-center gap-2">
            <button
              onClick={() => handleReaction(true)}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition ${
                reaction === 'helpful'
                  ? 'bg-emerald-50 border-emerald-300 text-emerald-700'
                  : 'bg-zinc-50 border-zinc-200 text-zinc-600 hover:bg-zinc-100'
              }`}
            >
              <ThumbsUp className="h-3.5 w-3.5" /> Helpful
            </button>
            <button
              onClick={() => handleReaction(false)}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition ${
                reaction === 'not_helpful'
                  ? 'bg-rose-50 border-rose-300 text-rose-700'
                  : 'bg-zinc-50 border-zinc-200 text-zinc-600 hover:bg-zinc-100'
              }`}
            >
              <ThumbsDown className="h-3.5 w-3.5" /> Needs improvement
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

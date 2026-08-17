import React, { useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useVideo } from '../../hooks/useVideos';
import { useAuth } from '../../contexts/AuthContext';
import { axiosClient } from '../../lib/axiosClient';
import { getYoutubeId } from '../../lib/utils';
import { 
  Video, 
  ArrowLeft, 
  ThumbsUp, 
  ThumbsDown, 
  ExternalLink,
  CheckCircle2,
  AlertCircle
} from 'lucide-react';

export default function VideoDetail() {
  const { id } = useParams();
  const { user } = useAuth();
  const { data: video, isLoading, error } = useVideo(id);
  const navigate = useNavigate();
  const [reaction, setReaction] = useState(null);
  const [toast, setToast] = useState('');

  const handleReaction = async (isHelpful) => {
    if (!user) {
      navigate('/login');
      return;
    }

    try {
      // Check if a reaction already exists
      const { data: existing } = await axiosClient.get(
        `/rest/v1/reactions?user_id=eq.${user.id}&resource_type=eq.video&resource_id=eq.${video.id}&select=id,is_helpful`
      );

      if (existing && existing.length > 0) {
        const current = existing[0];
        if (current.is_helpful === isHelpful) {
          // Same button clicked — remove reaction
          await axiosClient.delete(`/rest/v1/reactions?id=eq.${current.id}`);
          setReaction(null);
          setToast('Feedback removed.');
        } else {
          // Different button — update reaction
          await axiosClient.patch(
            `/rest/v1/reactions?id=eq.${current.id}`,
            { is_helpful: isHelpful }
          );
          setReaction(isHelpful ? 'helpful' : 'not_helpful');
          setToast('Feedback updated!');
        }
      } else {
        // No existing reaction — insert new
        await axiosClient.post('/rest/v1/reactions', {
          user_id: user.id,
          resource_type: 'video',
          resource_id: video.id,
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

  if (isLoading) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-16 animate-pulse space-y-6">
        <div className="h-6 bg-zinc-200 rounded w-1/4"></div>
        <div className="aspect-video bg-zinc-200 rounded-2xl"></div>
      </div>
    );
  }

  if (error || !video) {
    return (
      <div className="max-w-xl mx-auto px-4 py-16 text-center space-y-4">
        <AlertCircle className="h-10 w-10 text-rose-500 mx-auto" />
        <h2 className="text-lg font-bold text-zinc-900">Video Not Found</h2>
        <p className="text-xs text-zinc-500">The video tutorial you requested does not exist.</p>
        <Link to="/videos" className="inline-flex items-center gap-2 text-xs font-semibold text-indigo-600">
          <ArrowLeft className="h-3.5 w-3.5" /> Back to videos
        </Link>
      </div>
    );
  }

  const ytId = getYoutubeId(video.youtube_url);
  const embedUrl = ytId ? `https://www.youtube-nocookie.com/embed/${ytId}?autoplay=1` : null;

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <Link
        to="/videos"
        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition mb-6"
      >
        <ArrowLeft className="h-3.5 w-3.5" />
        Back to Video Academy
      </Link>

      {/* Subtle toast */}
      {toast && (
        <div className="toast-enter fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl shadow-lg text-xs font-medium text-zinc-800">
          <span className="w-2 h-2 rounded-full bg-emerald-500 shrink-0" />
          {toast}
        </div>
      )}

      {/* Video Player */}
      <div className="bg-white rounded-2xl border border-zinc-200/80 overflow-hidden shadow-sm space-y-6">
        <div className="aspect-video bg-black relative">
          {embedUrl ? (
            <iframe
              src={embedUrl}
              title={video.title}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
              className="w-full h-full border-0"
            />
          ) : (
            <div className="flex items-center justify-center h-full text-white text-xs">
              Direct video player:{' '}
              <a href={video.youtube_url} target="_blank" rel="noreferrer" className="underline ml-1">
                Open in YouTube
              </a>
            </div>
          )}
        </div>

        {/* Video Info */}
        <div className="p-8 space-y-6">
          <div className="space-y-3">
            <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
              {video.category}
            </span>

            <h1 className="text-2xl font-bold font-heading text-zinc-900 tracking-tight">
              {video.title}
            </h1>

            {video.description && (
              <p className="text-xs text-zinc-600 leading-relaxed whitespace-pre-line pt-2">
                {video.description}
              </p>
            )}
          </div>

          {/* External link */}
          <div className="pt-4 border-t border-zinc-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <a
              href={video.youtube_url}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600 hover:text-indigo-600 transition"
            >
              <ExternalLink className="h-3.5 w-3.5" />
              Watch on YouTube directly
            </a>

            {/* Reactions */}
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
    </div>
  );
}

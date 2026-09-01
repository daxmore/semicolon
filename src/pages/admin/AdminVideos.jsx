import React, { useState } from 'react';
import { useVideos, useVideoMutations } from '../../hooks/useVideos';
import { useCommunityCategories } from '../../hooks/useCategories';
import { generateSlug, generateToken, getYoutubeId } from '../../lib/utils';
import { Video, Plus, Trash2, Edit3, X, Search, Play } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

export default function AdminVideos() {
  const [searchTerm, setSearchTerm] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [editingVideo, setEditingVideo] = useState(null);
  const [videoToDelete, setVideoToDelete] = useState(null);

  const { data: categories } = useCommunityCategories();
  const { data: videos, isLoading } = useVideos({ search: searchTerm });
  const { createVideo, updateVideo, deleteVideo } = useVideoMutations();

  const [formData, setFormData] = useState({
    title: '',
    description: '',
    youtube_url: '',
    category: 'Frontend',
  });

  const openCreateModal = () => {
    setEditingVideo(null);
    setFormData({
      title: '',
      description: '',
      youtube_url: '',
      category: 'Frontend',
    });
    setShowModal(true);
  };

  const openEditModal = (video) => {
    setEditingVideo(video);
    setFormData({
      title: video.title,
      description: video.description || '',
      youtube_url: video.youtube_url,
      category: video.category,
    });
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingVideo) {
        await updateVideo.mutateAsync({
          id: editingVideo.id,
          ...formData,
        });
      } else {
        const slug = `${generateSlug(formData.title)}-${generateToken(6)}`;
        const token = generateToken(32);

        await createVideo.mutateAsync({
          ...formData,
          slug,
          token,
        });
      }
      setShowModal(false);
    } catch (err) {
      console.error('Error saving video:', err);
    }
  };

  const confirmDeleteVideo = async () => {
    if (!videoToDelete) return;
    try {
      await deleteVideo.mutateAsync(videoToDelete.id);
      setVideoToDelete(null);
    } catch (err) {
      console.error('Failed to delete video:', err);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Manage Video Tutorials</h2>
          <p className="text-xs text-zinc-500">Add YouTube tutorials and technical walk-through sessions.</p>
        </div>
        <button
          onClick={openCreateModal}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition cursor-pointer"
        >
          <Plus className="h-4 w-4" /> Add Video
        </button>
      </div>

      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
        <div className="relative w-full max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
          <input
            type="text"
            placeholder="Filter videos..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
          />
        </div>
        <span className="text-xs text-zinc-500">Total: <strong>{videos?.length || 0}</strong> videos</span>
      </div>

      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Video</th>
                <th className="px-6 py-3.5">Category</th>
                <th className="px-6 py-3.5">YouTube URL</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-400">
                    Loading video tutorials...
                  </td>
                </tr>
              ) : videos && videos.length > 0 ? (
                videos.map((v) => {
                  const yId = getYoutubeId(v.youtube_url);
                  return (
                    <tr key={v.id} className="hover:bg-zinc-50/80 transition">
                      <td className="px-6 py-4 font-semibold text-zinc-900 flex items-center gap-3">
                        <div className="w-12 h-8 rounded-lg bg-zinc-900 relative flex items-center justify-center shrink-0 overflow-hidden">
                          {yId ? (
                            <img
                              src={`https://img.youtube.com/vi/${yId}/hqdefault.jpg`}
                              alt=""
                              className="w-full h-full object-cover"
                            />
                          ) : (
                            <Play className="h-3 w-3 text-white" />
                          )}
                        </div>
                        <span className="max-w-xs truncate">{v.title}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100 font-medium">
                          {v.category}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-zinc-500 font-mono text-[11px] max-w-xs truncate">
                        {v.youtube_url}
                      </td>
                      <td className="px-6 py-4 text-right space-x-2">
                        <button
                          onClick={() => openEditModal(v)}
                          className="p-1.5 text-zinc-500 hover:text-indigo-600 rounded-lg hover:bg-zinc-100 transition cursor-pointer"
                          title="Edit Video"
                        >
                          <Edit3 className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => setVideoToDelete(v)}
                          className="p-1.5 text-zinc-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                          title="Delete Video"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                  );
                })
              ) : (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-500">
                    No videos found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 space-y-5">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <h3 className="font-bold text-base text-zinc-900">
                {editingVideo ? 'Edit Video' : 'Add New Video'}
              </h3>
              <button onClick={() => setShowModal(false)} className="p-1 text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Title</label>
                <input
                  type="text"
                  required
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="Next.js 15 Full Course"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                />
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Category</label>
                <select
                  value={formData.category}
                  onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                >
                  {(categories || []).map((cat) => (
                    <option key={cat} value={cat}>
                      {cat}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">YouTube URL</label>
                <input
                  type="url"
                  required
                  value={formData.youtube_url}
                  onChange={(e) => setFormData({ ...formData, youtube_url: e.target.value })}
                  placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                />
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Description (Optional)</label>
                <textarea
                  rows={3}
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Video overview or topic timestamps..."
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 resize-none"
                />
              </div>

              <div className="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 hover:bg-zinc-50 font-semibold cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={createVideo.isPending || updateVideo.isPending}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition cursor-pointer"
                >
                  {editingVideo ? 'Save Changes' : 'Create Video'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <DeleteConfirmModal
        isOpen={!!videoToDelete}
        onClose={() => setVideoToDelete(null)}
        onConfirm={confirmDeleteVideo}
        title="Delete Video Tutorial"
        itemName={videoToDelete?.title || ''}
        message="Are you sure you want to remove this video tutorial? This action cannot be undone."
        isLoading={deleteVideo.isPending}
      />
    </div>
  );
}

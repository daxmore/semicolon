import React, { useState } from 'react';
import { useBadges, useGamificationMutations } from '../../hooks/useGamification';
import { Award, Plus, Trash2, Edit3, X, Search } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

export default function AdminBadges() {
  const { data: badges, isLoading } = useBadges();
  const { createBadge, updateBadge, deleteBadge } = useGamificationMutations();

  const [showModal, setShowModal] = useState(false);
  const [editingBadge, setEditingBadge] = useState(null);
  const [badgeToDelete, setBadgeToDelete] = useState(null);

  const [formData, setFormData] = useState({
    badge_name: '',
    badge_type: 'mastery',
    required_xp: 100,
    description: '',
    svg_icon: '',
  });

  const openCreateModal = () => {
    setEditingBadge(null);
    setFormData({
      badge_name: '',
      badge_type: 'mastery',
      required_xp: 100,
      description: '',
      svg_icon: '',
    });
    setShowModal(true);
  };

  const openEditModal = (badge) => {
    setEditingBadge(badge);
    setFormData({
      badge_name: badge.badge_name,
      badge_type: badge.badge_type,
      required_xp: badge.required_xp,
      description: badge.description || '',
      svg_icon: badge.svg_icon || '',
    });
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingBadge) {
        await updateBadge.mutateAsync({
          id: editingBadge.id,
          ...formData,
        });
      } else {
        await createBadge.mutateAsync(formData);
      }
      setShowModal(false);
    } catch (err) {
      console.error(err);
    }
  };

  const confirmDeleteBadge = async () => {
    if (!badgeToDelete) return;
    try {
      await deleteBadge.mutateAsync(badgeToDelete.id);
      setBadgeToDelete(null);
    } catch (err) {
      console.error('Failed to delete badge:', err);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Gamification Badges</h2>
          <p className="text-xs text-zinc-500">Manage unlockable XP milestones and skill badges.</p>
        </div>
        <button
          onClick={openCreateModal}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <Plus className="h-4 w-4" /> Add New Badge
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {isLoading ? (
          <div className="col-span-full py-8 text-center text-xs text-zinc-400">
            Loading badges...
          </div>
        ) : badges && badges.length > 0 ? (
          badges.map((b) => (
            <div
              key={b.id}
              className="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm flex items-start justify-between gap-3"
            >
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                  {b.svg_icon ? (
                    <div
                      className="w-6 h-6 flex items-center justify-center"
                      dangerouslySetInnerHTML={{ __html: b.svg_icon }}
                    />
                  ) : (
                    <Award className="h-5 w-5" />
                  )}
                </div>
                <div>
                  <h4 className="font-bold text-xs text-zinc-900">{b.badge_name}</h4>
                  <p className="text-[11px] text-amber-600 font-semibold">{b.required_xp} XP Required</p>
                  <p className="text-[11px] text-zinc-500 mt-1 leading-relaxed">{b.description || 'No description'}</p>
                </div>
              </div>

              <div className="flex items-center gap-1 shrink-0">
                <button
                  onClick={() => openEditModal(b)}
                  className="p-1.5 text-zinc-400 hover:text-indigo-600 hover:bg-zinc-100 rounded-lg"
                  title="Edit Badge"
                >
                  <Edit3 className="h-3.5 w-3.5" />
                </button>
                <button
                  onClick={() => setBadgeToDelete(b)}
                  className="p-1.5 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer"
                  title="Delete Badge"
                >
                  <Trash2 className="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          ))
        ) : (
          <div className="col-span-full py-12 text-center text-zinc-500 bg-white rounded-2xl border border-zinc-200">
            No badges found.
          </div>
        )}
      </div>

      {/* Modal Form */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <h3 className="font-bold text-sm text-zinc-900">
                {editingBadge ? 'Edit Badge' : 'Create Gamification Badge'}
              </h3>
              <button onClick={() => setShowModal(false)} className="p-1 text-zinc-400 hover:text-zinc-600">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Badge Name</label>
                <input
                  type="text"
                  required
                  value={formData.badge_name}
                  onChange={(e) => setFormData({ ...formData, badge_name: e.target.value })}
                  placeholder="Syntax Master"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Required XP</label>
                <input
                  type="number"
                  min="0"
                  required
                  value={formData.required_xp}
                  onChange={(e) => setFormData({ ...formData, required_xp: parseInt(e.target.value, 10) })}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Description</label>
                <textarea
                  rows="2"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Earned for solving challenges..."
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Raw SVG Markup (Optional)</label>
                <textarea
                  rows="2"
                  value={formData.svg_icon}
                  onChange={(e) => setFormData({ ...formData, svg_icon: e.target.value })}
                  placeholder="<svg viewBox='0 0 24 24'>...</svg>"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl font-mono text-[11px] focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 font-semibold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold"
                >
                  Save Badge
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Badge Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!badgeToDelete}
        onClose={() => setBadgeToDelete(null)}
        onConfirm={confirmDeleteBadge}
        title="Delete Badge"
        itemName={badgeToDelete?.badge_name || ''}
        message="Are you sure you want to delete this badge? Users who have earned it will lose this badge."
        isLoading={deleteBadge.isPending}
      />
    </div>
  );
}


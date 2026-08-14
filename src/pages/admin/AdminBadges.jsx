import React, { useState } from 'react';
import { useBadges, useGamificationMutations } from '../../hooks/useGamification';
import { Award, Plus, Trash2, Edit3, X, Search } from 'lucide-react';

export default function AdminBadges() {
  const { data: badges, isLoading } = useBadges();
  const { createBadge, updateBadge, deleteBadge } = useGamificationMutations();

  const [showModal, setShowModal] = useState(false);
  const [editingBadge, setEditingBadge] = useState(null);

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

  const handleDelete = async (id, name) => {
    if (window.confirm(`Delete badge "${name}"?`)) {
      await deleteBadge.mutateAsync(id);
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

      {/* Badges Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {isLoading ? (
          [1, 2, 3].map((n) => (
            <div key={n} className="bg-white p-6 rounded-2xl border border-zinc-200 animate-pulse h-48"></div>
          ))
        ) : badges && badges.length > 0 ? (
          badges.map((b) => (
            <div
              key={b.id}
              className="bg-white rounded-2xl border border-zinc-200 p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition"
            >
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600">
                    {b.svg_icon ? (
                      <div dangerouslySetInnerHTML={{ __html: b.svg_icon }} className="w-6 h-6" />
                    ) : (
                      <Award className="h-6 w-6" />
                    )}
                  </div>
                  <span className="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                    {b.required_xp} XP Req.
                  </span>
                </div>

                <div className="space-y-1">
                  <h3 className="font-bold text-sm text-zinc-900">{b.badge_name}</h3>
                  <p className="text-xs text-zinc-500 line-clamp-2">{b.description}</p>
                </div>
              </div>

              <div className="mt-4 pt-4 border-t border-zinc-100 flex items-center justify-end gap-2 text-xs">
                <button
                  onClick={() => openEditModal(b)}
                  className="p-1.5 text-zinc-500 hover:text-indigo-600 rounded-lg hover:bg-zinc-100 transition"
                  title="Edit Badge"
                >
                  <Edit3 className="h-4 w-4" />
                </button>
                <button
                  onClick={() => handleDelete(b.id, b.badge_name)}
                  className="p-1.5 text-zinc-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                  title="Delete Badge"
                >
                  <Trash2 className="h-4 w-4" />
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
    </div>
  );
}

import React, { useState } from 'react';
import { usePapers, usePaperMutations } from '../../hooks/usePapers';
import { SYSTEM_CATEGORIES, generateSlug, generateToken } from '../../lib/utils';
import { FileText, Plus, Trash2, Edit3, X, Search } from 'lucide-react';

export default function AdminPapers() {
  const [searchTerm, setSearchTerm] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [editingPaper, setEditingPaper] = useState(null);

  const { data: papers, isLoading } = usePapers({ search: searchTerm });
  const { createPaper, updatePaper, deletePaper } = usePaperMutations();

  const [formData, setFormData] = useState({
    title: '',
    subject: 'Backend',
    year: 2024,
    private_path: '',
  });

  const openCreateModal = () => {
    setEditingPaper(null);
    setFormData({
      title: '',
      subject: 'Backend',
      year: new Date().getFullYear(),
      private_path: '',
    });
    setShowModal(true);
  };

  const openEditModal = (paper) => {
    setEditingPaper(paper);
    setFormData({
      title: paper.title,
      subject: paper.subject,
      year: paper.year,
      private_path: paper.private_path,
    });
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingPaper) {
        await updatePaper.mutateAsync({
          id: editingPaper.id,
          ...formData,
        });
      } else {
        const slug = `${generateSlug(formData.title)}-${generateToken(6)}`;
        const token = generateToken(32);

        await createPaper.mutateAsync({
          ...formData,
          slug,
          token,
        });
      }
      setShowModal(false);
    } catch (err) {
      console.error('Error saving paper:', err);
    }
  };

  const handleDelete = async (id, title) => {
    if (window.confirm(`Are you sure you want to delete "${title}"?`)) {
      await deletePaper.mutateAsync(id);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Manage Research Papers</h2>
          <p className="text-xs text-zinc-500">Upload and moderate academic papers, exam archives, and research.</p>
        </div>
        <button
          onClick={openCreateModal}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <Plus className="h-4 w-4" /> Add Paper
        </button>
      </div>

      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
        <div className="relative w-full max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
          <input
            type="text"
            placeholder="Filter papers..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
          />
        </div>
        <span className="text-xs text-zinc-500">Total: <strong>{papers?.length || 0}</strong> papers</span>
      </div>

      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Title</th>
                <th className="px-6 py-3.5">Subject</th>
                <th className="px-6 py-3.5">Year</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-400">
                    Loading papers...
                  </td>
                </tr>
              ) : papers && papers.length > 0 ? (
                papers.map((p) => (
                  <tr key={p.id} className="hover:bg-zinc-50/80 transition">
                    <td className="px-6 py-4 font-semibold text-zinc-900 max-w-md truncate">
                      {p.title}
                    </td>
                    <td className="px-6 py-4">
                      <span className="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100 font-medium">
                        {p.subject}
                      </span>
                    </td>
                    <td className="px-6 py-4 font-medium text-zinc-600">{p.year}</td>
                    <td className="px-6 py-4 text-right space-x-2">
                      <button
                        onClick={() => openEditModal(p)}
                        className="p-1.5 text-zinc-500 hover:text-indigo-600 rounded-lg hover:bg-zinc-100 transition"
                        title="Edit Paper"
                      >
                        <Edit3 className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => handleDelete(p.id, p.title)}
                        className="p-1.5 text-zinc-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition"
                        title="Delete Paper"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-zinc-500">
                    No papers found.
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
                {editingPaper ? 'Edit Paper' : 'Add Research Paper'}
              </h3>
              <button onClick={() => setShowModal(false)} className="p-1 text-zinc-400 hover:text-zinc-600">
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
                  placeholder="Raft: Consensus Algorithm"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">Subject</label>
                  <select
                    value={formData.subject}
                    onChange={(e) => setFormData({ ...formData, subject: e.target.value })}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                  >
                    {SYSTEM_CATEGORIES.map((cat) => (
                      <option key={cat} value={cat}>
                        {cat}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">Publication Year</label>
                  <input
                    type="number"
                    required
                    min="1970"
                    max="2030"
                    value={formData.year}
                    onChange={(e) => setFormData({ ...formData, year: parseInt(e.target.value, 10) })}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                  />
                </div>
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">File Path / URL</label>
                <input
                  type="text"
                  required
                  value={formData.private_path}
                  onChange={(e) => setFormData({ ...formData, private_path: e.target.value })}
                  placeholder="https://storage.example.com/papers/raft.pdf"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
                />
              </div>

              <div className="flex items-center justify-end gap-2 pt-4 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 hover:bg-zinc-50 font-semibold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={createPaper.isPending || updatePaper.isPending}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition"
                >
                  {editingPaper ? 'Save Changes' : 'Create Paper'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

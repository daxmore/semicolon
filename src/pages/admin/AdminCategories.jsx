import React, { useState } from 'react';
import { useCommunityCategories, useTopicRequests, useCategoryMutations } from '../../hooks/useCategories';
import { Plus, Trash2, Edit2, CheckCircle, XCircle, Search, Filter, Layers, MessageSquare, Send, X, HelpCircle } from 'lucide-react';
import { useToast } from '../../contexts/ToastContext';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';
import { timeAgo } from '../../lib/utils';

export default function AdminCategories() {
  const { data: categories, isLoading: loadingCats } = useCommunityCategories();
  const { data: topicRequests, isLoading: loadingReqs } = useTopicRequests();
  const { addCategory, updateCategory, deleteCategory, updateTopicRequestStatus } = useCategoryMutations();
  const { showToast } = useToast();

  const [activeTab, setActiveTab] = useState('topics'); // 'topics' | 'requests'
  const [newCategoryName, setNewCategoryName] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [categoryToDelete, setCategoryToDelete] = useState(null);

  // Edit category modal state
  const [editingCategory, setEditingCategory] = useState(null); // { oldName: '', newName: '' }

  // Decision & Note modal for user topic requests
  const [pendingAction, setPendingAction] = useState(null); // { req, status: 'approved'|'rejected', note: '' }

  const handleAddCategory = async (e) => {
    e.preventDefault();
    if (!newCategoryName.trim()) return;

    try {
      await addCategory.mutateAsync(newCategoryName.trim());
      showToast(`Category "${newCategoryName.trim()}" added successfully!`);
      setNewCategoryName('');
    } catch (err) {
      showToast(err.message || 'Failed to add category', 'error');
    }
  };

  const confirmDeleteCategory = async () => {
    if (!categoryToDelete) return;
    try {
      await deleteCategory.mutateAsync(categoryToDelete);
      showToast(`Category "${categoryToDelete}" deleted.`);
      setCategoryToDelete(null);
    } catch (err) {
      showToast('Failed to delete category', 'error');
    }
  };

  const handleSaveEditCategory = async (e) => {
    e.preventDefault();
    if (!editingCategory || !editingCategory.newName.trim()) return;

    try {
      await updateCategory.mutateAsync({
        oldName: editingCategory.oldName,
        newName: editingCategory.newName.trim(),
      });
      showToast(`Topic updated to "${editingCategory.newName.trim()}"!`);
      setEditingCategory(null);
    } catch (err) {
      showToast(err.message || 'Failed to update category', 'error');
    }
  };

  const handleOpenActionModal = (req, status) => {
    setPendingAction({
      req,
      status,
      note: '',
    });
  };

  const handleConfirmDecision = async (e) => {
    e.preventDefault();
    if (!pendingAction) return;

    const { req, status, note } = pendingAction;
    try {
      await updateTopicRequestStatus.mutateAsync({
        id: req.id,
        status,
        note: note.trim(),
        user_id: req.user_id,
        topic_name: req.topic_name,
      });
      showToast(`Topic request ${status.toUpperCase()} & notification delivered!`);
      setPendingAction(null);
    } catch (err) {
      showToast('Failed to process topic request', 'error');
    }
  };

  const filteredCategories = (categories || []).filter((c) =>
    c.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const pendingRequestsCount = (topicRequests || []).filter((r) => r.status === 'pending').length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Community Topics & Categories</h2>
          <p className="text-xs text-zinc-500">
            Manage active community categories and review user requests for new topics with automated notifications.
          </p>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex items-center gap-2 border-b border-zinc-200 pb-2">
        <button
          onClick={() => setActiveTab('topics')}
          className={`px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer ${
            activeTab === 'topics'
              ? 'bg-zinc-900 text-white shadow-sm'
              : 'text-zinc-600 hover:bg-zinc-100'
          }`}
        >
          <Layers className="h-4 w-4" />
          <span>Active Categories ({categories?.length || 0})</span>
        </button>

        <button
          onClick={() => setActiveTab('requests')}
          className={`px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer ${
            activeTab === 'requests'
              ? 'bg-zinc-900 text-white shadow-sm'
              : 'text-zinc-600 hover:bg-zinc-100'
          }`}
        >
          <HelpCircle className="h-4 w-4" />
          <span>User Topic Requests</span>
          {pendingRequestsCount > 0 && (
            <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">
              {pendingRequestsCount} new
            </span>
          )}
        </button>
      </div>

      {activeTab === 'topics' ? (
        /* Tab 1: Active Categories */
        <div className="space-y-6">
          {/* Add Category Form */}
          <div className="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm">
            <h3 className="text-sm font-bold text-zinc-900 mb-3 flex items-center gap-2">
              <Plus className="h-4 w-4 text-indigo-600" /> Add New Community Topic
            </h3>
            <form onSubmit={handleAddCategory} className="flex flex-col sm:flex-row gap-3">
              <input
                type="text"
                required
                placeholder="e.g. Rust, Web3, Unreal Engine, Quantum Computing..."
                value={newCategoryName}
                onChange={(e) => setNewCategoryName(e.target.value)}
                className="flex-1 px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
              />
              <button
                type="submit"
                disabled={addCategory.isPending}
                className="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer flex items-center justify-center gap-1.5"
              >
                <Plus className="h-4 w-4" />
                {addCategory.isPending ? 'Adding...' : 'Add Topic'}
              </button>
            </form>
          </div>

          {/* Search Toolbar */}
          <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
            <div className="relative w-full max-w-sm">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
              <input
                type="text"
                placeholder="Search topics..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-9 pr-4 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
              />
            </div>
            <span className="text-xs text-zinc-500">Total: <strong>{filteredCategories.length}</strong> categories</span>
          </div>

          {/* Categories Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            {filteredCategories.map((cat) => (
              <div
                key={cat}
                className="bg-white p-4 rounded-xl border border-zinc-200 shadow-xs flex items-center justify-between hover:border-indigo-200 transition group"
              >
                <span className="text-xs font-semibold text-zinc-900 truncate">{cat}</span>
                <div className="flex items-center gap-1">
                  <button
                    type="button"
                    onClick={() => setEditingCategory({ oldName: cat, newName: cat })}
                    className="p-1 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer opacity-80 group-hover:opacity-100"
                    title="Edit topic name"
                  >
                    <Edit2 className="h-3.5 w-3.5" />
                  </button>
                  <button
                    type="button"
                    onClick={() => setCategoryToDelete(cat)}
                    className="p-1 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer opacity-80 group-hover:opacity-100"
                    title="Delete category"
                  >
                    <Trash2 className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      ) : (
        /* Tab 2: User Topic Requests */
        <div className="space-y-6">
          <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
                  <tr>
                    <th className="px-6 py-3.5">Proposed Topic</th>
                    <th className="px-6 py-3.5">Why Needed & Purpose</th>
                    <th className="px-6 py-3.5">Requested By</th>
                    <th className="px-6 py-3.5">Status</th>
                    <th className="px-6 py-3.5 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-zinc-200 text-zinc-800">
                  {loadingReqs ? (
                    <tr>
                      <td colSpan="5" className="px-6 py-8 text-center text-zinc-400">
                        Loading topic requests...
                      </td>
                    </tr>
                  ) : !topicRequests || topicRequests.length === 0 ? (
                    <tr>
                      <td colSpan="5" className="px-6 py-8 text-center text-zinc-500">
                        No community topic requests submitted yet.
                      </td>
                    </tr>
                  ) : (
                    topicRequests.map((req) => (
                      <tr key={req.id} className="hover:bg-zinc-50/80 transition">
                        <td className="px-6 py-4 font-bold text-zinc-900">
                          {req.topic_name}
                        </td>
                        <td className="px-6 py-4 max-w-sm space-y-1">
                          <p className="text-zinc-700 font-medium">{req.reason}</p>
                          {req.purpose && (
                            <p className="text-zinc-500 text-[11px] italic">Purpose: {req.purpose}</p>
                          )}
                        </td>
                        <td className="px-6 py-4">
                          <p className="font-semibold text-zinc-900">{req.username || req.profiles?.username || 'User'}</p>
                          <span className="text-[10px] text-zinc-400">{timeAgo(req.created_at)}</span>
                        </td>
                        <td className="px-6 py-4">
                          <span
                            className={`capitalize font-bold text-[11px] px-2.5 py-1 rounded-full border ${
                              req.status === 'approved'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : req.status === 'rejected'
                                ? 'bg-rose-50 text-rose-700 border-rose-200'
                                : 'bg-amber-50 text-amber-700 border-amber-200'
                            }`}
                          >
                            {req.status}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right space-x-2">
                          {req.status === 'pending' ? (
                            <>
                              <button
                                type="button"
                                onClick={(e) => {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  handleOpenActionModal(req, 'approved');
                                }}
                                disabled={updateTopicRequestStatus.isPending}
                                className="px-2.5 py-1 rounded-lg font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 active:bg-emerald-200 transition cursor-pointer"
                                title="Approve topic and notify user"
                              >
                                Approve & Add
                              </button>
                              <button
                                type="button"
                                onClick={(e) => {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  handleOpenActionModal(req, 'rejected');
                                }}
                                disabled={updateTopicRequestStatus.isPending}
                                className="px-2.5 py-1 rounded-lg font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 active:bg-rose-200 transition cursor-pointer"
                                title="Reject topic and notify user"
                              >
                                Reject
                              </button>
                            </>
                          ) : (
                            <span className="text-[11px] font-medium text-zinc-400 italic">
                              Finalized ({req.status})
                            </span>
                          )}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Decision & Notification Modal for Topic Requests */}
      {pendingAction && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center gap-2">
                <div
                  className={`w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs ${
                    pendingAction.status === 'approved'
                      ? 'bg-emerald-100 text-emerald-700'
                      : 'bg-rose-100 text-rose-700'
                  }`}
                >
                  {pendingAction.status === 'approved' ? '✓' : '✕'}
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-900">
                    {pendingAction.status === 'approved' ? 'Approve Community Topic' : 'Reject Community Topic'}
                  </h3>
                  <p className="text-[11px] text-zinc-400">
                    "{pendingAction.req.topic_name}" • Requested by {pendingAction.req.username || 'User'}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setPendingAction(null)}
                className="p-1 text-zinc-400 hover:text-zinc-600 cursor-pointer"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleConfirmDecision} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">
                  Custom Admin Note / Message (Sent to User Notification):
                </label>
                <textarea
                  rows={3}
                  value={pendingAction.note}
                  onChange={(e) => setPendingAction({ ...pendingAction, note: e.target.value })}
                  placeholder={
                    pendingAction.status === 'approved'
                      ? 'Optional note. e.g. "We love this idea! The community is now live and available in topics."'
                      : 'Optional note. e.g. "This topic is closely related to existing categories."'
                  }
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs text-zinc-800 resize-none"
                />
              </div>

              {/* Default note fallback preview */}
              <div className="p-3 bg-zinc-50 border border-zinc-200 rounded-xl space-y-1">
                <p className="font-semibold text-zinc-600 text-[11px]">Default Notification (if no note entered):</p>
                <p className="text-[11px] text-zinc-500 italic">
                  {pendingAction.status === 'approved'
                    ? `Great news! Your request to add the "${pendingAction.req.topic_name}" community topic has been approved and is now active.`
                    : `Thank you for your suggestion. After review, the request for "${pendingAction.req.topic_name}" was not approved at this time.`}
                </p>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setPendingAction(null)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 font-semibold hover:bg-zinc-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={updateTopicRequestStatus.isPending}
                  className={`px-5 py-2 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer flex items-center gap-1.5 ${
                    pendingAction.status === 'approved'
                      ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20'
                      : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20'
                  }`}
                >
                  <Send className="h-3.5 w-3.5" />
                  {updateTopicRequestStatus.isPending
                    ? 'Processing...'
                    : pendingAction.status === 'approved'
                    ? 'Approve, Add & Notify'
                    : 'Reject & Notify'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Edit Category Modal */}
      {editingCategory && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                  <Edit2 className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-900">Edit Community Topic</h3>
                  <p className="text-[11px] text-zinc-400">Rename "{editingCategory.oldName}"</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setEditingCategory(null)}
                className="p-1 text-zinc-400 hover:text-zinc-600 cursor-pointer"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSaveEditCategory} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Topic Name</label>
                <input
                  type="text"
                  required
                  value={editingCategory.newName}
                  onChange={(e) => setEditingCategory({ ...editingCategory, newName: e.target.value })}
                  placeholder="Enter topic name..."
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs text-zinc-800"
                />
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setEditingCategory(null)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 font-semibold hover:bg-zinc-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={updateCategory.isPending}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer"
                >
                  {updateCategory.isPending ? 'Saving...' : 'Save Topic'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Category Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!categoryToDelete}
        onClose={() => setCategoryToDelete(null)}
        onConfirm={confirmDeleteCategory}
        title="Delete Community Topic"
        itemName={categoryToDelete || ''}
        message="Are you sure you want to remove this community category?"
        isLoading={deleteCategory.isPending}
      />
    </div>
  );
}

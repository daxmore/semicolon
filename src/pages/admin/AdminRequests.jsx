import React, { useState } from 'react';
import { useAdminRequests, useRequestMutations } from '../../hooks/useRequests';
import { timeAgo } from '../../lib/utils';
import { HelpCircle, CheckCircle, XCircle, Trash2, Search, Filter, MessageSquare, Send, X } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';
import { useToast } from '../../contexts/ToastContext';

export default function AdminRequests() {
  const [statusFilter, setStatusFilter] = useState('all');
  const [requestToDelete, setRequestToDelete] = useState(null);
  
  // Note Modal state
  const [pendingAction, setPendingAction] = useState(null); // { req, status: 'approved'|'rejected', note: '' }

  const { showToast } = useToast();
  const { data: requestsList, isLoading } = useAdminRequests(statusFilter);
  const { updateRequestStatus, deleteRequest } = useRequestMutations();

  const handleOpenActionModal = (req, status) => {
    setPendingAction({
      req,
      status,
      note: '',
    });
  };

  const handleConfirmStatusChange = async (e) => {
    if (e) e.preventDefault();
    if (!pendingAction) return;

    const { req, status, note } = pendingAction;
    try {
      await updateRequestStatus.mutateAsync({
        id: req.id,
        status,
        note: note.trim(),
        user_id: req.user_id,
        title: req.title,
        material_type: req.material_type,
      });
      showToast(`Request marked as ${status.toUpperCase()} & notification delivered!`);
      setPendingAction(null);
    } catch (err) {
      console.error('Failed to update request status:', err);
      showToast('Failed to update status', 'error');
    }
  };

  const confirmDeleteRequest = async () => {
    if (!requestToDelete) return;
    try {
      await deleteRequest.mutateAsync(requestToDelete.id);
      setRequestToDelete(null);
      showToast('Material request deleted.');
    } catch (err) {
      console.error('Failed to delete request:', err);
      showToast('Failed to delete request', 'error');
    }
  };

  return (
    <div className="space-y-6">

      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Manage Material Requests</h2>
          <p className="text-xs text-zinc-500">Review requests from the community and update their status.</p>
        </div>
      </div>

      {/* Filter Toolbar */}
      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
        <div className="flex items-center gap-2">
          <span className="text-xs font-semibold text-zinc-700">Status:</span>
          <div className="flex gap-1">
            {['all', 'pending', 'approved', 'rejected'].map((st) => (
              <button
                key={st}
                onClick={() => setStatusFilter(st)}
                className={`px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider transition ${
                  statusFilter === st
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
                }`}
              >
                {st}
              </button>
            ))}
          </div>
        </div>
        <span className="text-xs text-zinc-500">Total: <strong>{requestsList?.length || 0}</strong> requests</span>
      </div>

      {/* Requests Table */}
      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Material</th>
                <th className="px-6 py-3.5">Type &amp; Category</th>
                <th className="px-6 py-3.5">Requested By</th>
                <th className="px-6 py-3.5">Status</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-400">
                    Loading requests...
                  </td>
                </tr>
              ) : requestsList && requestsList.length > 0 ? (
                requestsList.map((req) => (
                  <tr key={req.id} className="hover:bg-zinc-50/80 transition">
                    <td className="px-6 py-4">
                      <p className="font-bold text-zinc-900">{req.title}</p>
                      {req.author_publisher && (
                        <p className="text-[11px] text-zinc-400">By {req.author_publisher}</p>
                      )}
                      {req.details && (
                        <p className="text-[11px] text-zinc-600 mt-1 max-w-sm">{req.details}</p>
                      )}
                    </td>

                    <td className="px-6 py-4">
                      <div className="space-y-0.5">
                        <span className="text-[10px] uppercase font-bold text-zinc-600 bg-zinc-100 px-1.5 py-0.5 rounded">
                          {req.material_type}
                        </span>
                        <span className="text-[11px] font-semibold text-indigo-600 block">
                          {req.community_category}
                        </span>
                      </div>
                    </td>

                    <td className="px-6 py-4 font-medium">
                      <p className="text-zinc-900">{req.profiles?.username || 'User'}</p>
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
                            onClick={() => handleOpenActionModal(req, 'approved')}
                            disabled={updateRequestStatus.isPending}
                            className="px-2.5 py-1 rounded-lg font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 active:bg-emerald-200 transition cursor-pointer"
                            title="Approve request and notify user"
                          >
                            Approve
                          </button>
                          <button
                            onClick={() => handleOpenActionModal(req, 'rejected')}
                            disabled={updateRequestStatus.isPending}
                            className="px-2.5 py-1 rounded-lg font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 active:bg-rose-200 transition cursor-pointer"
                            title="Reject request and notify user"
                          >
                            Reject
                          </button>
                        </>
                      ) : (
                        <span className="text-[11px] font-medium text-zinc-400 italic mr-2">
                          Decision finalized ({req.status})
                        </span>
                      )}
                      <button
                        onClick={() => setRequestToDelete(req)}
                        className="p-1 text-zinc-400 hover:text-rose-600 rounded transition cursor-pointer inline-flex items-center"
                        title="Delete Request"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-500">
                    No requests found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Decision & Notification Modal */}
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
                    {pendingAction.status === 'approved' ? 'Approve Material Request' : 'Reject Material Request'}
                  </h3>
                  <p className="text-[11px] text-zinc-400">
                    "{pendingAction.req.title}" • Requested by {pendingAction.req.profiles?.username || 'User'}
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

            <form onSubmit={handleConfirmStatusChange} className="space-y-4 text-xs">
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
                      ? 'Optional note. e.g. "We have added this book to the library under Cloud Computing!"'
                      : 'Optional note. e.g. "Unfortunately this paper is not currently available in public access."'
                  }
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs text-zinc-800 resize-none"
                />
              </div>

              {/* Default note fallback preview */}
              <div className="p-3 bg-zinc-50 border border-zinc-200 rounded-xl space-y-1">
                <p className="font-semibold text-zinc-600 text-[11px]">Default Notification (if no note entered):</p>
                <p className="text-[11px] text-zinc-500 italic">
                  {pendingAction.status === 'approved'
                    ? `Your request for ${pendingAction.req.material_type?.toUpperCase()} "${pendingAction.req.title}" was approved! Our team has added or scheduled it for the library.`
                    : `Thank you for submitting a request for "${pendingAction.req.title}". We are currently unable to add this material to the catalog.`}
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
                  disabled={updateRequestStatus.isPending}
                  className={`px-5 py-2 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer flex items-center gap-1.5 ${
                    pendingAction.status === 'approved'
                      ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20'
                      : 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20'
                  }`}
                >
                  <Send className="h-3.5 w-3.5" />
                  {updateRequestStatus.isPending
                    ? 'Delivering...'
                    : pendingAction.status === 'approved'
                    ? 'Approve & Notify'
                    : 'Reject & Notify'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Request Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!requestToDelete}
        onClose={() => setRequestToDelete(null)}
        onConfirm={confirmDeleteRequest}
        title="Delete Material Request"
        itemName={requestToDelete?.title || ''}
        message="Are you sure you want to permanently delete this material request?"
        isLoading={deleteRequest.isPending}
      />
    </div>
  );
}


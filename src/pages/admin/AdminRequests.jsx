import React, { useState } from 'react';
import { useAdminRequests, useRequestMutations } from '../../hooks/useRequests';
import { timeAgo } from '../../lib/utils';
import { HelpCircle, CheckCircle, XCircle, Trash2, Search, Filter } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

export default function AdminRequests() {
  const [statusFilter, setStatusFilter] = useState('all');
  const [requestToDelete, setRequestToDelete] = useState(null);
  const { data: requestsList, isLoading } = useAdminRequests(statusFilter);
  const { updateRequestStatus, deleteRequest } = useRequestMutations();

  const confirmDeleteRequest = async () => {
    if (!requestToDelete) return;
    try {
      await deleteRequest.mutateAsync(requestToDelete.id);
      setRequestToDelete(null);
    } catch (err) {
      console.error('Failed to delete request:', err);
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
                <th className="px-6 py-3.5">Type & Category</th>
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
                      <button
                        onClick={() =>
                          updateRequestStatus.mutate({ id: req.id, status: 'approved' })
                        }
                        className="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition"
                      >
                        Approve
                      </button>
                      <button
                        onClick={() =>
                          updateRequestStatus.mutate({ id: req.id, status: 'rejected' })
                        }
                        className="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg font-semibold transition"
                      >
                        Reject
                      </button>
                      <button
                        onClick={() => setRequestToDelete(req)}
                        className="p-1 text-zinc-400 hover:text-rose-600 rounded transition cursor-pointer"
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


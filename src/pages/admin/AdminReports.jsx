import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../../lib/axiosClient';
import { AlertTriangle, CheckCircle, XCircle, Trash2, Search, Filter } from 'lucide-react';

export default function AdminReports() {
  const [filterStatus, setFilterStatus] = useState('pending');
  const queryClient = useQueryClient();

  const { data: reports, isLoading } = useQuery({
    queryKey: ['admin_reports', filterStatus],
    queryFn: async () => {
      let query = '/rest/v1/community_reports?select=*,profiles:user_id(username,email)&order=created_at.desc';
      if (filterStatus !== 'all') {
        query += `&status=eq.${filterStatus}`;
      }
      const { data } = await axiosClient.get(query);
      return data || [];
    },
  });

  const updateStatus = useMutation({
    mutationFn: async ({ id, status }) => {
      await axiosClient.patch(`/rest/v1/community_reports?id=eq.${id}`, { status });
      return { id, status };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_reports'] });
    },
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Moderate Community Reports</h2>
          <p className="text-xs text-zinc-500">Review reported discussions and comments from users.</p>
        </div>
      </div>

      {/* Filter Toolbar */}
      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
        <div className="flex items-center gap-2">
          <span className="text-xs font-semibold text-zinc-700">Filter Status:</span>
          <div className="flex gap-1">
            {['pending', 'reviewed', 'dismissed', 'all'].map((st) => (
              <button
                key={st}
                onClick={() => setFilterStatus(st)}
                className={`px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider transition ${
                  filterStatus === st
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
                }`}
              >
                {st}
              </button>
            ))}
          </div>
        </div>
        <span className="text-xs text-zinc-500">Reports: <strong>{reports?.length || 0}</strong></span>
      </div>

      {/* Reports Table */}
      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Target</th>
                <th className="px-6 py-3.5">Target ID</th>
                <th className="px-6 py-3.5">Reported By</th>
                <th className="px-6 py-3.5">Reason</th>
                <th className="px-6 py-3.5">Status</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="6" className="px-6 py-8 text-center text-zinc-400">
                    Loading reports...
                  </td>
                </tr>
              ) : reports && reports.length > 0 ? (
                reports.map((r) => (
                  <tr key={r.id} className="hover:bg-zinc-50/80 transition">
                    <td className="px-6 py-4 font-bold uppercase tracking-wider">
                      <span
                        className={`px-2 py-0.5 rounded-full text-[10px] ${
                          r.target_type === 'post'
                            ? 'bg-amber-100 text-amber-800'
                            : 'bg-indigo-100 text-indigo-800'
                        }`}
                      >
                        {r.target_type}
                      </span>
                    </td>
                    <td className="px-6 py-4 font-mono font-medium">#{r.target_id}</td>
                    <td className="px-6 py-4 font-semibold text-zinc-900">
                      {r.profiles?.username || 'User'}
                    </td>
                    <td className="px-6 py-4 text-zinc-700 max-w-xs">{r.reason}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`capitalize font-bold text-[11px] px-2 py-0.5 rounded-md ${
                          r.status === 'pending'
                            ? 'bg-rose-50 text-rose-700 border border-rose-200'
                            : r.status === 'reviewed'
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-zinc-100 text-zinc-600'
                        }`}
                      >
                        {r.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      {r.status === 'pending' && (
                        <>
                          <button
                            onClick={() => updateStatus.mutate({ id: r.id, status: 'reviewed' })}
                            className="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition"
                          >
                            Mark Reviewed
                          </button>
                          <button
                            onClick={() => updateStatus.mutate({ id: r.id, status: 'dismissed' })}
                            className="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 rounded-lg font-semibold transition"
                          >
                            Dismiss
                          </button>
                        </>
                      )}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="6" className="px-6 py-8 text-center text-zinc-500">
                    No reports under this status.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../../lib/axiosClient';
import { AlertTriangle, CheckCircle, XCircle, Trash2, Search, Filter, ExternalLink, MessageSquare, FileText, Send, X } from 'lucide-react';
import { useToast } from '../../contexts/ToastContext';

export default function AdminReports() {
  const [filterStatus, setFilterStatus] = useState('pending');
  const [pendingAction, setPendingAction] = useState(null); // { report, status: 'reviewed'|'dismissed', note: '' }
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  const { data: reports, isLoading } = useQuery({
    queryKey: ['admin_reports', filterStatus],
    queryFn: async () => {
      let query = '/rest/v1/community_reports?select=*,profiles:user_id(username,email)&order=created_at.desc';
      if (filterStatus !== 'all') {
        query += `&status=eq.${filterStatus}`;
      }
      const { data } = await axiosClient.get(query);
      if (!data || data.length === 0) return [];

      // Look up post_id for any comment reports so admin can directly visit them
      const commentReportTargetIds = data
        .filter((r) => r.target_type === 'comment')
        .map((r) => r.target_id);

      let commentPostMap = {};
      if (commentReportTargetIds.length > 0) {
        const { data: commentData } = await axiosClient.get(
          `/rest/v1/community_comments?id=in.(${commentReportTargetIds.join(',')})&select=id,post_id,content`
        );
        (commentData || []).forEach((c) => {
          commentPostMap[c.id] = c;
        });
      }

      return data.map((r) => ({
        ...r,
        targetComment: commentPostMap[r.target_id] || null,
      }));
    },
  });

  const updateStatus = useMutation({
    mutationFn: async ({ id, status, note, user_id, target_type, target_id }) => {
      // 1. Update report status
      await axiosClient.patch(`/rest/v1/community_reports?id=eq.${id}`, { status });

      // 2. Dispatch notification to reporting user
      if (user_id) {
        const defaultTitle =
          status === 'reviewed'
            ? `Report #${id} Reviewed & Actioned`
            : `Report #${id} Concluded & Dismissed`;

        const defaultMessage =
          note?.trim() ||
          (status === 'reviewed'
            ? `Thank you for helping keep Semicolon safe! Our moderation team has reviewed and resolved your report regarding ${target_type} #${target_id}.`
            : `Thank you for submitting report #${id}. After reviewing, our moderation team found that the reported ${target_type} aligns with our Community Guidelines.`);

        const directLink =
          target_type === 'post'
            ? `/community/post/${target_id}`
            : `/community`;

        try {
          await axiosClient.post('/rest/v1/notifications', {
            user_id: user_id,
            type: 'system',
            title: defaultTitle,
            message: defaultMessage,
            link: directLink,
            is_read: false,
          });
        } catch (err) {
          console.warn('Failed to send notification via Supabase:', err);
        }
      }

      return { id, status };
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_reports'] });
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
    },
  });

  const handleOpenActionModal = (report, status) => {
    setPendingAction({
      report,
      status,
      note: '',
    });
  };

  const handleConfirmReportAction = async (e) => {
    if (e) e.preventDefault();
    if (!pendingAction) return;

    const { report, status, note } = pendingAction;
    try {
      await updateStatus.mutateAsync({
        id: report.id,
        status,
        note: note.trim(),
        user_id: report.user_id,
        target_type: report.target_type,
        target_id: report.target_id,
      });
      showToast(`Report marked as ${status} & notification sent!`);
      setPendingAction(null);
    } catch (err) {
      console.error('Failed to update report status:', err);
      showToast('Failed to conclude report', 'error');
    }
  };

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
                className={`px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider transition ${filterStatus === st
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
                reports.map((r) => {
                  const targetUrl =
                    r.target_type === 'post'
                      ? `/community/post/${r.target_id}`
                      : r.targetComment?.post_id
                        ? `/community/post/${r.targetComment.post_id}#comment-${r.target_id}`
                        : `/community`;

                  return (
                    <tr key={r.id} className="hover:bg-zinc-50/80 transition">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2">
                          <span
                            className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${r.target_type === 'post'
                                ? 'bg-amber-100 text-amber-800 border border-amber-200'
                                : 'bg-indigo-100 text-indigo-800 border border-indigo-200'
                              }`}
                          >
                            {r.target_type === 'post' ? (
                              <FileText className="h-3 w-3" />
                            ) : (
                              <MessageSquare className="h-3 w-3" />
                            )}
                            {r.target_type}
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 font-mono font-medium text-xs">
                        <Link
                          to={targetUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-semibold underline decoration-indigo-200 underline-offset-2"
                        >
                          #{r.target_id}
                          <ExternalLink className="h-3 w-3" />
                        </Link>
                      </td>
                      <td className="px-6 py-4 font-semibold text-zinc-900">
                        {r.profiles?.username || 'User'}
                      </td>
                      <td className="px-6 py-4 text-zinc-700 max-w-xs">{r.reason}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`capitalize font-bold text-[11px] px-2 py-0.5 rounded-md ${r.status === 'pending'
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
                        <Link
                          to={targetUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition"
                          title="Open and view content in new tab"
                        >
                          <span>View Content</span>
                          <ExternalLink className="h-3 w-3" />
                        </Link>

                        {r.status === 'pending' && (
                          <>
                            <button
                              onClick={() => handleOpenActionModal(r, 'reviewed')}
                              disabled={updateStatus.isPending}
                              className="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-semibold transition cursor-pointer"
                            >
                              Mark Reviewed
                            </button>
                            <button
                              onClick={() => handleOpenActionModal(r, 'dismissed')}
                              disabled={updateStatus.isPending}
                              className="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 rounded-lg font-semibold transition cursor-pointer"
                            >
                              Dismiss
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  );
                })
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

      {/* Decision & Notification Modal */}
      {pendingAction && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 space-y-4">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center gap-2">
                <div
                  className={`w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs ${pendingAction.status === 'reviewed'
                      ? 'bg-emerald-100 text-emerald-700'
                      : 'bg-zinc-100 text-zinc-700'
                    }`}
                >
                  {pendingAction.status === 'reviewed' ? '✓' : '—'}
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-900">
                    {pendingAction.status === 'reviewed' ? 'Conclude & Mark Reviewed' : 'Dismiss Community Report'}
                  </h3>
                  <p className="text-[11px] text-zinc-400">
                    Report #{pendingAction.report.id} • Submitted by {pendingAction.report.profiles?.username || 'User'}
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

            <form onSubmit={handleConfirmReportAction} className="space-y-4 text-xs">
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">
                  Custom Admin Note / Message (Sent to User Notification):
                </label>
                <textarea
                  rows={3}
                  value={pendingAction.note}
                  onChange={(e) => setPendingAction({ ...pendingAction, note: e.target.value })}
                  placeholder={
                    pendingAction.status === 'reviewed'
                      ? 'Optional note. e.g. "We have reviewed this discussion and taken appropriate moderation action."'
                      : 'Optional note. e.g. "After reviewing this content, no community violation was found."'
                  }
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs text-zinc-800 resize-none"
                />
              </div>

              {/* Default note fallback preview */}
              <div className="p-3 bg-zinc-50 border border-zinc-200 rounded-xl space-y-1">
                <p className="font-semibold text-zinc-600 text-[11px]">Default Notification (if no note entered):</p>
                <p className="text-[11px] text-zinc-500 italic">
                  {pendingAction.status === 'reviewed'
                    ? `Thank you for helping keep Semicolon safe! Our moderation team has reviewed and resolved your report regarding ${pendingAction.report.target_type} #${pendingAction.report.target_id}.`
                    : `Thank you for submitting report #${pendingAction.report.id}. After reviewing, our moderation team found that the reported ${pendingAction.report.target_type} aligns with our Community Guidelines.`}
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
                  disabled={updateStatus.isPending}
                  className={`px-5 py-2 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60 cursor-pointer flex items-center gap-1.5 ${pendingAction.status === 'reviewed'
                      ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20'
                      : 'bg-zinc-700 hover:bg-zinc-800'
                    }`}
                >
                  <Send className="h-3.5 w-3.5" />
                  {updateStatus.isPending
                    ? 'Processing...'
                    : pendingAction.status === 'reviewed'
                      ? 'Confirm & Notify'
                      : 'Dismiss & Notify'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

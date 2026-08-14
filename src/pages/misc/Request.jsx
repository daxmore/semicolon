import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useAuth } from '../../contexts/AuthContext';
import { useUserRequests, useRequestMutations } from '../../hooks/useRequests';
import { SYSTEM_CATEGORIES, timeAgo } from '../../lib/utils';
import { HelpCircle, Send, CheckCircle2, AlertCircle, Clock, BookOpen } from 'lucide-react';

const schema = yup.object({
  material_type: yup.string().required('Material type is required'),
  community_category: yup.string().required('Category is required'),
  title: yup.string().min(3, 'Title is too short').required('Title is required'),
  author_publisher: yup.string().nullable(),
  details: yup.string().nullable(),
});

export default function Request() {
  const { user } = useAuth();
  const { data: myRequests, isLoading } = useUserRequests(user?.id);
  const { createRequest } = useRequestMutations();
  const [toast, setToast] = useState('');
  const [errorMsg, setErrorMsg] = useState('');

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      material_type: 'book',
      community_category: 'Frontend',
      title: '',
      author_publisher: '',
      details: '',
    },
  });

  const onSubmit = async (values) => {
    try {
      setErrorMsg('');
      await createRequest.mutateAsync({
        user_id: user.id,
        material_type: values.material_type,
        community_category: values.community_category,
        title: values.title,
        author_publisher: values.author_publisher || null,
        details: values.details || null,
        status: 'pending',
      });

      reset();
      setToast('Your request has been submitted successfully! We will review it soon.');
      setTimeout(() => setToast(''), 4000);
    } catch (err) {
      console.error(err);
      setErrorMsg(err.message || 'Failed to submit material request.');
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
      {/* Header */}
      <div className="border-b border-zinc-200 pb-6 space-y-2">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs font-semibold">
          <HelpCircle className="h-3.5 w-3.5" />
          Community Sourcing
        </div>
        <h1 className="text-3xl font-bold font-heading text-zinc-900 tracking-tight">
          Request Learning Material
        </h1>
        <p className="text-xs text-zinc-500 max-w-xl">
          Looking for a specific engineering book, seminal academic paper, or video topic? Let us know and our curators will prioritize adding it.
        </p>
      </div>

      {toast && (
        <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 flex items-center gap-2">
          <CheckCircle2 className="h-4 w-4 shrink-0" />
          <span>{toast}</span>
        </div>
      )}

      {errorMsg && (
        <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle className="h-4 w-4 shrink-0" />
          <span>{errorMsg}</span>
        </div>
      )}

      {/* Request Form */}
      <div className="bg-white rounded-3xl border border-zinc-200/80 p-8 shadow-sm space-y-6">
        <h2 className="text-sm font-bold uppercase tracking-wider text-zinc-900">
          Submit a Request
        </h2>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 text-xs">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-zinc-700 mb-1">Material Type</label>
              <select
                {...register('material_type')}
                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
              >
                <option value="book">Book / Reference Manual</option>
                <option value="paper">Academic / Research Paper</option>
                <option value="video">Video Tutorial / Course</option>
                <option value="quiz">Skill Path / Quiz Topic</option>
              </select>
            </div>

            <div>
              <label className="block font-semibold text-zinc-700 mb-1">Category</label>
              <select
                {...register('community_category')}
                className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
              >
                {SYSTEM_CATEGORIES.map((cat) => (
                  <option key={cat} value={cat}>
                    {cat}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div>
            <label className="block font-semibold text-zinc-700 mb-1">Title / Topic Name</label>
            <input
              type="text"
              {...register('title')}
              placeholder="e.g. Designing Data-Intensive Applications 2nd Edition"
              className="w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
            />
            {errors.title && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.title.message}</p>
            )}
          </div>

          <div>
            <label className="block font-semibold text-zinc-700 mb-1">Author / Publisher (Optional)</label>
            <input
              type="text"
              {...register('author_publisher')}
              placeholder="e.g. Martin Kleppmann / O'Reilly"
              className="w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
            />
          </div>

          <div>
            <label className="block font-semibold text-zinc-700 mb-1">Additional Details / Link (Optional)</label>
            <textarea
              rows="3"
              {...register('details')}
              placeholder="Provide ISBN, paper URL, or why this content would benefit the community..."
              className="w-full p-4 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600"
            />
          </div>

          <div className="pt-2 flex justify-end">
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition flex items-center gap-2"
            >
              <Send className="h-3.5 w-3.5" />
              <span>Submit Request</span>
            </button>
          </div>
        </form>
      </div>

      {/* User's Previous Requests */}
      <div className="space-y-4">
        <h2 className="text-sm font-bold uppercase tracking-wider text-zinc-900">
          Your Submitted Requests
        </h2>

        <div className="bg-white rounded-2xl border border-zinc-200/80 shadow-sm overflow-hidden">
          {isLoading ? (
            <div className="p-8 text-center text-xs text-zinc-400">Loading your requests...</div>
          ) : myRequests && myRequests.length > 0 ? (
            <div className="divide-y divide-zinc-100">
              {myRequests.map((req) => (
                <div key={req.id} className="p-4 sm:p-5 flex items-center justify-between gap-4 text-xs">
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="text-[10px] font-bold uppercase bg-zinc-100 text-zinc-600 px-2 py-0.5 rounded">
                        {req.material_type}
                      </span>
                      <span className="text-[11px] font-semibold text-indigo-600">
                        {req.community_category}
                      </span>
                    </div>
                    <h3 className="font-bold text-zinc-900">{req.title}</h3>
                    {req.author_publisher && (
                      <p className="text-zinc-500 text-[11px]">By {req.author_publisher}</p>
                    )}
                  </div>

                  <div className="text-right shrink-0">
                    <span
                      className={`capitalize font-bold text-[10px] px-2.5 py-1 rounded-full border ${
                        req.status === 'approved'
                          ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                          : req.status === 'rejected'
                          ? 'bg-rose-50 text-rose-700 border-rose-200'
                          : 'bg-amber-50 text-amber-700 border-amber-200'
                      }`}
                    >
                      {req.status}
                    </span>
                    <span className="block text-[10px] text-zinc-400 mt-1">
                      {timeAgo(req.created_at)}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="p-8 text-center text-xs text-zinc-400">
              You haven't submitted any material requests yet.
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

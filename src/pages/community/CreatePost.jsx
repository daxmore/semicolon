import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import { useCommunityMutations } from '../../hooks/useCommunity';
import { useCommunityCategories } from '../../hooks/useCategories';
import { useAuth } from '../../contexts/AuthContext';
import { calculateLevel } from '../../lib/utils';
import { axiosClient } from '../../lib/axiosClient';
import { ArrowLeft, Sparkles, Image, AlertCircle, ArrowRight, Flame } from 'lucide-react';

const schema = yup.object({
  title: yup.string().min(5, 'Title must be at least 5 characters').required('Title is required'),
  category: yup.string().required('Please select a category'),
  description: yup.string().min(10, 'Description must be at least 10 characters').required('Description is required'),
  imageUrl: yup.string().nullable(),
});

export default function CreatePost() {
  const { user, profile, refreshProfile } = useAuth();
  const navigate = useNavigate();
  const { createPost } = useCommunityMutations();
  const { data: categories } = useCommunityCategories();
  const [imagePreview, setImagePreview] = useState('');
  const [serverError, setServerError] = useState('');

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      category: 'General Tech',
      imageUrl: '',
    },
  });

  const handleImageFileChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
      setServerError('Image must be smaller than 2MB');
      return;
    }

    const reader = new FileReader();
    reader.onloadend = () => {
      const b64 = reader.result;
      setImagePreview(b64);
      setValue('imageUrl', b64);
    };
    reader.readAsDataURL(file);
  };

  const onSubmit = async (values) => {
    if (!user) {
      navigate('/login');
      return;
    }

    try {
      setServerError('');
      const newPost = await createPost.mutateAsync({
        user_id: user.id,
        category: values.category,
        title: values.title,
        description: values.description,
        image_url: values.imageUrl || null,
      });

      // Gamification Reward: +10 XP for creating a discussion post
      try {
        const curTotal = profile?.xp_total || 0;
        const curWeekly = profile?.xp_weekly || 0;
        const newTotal = curTotal + 10;
        const newWeekly = curWeekly + 10;
        const newLvl = calculateLevel(newTotal);

        await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, {
          xp_total: newTotal,
          xp_weekly: newWeekly,
          level: newLvl,
        });
        await refreshProfile();
      } catch (xpErr) {
        console.warn('Could not increment post creation XP:', xpErr);
      }

      navigate(`/community/post/${newPost.id}`);
    } catch (err) {
      console.error(err);
      setServerError(err.message || 'Failed to create post. Please try again.');
    }
  };

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 py-10 space-y-6">
      <Link
        to="/community"
        className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-amber-600 transition"
      >
        <ArrowLeft className="h-3.5 w-3.5" /> Back to Discussions
      </Link>

      <div className="bg-white rounded-2xl border border-zinc-200/80 p-8 shadow-lg shadow-zinc-200/40 space-y-6">
        <div className="border-b border-zinc-100 pb-4 flex items-center justify-between">
          <div>
            <h1 className="text-xl font-bold font-heading text-zinc-900">Start a Discussion</h1>
            <p className="text-xs text-zinc-500 mt-0.5">Share code snippets, architectural patterns, or ask questions.</p>
          </div>
          <div className="flex items-center gap-1 px-3 py-1 bg-amber-50 border border-amber-200/60 rounded-full text-amber-700 text-xs font-bold shrink-0">
            <Flame className="h-3.5 w-3.5 fill-amber-500" />
            +10 XP Reward
          </div>
        </div>

        {serverError && (
          <div className="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
            <AlertCircle className="h-4 w-4 shrink-0" />
            <span>{serverError}</span>
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5 text-xs">
          {/* Category */}
          <div>
            <label className="block font-semibold text-zinc-700 mb-1.5">Discussion Category</label>
            <select
              {...register('category')}
              className="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition"
            >
              {(categories || []).map((cat) => (
                <option key={cat} value={cat}>
                  {cat}
                </option>
              ))}
            </select>
            {errors.category && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.category.message}</p>
            )}
          </div>

          {/* Title */}
          <div>
            <label className="block font-semibold text-zinc-700 mb-1.5">Topic Title</label>
            <input
              type="text"
              {...register('title')}
              placeholder="e.g. How to manage global server state with React Query and optimistic updates?"
              className={`w-full px-4 py-2.5 bg-zinc-50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition ${
                errors.title ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
              }`}
            />
            {errors.title && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.title.message}</p>
            )}
          </div>

          {/* Description (Markdown supported) */}
          <div>
            <label className="block font-semibold text-zinc-700 mb-1.5">
              Content (Markdown supported)
            </label>
            <textarea
              rows="6"
              {...register('description')}
              placeholder="Provide context, code samples, or specific questions..."
              className={`w-full p-4 bg-zinc-50 border rounded-xl font-mono text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 transition ${
                errors.description ? 'border-rose-300 bg-rose-50/30' : 'border-zinc-200'
              }`}
            />
            {errors.description && (
              <p className="mt-1 text-[11px] text-rose-600 font-medium">{errors.description.message}</p>
            )}
          </div>

          {/* Image Upload / URL */}
          <div className="space-y-2 pt-2 border-t border-zinc-100">
            <label className="block font-semibold text-zinc-700 flex items-center gap-1.5">
              <Image className="h-4 w-4 text-amber-500" />
              Attach Screenshot (Optional)
            </label>
            <input
              type="file"
              accept="image/*"
              onChange={handleImageFileChange}
              className="text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 cursor-pointer"
            />
            {imagePreview && (
              <div className="mt-2 h-40 max-w-sm rounded-xl border border-zinc-200 overflow-hidden relative">
                <img src={imagePreview} alt="Preview" className="w-full h-full object-cover" />
                <button
                  type="button"
                  onClick={() => {
                    setImagePreview('');
                    setValue('imageUrl', '');
                  }}
                  className="absolute top-2 right-2 px-2 py-1 bg-black/60 text-white rounded text-[10px] hover:bg-black"
                >
                  Remove
                </button>
              </div>
            )}
          </div>

          {/* Submit */}
          <div className="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100">
            <Link
              to="/community"
              className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 hover:bg-zinc-50 font-semibold"
            >
              Cancel
            </Link>
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-md shadow-amber-600/20 transition flex items-center gap-1.5"
            >
              {isSubmitting ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <span>Publish Topic</span>
                  <ArrowRight className="h-3.5 w-3.5" />
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

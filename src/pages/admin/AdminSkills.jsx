import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../../lib/axiosClient';
import { Plus, Trash2, X, ChevronDown, ChevronRight, Layers } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

// ── Fixed tier definitions ────────────────────────────────────────────────────
const DEFAULT_TIERS = [
  { level_name: 'Basic',           unlock_order: 1, required_xp: 0   },
  { level_name: 'Practical',       unlock_order: 2, required_xp: 100 },
  { level_name: 'Problem Solving', unlock_order: 3, required_xp: 250 },
  { level_name: 'Interview Ready', unlock_order: 4, required_xp: 500 },
];

const TIER_COLORS = {
  'Basic':           'bg-green-50 text-green-700 border-green-200',
  'Practical':       'bg-blue-50 text-blue-700 border-blue-200',
  'Problem Solving': 'bg-amber-50 text-amber-700 border-amber-200',
  'Interview Ready': 'bg-purple-50 text-purple-700 border-purple-200',
};

// ── Data hooks ────────────────────────────────────────────────────────────────
function useSkillsAdmin() {
  return useQuery({
    queryKey: ['admin_skills_full'],
    queryFn: async () => {
      const { data } = await axiosClient.get('/rest/v1/skills?select=*&order=id.asc');
      return data || [];
    },
  });
}

function useSkillLevels(skillId) {
  return useQuery({
    queryKey: ['admin_skill_levels', skillId],
    queryFn: async () => {
      const { data } = await axiosClient.get(
        `/rest/v1/skill_levels?skill_id=eq.${skillId}&select=*&order=unlock_order.asc`
      );
      return data || [];
    },
    enabled: !!skillId,
  });
}

// ── SVG Preview component ─────────────────────────────────────────────────────
function SvgPreview({ svgCode }) {
  if (!svgCode?.trim()) {
    return (
      <div className="w-full h-full flex items-center justify-center text-zinc-300">
        <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth={1.5}>
          <rect x="3" y="3" width="18" height="18" rx="3" />
          <path d="M9 9l6 6M15 9l-6 6" />
        </svg>
      </div>
    );
  }
  return (
    <div
      className="w-full h-full flex items-center justify-center [&>svg]:h-6 [&>svg]:w-6"
      dangerouslySetInnerHTML={{ __html: svgCode }}
    />
  );
}

// ── Main Page ─────────────────────────────────────────────────────────────────
export default function AdminSkills() {
  const queryClient = useQueryClient();
  const { data: skills, isLoading } = useSkillsAdmin();

  const [expandedSkill, setExpandedSkill] = useState(null);
  const [showSkillModal, setShowSkillModal] = useState(false);
  const [skillToDelete, setSkillToDelete] = useState(null);
  const [skillForm, setSkillForm] = useState({ name: '', description: '', icon_svg: '' });

  // ── Mutations ─────────────────────────────────────────────────────────────
  const createSkill = useMutation({
    mutationFn: async (payload) => {
      // 1. Create the skill
      const { data: created } = await axiosClient.post(
        '/rest/v1/skills',
        { name: payload.name, description: payload.description, icon: payload.icon_svg },
        { headers: { Prefer: 'return=representation' } }
      );
      const newSkill = Array.isArray(created) ? created[0] : created;

      // 2. Auto-create the 4 fixed tiers
      await Promise.all(
        DEFAULT_TIERS.map((tier) =>
          axiosClient.post(
            '/rest/v1/skill_levels',
            { ...tier, skill_id: newSkill.id },
            { headers: { Prefer: 'return=minimal' } }
          )
        )
      );
      return newSkill;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_skills_full'] });
    },
  });

  const deleteSkill = useMutation({
    mutationFn: async (id) => axiosClient.delete(`/rest/v1/skills?id=eq.${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin_skills_full'] });
      setSkillToDelete(null);
    },
  });

  // ── Handlers ──────────────────────────────────────────────────────────────
  const handleAddSkill = async (e) => {
    e.preventDefault();
    await createSkill.mutateAsync(skillForm);
    setSkillForm({ name: '', description: '', icon_svg: '' });
    setShowSkillModal(false);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Skills & Level Tiers</h2>
          <p className="text-xs text-zinc-500">
            Create a skill — 4 tiers (<strong>Basic · Practical · Problem Solving · Interview Ready</strong>) are added automatically.
          </p>
        </div>
        <button
          onClick={() => setShowSkillModal(true)}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <Plus className="h-4 w-4" /> Add Skill
        </button>
      </div>

      {/* Skills list */}
      <div className="space-y-3">
        {isLoading ? (
          <div className="bg-white rounded-xl border border-zinc-200 p-8 text-center text-xs text-zinc-400">
            Loading skills...
          </div>
        ) : skills?.length === 0 ? (
          <div className="bg-white rounded-xl border border-zinc-200 p-10 text-center">
            <Layers className="h-8 w-8 text-zinc-300 mx-auto mb-2" />
            <p className="text-sm font-semibold text-zinc-500">No skills yet</p>
            <p className="text-xs text-zinc-400 mt-1">Click "Add Skill" — tiers are created automatically.</p>
          </div>
        ) : (
          skills?.map((skill) => (
            <SkillRow
              key={skill.id}
              skill={skill}
              expanded={expandedSkill === skill.id}
              onToggle={() => setExpandedSkill(expandedSkill === skill.id ? null : skill.id)}
              onDelete={() => setSkillToDelete(skill)}
            />
          ))
        )}
      </div>

      {/* ── Add Skill Modal ── */}
      {showSkillModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-zinc-200 space-y-5">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <h3 className="font-bold text-base text-zinc-900">Add New Skill</h3>
              <button onClick={() => setShowSkillModal(false)} className="p-1 text-zinc-400 hover:text-zinc-600">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleAddSkill} className="space-y-4 text-xs">
              {/* SVG input + preview */}
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">
                  Icon SVG Code
                  <span className="ml-1 font-normal text-zinc-400">(paste your &lt;svg&gt; code)</span>
                </label>
                <div className="flex gap-3">
                  {/* Preview box */}
                  <div className="w-16 h-16 shrink-0 rounded-xl border border-zinc-200 bg-indigo-50 text-indigo-600 overflow-hidden p-1">
                    <SvgPreview svgCode={skillForm.icon_svg} />
                  </div>
                  {/* Textarea */}
                  <textarea
                    rows={4}
                    value={skillForm.icon_svg}
                    onChange={(e) => setSkillForm({ ...skillForm, icon_svg: e.target.value })}
                    placeholder={`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">\n  <path d="..." />\n</svg>`}
                    className="flex-1 px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-[11px] font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 resize-none"
                  />
                </div>
                <p className="mt-1 text-[10px] text-zinc-400">
                  Tip: get free SVGs from <a href="https://lucide.dev/icons" target="_blank" rel="noreferrer" className="text-indigo-500 underline">lucide.dev</a> or <a href="https://heroicons.com" target="_blank" rel="noreferrer" className="text-indigo-500 underline">heroicons.com</a>
                </p>
              </div>

              {/* Name */}
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">
                  Skill Name <span className="text-red-500">*</span>
                </label>
                <input
                  required
                  type="text"
                  value={skillForm.name}
                  onChange={(e) => setSkillForm({ ...skillForm, name: e.target.value })}
                  placeholder="e.g. Data Structures"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              {/* Description */}
              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Description</label>
                <textarea
                  rows={2}
                  value={skillForm.description}
                  onChange={(e) => setSkillForm({ ...skillForm, description: e.target.value })}
                  placeholder="Short description of this skill..."
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 resize-none"
                />
              </div>

              {/* Tier preview note */}
              <div className="bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3">
                <p className="font-semibold text-zinc-700 mb-2">Auto-created tiers:</p>
                <div className="flex flex-wrap gap-2">
                  {DEFAULT_TIERS.map((t) => (
                    <span
                      key={t.level_name}
                      className={`px-2.5 py-0.5 rounded-full border text-[10px] font-semibold ${TIER_COLORS[t.level_name]}`}
                    >
                      {t.level_name}
                    </span>
                  ))}
                </div>
              </div>

              <div className="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <button
                  type="button"
                  onClick={() => setShowSkillModal(false)}
                  className="px-4 py-2 border border-zinc-200 rounded-xl text-zinc-600 hover:bg-zinc-50 font-semibold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={createSkill.isPending}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition disabled:opacity-60"
                >
                  {createSkill.isPending ? 'Creating...' : 'Create Skill'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Skill Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!skillToDelete}
        onClose={() => setSkillToDelete(null)}
        onConfirm={confirmDeleteSkill}
        title="Delete Developer Skill"
        itemName={skillToDelete?.name || ''}
        message="Are you sure you want to delete this skill? All associated skill tiers, quiz questions, MCQ options, and user progress will be permanently deleted."
        isLoading={deleteSkill.isPending}
      />
    </div>
  );
}

// ── SkillRow ──────────────────────────────────────────────────────────────────
function SkillRow({ skill, expanded, onToggle, onDelete }) {
  const { data: tiers, isLoading } = useSkillLevels(expanded ? skill.id : null);

  return (
    <div className="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden">
      {/* Header */}
      <div
        className="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-zinc-50/60 transition"
        onClick={onToggle}
      >
        <div className="flex items-center gap-3 flex-1 min-w-0">
          {/* SVG Icon */}
          <div className="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 overflow-hidden p-1.5">
            {skill.icon ? (
              <div
                className="w-full h-full [&>svg]:w-full [&>svg]:h-full"
                dangerouslySetInnerHTML={{ __html: skill.icon }}
              />
            ) : (
              <Layers className="h-4 w-4" />
            )}
          </div>
          <div className="min-w-0">
            <p className="font-bold text-zinc-900 text-sm truncate">{skill.name}</p>
            {skill.description && (
              <p className="text-[11px] text-zinc-400 truncate">{skill.description}</p>
            )}
          </div>
          {expanded
            ? <ChevronDown className="h-4 w-4 text-zinc-400 ml-2 shrink-0" />
            : <ChevronRight className="h-4 w-4 text-zinc-400 ml-2 shrink-0" />
          }
        </div>
        <div className="ml-4 shrink-0" onClick={(e) => e.stopPropagation()}>
          <button
            onClick={onDelete}
            className="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
            title="Delete Skill"
          >
            <Trash2 className="h-3.5 w-3.5" />
          </button>
        </div>
      </div>

      {/* Tiers */}
      {expanded && (
        <div className="border-t border-zinc-100 bg-zinc-50/60">
          {isLoading ? (
            <p className="px-5 py-4 text-xs text-zinc-400">Loading tiers...</p>
          ) : (
            <div className="px-5 py-4 flex flex-wrap gap-2">
              {tiers?.map((tier) => (
                <span
                  key={tier.id}
                  className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-[11px] font-semibold ${
                    TIER_COLORS[tier.level_name] || 'bg-zinc-50 text-zinc-700 border-zinc-200'
                  }`}
                >
                  {tier.level_name}
                  <span className="opacity-60 font-normal">· {tier.required_xp ?? 0} XP</span>
                </span>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

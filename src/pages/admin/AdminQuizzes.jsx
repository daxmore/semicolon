import React, { useState } from 'react';
import { useSkills, useQuizMutations } from '../../hooks/useAcademy';
import { axiosClient } from '../../lib/axiosClient';
import { useQuery } from '@tanstack/react-query';
import { HelpCircle, Plus, Trash2, X, Search, Check, AlertCircle } from 'lucide-react';
import DeleteConfirmModal from '../../components/common/DeleteConfirmModal';

export default function AdminQuizzes() {
  const [selectedSkill, setSelectedSkill] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [questionToDelete, setQuestionToDelete] = useState(null);

  const { data: skills } = useSkills();
  const { createQuestion, deleteQuestion } = useQuizMutations();

  // Fetch skill levels for dropdown
  const { data: levels } = useQuery({
    queryKey: ['admin_levels', selectedSkill],
    queryFn: async () => {
      if (!selectedSkill) return [];
      const { data } = await axiosClient.get(
        `/rest/v1/skill_levels?skill_id=eq.${selectedSkill}&select=*&order=unlock_order.asc`
      );
      return data || [];
    },
    enabled: !!selectedSkill,
  });

  // Fetch all existing questions
  const { data: questionsList, isLoading } = useQuery({
    queryKey: ['admin_questions', selectedSkill],
    queryFn: async () => {
      let query = '/rest/v1/questions?select=*,skills(name),skill_levels(level_name)&order=id.desc';
      if (selectedSkill) {
        query += `&skill_id=eq.${selectedSkill}`;
      }
      const { data } = await axiosClient.get(query);
      return data || [];
    },
  });

  const [formData, setFormData] = useState({
    skill_id: '',
    level_id: '',
    question_text: '',
    xp_reward: 10,
    options: ['', '', '', ''],
    correct_index: 0,
  });

  const handleOpenModal = () => {
    setFormData({
      skill_id: skills?.[0]?.id || '',
      level_id: '',
      question_text: '',
      xp_reward: 10,
      options: ['', '', '', ''],
      correct_index: 0,
    });
    setSelectedSkill(skills?.[0]?.id || '');
    setShowModal(true);
  };

  const handleOptionChange = (idx, value) => {
    const updated = [...formData.options];
    updated[idx] = value;
    setFormData({ ...formData, options: updated });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await createQuestion.mutateAsync({
        skill_id: parseInt(formData.skill_id, 10),
        level_id: parseInt(formData.level_id, 10),
        question_text: formData.question_text,
        xp_reward: parseInt(formData.xp_reward, 10),
        options: formData.options.filter((o) => o.trim().length > 0),
        correct_index: formData.correct_index,
      });
      setShowModal(false);
    } catch (err) {
      console.error('Error creating question:', err);
    }
  };

  const confirmDeleteQuestion = async () => {
    if (!questionToDelete) return;
    try {
      await deleteQuestion.mutateAsync(questionToDelete.id);
      setQuestionToDelete(null);
    } catch (err) {
      console.error('Failed to delete question:', err);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-xl font-bold text-zinc-900">Manage Quizzes & Questions</h2>
          <p className="text-xs text-zinc-500">Configure quiz questions, MCQ options, and custom XP rewards for all skills.</p>
        </div>
        <button
          onClick={handleOpenModal}
          className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition"
        >
          <Plus className="h-4 w-4" /> Add Question
        </button>
      </div>

      {/* Filter by Skill */}
      <div className="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm flex items-center gap-3">
        <span className="text-xs font-semibold text-zinc-700 shrink-0">Filter by Skill:</span>
        <select
          value={selectedSkill}
          onChange={(e) => setSelectedSkill(e.target.value)}
          className="px-3 py-1.5 bg-zinc-50 border border-zinc-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        >
          <option value="">All Skills</option>
          {skills?.map((s) => (
            <option key={s.id} value={s.id}>
              {s.name}
            </option>
          ))}
        </select>
      </div>

      {/* Questions Table */}
      <div className="bg-white rounded-xl border border-zinc-200 overflow-hidden shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-50 border-b border-zinc-200 text-zinc-600 font-semibold uppercase tracking-wider">
              <tr>
                <th className="px-6 py-3.5">Question</th>
                <th className="px-6 py-3.5">Skill</th>
                <th className="px-6 py-3.5">Tier / Level</th>
                <th className="px-6 py-3.5">XP Reward</th>
                <th className="px-6 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-200 text-zinc-800">
              {isLoading ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-400">
                    Loading questions...
                  </td>
                </tr>
              ) : questionsList && questionsList.length > 0 ? (
                questionsList.map((q) => (
                  <tr key={q.id} className="hover:bg-zinc-50/80 transition">
                    <td className="px-6 py-4 font-semibold text-zinc-900 max-w-sm truncate">
                      {q.question_text}
                    </td>
                    <td className="px-6 py-4">
                      <span className="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100 font-medium">
                        {q.skills?.name || `Skill #${q.skill_id}`}
                      </span>
                    </td>
                    <td className="px-6 py-4 capitalize font-medium text-zinc-600">
                      {q.skill_levels?.level_name || `Level #${q.level_id}`}
                    </td>
                    <td className="px-6 py-4 font-bold text-amber-600">+{q.xp_reward} XP</td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => setQuestionToDelete(q)}
                        className="p-1.5 text-zinc-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                        title="Delete Question"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-zinc-500">
                    No questions created yet.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Add Question */}
      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in">
          <div className="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-zinc-200 space-y-5">
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <h3 className="font-bold text-base text-zinc-900">Add New Quiz Question</h3>
              <button onClick={() => setShowModal(false)} className="p-1 text-zinc-400 hover:text-zinc-600">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">Skill</label>
                  <select
                    required
                    value={formData.skill_id}
                    onChange={(e) => {
                      setFormData({ ...formData, skill_id: e.target.value });
                      setSelectedSkill(e.target.value);
                    }}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  >
                    <option value="">Select Skill</option>
                    {skills?.map((s) => (
                      <option key={s.id} value={s.id}>
                        {s.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">Skill Level</label>
                  <select
                    required
                    value={formData.level_id}
                    onChange={(e) => setFormData({ ...formData, level_id: e.target.value })}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  >
                    <option value="">Select Level</option>
                    {levels?.map((l) => (
                      <option key={l.id} value={l.id}>
                        {l.level_name} (Order: {l.unlock_order})
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block font-semibold text-zinc-700 mb-1">Question Text</label>
                <textarea
                  rows="2"
                  required
                  value={formData.question_text}
                  onChange={(e) => setFormData({ ...formData, question_text: e.target.value })}
                  placeholder="What is the output of typeof NaN in JavaScript?"
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div className="space-y-2">
                <label className="block font-semibold text-zinc-700">Options (Select the correct answer)</label>
                {formData.options.map((opt, idx) => (
                  <div key={idx} className="flex items-center gap-2">
                    <input
                      type="radio"
                      name="correct_option"
                      checked={formData.correct_index === idx}
                      onChange={() => setFormData({ ...formData, correct_index: idx })}
                      className="text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    />
                    <input
                      type="text"
                      required={idx < 2}
                      value={opt}
                      onChange={(e) => {
                        const next = [...formData.options];
                        next[idx] = e.target.value;
                        setFormData({ ...formData, options: next });
                      }}
                      placeholder={`Option ${idx + 1}`}
                      className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    />
                  </div>
                ))}
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">XP Reward</label>
                  <input
                    type="number"
                    min="1"
                    required
                    value={formData.xp_reward}
                    onChange={(e) => setFormData({ ...formData, xp_reward: e.target.value })}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  />
                </div>
                <div>
                  <label className="block font-semibold text-zinc-700 mb-1">Difficulty</label>
                  <select
                    value={formData.difficulty}
                    onChange={(e) => setFormData({ ...formData, difficulty: e.target.value })}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                  >
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                  </select>
                </div>
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
                  disabled={createQuestion.isPending}
                  className="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition"
                >
                  Save Question
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Delete Question Confirmation Modal */}
      <DeleteConfirmModal
        isOpen={!!questionToDelete}
        onClose={() => setQuestionToDelete(null)}
        onConfirm={confirmDeleteQuestion}
        title="Delete Quiz Question"
        itemName={questionToDelete?.question_text || ''}
        message="Are you sure you want to delete this question and all its MCQ options? This action cannot be undone."
        isLoading={deleteQuestion.isPending}
      />
    </div>
  );
}

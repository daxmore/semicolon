import React, { useState } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { useQuizQuestions } from '../../hooks/useAcademy';
import { useAuth } from '../../contexts/AuthContext';
import { axiosClient } from '../../lib/axiosClient';
import { calculateLevel } from '../../lib/utils';
import { Award, CheckCircle2, XCircle, ArrowRight, RotateCcw, Flame, Trophy, AlertCircle } from 'lucide-react';

export default function Quiz() {
  const [searchParams] = useSearchParams();
  const skillId = searchParams.get('skill');
  const levelId = searchParams.get('level');
  const navigate = useNavigate();

  const { user, profile, refreshProfile } = useAuth();
  const { data: questions, isLoading, error } = useQuizQuestions(skillId, levelId);

  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState(null);
  const [score, setScore] = useState(0);
  const [xpEarned, setXpEarned] = useState(0);
  const [quizFinished, setQuizFinished] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // Current Question
  const currentQ = questions?.[currentIndex];

  const handleSelectOption = (optId) => {
    setSelectedOption(optId);
  };

  const handleNext = async () => {
    if (selectedOption === null) return;

    // Check if chosen option is correct
    const chosen = currentQ.options.find((o) => o.id === selectedOption);
    const isCorrect = chosen?.is_correct === true;
    const gainedXp = isCorrect ? currentQ.xp_reward || 10 : 0;

    const newScore = score + (isCorrect ? 1 : 0);
    const newTotalXp = xpEarned + gainedXp;

    setScore(newScore);
    setXpEarned(newTotalXp);

    if (currentIndex + 1 < questions.length) {
      setCurrentIndex(currentIndex + 1);
      setSelectedOption(null);
    } else {
      // Finished Quiz -> Submit XP & Progress
      setSubmitting(true);
      try {
        if (user) {
          // 1. Award XP to user profile
          const updatedXp = (profile?.xp_total || 0) + newTotalXp;
          const updatedWeeklyXp = (profile?.xp_weekly || 0) + newTotalXp;
          const newLevel = calculateLevel(updatedXp);

          await axiosClient.patch(`/rest/v1/profiles?id=eq.${user.id}`, {
            xp_total: updatedXp,
            xp_weekly: updatedWeeklyXp,
            level: newLevel,
          });

          // 2. Mark level complete in user_skill_progress
          if (newScore >= Math.ceil(questions.length * 0.6)) {
            // Passed quiz!
            await axiosClient.post(
              '/rest/v1/user_skill_progress',
              {
                user_id: user.id,
                skill_id: parseInt(skillId, 10),
                current_level: parseInt(levelId, 10),
                completed_levels_json: [`level_${levelId}`],
              },
              { headers: { Prefer: 'resolution=merge-duplicates' } }
            );
          }

          await refreshProfile();
        }
      } catch (err) {
        console.error('Error submitting quiz score:', err);
      } finally {
        setSubmitting(false);
        setQuizFinished(true);
      }
    }
  };

  if (isLoading) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 text-center space-y-4">
        <div className="w-10 h-10 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto" />
        <p className="text-xs text-zinc-500 font-medium">Preparing quiz challenges...</p>
      </div>
    );
  }

  if (error || !questions || questions.length === 0) {
    return (
      <div className="max-w-md mx-auto px-4 py-16 text-center space-y-4 bg-white p-8 rounded-2xl border border-zinc-200 shadow-sm mt-10">
        <AlertCircle className="h-8 w-8 text-amber-500 mx-auto" />
        <h2 className="text-base font-bold text-zinc-900">No Questions Configured</h2>
        <p className="text-xs text-zinc-500">
          This level does not have quiz questions yet. Admin can add them via Admin Quizzes.
        </p>
        <Link to="/academy" className="inline-block text-xs font-semibold text-indigo-600">
          &larr; Back to Academy
        </Link>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto px-4 sm:px-6 py-12">
      {!quizFinished ? (
        <div className="bg-white rounded-2xl border border-zinc-200/80 p-8 shadow-lg shadow-zinc-200/40 space-y-8">
          {/* Progress Header */}
          <div className="space-y-2 border-b border-zinc-100 pb-4">
            <div className="flex items-center justify-between text-xs font-semibold text-zinc-500">
              <span>
                Question {currentIndex + 1} of {questions.length}
              </span>
              <span className="text-indigo-600 font-bold">+{currentQ?.xp_reward || 10} XP</span>
            </div>
            <div className="w-full h-1.5 bg-zinc-100 rounded-full overflow-hidden">
              <div
                className="h-full bg-indigo-600 transition-all duration-300 rounded-full"
                style={{ width: `${((currentIndex + 1) / questions.length) * 100}%` }}
              />
            </div>
          </div>

          {/* Question Text */}
          <div className="space-y-2">
            <h2 className="text-lg font-bold text-zinc-900 leading-snug">
              {currentQ.question_text}
            </h2>
          </div>

          {/* Options */}
          <div className="space-y-3">
            {currentQ.options?.map((opt, idx) => {
              const isSelected = selectedOption === opt.id;
              return (
                <button
                  key={opt.id}
                  onClick={() => handleSelectOption(opt.id)}
                  className={`w-full text-left p-4 rounded-xl text-xs font-medium border transition flex items-center gap-3 ${
                    isSelected
                      ? 'bg-indigo-50/80 border-indigo-600 text-indigo-950 font-semibold shadow-sm'
                      : 'bg-zinc-50 border-zinc-200/80 text-zinc-700 hover:bg-zinc-100/80'
                  }`}
                >
                  <span
                    className={`w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0 ${
                      isSelected
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white border border-zinc-200 text-zinc-600'
                    }`}
                  >
                    {String.fromCharCode(65 + idx)}
                  </span>
                  <span className="flex-1">{opt.option_text}</span>
                </button>
              );
            })}
          </div>

          {/* Action */}
          <div className="pt-4 border-t border-zinc-100 flex justify-end">
            <button
              onClick={handleNext}
              disabled={selectedOption === null || submitting}
              className="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition flex items-center gap-2"
            >
              <span>{currentIndex + 1 === questions.length ? 'Submit Quiz' : 'Next Question'}</span>
              <ArrowRight className="h-3.5 w-3.5" />
            </button>
          </div>
        </div>
      ) : (
        /* Results Card */
        <div className="bg-white rounded-2xl border border-zinc-200 p-8 sm:p-10 shadow-xl text-center space-y-6 animate-in fade-in">
          <div className="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center mx-auto text-amber-500">
            <Trophy className="h-8 w-8" />
          </div>

          <div className="space-y-2">
            <h2 className="text-2xl font-bold font-heading text-zinc-900">Quiz Completed!</h2>
            <p className="text-xs text-zinc-500">
              You scored {score} out of {questions.length} questions correctly.
            </p>
          </div>

          {/* XP Gained Banner */}
          <div className="p-4 rounded-xl bg-gradient-to-r from-indigo-50 via-amber-50 to-indigo-50 border border-amber-200/60 flex items-center justify-center gap-3">
            <Flame className="h-5 w-5 text-amber-500 fill-amber-500" />
            <span className="text-sm font-bold text-zinc-900">
              +{xpEarned} XP Earned & Added to Profile!
            </span>
          </div>

          {/* Actions */}
          <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4 border-t border-zinc-100">
            <button
              onClick={() => {
                setCurrentIndex(0);
                setSelectedOption(null);
                setScore(0);
                setXpEarned(0);
                setQuizFinished(false);
              }}
              className="w-full sm:w-auto px-5 py-2.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5"
            >
              <RotateCcw className="h-3.5 w-3.5" /> Retake Quiz
            </button>
            <Link
              to="/academy"
              className="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition flex items-center justify-center gap-1.5"
            >
              <span>Back to Academy</span>
              <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}

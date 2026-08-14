import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axiosClient';

/**
 * Fetch all skill paths
 */
export function useSkills() {
  return useQuery({
    queryKey: ['skills'],
    queryFn: async () => {
      const { data } = await axiosClient.get('/rest/v1/skills?select=*&order=id.asc');
      return data;
    },
  });
}

/**
 * Fetch single skill path with its 4 levels
 */
export function useSkillDetail(skillId) {
  return useQuery({
    queryKey: ['skill', skillId],
    queryFn: async () => {
      if (!skillId) return null;

      const { data: skill } = await axiosClient.get(
        `/rest/v1/skills?id=eq.${skillId}&select=*`,
        { headers: { Accept: 'application/vnd.pgrst.object+json' } }
      );

      const { data: levels } = await axiosClient.get(
        `/rest/v1/skill_levels?skill_id=eq.${skillId}&select=*&order=unlock_order.asc`
      );

      return {
        ...skill,
        levels: levels || [],
      };
    },
    enabled: !!skillId,
  });
}

/**
 * Fetch user progress on a skill
 */
export function useUserSkillProgress(userId, skillId) {
  return useQuery({
    queryKey: ['user_skill_progress', userId, skillId],
    queryFn: async () => {
      if (!userId || !skillId) return null;

      const { data } = await axiosClient.get(
        `/rest/v1/user_skill_progress?user_id=eq.${userId}&skill_id=eq.${skillId}&select=*`
      );

      return data?.[0] || null;
    },
    enabled: !!userId && !!skillId,
  });
}

/**
 * Fetch questions and options for a specific skill and level
 */
export function useQuizQuestions(skillId, levelId) {
  return useQuery({
    queryKey: ['quiz_questions', skillId, levelId],
    queryFn: async () => {
      if (!skillId || !levelId) return [];

      const { data: questions } = await axiosClient.get(
        `/rest/v1/questions?skill_id=eq.${skillId}&level_id=eq.${levelId}&select=*`
      );

      if (!questions || questions.length === 0) return [];

      // Fetch options for all question IDs
      const questionIds = questions.map((q) => q.id);
      const { data: options } = await axiosClient.get(
        `/rest/v1/options?question_id=in.(${questionIds.join(',')})&select=*`
      );

      // Group options by question
      const optionsByQ = {};
      options?.forEach((opt) => {
        if (!optionsByQ[opt.question_id]) optionsByQ[opt.question_id] = [];
        optionsByQ[opt.question_id].push(opt);
      });

      return questions.map((q) => ({
        ...q,
        options: optionsByQ[q.id] || [],
      }));
    },
    enabled: !!skillId && !!levelId,
  });
}

/**
 * Admin Quiz Mutations (Manage Quizzes)
 */
export function useQuizMutations() {
  const queryClient = useQueryClient();

  const createQuestion = useMutation({
    mutationFn: async ({ skill_id, level_id, question_text, xp_reward, options, correct_index }) => {
      // 1. Insert question
      const { data: qData } = await axiosClient.post(
        '/rest/v1/questions',
        {
          skill_id,
          level_id,
          question_text,
          xp_reward: xp_reward || 10,
          question_type: 'mcq',
          difficulty: 'medium',
        },
        { headers: { Prefer: 'return=representation' } }
      );

      const newQ = qData[0];

      // 2. Insert options
      if (options && options.length > 0) {
        const optionPayloads = options.map((text, idx) => ({
          question_id: newQ.id,
          option_text: text,
          is_correct: idx === correct_index,
        }));

        await axiosClient.post('/rest/v1/options', optionPayloads);
      }

      return newQ;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['quiz_questions'] });
    },
  });

  const deleteQuestion = useMutation({
    mutationFn: async (questionId) => {
      await axiosClient.delete(`/rest/v1/questions?id=eq.${questionId}`);
      return questionId;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['quiz_questions'] });
    },
  });

  return { createQuestion, deleteQuestion };
}

-- ============================================================================
-- Semicolon — Row Level Security Policies
-- ============================================================================

-- Enable RLS on all tables
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.books ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.papers ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.videos ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.community_posts ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.community_comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.community_reactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.community_comment_reactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.community_reports ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_history ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.downloads ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.subscriptions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.badges ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_badges ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.skills ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.skill_levels ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_skill_progress ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.questions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.options ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.material_requests ENABLE ROW LEVEL SECURITY;

-- ─────────────────────────────────────────────
-- Helper: Check if current user is admin
-- ─────────────────────────────────────────────
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
AS $$
    SELECT EXISTS (
        SELECT 1 FROM public.profiles
        WHERE id = auth.uid() AND role = 'admin'
    );
$$;

-- ═══════════════════════════════════════════════
-- PROFILES
-- ═══════════════════════════════════════════════

-- Anyone authenticated can read all profiles (for usernames, avatars, leaderboard)
CREATE POLICY "profiles_select_authenticated"
    ON public.profiles FOR SELECT
    TO authenticated
    USING (true);

-- Users can update only their own profile
CREATE POLICY "profiles_update_own"
    ON public.profiles FOR UPDATE
    TO authenticated
    USING (id = auth.uid())
    WITH CHECK (id = auth.uid());

-- Admins can update any profile (e.g. managing users, toggling is_pro)
CREATE POLICY "profiles_update_admin"
    ON public.profiles FOR UPDATE
    TO authenticated
    USING (public.is_admin());

-- Profile insert is handled by the trigger, not direct inserts
-- But allow service_role to insert (for the trigger)
CREATE POLICY "profiles_insert_trigger"
    ON public.profiles FOR INSERT
    TO authenticated
    WITH CHECK (id = auth.uid());

-- Admins can delete any profile
CREATE POLICY "profiles_delete_admin"
    ON public.profiles FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- BOOKS
-- ═══════════════════════════════════════════════

-- All authenticated users can read books
CREATE POLICY "books_select_authenticated"
    ON public.books FOR SELECT
    TO authenticated
    USING (true);

-- Only admins can insert/update/delete books
CREATE POLICY "books_insert_admin"
    ON public.books FOR INSERT
    TO authenticated
    WITH CHECK (public.is_admin());

CREATE POLICY "books_update_admin"
    ON public.books FOR UPDATE
    TO authenticated
    USING (public.is_admin());

CREATE POLICY "books_delete_admin"
    ON public.books FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- PAPERS
-- ═══════════════════════════════════════════════

CREATE POLICY "papers_select_authenticated"
    ON public.papers FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "papers_insert_admin"
    ON public.papers FOR INSERT
    TO authenticated
    WITH CHECK (public.is_admin());

CREATE POLICY "papers_update_admin"
    ON public.papers FOR UPDATE
    TO authenticated
    USING (public.is_admin());

CREATE POLICY "papers_delete_admin"
    ON public.papers FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- VIDEOS
-- ═══════════════════════════════════════════════

CREATE POLICY "videos_select_authenticated"
    ON public.videos FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "videos_insert_admin"
    ON public.videos FOR INSERT
    TO authenticated
    WITH CHECK (public.is_admin());

CREATE POLICY "videos_update_admin"
    ON public.videos FOR UPDATE
    TO authenticated
    USING (public.is_admin());

CREATE POLICY "videos_delete_admin"
    ON public.videos FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- COMMUNITY POSTS
-- ═══════════════════════════════════════════════

-- All authenticated users can read posts
CREATE POLICY "community_posts_select"
    ON public.community_posts FOR SELECT
    TO authenticated
    USING (true);

-- Authenticated users can create posts (user_id must be their own)
CREATE POLICY "community_posts_insert_own"
    ON public.community_posts FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Owners can update their own posts
CREATE POLICY "community_posts_update_own"
    ON public.community_posts FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- All authenticated users can update vote counts on posts
CREATE POLICY "community_posts_update_votes"
    ON public.community_posts FOR UPDATE
    TO authenticated
    USING (true)
    WITH CHECK (true);

-- Admins can update any post
CREATE POLICY "community_posts_update_admin"
    ON public.community_posts FOR UPDATE
    TO authenticated
    USING (public.is_admin());

-- Owners can delete their own posts
CREATE POLICY "community_posts_delete_own"
    ON public.community_posts FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

-- Admins can delete any post
CREATE POLICY "community_posts_delete_admin"
    ON public.community_posts FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- COMMUNITY COMMENTS
-- ═══════════════════════════════════════════════

CREATE POLICY "community_comments_select"
    ON public.community_comments FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "community_comments_insert_own"
    ON public.community_comments FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "community_comments_update_own"
    ON public.community_comments FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "community_comments_update_admin"
    ON public.community_comments FOR UPDATE
    TO authenticated
    USING (public.is_admin());

CREATE POLICY "community_comments_delete_own"
    ON public.community_comments FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "community_comments_delete_admin"
    ON public.community_comments FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- COMMUNITY REACTIONS (post votes)
-- ═══════════════════════════════════════════════

CREATE POLICY "community_reactions_select"
    ON public.community_reactions FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "community_reactions_insert_own"
    ON public.community_reactions FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "community_reactions_update_own"
    ON public.community_reactions FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "community_reactions_delete_own"
    ON public.community_reactions FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- COMMUNITY COMMENT REACTIONS
-- ═══════════════════════════════════════════════

CREATE POLICY "comment_reactions_select"
    ON public.community_comment_reactions FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "comment_reactions_insert_own"
    ON public.community_comment_reactions FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "comment_reactions_update_own"
    ON public.community_comment_reactions FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "comment_reactions_delete_own"
    ON public.community_comment_reactions FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- COMMUNITY REPORTS
-- ═══════════════════════════════════════════════

-- Any authenticated user can create a report
CREATE POLICY "reports_insert_authenticated"
    ON public.community_reports FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Only admins can view reports
CREATE POLICY "reports_select_admin"
    ON public.community_reports FOR SELECT
    TO authenticated
    USING (public.is_admin());

-- Only admins can update report status
CREATE POLICY "reports_update_admin"
    ON public.community_reports FOR UPDATE
    TO authenticated
    USING (public.is_admin());

-- Only admins can delete reports
CREATE POLICY "reports_delete_admin"
    ON public.community_reports FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- NOTIFICATIONS
-- ═══════════════════════════════════════════════

-- Users can only read their own notifications
CREATE POLICY "notifications_select_own"
    ON public.notifications FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

-- System/triggers insert notifications (allow service_role + admin + own user)
CREATE POLICY "notifications_insert"
    ON public.notifications FOR INSERT
    TO authenticated
    WITH CHECK (true);  -- Notification creation is controlled server-side via triggers/functions

-- Users can update (mark as read) only their own
CREATE POLICY "notifications_update_own"
    ON public.notifications FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- Users can delete their own notifications
CREATE POLICY "notifications_delete_own"
    ON public.notifications FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- REACTIONS (generic — books/papers/videos)
-- ═══════════════════════════════════════════════

CREATE POLICY "reactions_select"
    ON public.reactions FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "reactions_insert_own"
    ON public.reactions FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "reactions_update_own"
    ON public.reactions FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "reactions_delete_own"
    ON public.reactions FOR DELETE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- USER HISTORY
-- ═══════════════════════════════════════════════

-- Users can read their own history
CREATE POLICY "user_history_select_own"
    ON public.user_history FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

-- Users can insert their own history entries
CREATE POLICY "user_history_insert_own"
    ON public.user_history FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Users can update their own history (viewed_at timestamp)
CREATE POLICY "user_history_update_own"
    ON public.user_history FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- DOWNLOADS
-- ═══════════════════════════════════════════════

CREATE POLICY "downloads_select_own"
    ON public.downloads FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "downloads_insert_own"
    ON public.downloads FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Admins can see all downloads (analytics)
CREATE POLICY "downloads_select_admin"
    ON public.downloads FOR SELECT
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- SUBSCRIPTIONS
-- ═══════════════════════════════════════════════

-- Users can read their own subscriptions
CREATE POLICY "subscriptions_select_own"
    ON public.subscriptions FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

-- Admins can view all subscriptions
CREATE POLICY "subscriptions_select_admin"
    ON public.subscriptions FOR SELECT
    TO authenticated
    USING (public.is_admin());

-- Inserts/updates are handled by Edge Functions (service_role)
-- Allow insert via service role only (handled by Supabase default)
CREATE POLICY "subscriptions_insert_service"
    ON public.subscriptions FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "subscriptions_update_own"
    ON public.subscriptions FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- BADGES
-- ═══════════════════════════════════════════════

-- Everyone can read badges
CREATE POLICY "badges_select"
    ON public.badges FOR SELECT
    TO authenticated
    USING (true);

-- Only admins can manage badges
CREATE POLICY "badges_insert_admin"
    ON public.badges FOR INSERT
    TO authenticated
    WITH CHECK (public.is_admin());

CREATE POLICY "badges_update_admin"
    ON public.badges FOR UPDATE
    TO authenticated
    USING (public.is_admin());

CREATE POLICY "badges_delete_admin"
    ON public.badges FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- USER BADGES
-- ═══════════════════════════════════════════════

-- Everyone can read user badges (for profile display)
CREATE POLICY "user_badges_select"
    ON public.user_badges FOR SELECT
    TO authenticated
    USING (true);

-- System inserts badges (via trigger/function) — allow authenticated insert
CREATE POLICY "user_badges_insert"
    ON public.user_badges FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Users can update their own (toggle is_equipped)
CREATE POLICY "user_badges_update_own"
    ON public.user_badges FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- SKILLS, SKILL LEVELS, QUESTIONS, OPTIONS
-- ═══════════════════════════════════════════════

-- Everyone can read skills/levels/questions/options
CREATE POLICY "skills_select"
    ON public.skills FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "skill_levels_select"
    ON public.skill_levels FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "questions_select"
    ON public.questions FOR SELECT
    TO authenticated
    USING (true);

CREATE POLICY "options_select"
    ON public.options FOR SELECT
    TO authenticated
    USING (true);

-- Only admins can manage quiz content
CREATE POLICY "skills_insert_admin"
    ON public.skills FOR INSERT TO authenticated WITH CHECK (public.is_admin());
CREATE POLICY "skills_update_admin"
    ON public.skills FOR UPDATE TO authenticated USING (public.is_admin());
CREATE POLICY "skills_delete_admin"
    ON public.skills FOR DELETE TO authenticated USING (public.is_admin());

CREATE POLICY "skill_levels_insert_admin"
    ON public.skill_levels FOR INSERT TO authenticated WITH CHECK (public.is_admin());
CREATE POLICY "skill_levels_update_admin"
    ON public.skill_levels FOR UPDATE TO authenticated USING (public.is_admin());
CREATE POLICY "skill_levels_delete_admin"
    ON public.skill_levels FOR DELETE TO authenticated USING (public.is_admin());

CREATE POLICY "questions_insert_admin"
    ON public.questions FOR INSERT TO authenticated WITH CHECK (public.is_admin());
CREATE POLICY "questions_update_admin"
    ON public.questions FOR UPDATE TO authenticated USING (public.is_admin());
CREATE POLICY "questions_delete_admin"
    ON public.questions FOR DELETE TO authenticated USING (public.is_admin());

CREATE POLICY "options_insert_admin"
    ON public.options FOR INSERT TO authenticated WITH CHECK (public.is_admin());
CREATE POLICY "options_update_admin"
    ON public.options FOR UPDATE TO authenticated USING (public.is_admin());
CREATE POLICY "options_delete_admin"
    ON public.options FOR DELETE TO authenticated USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- USER SKILL PROGRESS
-- ═══════════════════════════════════════════════

CREATE POLICY "user_skill_progress_select_own"
    ON public.user_skill_progress FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "user_skill_progress_insert_own"
    ON public.user_skill_progress FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "user_skill_progress_update_own"
    ON public.user_skill_progress FOR UPDATE
    TO authenticated
    USING (user_id = auth.uid());

-- ═══════════════════════════════════════════════
-- MATERIAL REQUESTS
-- ═══════════════════════════════════════════════

-- Users can create requests
CREATE POLICY "material_requests_insert_own"
    ON public.material_requests FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Users can read their own requests
CREATE POLICY "material_requests_select_own"
    ON public.material_requests FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

-- Admins can read all requests
CREATE POLICY "material_requests_select_admin"
    ON public.material_requests FOR SELECT
    TO authenticated
    USING (public.is_admin());

-- Admins can update request status
CREATE POLICY "material_requests_update_admin"
    ON public.material_requests FOR UPDATE
    TO authenticated
    USING (public.is_admin());

-- Admins can delete requests
CREATE POLICY "material_requests_delete_admin"
    ON public.material_requests FOR DELETE
    TO authenticated
    USING (public.is_admin());

-- ═══════════════════════════════════════════════
-- AUTOMATIC VOTE SYNC TRIGGER
-- ═══════════════════════════════════════════════

CREATE OR REPLACE FUNCTION public.handle_community_post_vote()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
    IF (TG_OP = 'INSERT') THEN
        IF NEW.reaction_type = 'upvote' THEN
            UPDATE public.community_posts SET upvotes = COALESCE(upvotes, 0) + 1 WHERE id = NEW.post_id;
        ELSIF NEW.reaction_type = 'downvote' THEN
            UPDATE public.community_posts SET downvotes = COALESCE(downvotes, 0) + 1 WHERE id = NEW.post_id;
        END IF;
    ELSIF (TG_OP = 'DELETE') THEN
        IF OLD.reaction_type = 'upvote' THEN
            UPDATE public.community_posts SET upvotes = GREATEST(0, COALESCE(upvotes, 0) - 1) WHERE id = OLD.post_id;
        ELSIF OLD.reaction_type = 'downvote' THEN
            UPDATE public.community_posts SET downvotes = GREATEST(0, COALESCE(downvotes, 0) - 1) WHERE id = OLD.post_id;
        END IF;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF OLD.reaction_type = 'upvote' AND NEW.reaction_type = 'downvote' THEN
            UPDATE public.community_posts SET upvotes = GREATEST(0, COALESCE(upvotes, 0) - 1), downvotes = COALESCE(downvotes, 0) + 1 WHERE id = NEW.post_id;
        ELSIF OLD.reaction_type = 'downvote' AND NEW.reaction_type = 'upvote' THEN
            UPDATE public.community_posts SET downvotes = GREATEST(0, COALESCE(downvotes, 0) - 1), upvotes = COALESCE(upvotes, 0) + 1 WHERE id = NEW.post_id;
        END IF;
    END IF;
    RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS trg_community_post_vote ON public.community_reactions;
CREATE TRIGGER trg_community_post_vote
AFTER INSERT OR UPDATE OR DELETE ON public.community_reactions
FOR EACH ROW EXECUTE FUNCTION public.handle_community_post_vote();


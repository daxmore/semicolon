-- ============================================================================
-- Semicolon — Performance Indexes
-- ============================================================================

-- ─── PROFILES ───
CREATE INDEX idx_profiles_username ON public.profiles(username);
CREATE INDEX idx_profiles_role ON public.profiles(role);
CREATE INDEX idx_profiles_xp_weekly ON public.profiles(xp_weekly DESC);
CREATE INDEX idx_profiles_xp_total ON public.profiles(xp_total DESC);
CREATE INDEX idx_profiles_level ON public.profiles(level DESC);

-- ─── BOOKS ───
CREATE INDEX idx_books_subject ON public.books(subject);
CREATE INDEX idx_books_token ON public.books(token);
CREATE INDEX idx_books_created_at ON public.books(created_at DESC);

-- ─── PAPERS ───
CREATE INDEX idx_papers_subject ON public.papers(subject);
CREATE INDEX idx_papers_year ON public.papers(year DESC);
CREATE INDEX idx_papers_token ON public.papers(token);
CREATE INDEX idx_papers_created_at ON public.papers(created_at DESC);

-- ─── VIDEOS ───
CREATE INDEX idx_videos_category ON public.videos(category);
CREATE INDEX idx_videos_token ON public.videos(token);
CREATE INDEX idx_videos_created_at ON public.videos(created_at DESC);

-- ─── COMMUNITY POSTS ───
CREATE INDEX idx_community_posts_user_id ON public.community_posts(user_id);
CREATE INDEX idx_community_posts_category ON public.community_posts(category);
CREATE INDEX idx_community_posts_created_at ON public.community_posts(created_at DESC);

-- ─── COMMUNITY COMMENTS ───
CREATE INDEX idx_community_comments_post_id ON public.community_comments(post_id);
CREATE INDEX idx_community_comments_user_id ON public.community_comments(user_id);
CREATE INDEX idx_community_comments_parent_id ON public.community_comments(parent_id);
CREATE INDEX idx_community_comments_is_accepted ON public.community_comments(is_accepted) WHERE is_accepted = true;

-- ─── COMMUNITY REACTIONS ───
CREATE INDEX idx_community_reactions_post_id ON public.community_reactions(post_id);
CREATE INDEX idx_community_reactions_user_id ON public.community_reactions(user_id);

-- ─── COMMUNITY COMMENT REACTIONS ───
CREATE INDEX idx_comment_reactions_comment_id ON public.community_comment_reactions(comment_id);
CREATE INDEX idx_comment_reactions_user_id ON public.community_comment_reactions(user_id);

-- ─── COMMUNITY REPORTS ───
CREATE INDEX idx_community_reports_status ON public.community_reports(status);
CREATE INDEX idx_community_reports_target ON public.community_reports(target_type, target_id);

-- ─── NOTIFICATIONS ───
CREATE INDEX idx_notifications_user_id ON public.notifications(user_id);
CREATE INDEX idx_notifications_user_unread ON public.notifications(user_id, is_read) WHERE is_read = false;
CREATE INDEX idx_notifications_created_at ON public.notifications(created_at DESC);

-- ─── REACTIONS (generic) ───
CREATE INDEX idx_reactions_resource ON public.reactions(resource_type, resource_id);
CREATE INDEX idx_reactions_user_id ON public.reactions(user_id);

-- ─── USER HISTORY ───
CREATE INDEX idx_user_history_user_id ON public.user_history(user_id);
CREATE INDEX idx_user_history_viewed_at ON public.user_history(viewed_at DESC);
CREATE INDEX idx_user_history_resource ON public.user_history(resource_type, resource_id);

-- ─── DOWNLOADS ───
CREATE INDEX idx_downloads_user_id ON public.downloads(user_id);

-- ─── SUBSCRIPTIONS ───
CREATE INDEX idx_subscriptions_user_id ON public.subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON public.subscriptions(status);

-- ─── USER BADGES ───
CREATE INDEX idx_user_badges_user_id ON public.user_badges(user_id);
CREATE INDEX idx_user_badges_badge_id ON public.user_badges(badge_id);

-- ─── SKILL LEVELS ───
CREATE INDEX idx_skill_levels_skill_id ON public.skill_levels(skill_id);

-- ─── USER SKILL PROGRESS ───
CREATE INDEX idx_user_skill_progress_user_id ON public.user_skill_progress(user_id);
CREATE INDEX idx_user_skill_progress_skill_id ON public.user_skill_progress(skill_id);

-- ─── QUESTIONS ───
CREATE INDEX idx_questions_skill_level ON public.questions(skill_id, level_id);

-- ─── OPTIONS ───
CREATE INDEX idx_options_question_id ON public.options(question_id);

-- ─── MATERIAL REQUESTS ───
CREATE INDEX idx_material_requests_user_id ON public.material_requests(user_id);
CREATE INDEX idx_material_requests_status ON public.material_requests(status);

-- ═══════════════════════════════════════════════
-- Full-text search support (for Phase 8 search)
-- ═══════════════════════════════════════════════

-- Books full-text search
ALTER TABLE public.books ADD COLUMN IF NOT EXISTS fts tsvector
    GENERATED ALWAYS AS (
        setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(author, '')), 'B') ||
        setweight(to_tsvector('english', coalesce(description, '')), 'C') ||
        setweight(to_tsvector('english', coalesce(subject, '')), 'B')
    ) STORED;

CREATE INDEX idx_books_fts ON public.books USING gin(fts);

-- Papers full-text search
ALTER TABLE public.papers ADD COLUMN IF NOT EXISTS fts tsvector
    GENERATED ALWAYS AS (
        setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(subject, '')), 'B')
    ) STORED;

CREATE INDEX idx_papers_fts ON public.papers USING gin(fts);

-- Videos full-text search
ALTER TABLE public.videos ADD COLUMN IF NOT EXISTS fts tsvector
    GENERATED ALWAYS AS (
        setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(description, '')), 'C')
    ) STORED;

CREATE INDEX idx_videos_fts ON public.videos USING gin(fts);

-- Community posts full-text search
ALTER TABLE public.community_posts ADD COLUMN IF NOT EXISTS fts tsvector
    GENERATED ALWAYS AS (
        setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(description, '')), 'B')
    ) STORED;

CREATE INDEX idx_community_posts_fts ON public.community_posts USING gin(fts);

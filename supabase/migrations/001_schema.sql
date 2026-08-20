-- ============================================================================
-- Semicolon — Supabase Postgres Schema
-- Migrated from PHP/MySQL legacy codebase
-- ============================================================================

-- ─────────────────────────────────────────────
-- 1. PROFILES (extends Supabase auth.users)
-- ─────────────────────────────────────────────
CREATE TABLE public.profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    avatar_url TEXT,
    role TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    is_pro BOOLEAN NOT NULL DEFAULT FALSE,
    is_banned BOOLEAN NOT NULL DEFAULT FALSE,
    security_question TEXT,
    security_answer TEXT,
    -- RPG / Gamification fields
    xp_total INTEGER NOT NULL DEFAULT 0,
    xp_weekly INTEGER NOT NULL DEFAULT 0,
    daily_xp_earned INTEGER NOT NULL DEFAULT 0,
    level INTEGER NOT NULL DEFAULT 1,
    daily_streak INTEGER NOT NULL DEFAULT 0,
    last_activity_date DATE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.profiles IS 'Application-level user profile extending Supabase auth.users';

-- ─────────────────────────────────────────────
-- 2. BOOKS
-- ─────────────────────────────────────────────
CREATE TABLE public.books (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    description TEXT NOT NULL,
    subject TEXT NOT NULL,
    difficulty TEXT NOT NULL DEFAULT 'Medium' CHECK (difficulty IN ('Easy', 'Medium', 'Hard')),
    private_path TEXT NOT NULL,       -- local Supabase Storage path or external URL
    slug TEXT UNIQUE NOT NULL,
    token TEXT UNIQUE NOT NULL,       -- public access token
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 3. PAPERS
-- ─────────────────────────────────────────────
CREATE TABLE public.papers (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title TEXT NOT NULL,
    subject TEXT NOT NULL,
    year INTEGER NOT NULL,
    private_path TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 4. VIDEOS
-- ─────────────────────────────────────────────
CREATE TABLE public.videos (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    youtube_url TEXT NOT NULL,
    category TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    token TEXT UNIQUE NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 5. COMMUNITY POSTS
-- ─────────────────────────────────────────────
CREATE TABLE public.community_posts (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT NOT NULL,        -- supports Markdown
    image_url TEXT,                    -- base64 data URI or external URL
    category TEXT NOT NULL,
    upvotes INTEGER NOT NULL DEFAULT 0,
    downvotes INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 6. COMMUNITY COMMENTS (threaded)
-- ─────────────────────────────────────────────
CREATE TABLE public.community_comments (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    post_id BIGINT NOT NULL REFERENCES public.community_posts(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    parent_id BIGINT REFERENCES public.community_comments(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    upvotes INTEGER NOT NULL DEFAULT 0,
    downvotes INTEGER NOT NULL DEFAULT 0,
    is_accepted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 7. COMMUNITY REACTIONS (post votes)
-- ─────────────────────────────────────────────
CREATE TABLE public.community_reactions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    post_id BIGINT NOT NULL REFERENCES public.community_posts(id) ON DELETE CASCADE,
    reaction_type TEXT NOT NULL CHECK (reaction_type IN ('upvote', 'downvote')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id, post_id)
);

-- ─────────────────────────────────────────────
-- 8. COMMUNITY COMMENT REACTIONS (comment votes)
-- ─────────────────────────────────────────────
CREATE TABLE public.community_comment_reactions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    comment_id BIGINT NOT NULL REFERENCES public.community_comments(id) ON DELETE CASCADE,
    reaction_type TEXT NOT NULL CHECK (reaction_type IN ('upvote', 'downvote')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id, comment_id)
);

-- ─────────────────────────────────────────────
-- 9. COMMUNITY REPORTS
-- ─────────────────────────────────────────────
CREATE TABLE public.community_reports (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    target_type TEXT NOT NULL CHECK (target_type IN ('post', 'comment')),
    target_id BIGINT NOT NULL,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'reviewed', 'dismissed')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 10. NOTIFICATIONS
-- ─────────────────────────────────────────────
CREATE TABLE public.notifications (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    type TEXT NOT NULL DEFAULT 'system',
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    link TEXT,                         -- in-app navigation URL
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 11. REACTIONS (generic: books/papers/videos helpful/not helpful)
-- ─────────────────────────────────────────────
CREATE TABLE public.reactions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    resource_type TEXT NOT NULL CHECK (resource_type IN ('book', 'paper', 'video')),
    resource_id BIGINT NOT NULL,
    is_helpful BOOLEAN NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id, resource_type, resource_id)
);

-- ─────────────────────────────────────────────
-- 12. USER HISTORY (view tracking)
-- ─────────────────────────────────────────────
CREATE TABLE public.user_history (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    resource_type TEXT NOT NULL CHECK (resource_type IN ('book', 'paper', 'video', 'post')),
    resource_id BIGINT NOT NULL,
    viewed_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id, resource_type, resource_id)
);

-- ─────────────────────────────────────────────
-- 13. DOWNLOADS (Pro users only)
-- ─────────────────────────────────────────────
CREATE TABLE public.downloads (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    resource_type TEXT NOT NULL CHECK (resource_type IN ('book', 'paper')),
    resource_id BIGINT NOT NULL,
    downloaded_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 14. SUBSCRIPTIONS (Stripe)
-- ─────────────────────────────────────────────
CREATE TABLE public.subscriptions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    stripe_checkout_id TEXT,           -- Stripe Checkout Session ID
    stripe_payment_intent TEXT,        -- Payment Intent for refunds
    amount NUMERIC(10, 2) NOT NULL,    -- e.g. 499.00
    currency TEXT NOT NULL DEFAULT 'inr',
    status TEXT NOT NULL DEFAULT 'paid' CHECK (status IN ('paid', 'cancelled', 'refunded')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- 15. BADGES
-- ─────────────────────────────────────────────
CREATE TABLE public.badges (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    badge_name TEXT UNIQUE NOT NULL,
    badge_type TEXT NOT NULL DEFAULT 'mastery',
    required_xp INTEGER NOT NULL DEFAULT 0,
    description TEXT,
    svg_icon TEXT                       -- raw SVG markup
);

-- ─────────────────────────────────────────────
-- 16. USER BADGES (junction)
-- ─────────────────────────────────────────────
CREATE TABLE public.user_badges (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    badge_id BIGINT NOT NULL REFERENCES public.badges(id) ON DELETE CASCADE,
    is_equipped BOOLEAN NOT NULL DEFAULT FALSE,
    earned_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    UNIQUE (user_id, badge_id)
);

-- ─────────────────────────────────────────────
-- 17. SKILLS
-- ─────────────────────────────────────────────
CREATE TABLE public.skills (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT
);

-- ─────────────────────────────────────────────
-- 18. SKILL LEVELS
-- ─────────────────────────────────────────────
CREATE TABLE public.skill_levels (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    skill_id BIGINT NOT NULL REFERENCES public.skills(id) ON DELETE CASCADE,
    level_name TEXT NOT NULL,          -- 'easy', 'medium', 'hard', 'interview'
    unlock_order INTEGER NOT NULL DEFAULT 0,
    required_xp INTEGER NOT NULL DEFAULT 0
);

-- ─────────────────────────────────────────────
-- 19. USER SKILL PROGRESS
-- ─────────────────────────────────────────────
CREATE TABLE public.user_skill_progress (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    skill_id BIGINT NOT NULL REFERENCES public.skills(id) ON DELETE CASCADE,
    current_level INTEGER NOT NULL DEFAULT 0,
    interview_unlocked BOOLEAN NOT NULL DEFAULT FALSE,
    completed_levels_json JSONB DEFAULT '[]'::jsonb,
    UNIQUE (user_id, skill_id)
);

-- ─────────────────────────────────────────────
-- 20. QUESTIONS
-- ─────────────────────────────────────────────
CREATE TABLE public.questions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    skill_id BIGINT NOT NULL REFERENCES public.skills(id) ON DELETE CASCADE,
    level_id BIGINT NOT NULL REFERENCES public.skill_levels(id) ON DELETE CASCADE,
    question_text TEXT NOT NULL,
    xp_reward INTEGER NOT NULL DEFAULT 5,
    question_type TEXT NOT NULL DEFAULT 'mcq',
    difficulty TEXT NOT NULL DEFAULT 'medium'
);

-- ─────────────────────────────────────────────
-- 21. OPTIONS (for quiz questions)
-- ─────────────────────────────────────────────
CREATE TABLE public.options (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    question_id BIGINT NOT NULL REFERENCES public.questions(id) ON DELETE CASCADE,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE
);

-- ─────────────────────────────────────────────
-- 22. MATERIAL REQUESTS
-- ─────────────────────────────────────────────
CREATE TABLE public.material_requests (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES public.profiles(id) ON DELETE CASCADE,
    material_type TEXT NOT NULL,       -- 'book', 'paper', 'video', etc.
    community_category TEXT,
    title TEXT NOT NULL,
    author_publisher TEXT,
    details TEXT,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ─────────────────────────────────────────────
-- TRIGGER: Auto-create profile on signup
-- ─────────────────────────────────────────────
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER SET search_path = public
AS $$
BEGIN
    INSERT INTO public.profiles (id, username, email)
    VALUES (
        NEW.id,
        COALESCE(NEW.raw_user_meta_data->>'username', split_part(NEW.email, '@', 1)),
        NEW.email
    );
    RETURN NEW;
END;
$$;

CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW
    EXECUTE FUNCTION public.handle_new_user();

-- ─────────────────────────────────────────────
-- FUNCTION: Weekly XP reset (run via pg_cron)
-- Resets xp_weekly to 0 every Monday at 00:00 UTC
-- ─────────────────────────────────────────────
CREATE OR REPLACE FUNCTION public.reset_weekly_xp()
RETURNS void
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
    UPDATE public.profiles SET xp_weekly = 0;
END;
$$;

-- TODO: Enable pg_cron extension in Supabase Dashboard and add:
-- SELECT cron.schedule('reset-weekly-xp', '0 0 * * 1', 'SELECT public.reset_weekly_xp()');

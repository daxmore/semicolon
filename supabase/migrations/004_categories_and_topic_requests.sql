-- ============================================================================
-- Semicolon — Migration: Categories & Topic Requests Tables
-- ============================================================================

-- ─────────────────────────────────────────────
-- 1. CATEGORIES TABLE (for Admin managed topics)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS public.categories (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name TEXT UNIQUE NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.categories ENABLE ROW LEVEL SECURITY;

-- Everyone (authenticated and anon) can view categories
CREATE POLICY "categories_select_all"
    ON public.categories FOR SELECT
    TO public
    USING (true);

-- Only Admins can insert/update/delete categories
CREATE POLICY "categories_admin_all"
    ON public.categories FOR ALL
    TO authenticated
    USING (public.is_admin())
    WITH CHECK (public.is_admin());

-- Seed default categories if table is newly created
INSERT INTO public.categories (name) VALUES
    ('Frontend'),
    ('Backend'),
    ('Full Stack'),
    ('App Dev'),
    ('Game Dev'),
    ('UI/UX Design'),
    ('Graphic Design'),
    ('Video Editing'),
    ('Motion Graphics'),
    ('Data Science'),
    ('AI & ML'),
    ('Cybersecurity'),
    ('DevOps'),
    ('Cloud Computing'),
    ('General Tech')
ON CONFLICT (name) DO NOTHING;


-- ─────────────────────────────────────────────
-- 2. TOPIC REQUESTS TABLE (User proposals for new communities)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS public.topic_requests (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id UUID REFERENCES public.profiles(id) ON DELETE CASCADE,
    username TEXT,
    topic_name TEXT NOT NULL,
    reason TEXT NOT NULL,
    purpose TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    admin_note TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.topic_requests ENABLE ROW LEVEL SECURITY;

-- Users can read their own topic requests
CREATE POLICY "topic_requests_select_own"
    ON public.topic_requests FOR SELECT
    TO authenticated
    USING (user_id = auth.uid() OR public.is_admin());

-- Authenticated users can submit a topic request
CREATE POLICY "topic_requests_insert_auth"
    ON public.topic_requests FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

-- Admins can update topic requests (status, admin notes)
CREATE POLICY "topic_requests_admin_update"
    ON public.topic_requests FOR UPDATE
    TO authenticated
    USING (public.is_admin())
    WITH CHECK (public.is_admin());

-- Admins can delete topic requests
CREATE POLICY "topic_requests_admin_delete"
    ON public.topic_requests FOR DELETE
    TO authenticated
    USING (public.is_admin());

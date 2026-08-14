/**
 * Utility helpers migrated from includes/functions.php
 */

/**
 * System categories — matches get_system_categories() in PHP
 */
export const SYSTEM_CATEGORIES = [
  'Frontend',
  'Backend',
  'Full Stack',
  'App Dev',
  'Game Dev',
  'UI/UX Design',
  'Graphic Design',
  'Video Editing',
  'Motion Graphics',
  'Data Science',
  'AI & ML',
  'Cybersecurity',
  'DevOps',
  'Cloud Computing',
  'General Tech',
];

/**
 * Generate a URL-friendly slug from a string.
 * Mirrors generate_slug() in PHP.
 */
export function generateSlug(str) {
  return str
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Generate a random hex token.
 * Mirrors generate_token() in PHP.
 */
export function generateToken(length = 32) {
  const array = new Uint8Array(length / 2);
  crypto.getRandomValues(array);
  return Array.from(array, (b) => b.toString(16).padStart(2, '0')).join('');
}

/**
 * Extract YouTube video ID from various URL formats.
 * Mirrors get_youtube_id() in PHP.
 */
export function getYoutubeId(url) {
  if (!url) return null;

  const patterns = [
    /youtu\.be\/([a-zA-Z0-9_-]+)/,
    /youtube\.com\/embed\/([a-zA-Z0-9_-]+)/,
    /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/,
  ];

  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match) return match[1];
  }

  try {
    const parsed = new URL(url);
    return parsed.searchParams.get('v') || null;
  } catch {
    return null;
  }
}

/**
 * Calculate user level from total XP.
 * Formula: Level = floor(sqrt(xp_total / 100)) + 1
 * Mirrors calculate_level_from_xp() in PHP.
 */
export function calculateLevel(xpTotal) {
  if (xpTotal < 0) return 1;
  return Math.floor(Math.sqrt(xpTotal / 100)) + 1;
}

/**
 * Password validation — matches is_valid_password_format() in PHP.
 * 6+ chars, at least one uppercase, one digit, one symbol.
 */
export function isValidPasswordFormat(password) {
  return /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/.test(password);
}

/**
 * Format a timestamp to a relative time string (e.g., "2 hours ago").
 */
export function timeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);

  const intervals = [
    { label: 'year', seconds: 31536000 },
    { label: 'month', seconds: 2592000 },
    { label: 'week', seconds: 604800 },
    { label: 'day', seconds: 86400 },
    { label: 'hour', seconds: 3600 },
    { label: 'minute', seconds: 60 },
  ];

  for (const interval of intervals) {
    const count = Math.floor(seconds / interval.seconds);
    if (count >= 1) {
      return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
    }
  }

  return 'just now';
}

/**
 * Truncate text to a maximum length with an ellipsis.
 */
export function truncate(str, maxLength = 100) {
  if (!str) return '';
  if (str.length <= maxLength) return str;
  return str.slice(0, maxLength) + '…';
}

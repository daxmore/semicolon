<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/functions.php';

$header_user_avatar = null;
$unread_notifs = 0;
if (isset($_SESSION['user_id'])) {
    $header_user_data = get_user_by_id($_SESSION['user_id']);
    $header_user_avatar = $header_user_data['avatar_url'] ?? null;
    
    // Fetch unread notifications count
    if (isset($conn)) {
        $stmt_notif = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt_notif->bind_param('i', $_SESSION['user_id']);
        $stmt_notif->execute();
        $stmt_notif->bind_result($unread_notifs);
        $stmt_notif->fetch();
        $stmt_notif->close();
    }
}
?>
<header class="glass sticky top-0 z-50 transition-all duration-300 border-b border-zinc-200/80">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between">
      
      <!-- Section 1: Logo -->
      <div class="flex items-center">
        <a class="block text-indigo-600 font-bold text-2xl tracking-tighter md:mr-48" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>">
          <?php echo file_get_contents('assets/images/logo.svg'); ?>
        </a>
      </div>

      <!-- Section 2: Navigation (Center) -->
      <div class="hidden md:block">
          <nav aria-label="Global">
            <ul class="flex items-center gap-8 text-sm font-medium">
              <li><a class="text-zinc-600 transition hover:text-indigo-600" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>">Home</a></li>
              
              <!-- Community Link -->
              <li><a class="text-zinc-600 transition hover:text-indigo-600" href="community.php">Community</a></li>
              
              <!-- Resources Dropdown -->
              <li class="relative group">
                  <button class="flex items-center gap-1 text-zinc-600 transition hover:text-indigo-600 focus:outline-none">
                      Resources
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                  </button>
                  <div class="absolute left-0 top-full pt-4 w-48 origin-top-left opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                      <div class="flex flex-col rounded-xl bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden py-2">
                          <a href="books.php" class="px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">Books</a>
                          <a href="papers.php" class="px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">Papers</a>
                          <a href="videos.php" class="px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-indigo-600 transition">Videos</a>
                      </div>
                  </div>
              </li>
              
              <?php if (isset($_SESSION['user_id'])): ?>
              <!-- Academy Dropdown -->
              <li class="relative group">
                  <button class="flex items-center gap-1 text-zinc-600 transition hover:text-indigo-600 focus:outline-none font-semibold">
                      Academy
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                  </button>
                  <div class="absolute left-0 top-full pt-4 w-48 origin-top-left opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                      <div class="flex flex-col rounded-xl bg-white shadow-lg ring-1 ring-amber-500/20 overflow-hidden py-2 border border-amber-500/10">
                          <a href="academy.php" class="px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                              Dashboard
                          </a>
                          <a href="leaderboard.php" class="px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                              Leaderboard
                          </a>
                          <a href="profile.php#badges" class="px-4 py-2 text-sm text-zinc-700 hover:bg-amber-50 hover:text-amber-600 transition flex items-center gap-2">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                              My Badges
                          </a>
                      </div>
                  </div>
              </li>
              <li><a class="text-zinc-600 transition hover:text-indigo-600" href="request.php">Request</a></li>
              <?php endif; ?>
              <li><a class="text-zinc-600 transition hover:text-indigo-600" href="pricing.php">Pricing</a></li>
              <?php if (!isset($_SESSION['user_id'])): ?>
              <li><a class="text-zinc-600 transition hover:text-indigo-600" href="about.php">About</a></li>
              <?php endif; ?>
            </ul>
          </nav>
      </div>

      <!-- Section 3: Actions (Right) -->
      <div class="flex items-center gap-3">
        <!-- Search Icon Button -->
        <button id="search-trigger" class="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-500 hover:text-zinc-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Notifications -->
            <a href="notifications.php" class="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 transition flex items-center justify-center text-zinc-500 hover:text-zinc-700 relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <?php if ($unread_notifs > 0): ?>
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full shadow-sm ring-2 ring-white">
                        <?php echo $unread_notifs > 99 ? '99+' : $unread_notifs; ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-2 rounded-full bg-zinc-100 hover:bg-zinc-200 pl-1 pr-3 py-1 text-sm font-medium text-zinc-700 transition">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm overflow-hidden">
                        <?php if ($header_user_avatar): ?>
                            <img src="/Semicolon/<?php echo htmlspecialchars($header_user_avatar); ?>" alt="Avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <span class="hidden sm:inline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="absolute right-0 pt-2 w-48 origin-top-right opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                    <div class="rounded-xl bg-white shadow-lg ring-1 ring-zinc-200 overflow-hidden py-1">
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>
                        <a href="notifications.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifications
                        </a>
                        <a href="history.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            History
                        </a>
                        <a href="manage_posts.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Manage Posts
                        </a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/index.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Admin Panel
                        </a>
                        <?php endif; ?>
                        <div class="border-t border-zinc-100 my-1"></div>
                        <a href="auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <a class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition" href="auth/login.php">
                Login
            </a>
            <a class="hidden sm:inline-flex rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition" href="auth/signup.php">
                Register
            </a>
        <?php endif; ?>

        <!-- Mobile Menu Button -->
        <div class="block md:hidden">
            <button id="mobile-menu-button" class="w-10 h-10 rounded-full bg-zinc-100 hover:bg-zinc-200 flex items-center justify-center text-zinc-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden md:hidden absolute top-16 left-0 w-full bg-white shadow-lg py-4 z-40 border-t border-zinc-100">
      <nav aria-label="Mobile Global">
          <ul class="flex flex-col items-center gap-4 text-base font-medium">
              <li><a class="text-zinc-600 hover:text-indigo-600 transition" href="index.php">Home</a></li>
              
              <!-- Community Link (Mobile) -->
              <li><a class="text-zinc-600 hover:text-indigo-600 transition" href="community.php">Community</a></li>
              
              <li class="w-full px-6">
                  <details class="group/mobile-resources w-full cursor-pointer">
                      <summary class="flex items-center justify-center gap-1 text-zinc-600 hover:text-indigo-600 transition list-none font-medium text-base [&::-webkit-details-marker]:hidden">
                          Resources
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open/mobile-resources:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                      </summary>
                      <div class="mt-3 flex flex-col items-center gap-3 bg-zinc-50 rounded-xl py-3 border border-zinc-100">
                          <a class="text-zinc-600 hover:text-indigo-600 transition text-sm" href="books.php">Books</a>
                          <a class="text-zinc-600 hover:text-indigo-600 transition text-sm" href="papers.php">Papers</a>
                          <a class="text-zinc-600 hover:text-indigo-600 transition text-sm" href="videos.php">Videos</a>
                      </div>
                  </details>
              </li>
              
              <?php if (isset($_SESSION['user_id'])): ?>
              <li class="w-full px-6">
                  <details class="group/mobile-academy w-full cursor-pointer">
                      <summary class="flex items-center justify-center gap-1 text-zinc-600 hover:text-amber-600 transition list-none font-semibold text-base [&::-webkit-details-marker]:hidden">
                          Academy
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open/mobile-academy:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                      </summary>
                      <div class="mt-3 flex flex-col items-center gap-3 bg-amber-50 rounded-xl py-3 border border-amber-100">
                          <a class="text-zinc-700 hover:text-amber-700 transition text-sm flex items-center gap-2" href="academy.php">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Dashboard
                          </a>
                          <a class="text-zinc-700 hover:text-amber-700 transition text-sm flex items-center gap-2" href="leaderboard.php">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg> Leaderboard
                          </a>
                          <a class="text-zinc-700 hover:text-amber-700 transition text-sm flex items-center gap-2" href="profile.php#badges">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg> My Badges
                          </a>
                      </div>
                  </details>
              </li>
              <li><a class="text-zinc-600 hover:text-indigo-600 transition" href="request.php">Request</a></li>
              <?php endif; ?>
              <li><a class="text-zinc-600 hover:text-indigo-600 transition" href="pricing.php">Pricing</a></li>
              <?php if (!isset($_SESSION['user_id'])): ?>
              <li><a class="text-zinc-600 hover:text-indigo-600 transition" href="about.php">About</a></li>
              <?php endif; ?>
          </ul>
      </nav>
  </div>
</header>

<!-- Sleek Fullscreen Search Overlay -->
<div id="search-overlay" class="fixed inset-0 z-[100] hidden">
    <!-- Dark backdrop with blur -->
    <div class="absolute inset-0 bg-zinc-950/70 backdrop-blur-md transition-opacity duration-300" id="search-backdrop"></div>
    
    <!-- Search Modal -->
    <div class="relative flex flex-col items-center pt-[12vh] px-4">
        <!-- Close Button -->
        <div class="absolute top-6 right-6">
            <button id="search-close" class="group flex items-center gap-2 text-zinc-400 hover:text-white transition-colors">
                <span class="text-xs opacity-0 group-hover:opacity-100 transition-opacity font-mono">ESC</span>
                <div class="w-10 h-10 rounded-full bg-zinc-800/80 border border-zinc-700/50 flex items-center justify-center hover:bg-zinc-700/80 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </button>
        </div>

        <!-- Search Container -->
        <div class="w-full max-w-xl" id="search-container">
            <!-- Search Input with glow effect -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-teal-500/20 rounded-2xl blur-xl opacity-0 group-focus-within:opacity-100 transition-opacity duration-500"></div>
                <div class="relative flex items-center bg-zinc-900/90 backdrop-blur-xl rounded-2xl border border-zinc-700/50 shadow-2xl overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        type="text" 
                        id="fullscreen-search-input" 
                        placeholder="Search..." 
                        class="flex-1 py-4 px-4 text-lg text-white bg-transparent border-0 focus:ring-0 focus:outline-none placeholder:text-zinc-500 font-light"
                        autocomplete="off"
                    >
                    <div id="search-loading" class="hidden mr-4">
                        <div class="w-5 h-5 border-2 border-zinc-700 border-t-indigo-500 rounded-full animate-spin"></div>
                    </div>
                </div>
            </div>
            
            <!-- Results Container -->
            <div id="fullscreen-search-results" class="mt-3">
                <!-- Suggestions -->
                <div id="search-suggestions" class="bg-zinc-900/80 backdrop-blur-xl rounded-xl border border-zinc-800/50 shadow-2xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-zinc-800/50">
                        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-[0.15em]">Trending</span>
                    </div>
                    <div class="py-1">
                        <button class="search-suggestion w-full flex items-center gap-4 px-5 py-3 text-left text-zinc-300 hover:bg-white/5 transition-colors group">
                            <span class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 text-sm">📚</span>
                            <span class="flex-1 font-light">Clean Code</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                        <button class="search-suggestion w-full flex items-center gap-4 px-5 py-3 text-left text-zinc-300 hover:bg-white/5 transition-colors group">
                            <span class="w-8 h-8 rounded-lg bg-teal-500/10 flex items-center justify-center text-teal-400 text-sm">📄</span>
                            <span class="flex-1 font-light">System Design Patterns</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                        <button class="search-suggestion w-full flex items-center gap-4 px-5 py-3 text-left text-zinc-300 hover:bg-white/5 transition-colors group">
                            <span class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-400 text-sm">🎥</span>
                            <span class="flex-1 font-light">React Fundamentals</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                        <button class="search-suggestion w-full flex items-center gap-4 px-5 py-3 text-left text-zinc-300 hover:bg-white/5 transition-colors group">
                            <span class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400 text-sm">📚</span>
                            <span class="flex-1 font-light">Machine Learning Basics</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-5 py-3 border-t border-zinc-800/50 flex items-center justify-between text-[11px] text-zinc-600">
                        <span><kbd class="px-1.5 py-0.5 bg-zinc-800 rounded text-zinc-500 font-mono">↵</kbd> to search</span>
                        <span><kbd class="px-1.5 py-0.5 bg-zinc-800 rounded text-zinc-500 font-mono">esc</kbd> to close</span>
                    </div>
                </div>
                
                <!-- Live Results -->
                <div id="live-search-results" class="hidden bg-zinc-900/80 backdrop-blur-xl rounded-xl border border-zinc-800/50 shadow-2xl overflow-hidden max-h-[55vh] overflow-y-auto"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Search overlay animations */
    #search-overlay.active #search-backdrop { opacity: 1; }
    #search-overlay #search-backdrop { opacity: 0; }
    #search-overlay.active #search-container {
        opacity: 1;
        transform: translateY(0);
    }
    #search-overlay #search-container {
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Custom scrollbar */
    #live-search-results::-webkit-scrollbar { width: 6px; }
    #live-search-results::-webkit-scrollbar-track { background: transparent; }
    #live-search-results::-webkit-scrollbar-thumb { background: rgba(113, 113, 122, 0.3); border-radius: 3px; }
    #live-search-results::-webkit-scrollbar-thumb:hover { background: rgba(113, 113, 122, 0.5); }
</style>

<script>
    // Mobile Menu Toggle
    const btn = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');

    if (btn && menu) {
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    }

    // Fullscreen Search
    const searchTrigger = document.getElementById('search-trigger');
    const searchOverlay = document.getElementById('search-overlay');
    const searchClose = document.getElementById('search-close');
    const searchBackdrop = document.getElementById('search-backdrop');
    const searchInput = document.getElementById('fullscreen-search-input');
    const searchSuggestions = document.getElementById('search-suggestions');
    const liveSearchResults = document.getElementById('live-search-results');
    const searchLoading = document.getElementById('search-loading');
    let debounceTimer;

    // Open Search
    if (searchTrigger && searchOverlay) {
        searchTrigger.addEventListener('click', () => {
            searchOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                searchOverlay.classList.add('active');
                searchInput.focus();
            });
        });
    }

    // Close Search
    function closeSearch() {
        searchOverlay.classList.remove('active');
        setTimeout(() => {
            searchOverlay.classList.add('hidden');
            document.body.style.overflow = '';
            searchInput.value = '';
            liveSearchResults.classList.add('hidden');
            liveSearchResults.innerHTML = '';
            searchSuggestions.classList.remove('hidden');
        }, 300);
    }

    if (searchClose) searchClose.addEventListener('click', closeSearch);
    if (searchBackdrop) searchBackdrop.addEventListener('click', closeSearch);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !searchOverlay.classList.contains('hidden')) closeSearch();
    });

    // Live Search
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                liveSearchResults.classList.add('hidden');
                liveSearchResults.innerHTML = '';
                searchSuggestions.classList.remove('hidden');
                searchLoading?.classList.add('hidden');
                return;
            }

            searchSuggestions.classList.add('hidden');
            searchLoading?.classList.remove('hidden');

            debounceTimer = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchLoading?.classList.add('hidden');
                        liveSearchResults.innerHTML = '';
                        liveSearchResults.classList.remove('hidden');
                        
                        if (data.length > 0) {
                            const header = document.createElement('div');
                            header.className = 'px-5 py-3 border-b border-zinc-800/50';
                            header.innerHTML = `<span class="text-[11px] font-medium text-zinc-500 uppercase tracking-[0.15em]">${data.length} Results</span>`;
                            liveSearchResults.appendChild(header);
                            
                            const resultsContainer = document.createElement('div');
                            resultsContainer.className = 'py-1';
                            
                            data.forEach(item => {
                                const iconBg = item.type === 'book' ? 'bg-indigo-500/10 text-indigo-400' : 
                                               item.type === 'paper' ? 'bg-teal-500/10 text-teal-400' : 'bg-rose-500/10 text-rose-400';
                                const icon = item.type === 'book' ? '📚' : item.type === 'paper' ? '📄' : '🎥';
                                
                                const a = document.createElement('a');
                                a.href = `view.php?token=${item.token}`;
                                a.className = 'w-full flex items-center gap-4 px-5 py-3 text-left hover:bg-white/5 transition-colors group';
                                a.innerHTML = `
                                    <span class="w-10 h-10 rounded-lg ${iconBg} flex items-center justify-center text-base">${icon}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-zinc-200 font-light truncate">${item.title}</p>
                                        <p class="text-xs text-zinc-500 truncate">${item.description || item.subject || ''}</p>
                                    </div>
                                    <span class="text-[10px] uppercase font-medium text-zinc-600 px-2 py-1 bg-zinc-800/50 rounded">${item.type}</span>
                                `;
                                resultsContainer.appendChild(a);
                            });
                            
                            liveSearchResults.appendChild(resultsContainer);
                        } else {
                            liveSearchResults.innerHTML = `
                                <div class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-zinc-800 flex items-center justify-center text-2xl">🔍</div>
                                    <p class="text-zinc-400 font-light">No results for "${query}"</p>
                                    <p class="text-xs text-zinc-600 mt-1">Try different keywords</p>
                                </div>
                            `;
                        }
                    })
                    .catch(err => {
                        searchLoading?.classList.add('hidden');
                        console.error('Search error:', err);
                        liveSearchResults.classList.remove('hidden');
                        liveSearchResults.innerHTML = `<div class="px-5 py-8 text-center text-red-400"><p>Error searching. Please try again.</p></div>`;
                    });
            }, 300);
        });
    }

    // Suggestion clicks
    document.querySelectorAll('.search-suggestion').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.querySelector('span.flex-1')?.textContent || btn.textContent.trim();
            searchInput.value = text;
            searchInput.dispatchEvent(new Event('input'));
        });
    });
</script>
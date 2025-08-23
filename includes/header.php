<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<header class="bg-white">
  <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between">
      <div class="md:flex md:items-center md:gap-12">
        <a class="block text-teal-600" href="index.php">
          <span class="sr-only">Home</span>
          <?php echo file_get_contents('assets/images/logo.svg'); ?>
        </a>
      </div>

      <div class="hidden md:block">
        <nav aria-label="Global">
          <ul class="flex items-center gap-6 text-sm">
            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="home.php"> Home </a>
            </li>
        
            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="about.php"> About </a>
            </li>

            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="books.php"> Books </a>
            </li>

            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="papers.php"> Papers </a>
            </li>

            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="videos.php"> Videos </a>
            </li>

            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="contact.php"> Contact </a>
            </li>

            <li>
              <a class="text-gray-500 transition hover:text-gray-500/75" href="request.php"> Request Material </a>
            </li>
          </ul>
        </nav>
      </div>

      <div class="flex items-center gap-4">
        <div class="sm:flex sm:gap-4">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow" href="dashboard.php">
              Dashboard
            </a>
            <a class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-medium text-teal-600" href="auth/logout.php">
              Logout
            </a>
          <?php else: ?>
            <a class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow" href="auth/login.php">
              Login
            </a>

            <div class="hidden sm:flex">
              <a class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-medium text-teal-600" href="auth/signup.php">
                Register
              </a>
            </div>
          <?php endif; ?>
        </div>

        <div class="block md:hidden">
          <button class="rounded bg-gray-100 p-2 text-gray-600 transition hover:text-gray-600/75">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>
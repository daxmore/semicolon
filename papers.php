<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Redirect admin users to the admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

$subjects = get_distinct_values('subject', 'papers');
$years = get_distinct_values('year', 'papers');

$subject = $_GET['subject'] ?? null;
$year = $_GET['year'] ?? null;

$papers = get_papers($subject, $year);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papers - Semicolon</title>
    <link href="assets/css/index.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold">Papers</h1>

        <form action="papers.php" method="get" class="mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <select name="subject" id="subject"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($subject === $s) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                    <select name="year" id="year"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo htmlspecialchars($y); ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>><?php echo htmlspecialchars($y); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit"
                    class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">Filter</button>
            </div>
        </form>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($papers as $paper): ?>
                <div class="block rounded-lg p-4 shadow-sm shadow-indigo-100">
                    <div class="mt-4">
                        <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($paper['title']); ?></h3>
                        <p class="mt-2 text-sm text-gray-500">Subject: <?php echo htmlspecialchars($paper['subject']); ?>
                        </p>
                        <p class="mt-2 text-sm text-gray-500">Year: <?php echo htmlspecialchars($paper['year']); ?></p>
                        <div class="mt-4">
                            <a href="download_paper.php?id=<?php echo $paper['id']; ?>"
                                class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Download</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>
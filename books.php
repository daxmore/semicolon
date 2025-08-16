<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$subjects = get_distinct_values('subject');
$semesters = get_distinct_values('semester');


$subject = $_GET['subject'] ?? null;
$semester = $_GET['semester'] ?? null;


$books = get_books($subject, $semester);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Semicolon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold">Books</h1>

        <form action="books.php" method="get" class="mt-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <select name="subject" id="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($subject === $s) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
                    <select name="semester" id="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All</option>
                        <?php foreach ($semesters as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($semester === $s) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
            </div>
            <div class="mt-4">
                <button type="submit" class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">Filter</button>
            </div>
        </form>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($books as $book): ?>
                <div class="block rounded-lg p-4 shadow-lg">
                    

                    <div class="mt-2">
                        <dl>
                            <div>
                                <dt class="sr-only">Title</dt>
                                <dd class="font-medium"><?php echo htmlspecialchars($book['title']); ?></dd>
                            </div>
                            <div>
                                <dt class="sr-only">Author</dt>
                                <dd class="text-sm text-gray-500"><?php echo htmlspecialchars($book['author']); ?></dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex items-center gap-8 text-xs">
                            <div class="sm:inline-flex sm:shrink-0 sm:items-center sm:gap-2">
                                <div class="mt-1.5 sm:mt-0">
                                    <p class="text-gray-500">Subject</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($book['subject']); ?></p>
                                </div>
                            </div>

                            <div class="sm:inline-flex sm:shrink-0 sm:items-center sm:gap-2">
                                <div class="mt-1.5 sm:mt-0">
                                    <p class="text-gray-500">Semester</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($book['semester']); ?></p>
                                </div>
                            </div>

                            
                        </div>
                        <div class="mt-4">
                            <a href="<?php echo htmlspecialchars($book['file_path']); ?>" class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow" download>Download</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
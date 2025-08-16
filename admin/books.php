<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle form submissions for CRUD operations
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $subject = $_POST['subject'];
    $semester = $_POST['semester'];
    $difficulty = $_POST['difficulty'];
    $file_path = $_POST['file_path'];
    $cover_image = $_POST['cover_image'];

    $sql = "INSERT INTO books (title, author, description, subject, semester, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $title, $author, $description, $subject, $semester, $file_path, $cover_image);
    $stmt->execute();
} elseif ($action === 'update') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $subject = $_POST['subject'];
    $semester = $_POST['semester'];
    $difficulty = $_POST['difficulty'];
    $file_path = $_POST['file_path'];
    $cover_image = $_POST['cover_image'];

    $sql = "UPDATE books SET title = ?, author = ?, description = ?, subject = ?, semester = ?, file_path = ?, cover_image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssi', $title, $author, $description, $subject, $semester, $file_path, $cover_image, $id);
    $stmt->execute();
} elseif ($action === 'delete') {
    $id = $_POST['id'];
    $sql = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

$books = get_books();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold">Manage Books</h1>

        <div class="mt-8">
            <h2 class="text-2xl font-bold">Add New Book</h2>
            <form action="books.php" method="post" class="mt-4">
                <input type="hidden" name="action" value="add">
                <!-- Add form fields here -->
            </form>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold">Existing Books</h2>
            <div class="mt-4">
                <input type="text" id="searchInput" onkeyup="searchBooks()" placeholder="Search for books.." class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="overflow-x-auto mt-4">
                <table id="booksTable" class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                    <!-- Table header -->
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <!-- Table data -->
                                <td class="whitespace-nowrap px-4 py-2">
                                    <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">Edit</a>
                                    <form action="books.php" method="post" class="inline-block js-confirm-delete">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function searchBooks() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("booksTable");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; // Search by title
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

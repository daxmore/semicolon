<?php
function get_distinct_values($column, $table = 'books')
{
    global $conn;
    $sql = "SELECT DISTINCT $column FROM $table";
    $result = $conn->query($sql);
    $values = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
    }
    return $values;
}

function get_books($subject = null, $semester = null)
{
    global $conn;
    $sql = "SELECT * FROM books WHERE 1=1";
    if ($subject) {
        $sql .= " AND subject = ?";
    }
    if ($semester) {
        $sql .= " AND semester = ?";
    }

    $stmt = $conn->prepare($sql);

    $types = '';
    $params = [];
    if ($subject) {
        $types .= 's';
        $params[] = &$subject;
    }
    if ($semester) {
        $types .= 's';
        $params[] = &$semester;
    }


    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    return $books;
}

function get_papers($subject = null, $year = null)
{
    global $conn;
    $sql = "SELECT * FROM papers WHERE 1=1";
    if ($subject) {
        $sql .= " AND subject = ?";
    }
    if ($year) {
        $sql .= " AND year = ?";
    }
    $stmt = $conn->prepare($sql);

    $types = '';
    $params = [];
    if ($subject) {
        $types .= 's';
        $params[] = &$subject;
    }
    if ($year) {
        $types .= 'i';
        $params[] = &$year;
    }

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $papers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $papers[] = $row;
        }
    }
    return $papers;
}

function get_youtube_id($url)
{
    $video_id = false;
    $url_parts = parse_url($url);
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query_params);
        if (isset($query_params['v'])) {
            $video_id = $query_params['v'];
        }
    }
    return $video_id;
}
function getUserByUsername($username)
{
    global $conn;
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createUser($username, $password)
{
    global $conn;
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $password);
    return $stmt->execute();
}

function get_count($table)
{
    global $conn;
    $sql = "SELECT COUNT(*) FROM " . $table;
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return $row[0];
    }
    return 0;
}
?>
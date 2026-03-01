<?php
$files = [
    'videos.php', 'request.php', 'profile.php', 'pricing.php',
    'papers.php', 'notifications.php', 'manage_posts.php',
    'index.php', 'history.php', 'dashboard.php',
    'community_post_detail.php', 'community_create.php',
    'community.php', 'books.php', 'admin/header.php', 'about.php', 'admin/index.php', 'admin/requests.php'
];
foreach($files as $file) {
    if(!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $changed = false;
    // Add darkMode: 'class'
    if(strpos($content, "tailwind.config = {") !== false && strpos($content, "darkMode: 'class'") === false) {
        $content = str_replace("tailwind.config = {", "tailwind.config = {\n            darkMode: 'class',", $content);
        $changed = true;
    }
    
    // Add theme.js
    if(strpos($content, "</head>") !== false && strpos($content, "theme.js") === false) {
        // use absolute path so we don't worry about base
        $content = str_replace("</head>", "    <script src=\"/Semicolon/assets/js/theme.js\"></script>\n</head>", $content);
        $changed = true;
    }
    
    if ($changed) {
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

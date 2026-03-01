<?php
$files = glob("*.php") + glob("includes/*.php") + glob("admin/*.php");

$replacements = [
    '/(?<=["\s])bg-\[\#FAFAFA\](?=[\s"])(?! dark:bg-zinc-950)/' => 'bg-[#FAFAFA] dark:bg-zinc-950',
    '/(?<=["\s])bg-white(?=[\s"])(?! dark:bg-zinc-900)/' => 'bg-white dark:bg-zinc-900',
    '/(?<=["\s])text-zinc-900(?=[\s"])(?! dark:text-white)/' => 'text-zinc-900 dark:text-white',
    '/(?<=["\s])text-zinc-800(?=[\s"])(?! dark:text-zinc-200)/' => 'text-zinc-800 dark:text-zinc-200',
    '/(?<=["\s])text-zinc-700(?=[\s"])(?! dark:text-zinc-300)/' => 'text-zinc-700 dark:text-zinc-300',
    '/(?<=["\s])text-zinc-600(?=[\s"])(?! dark:text-zinc-400)/' => 'text-zinc-600 dark:text-zinc-400',
    '/(?<=["\s])bg-zinc-50(?=[\s"])(?! dark:bg-zinc-800\/50)/' => 'bg-zinc-50 dark:bg-zinc-800/50',
    '/(?<=["\s])bg-zinc-100(?=[\s"])(?! dark:bg-zinc-800)/' => 'bg-zinc-100 dark:bg-zinc-800',
    '/(?<=["\s])border-zinc-100(?=[\s"])(?! dark:border-zinc-800)/' => 'border-zinc-100 dark:border-zinc-800',
    '/(?<=["\s])border-zinc-200(?=[\s"])(?! dark:border-zinc-700)/' => 'border-zinc-200 dark:border-zinc-700',
    '/(?<=["\s])bg-zinc-900(?=[\s"])(?! dark:bg-white)/' => 'bg-zinc-900 dark:bg-zinc-950', // Fixed intentional
];

foreach($files as $file) {
    if(is_dir($file)) continue;
    $original = file_get_contents($file);
    $content = $original;
    foreach($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // Add text color to the body so it cascades nicely if not already present
    $content = str_replace('<body class="antialiased bg-[#FAFAFA] dark:bg-zinc-950 dark:text-zinc-200">', '<body class="antialiased bg-[#FAFAFA] dark:bg-zinc-950 dark:text-zinc-200">', $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Themed $file\n";
    }
}

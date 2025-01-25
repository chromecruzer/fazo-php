<?php
$zip = new ZipArchive();
$zipFile = getcwd() . '/build/fazo.zip'; // Path to the zip file

if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
    exit("Cannot open <$zipFile>\n");
}

// Add index.php from the current directory
$zip->addFile(getcwd() . '/index.php', 'index.php');

// Add all files from the src directory
$srcDir = getcwd() . '/src';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isDir()) {
        // Add directories as well (preserving folder structure)
        $zip->addEmptyDir('src/' . $iterator->getSubPathName());
    } else {
        // Add files to the ZIP (preserving relative paths)
        $zip->addFile($file, 'src/' . $iterator->getSubPathName());
    }
}

$zip->close();
echo "Test ZIP file created successfully: $zipFile\n";

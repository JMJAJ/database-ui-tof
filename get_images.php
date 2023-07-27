<?php
function getImageData($dir) {
    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif'];
    $subfolders = [];
    $images = [];

    if (is_dir($dir)) {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $subfolders[] = $file;
                } else {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, $imageExtensions)) {
                        $images[] = $file;
                    }
                }
            }
        }
    }

    return ['subfolders' => $subfolders, 'images' => $images];
}

$imgDir = isset($_GET['folder']) ? $_GET['folder'] : 'UI';
$data = getImageData($imgDir);

header('Content-Type: application/json');
echo json_encode($data);
?>

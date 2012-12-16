<?php
require_once 'SubtitlesManager.php';

if (empty($argv[1])) {
    echo 'error! you must supply a file';
} else {
    if (is_file($argv[1])) {
        $manager = new OpenSubtitles\SubtitlesManager();
        $sub = $manager->getSubtitles($argv[1]);
        if (!empty($sub)) {
            echo $sub;
        } else {
            echo 'error! impossible to find the subtitle';
        }
    } else {
        echo 'error! file ' . $argv[1] . ' doesnt exist';
    }
}


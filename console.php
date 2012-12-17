<?php
/**
 * Example that use SubtitleManager in console
 *
 * @author César Rodríguez <kesarr@gmail.com>
 * @example php console.php <filepath>
 */

require_once 'SubtitlesManager.php';

if (empty($argv[1])) {
    echo 'error! you must supply a file';
} else {
    if (is_file($argv[1])) {
        $manager = new OpenSubtitles\SubtitlesManager();
        $sub = $manager->getSubtitleUrls($argv[1]);
        if (!empty($sub) && !empty($sub[0])) {
            $manager->downloadSubtitle($sub[0], $argv[1]);
        } else {
            echo 'error! impossible to find the subtitle';
        }
    } else {
        echo 'error! file ' . $argv[1] . ' doesnt exist';
    }
}


<?php
/**
 * Example that use SubtitleManager in console
 *
 * @author César Rodríguez <kesarr@gmail.com>
 * @example php console.php <filepath>
 */

use Symfony\Component\Yaml\Yaml;

require_once 'vendor/autoload.php';

if (empty($argv[1])) {
    echo 'error! you must supply a file';
    return 1;
} 
if (!is_file($argv[1])) {
    echo 'error! file ' . $argv[1] . ' doesnt exist';
    return 1;
}

$config = Yaml::parse(__DIR__ . '/config/configuration.yml.dist');
$manager = new OpenSubtitlesApi\SubtitlesManager($config['username'], $config['password'], $config['lang']);
$sub = $manager->getSubtitleUrls($argv[1]);
if (!empty($sub) && !empty($sub[0])) {
    $manager->downloadSubtitle($sub[0], $argv[1]);
} else {
    echo 'error! impossible to find the subtitle';
}


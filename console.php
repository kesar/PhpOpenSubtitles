<?php
/**
 * Example that use SubtitleManager in console
 *
 * @author  César Rodríguez <kesarr@gmail.com>
 * @example php console.php <filepath>
 */

use Symfony\Component\Yaml\Yaml;
use OpenSubtitlesApi\SubtitlesManager;
use OpenSubtitlesApi\FileGenerator;

require_once 'vendor/autoload.php';

$file = $argv[1];

if (empty($file)) {
    echo 'error! you must supply a file';

    return 1;
}
if (!is_file($file)) {
    echo 'error! file ' . $file . ' does not exist';

    return 1;
}

$config = Yaml::parse(__DIR__ . '/config/configuration.yml.dist');

if (empty($config)) {
    echo 'error! config file does not exist';

    return 1;
}

$manager   = new SubtitlesManager($config['username'], $config['password'], $config['language']);
$subtitles = $manager->get($file);

if (!empty($subtitles) && !empty($subtitles[0])) {
    $fileGenerator = new FileGenerator();
    $fileGenerator->downloadSubtitle($subtitles[0], $file);
} else {
    echo 'error! impossible to find the subtitle';
}

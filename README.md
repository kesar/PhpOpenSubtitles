PhpOpenSubtitles
=====

Library to connect with [opensubtitles.org](http://www.opensubtitles.org)

[![Build Status](https://travis-ci.org/kesar/PhpOpenSubtitles.png?branch=master)](https://travis-ci.org/kesar/PhpOpenSubtitles)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kesar/PhpOpenSubtitles/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kesar/PhpOpenSubtitles/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kesar/PhpOpenSubtitles/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kesar/PhpOpenSubtitles/?branch=master)

## Example

1. Update your config/configuration.yml.dist with login from [Open Subtitles](http://opensubtitles.org) 

2. run in console ``php console.php <filePathOfYourVideoFile>```


```php
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

$manager   = new SubtitlesManager($config['username'], $config['password'], $config['lang']);
$subtitles = $manager->get($file);

if (!empty($subtitles) && !empty($subtitles[0])) {
    $fileGenerator = new FileGenerator();
    $fileGenerator->downloadSubtitle($subtitles[0], $file);
} else {
    echo 'error! impossible to find the subtitle';
}
```

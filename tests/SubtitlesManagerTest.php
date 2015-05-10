<?php
namespace OpenSubtitlesApi\Tests;

use OpenSubtitlesApi\SubtitlesManager;

class SubtitlesManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubtitlesManager
     */
    protected $subtitleManager;

    protected function setUp()
    {
        $this->subtitleManager = new SubtitlesManager('username', 'password', 'SPA');
    }

    public function testGetSubtitles()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

<?php

namespace OpenSubtitlesApi;

class FileGenerator
{
    /**
     * Download subtitle and put it in the same folder than the video with the same name + srt
     *
     * @param string $url
     * @param string $originalFile
     */
    public function downloadSubtitle($url, $originalFile)
    {
        $subtitleFile    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $originalFile) . '.srt';
        $subtitleContent = gzdecode(file_get_contents($url));

        file_put_contents($subtitleFile, $subtitleContent);
    }
}

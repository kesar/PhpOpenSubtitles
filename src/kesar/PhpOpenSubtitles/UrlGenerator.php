<?php

namespace OpenSubtitlesApi;

class UrlGenerator
{
    /**
     * Retrieve the url of the first subtitle found
     *
     * @param array $subtitles
     * @param bool  $all
     *
     * @return array
     */
    public function getSubtitleUrls(array $subtitles, $all = false)
    {
        $subtitlesUrls = array();

        if (!empty($subtitles['data'])) {
            foreach ($subtitles['data'] as $sub) {
                if (!empty($sub['SubDownloadLink'])) {
                    $subtitlesUrls[] = $sub['SubDownloadLink'];
                    if ($all === false) {
                        return $subtitlesUrls;
                    }
                }
            }
        }

        return $subtitlesUrls;
    }
}

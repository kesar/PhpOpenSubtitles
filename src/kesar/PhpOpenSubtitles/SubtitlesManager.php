<?php

namespace OpenSubtitlesApi;

/**
 * Class to connect to OSDb and retrieve subtitles
 *
 * @author César Rodríguez <kesarr@gmail.com>
 */
class SubtitlesManager
{
    const SEARCH_URL = 'http://api.opensubtitles.org/xml-rpc';

    private $username;
    private $password;
    private $lang;
    private $userAgent;

    public function __construct($username, $password, $lang, $userAgent = 'OSTestUserAgent')
    {
        $this->username  = $username;
        $this->password  = $password;
        $this->lang      = $lang;
        $this->userAgent = $userAgent;
    }


    /**
     * Log in the OpenSubtitles.org API
     */
    private function logIn()
    {
        $request  = xmlrpc_encode_request(
            "LogIn",
            array($this->username, $this->password, $this->lang, $this->userAgent)
        );
        $context  = stream_context_create(
            array(
                'http' => array(
                    'method'  => "POST",
                    'header'  => "Content-Type: text/xml",
                    'content' => $request
                )
            )
        );
        $file     = file_get_contents(self::SEARCH_URL, false, $context);
        $response = xmlrpc_decode($file);
        if (($response && xmlrpc_is_fault($response))) {
            trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
        } else {
            if (empty($response['status']) || $response['status'] != '200 OK') {
                trigger_error('no login');
            } else {
                return $response['token'];
            }
        }

        return false;
    }

    /**
     * Search for a list of subtitles in opensubtitles.org
     *
     * @param string $userToken
     * @param string $movieToken
     * @param int    $filesize
     *
     * @return bool|array
     */
    private function searchSubtitles($userToken, $movieToken, $filesize)
    {
        $request  = xmlrpc_encode_request(
            "SearchSubtitles",
            array(
                $userToken,
                array(
                    array('sublanguageid' => $this->lang, 'moviehash' => $movieToken, 'moviebytesize' => $filesize)
                )
            )
        );
        $context  = stream_context_create(
            array(
                'http' => array(
                    'method'  => "POST",
                    'header'  => "Content-Type: text/xml",
                    'content' => $request
                )
            )
        );
        $file     = file_get_contents(self::SEARCH_URL, false, $context);
        $response = xmlrpc_decode($file);
        if (($response && xmlrpc_is_fault($response))) {
            trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
        } else {
            if (empty($response['status']) || $response['status'] != '200 OK') {
                trigger_error('no login');
            } else {
                return $response;
            }
        }

        return false;
    }

    /**
     * Retrieve the url of the first subtitle found
     *
     * @param      $file
     * @param bool $all
     *
     * @return array
     */
    public function getSubtitleUrls($file, $all = false)
    {
        $subtitlesUrls = array();

        if (!is_file($file)) {
            return $subtitlesUrls;
        }
        $userToken = $this->logIn();
        $hashGenerator = new HashGenerator();

        $fileHash  = $hashGenerator->getHashFromFile($file);

        $subtitles = $this->searchSubtitles($userToken, $fileHash, filesize($file));

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

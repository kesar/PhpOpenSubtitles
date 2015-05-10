<?php

namespace OpenSubtitlesApi;

/**
 * Class to connect to OSDb and retrieve subtitles
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
     * @param int    $fileSize
     *
     * @return bool|array
     */
    private function searchSubtitles($userToken, $movieToken, $fileSize)
    {
        $request  = xmlrpc_encode_request(
            "SearchSubtitles",
            array(
                $userToken,
                array(
                    array('sublanguageid' => $this->lang, 'moviehash' => $movieToken, 'moviebytesize' => $fileSize)
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

    public function get($file, $all = false)
    {
        $subtitlesUrls = array();

        if (!is_file($file)) {
            return $subtitlesUrls;
        }
        $userToken     = $this->logIn();
        $hashGenerator = new HashGenerator();

        $fileHash = $hashGenerator->getHashFromFile($file);

        $subtitles = $this->searchSubtitles($userToken, $fileHash, filesize($file));

        $urlGenerator = new UrlGenerator();

        return $urlGenerator->getSubtitleUrls($subtitles, $all);
    }
}

<?php
namespace OpenSubtitles;

/**
 * Class to connect to OSDb and retrieve subtitles
 *
 * @author César Rodríguez <kesarr@gmail.com>
 * @example php subtitles.php <path>
 */
class SubtitlesManager
{

    const SEARCH_URL = 'http://api.opensubtitles.org/xml-rpc';
    const LANGUAGE = 'spa';

    /**
     * Log in the opensubtitles.org API
     *
     * @param string $username
     * @param string $password
     * @param string $lang
     * @param string $userAgent
     * @return string|Boolean
     */
    private function logIn($username = '', $password = '', $lang = '', $userAgent = 'OS Test User Agent')
    {
        $request = xmlrpc_encode_request("LogIn", array($username, $password, $lang, $userAgent));
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => "POST",
                    'header' => "Content-Type: text/xml",
                    'content' => $request
                )
            )
        );
        $file = file_get_contents(self::SEARCH_URL, false, $context);
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
     * @param int $filesize
     * @return bool|array
     */
    private function searchSubtitles($userToken, $movieToken, $filesize)
    {
        $request = xmlrpc_encode_request(
            "SearchSubtitles",
            array(
                $userToken,
                array(
                    array('sublanguageid' => self::LANGUAGE, 'moviehash' => $movieToken, 'moviebytesize' => $filesize)
                )
            )
        );
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => "POST",
                    'header' => "Content-Type: text/xml",
                    'content' => $request
                )
            )
        );
        $file = file_get_contents(self::SEARCH_URL, false, $context);
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
     * @param $file
     * @return null|string
     */
    public function getSubtitles($file)
    {
        if (!is_file($file)) {
            return null;
        }
        $userToken = $this->logIn();
        $fileHash = $this->openSubtitlesHash($file);

        $subtitles = $this->searchSubtitles($userToken, $fileHash, filesize($file));
        if (!empty($subtitles['data'][0]['SubDownloadLink'])) {
            return $subtitles['data'][0]['SubDownloadLink'];
        }
        return null;
    }

    /**
     * Hash to send to opensubtitles
     *
     * @param string $file
     * @return string
     */
    private function openSubtitlesHash($file)
    {
        $handle = fopen($file, "rb");
        $fsize = filesize($file);

        $hash = array(
            3 => 0,
            2 => 0,
            1 => ($fsize >> 16) & 0xFFFF,
            0 => $fsize & 0xFFFF
        );

        for ($i = 0; $i < 8192; $i++) {
            $tmp = $this->readUINT64($handle);
            $hash = $this->addUINT64($hash, $tmp);
        }

        $offset = $fsize - 65536;
        fseek($handle, $offset > 0 ? $offset : 0, SEEK_SET);

        for ($i = 0; $i < 8192; $i++) {
            $tmp = $this->readUINT64($handle);
            $hash = $this->addUINT64($hash, $tmp);
        }

        fclose($handle);
        return $this->uINT64FormatHex($hash);
    }

    private function readUINT64($handle)
    {
        $u = unpack("va/vb/vc/vd", fread($handle, 8));
        return array(0 => $u["a"], 1 => $u["b"], 2 => $u["c"], 3 => $u["d"]);
    }

    private function addUINT64($a, $b)
    {
        $o = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);

        $carry = 0;
        for ($i = 0; $i < 4; $i++) {
            if (($a[$i] + $b[$i] + $carry) > 0xffff) {
                $o[$i] += ($a[$i] + $b[$i] + $carry) & 0xffff;
                $carry = 1;
            } else {
                $o[$i] += ($a[$i] + $b[$i] + $carry);
                $carry = 0;
            }
        }
        return $o;
    }

    private function uINT64FormatHex($n)
    {
        return sprintf("%04x%04x%04x%04x", $n[3], $n[2], $n[1], $n[0]);
    }

}


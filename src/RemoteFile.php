<?php
namespace MichaelDrennen\RemoteFile;

class RemoteFile {

    /**
     * Given a remote URL, this function will return the file size in bytes.
     * @param string $url
     * @return int
     */
    public static function getFileSize(string $url) : int {
        // Assume failure.
        $result = -1;

        $curl = curl_init( $url );

        // Issue a HEAD request and follow any redirects.
        curl_setopt( $curl, CURLOPT_NOBODY, true );
        curl_setopt( $curl, CURLOPT_HEADER, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );

        $data = curl_exec( $curl );
        curl_close( $curl );

        if( $data ) {
            $content_length = "unknown";
            $status = "unknown";

            if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
                $status = (int)$matches[1];
            }

            if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
                $content_length = (int)$matches[1];
            }

            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if( $status == 200 || ($status > 300 && $status <= 308) ) {
                $result = $content_length;
            }
        }

        return (int)$result;
    }

    /**
     * Gets the remote file's size, but formats the bytes into a human readable string.
     * @param string $url
     * @param int $humanReadableDecimals
     * @return string
     */
    public static function getHumanReadableFileSize(string $url, int $humanReadableDecimals=2) : string {
        $bytes = self::getFileSize($url);
        return self::humanFileSize($bytes, $humanReadableDecimals);
    }

    /**
     * Based on code written by https://github.com/iamamused
     * Given an integer number of bytes, return a formatted string that is more easily read by a human.
     * @param int $bytes Number of bytes to be converted. 
     * @param int $decimals Precision of the return value. 2 decimals is arbitrarily set as default.
     * @return string The file size in a human readable format.
     */
    public static function humanFileSize(int $bytes, int $decimals = 2) : string {
        $size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
    
}
<?php

class url
{
    /**
     *  Morphs together a URL to send to
     * @param string $url
     */
    public static function to( $url, $https = null, $local = true )
    {
        $base_url = DIRECTORY;

        if (!$local) {
            if( $https )
                $base_url = 'https://';
            else
                $base_url = 'http://';

            $base_url .= $_SERVER['HTTP_HOST'].DIRECTORY;
        }

        $url = $base_url.$url;

        return $url;
    }

}

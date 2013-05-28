<?php

class cacher
{
    private $_contents;

    private $_file_name;

    private $_cache_time;

    private $_use_json = FALSE;

    /**
     * Sets $_use_json to TRUE
     * This is if the cached data is in array format.
     */
    public function use_json ()
    {
        $this->_use_json = TRUE;

        return $this;
    }

    /**
     * Sets the content that is to be cached
     * @param mixed $data - the data to be set. Can be an array ( if use_json is set ) or plain
     */
    public function setContent ( $data )
    {
        // If $_use_json has been set the content is encoded.
        if ($this->_use_json == TRUE) {
            $data = json_encode( $data );
        }

        $this->_contents = $data;

        return $this;
    }

    /**
     * Returns the cached content
     * @return $data;
     */
    public function getContent ()
    {
        $data = $this->_contents;

        // Decode the contents if $_use_json is set.
        if ($this->_use_json) {
            $data = json_decode( $this->_contents );
        }

        return $data;
    }

    /**
     * Set the file name of the file for caching.
     * @param string $filename - the filename
     */
    public function setFileName ( $filename )
    {
        if (!!$filename) {
            $this->_file_name = $filename;

            return $this;
        }
    }

    /**
     * Sets the cache time of the file
     * @param int $time - the time the file is to be cached for
     */
    public function setCacheTime ( $time )
    {
        if ( !!$time && is_numeric( $time ) ) {
            $this->_cache_time = $time;

            return $this;
        }
    }

    /**
     * Deletes the cache file.
     */
    public function deleteCache ()
    {
        unlink ( PATH.'cache/' .$this->_file_name .'.cache.txt' );
    }

    /**
     * Caches the file
     * @param int    $destination - the number of the item within the look up list
     * @param string $filename    - the cached file name
     */
    public function cacheFile ()
    {
        // Can not cache a file unless all 3 properties have been set
        if (!!$this->_file_name && !!$this->_contents && !!$this->_cache_time) {
            $file_name = PATH.'cache/' .$this->_file_name .'.cache.txt';

            if ( file_exists( $file_name ) && ( time() - $this->_cache_time > filemtime( $file_name ) ) ) {
                $contents = file_get_contents ( $file_name );
            } else {
                $create_file = fopen ( $file_name, 'w' );

                file_put_contents( $file_name, $this->_contents );

                fclose( $create_file );

                $contents = file_get_contents ( $file_name );
            }

            $this->_contents = $contents;

            return $this;
         } else {
             throw new \Exception ( 'To cache a file please ensure that all file name, contents and cache time have indeed been set.' );
         }
    }
}

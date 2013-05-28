<?php

class upload_images
{
    private $_files;
    private $_image_count;

    public function save_loop ()
    {
        for ($i = 0; $i < $this->_image_count; $i++) {
            if ($this->_files[ 'error' ][ $i ] == 0) {
                if (IP == '192.168.0.44:8888') {
                    $simple_image = new simple_image ();
                    $simple_image->load ( $this->_files[ 'tmp_name' ][ $i ] );
                    $simple_image->resizeToWidth ( 100 );
                    $simple_image->save ( PATH . 'assets/uploads/images/' . $random_name . '.' . $simple_image->image_ext );

                    $simple_image = new simple_image ();
                    $simple_image->load ( $this->_files[ 'tmp_name' ][ $i ] );
                    $simple_image->resizeToWidth ( 182 );
                    $simple_image->save ( PATH . 'assets/uploads/images/182-182/' . $random_name . '.' . $simple_image->image_ext );
                } else {

                }

            }
        }
    }
}

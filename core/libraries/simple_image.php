<?php
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class Simple_image
{
   public $image;
   public $image_type;
   public $image_cropped;
   public $resized;

   /**
    * This method is to load a image to a property based on its type
    *
    * @param string $filename
    *
    * @access public
    */
   public function load ( $filename )
   {
      $image_info = getimagesize($filename);

      $this->image_type = $image_info[2];

      if( $this->image_type == IMAGETYPE_JPEG )
         $this->image = imagecreatefromjpeg($filename);

      elseif( $this->image_type == IMAGETYPE_GIF )
         $this->image = imagecreatefromgif($filename);

      elseif( $this->image_type == IMAGETYPE_PNG )
         $this->image = imagecreatefrompng($filename);
   }

   /**
    * Method to save the loaded image to the directory of choice
    *
    * @param string $filename
    * @param constant $image_type
    * @param int $compression
    * @param int $permissions
    *
    * @access public
    */
   public function save ( $filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null )
   {
      if ( $image_type == IMAGETYPE_JPEG )
         imagejpeg ( $this->image, $filename, $compression );

      elseif ( $image_type == IMAGETYPE_GIF )
         imagegif ($this->image, $filename);

      elseif ( $image_type == IMAGETYPE_PNG )
         imagepng ($this->image, $filename);

      if ( $permissions != null )
         chmod ( $filename, $permissions );
   }

   /**
    * Method to out the loaded image
    *
    * @param constant $image_type
    *
    * @access public
    */
   public function output ( $image_type = IMAGETYPE_JPEG )
   {
      if ( $image_type == IMAGETYPE_JPEG )
         imagejpeg ( $this->image );

      elseif ( $image_type == IMAGETYPE_GIF )
         imagegif ( $this->image );

      elseif ( $image_type == IMAGETYPE_PNG )
         imagepng ( $this->image );
   }

   /**
    * Method to get the width of the loaded image
    *
    * @return int width
    *
    * @access public
    */
   public function getWidth ()
   {
      return imagesx ( $this->image );
   }

   /**
    * Method to return the height of the currently loaded image
    *
    * @return int height
    *
    * @access public
    */
   public function getHeight ()
   {
      return imagesy ( $this->image );
   }

   /**
    * Method to resize to height while maintaining aspect ratio
    *
    * @param int $height
    *
    * @access public
    */
   public function resizeToHeight ( $height )
   {
      $ratio = $height / $this->getHeight ();
      $width = $this->getWidth () * $ratio;
      $this->resize ( $width, $height );
   }

   /**
    * Method to resize to width while maintaining aspect ratio
    *
    * @param int $width
    *
    * @access public
    */
   public function resizeToWidth ( $width )
   {
      $ratio = $width / $this->getWidth ();
      $height = $this->getheight () * $ratio;
      $this->resize ( $width, $height );
   }

   /**
    * Method to scale a image based on a percentage
    *
    * @param int $scale
    *
    * @access public
    */
   public function scale ( $scale )
   {
      $width = $this->getWidth () * $scale / 100;
      $height = $this->getheight () * $scale / 100;
      $this->resize ( $width, $height );
   }

   /**
    * Method to resize a image based on a height and width
    *
    * @param int $width
    * @param int $height
    *
    * @access private
    */
   private function resize ( $width, $height )
   {
      $new_image = imagecreatetruecolor ( $width, $height );
      imagecopyresampled ( $new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth (), $this->getHeight () );
      $this->image = $new_image;
      $this->resized = 1;
   }

   /**
    * Method to first resize then crop a image based on a width and height
    * So i need to resize it first then crop it to get the correct image size and maintain aspect ratio
    *
    * @param int $width
    * @param int $height
    *
    * @return $this->image
    *
    * @author Dave Jones
    */
   public function resize_crop ( $width = 0, $height = 0 )
   {

          if ( ( $this->getWidth () < 250 && $this->getHeight () < 250 ) || ( $width < $height ) ) {
              $this->resizeToHeight ( $height );
          }

          if ( ( $this->getWidth () < 250 && $this->getHeight () < 250 ) && ( $width > $height ) ) {
              $this->resizeToHeight ( $height );
          }

          if ( ( $width == 85 && $height == 82 ) && ( $this->getWidth () != $this->getHeight () ) ) {
              $this->resizeToHeight( $height );
          }

          if ( ( $this->getWidth () < $this->getHeight () ) ) {
              $this->resizeToWidth( $width );
          }

          if ( ( $width < 100 && $height < 100 ) && ( $this->getWidth () < $this->getHeight () ) ) {
              $this->resizeToWidth ( $width );
          }

          if ($width == 340 && $height == 230) {
              $this->resizeToHeight ( $height );
          }

          if ($this->resized == 0) {
              $this->resizeToWidth( $width );
          }

       $this->crop ( $width, $height );

   }

   /**
    * Method to crop a image based on the desired width and height
    *
    * @param int $width
    * @param int $height
    *
    * @return $this->image
    *
    * @author Dave Jones
    */
   private function crop ( $width = 0, $height = 0 )
   {
         $x = ( $this->getWidth () - $width ) / 2;
         $y = ( $this->getHeight () - $height ) / 2;

         $cropped_image = imagecreatetruecolor($width, $height);
         imagecopy ( $cropped_image, $this->image, 0, 0, $x, $y, $this->getWidth (), $this->getHeight () );

         $this->image = $cropped_image;
   }

}

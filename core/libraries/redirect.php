<?php

class redirect
{
    private static $_url;
    private static $_https = null;

    public static function to($url, $https = null)
    {
        self::$_url = $url;
        self::$_https = $https;

        return new Redirect();
    }

    public static function compose()
    {
        header('location:'.URL::to( self::$_url, self::$_https) );
    }

    public function with( $name, $message )
    {
        Sessions::create( $name, $message );

        self::compose();
    }

}

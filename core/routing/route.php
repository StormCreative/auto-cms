<?php

class route
{
    /**
     * Routes a controller
     */
    public function get ()
    {
        // Load router and get controller,method and value
        $router = new Router();

        $uri = $router->map()->get();

        $controller = $uri['controller'];
        $method = $uri['method'];

        $path = PATH.$uri['directory'].$controller.'.php';

        // Will later clear this conditonal up
        if ( file_exists( $path ) ) {
            require_once( $path );

            $con = new $controller();

            // Default to index if we have a question mark matched
            if ( !isset ( $method ) || $method == '' || $method == $controller || preg_match('/\?/', $method) )
                $method = 'index';

            // Load the method and the value if one is set
            if( $con->{$method}( (!!$uri['else'] ? $uri['else'] : '') ))

                return TRUE;
        } else {
            throw new Exception( 'Can not find specified controller to load' );
        }
    }

}

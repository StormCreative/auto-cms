<?php

class router
{
    public $uri = array();

    public $path;

    public function __Construct()
    {
        // Remove the directory from the URI - so we do not have to set specific points for controller/model/vie
        $uri = str_replace ( DIRECTORY, '', $_SERVER['REQUEST_URI'] );

        $uri = preg_split ( '[\\/]', $uri, -1, PREG_SPLIT_NO_EMPTY );

        if ( count ( $uri ) > 0 )
            $this->uri = $uri;
    }

    public function map()
    {
        $routes = json_decode( file_get_contents(PATH.'core/settings/routes.json') );
        $path = implode('/', $this->uri);

        // Explode by question mark to stop query string from breaking the route
        $cleanup = explode('?', $path);
        $path = $cleanup[0];

        if ($path != '') {
            if (!!$routes[0]->{$path}) {
                $path = $routes[0]->{$path};
            }
        }

        $this->path = $path;

        return $this;
    }

    public function get()
    {
        $path = $this->path;

        $segments = explode('/', $path);

        $uri = array();
        $uri['directory'] = 'app/controllers/';

        $up = 0;

        if ($segments[0] == 'admin') {
            unset($segments[0]);
            $uri['directory'] = '_admin/controllers/';
            $up = 1;
        }

        $uri['controller'] = $segments[0+$up];

        if( $uri['controller'] == '' )
            $uri['controller'] = 'home';

        $uri['method'] = $segments[1+$up];

        unset( $segments[0+$up] );
        unset( $segments[1+$up] );

        $uri['else'] = implode(',', $segments );

        return $uri;
    }
}

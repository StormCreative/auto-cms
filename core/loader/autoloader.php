<?php
class autoloader
{
    private static $_instance;

    public static function autoload ( $class )
    {

        if (!self::$_instance) {
            try {

                $class = strtolower ( $class );
                //Models are in their own folder so we need to deal with that
                $class_actual = str_replace ( "_model", "", $class );

                if ( file_exists ( PATH.'core/' . $class . '.php' ) ) {
                    include PATH.'core/' . $class . '.php';
                }

                // Controllers always have uppercase
                elseif ( file_exists ( PATH.'app/controllers/' .  $class . '.php' ) )
                    include PATH.'app/controllers/' . $class . '.php';

                elseif ( file_exists ( PATH.'app/models/' . $class_actual . '/' . $class . '.php' ) )
                    include PATH.'app/models/' . $class_actual . '/' .  $class  . '.php';

                elseif ( file_exists ( PATH.'core/site/' . strtolower ( substr ( $class, 0, 1 ) ).substr ( $class, 1 ) . '.php' ) )
                    include PATH.'core/site/' . strtolower ( substr ( $class, 0, 1 ) ).substr ( $class, 1 ) . '.php';

                elseif ( file_exists ( PATH.'core/database/' . $class . '.php' ) )
                    include PATH.'core/database/' . $class . '.php';

                elseif ( file_exists ( PATH.'core/libraries/' . $class . '.php' ) )
                    include PATH.'core/libraries/' . $class . '.php';

                elseif ( file_exists ( PATH.'core/routing/' . $class . '.php' ) )
                    include PATH.'core/routing/' . $class . '.php';

                elseif ( file_exists ( PATH.'core/database/connection/' . $class . '.php' ) )
                    include PATH.'core/database/connection/' . $class . '.php';

                elseif ( file_exists ( PATH.'core/site/interfaces/' . $class . '.php' ) )
                    include PATH.'core/site/interfaces/' . $class . '.php';

                elseif ( file_exists ( PATH.'core/helpers/' . $class . '.php' ) )
                    include PATH.'core/helpers/' . $class . '.php';

                elseif ( file_exists ( PATH.'_admin/' . $class . '.php' ) )
                    include PATH.'_admin/' . $class . '.php';

                elseif ( file_exists ( PATH.'_admin/controllers/' . $class . '.php' ) )
                    include PATH.'_admin/controllers/' . $class . '.php';

                spl_autoload_extensions ( '.php' );
                spl_autoload_register ( array ( __CLASS__, 'autoload' ) );
            } catch ( PDOException $e ) {
                die ( $e->getMessage () );
            }
        }

        return self::$_instance;
    }
}

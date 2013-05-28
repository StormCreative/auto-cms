<?php

class listing extends Application_controller
{
    /**
     * Easily list any given table and columns
     */
    public function table( $table="" )
    {
        $binds = array();

        $model = $table.'_model';
        $model = new $model();

        $model->columns( 'id' );

        if (!!$_GET['columns']) {
            $columns = $_GET['columns'];
            // Why the hell is a column 'approved' being pushed for everything?!?!
            // Not all tables are going to have this and it just leads to isntant errors
            array_push ( $columns, 'approved' );

            $columns = $columns.',create_date';

        } else
            $columns = 'title,create_date';

        $model->columns( $columns );

        // Filter if post is set
        if ( post_set() ) {
            foreach ($_POST['search'] as $key => $value) {
                if (!!$value) {
                    $model->where( $key .' LIKE :'. $key );
                    $binds[ $key ] = '%'.$value.'%';
                }
            }
        }

        $data = $model->all( $binds );

        //We dont actually want a approved column so we need to unset it from the columns array before we explode it
        $x_columns = explode ( ',', $columns );
        // Why the hell is this unsetting the second columna and ALWAYS assuming there is an approved?
        // What if you want multiple columns they just get deleted and screw the entire table up...
        // When this is used something better needs to be thought up than just assuming you can unset the second value...!!!!!!!
        //unset( $x_columns[ 2 ] );

        $this->addTag('columns', $x_columns);
        $this->addTag('data', $data);
        $this->addTag('table', $table);

        $this->setView( 'listing/index' );

        $this->setScript ( 'listing' );
        $this->addStyle ( 'listing' );
        $this->addStyle ( 'jqueryui' );
    }

}

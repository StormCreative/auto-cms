<?php

class AJAX_listing
{
    public function approve ()
    {
        if (!!$_POST) {
            $name = $_POST[ 'table' ] . '_model';
            $model = new $name();

            if ( !!$model->save ( array ( 'id' => $_POST[ 'id' ], 'approved' => $_POST[ 'approve' ] ), FALSE ) )
                $return = array( 'done', 'approved' => $model->_data[ 'approved' ] );
            else
                $return = array( 'not done' );
        }

        die( json_encode( $return ) );
    }

    public function delete ()
    {
        if (!!$_POST) {
            $name = $_POST[ 'table' ] . '_model';
            $model = new $name();

            if ( $model->delete ( $_POST[ 'ids' ] ) )
                $return = array( 'deleted' );
            else
                $return = array( 'not deleted' );
        }

        die( json_encode( $return ) );
    }

    public function filter ()
    {
        if (!!$_POST) {
            $name = $_POST[ 'table' ] . '_model';
            $model = new $name();

            //Build where
            $where = array ();
            $values = array ();

            foreach ($_POST[ 'params' ] as $param) {
                if (!!$param[ 'value' ]) {
                    if ( strstr( $param[ 'type' ], 'id', true ) ) {
                        $where[] = $param[ 'type' ] . ' = :id';
                        $values[ 'id' ] = $param[ 'value' ];
                    } else {
                        $where[] = $param[ 'type' ] . ' LIKE :' . $param[ 'type' ];

                        if ( $param[ 'type' ] == 'create_date' )
                            $values[ $param[ 'type' ] ] = '%' . date ( "Y-m-d", strtotime ( $param[ 'value' ] ) ) . '%';

                        else
                            $values[ $param[ 'type' ] ] = '%' . $param[ 'value' ] . '%';
                    }
                }
            }

            $built_where = implode ( ' AND ', $where );
            $data = $model->where ( $built_where )->all( $values );

            if ( !!$data )
                $return = array( 'status' => '200', 'data' => $data );
            else
                $return = array( 'status' => 'empty', 'msg' => 'There were no results for that particular search' );
        }

        die( json_encode( $return ) );
    }
}

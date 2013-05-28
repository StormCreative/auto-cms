<?php

class table
{
    public $operations;

    public $query;

    public $table;

    private static $_instance;

    public static function load( $table )
    {
        return new Table( $table );
    }

    private function __Construct( $class_name )
    {
        $this->operations = new operations();
        $this->query = new query();

        $table = $class_name;

        $this->operations->table( $class_name );
        $this->table = $table;
    }

    public function find( $options )
    {
        $columns = '*';

        if( !!$options['columns'] )
            $columns = $options['columns'];

        $sql = 'SELECT '.$columns.' FROM '.$this->table;

        if ( isset($options['joins']) ) {
            $sql .= ' '. $options['joins'];
        }

        if ( isset( $options['where'] ) ) {
            $sql .= ' WHERE '.implode( ' AND ', $options['where'] );
            $binds = $options['binds'];
        }

        if (!!$options['order_by']) {
            $sql = $sql. 'ORDER BY '.$options['order_by'];
        }

        if (!!$options['group']) {
            $sql .= ' '.$options['group'];
        }

        $method = 'getObj';

        if (!!$options['all']) {
            $method = 'getAssoc';
        } 
        
        $output = $this->query->{$method}( $sql, $binds );

        return $output;
    }

    public function columns( $table="" )
    {
        if( !!$table && $table != "" )
            $table = $table;
        else
            $table = $this->table;

        return $this->query->describe_table( $table );
    }

    public function insert( $data )
    {
        $output = $this->operations->insert( $data );

        return $output;
    }

    public function update( $data, $id )
    {
        $output = $this->operations->update( $data, $id );

        return $output;
    }

}

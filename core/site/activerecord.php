<?php

//Had to put this in for the unit tests because they were failing because they couldnt pick up a helper function
if(php_sapi_name() == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
    include 'core/helpers/helpers.php';
}

abstract class activerecord
{
    public $attributes = array();

    public $errors = array();

    protected $_rules = array();

    protected $_take_over = true;

    protected $_columns = array();

    private $_assoc_data = array();

    protected $_has_image = FALSE;
    protected $_has_upload = FALSE;

    protected $_order_by;

    private $_where;

    public $has_many;

    public $validates;

    public $has_one;

    /**
     * Constructs the model pretty much!
     *
     * @param array $attribues - array of attributes to save - can take in an assoc array
     * for passing in the association tables data to save too
     * @param bool/optional $takeover - if parameters are set it will say whether to reset the data on the first find
     * this is useful so that the attributes passed in are not overwritten by the contents of the find
     */
    public function __Construct($attributes = array(), $takeover = false)
    {
        if ( count( $attributes ) > 0 ) {
            $this->set_attributes( $attributes );
            $this->_take_over = $takeover;
        }

        $this->set_up_source_columns();
    }

    /**
     * Will assign the given attributes to the class
     *
     * @param $attributes array - the array of attributes key => value
     * @return $this
     */
    private function set_attributes( $attributes )
    {
        if ($this->_take_over) {
            foreach ($attributes as $column => $value) {
                $this->attributes[ $column ] = $value;
            }
        } else {
            $this->attributes['id'] = $attributes->id;
        }

        return $this;
    }

    /**
     * Method to build up the where clause
     *
     * @param string $where - the string like " gary = :gary " for binded values
     * @return $this;
     */
    public function where( $where )
    {
        $this->_where[] = $where;

        return $this;
    }

    public function order_by( $column, $type = "DESC" )
    {
        if ($type == false) {
            $this->_order_by = $column;
        } else {
            $this->_order_by = $this->full_table().'.'.$column.' '.$type;
        }

        return $this;
    }

    /**
     * Find a row of data - returns all associated table data
     *
     * @params infinte - 1 -> value, 2 -> column
     * @return $this
     */
    public function find( /* multiple arguments */ )
    {
        $args = func_get_args();

        $column = 'id';

        if( !!$args[1] )
            $column = $args[1];

        if ( count( $this->_where ) > 0 ) {
            $options['where'] = $this->_where;
            $options['binds'] = $args[0];
        } else {
            $options['where'] = array( $this->full_table().'.'.$column .' = :'.$column );
            $options['binds'] = array( $column => $args[0] );
        }

        $options['joins'] = $this->build_up_has_many_association();
        $has_one = $this->build_has_one_association();

        if (!!$has_one) {
            $options['joins'] .= ' '. $has_one;
        }

        $options['columns'] = implode( ', ', $this->_columns );
        $options['order_by'] = $this->_order_by;

        if ($this->_has_image) {
            $image_column = ', (SELECT imgname FROM ' . DB_SUFFIX . '_image WHERE id = image_id) as image';

            $options['columns'] .= $image_column;
        }

        if ( $this->_has_upload ) {
            $upload_columns = ', ( SELECT name FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_name
                               , ( SELECT title FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_title';

            $options[ 'columns' ] .= $upload_columns;
        }   

        $output = $this->table()->find( $options );

        if ($output != false) {
            $this->set_attributes( $output );
            $this->build_up_has_many_association_find();
        }

        return $this;
    }

    /**
     * Gets an associative array of data rather than one row
     *
     * @params array $binds - if there a where clause the binded values
     * @return bool/array - the outcome of the find
     */
    public function all( $binds = array(), $joins = TRUE )
    {
        $options['joins'] = $this->build_up_has_many_association();

        $has_one = $this->build_has_one_association();

        if (!!$has_one) {
            $options['joins'] .= ' '. $has_one;
        }

        if (!$joins) {
            $options['group'] = 'GROUP BY '.$this->full_table().'.id';
        }

        $options['columns'] = implode( ', ', $this->_columns );

        if ($this->_has_image) {
            $image_column = ', (SELECT imgname FROM ' . DB_SUFFIX . '_image WHERE id = image_id) as image';

            $options['columns'] .= $image_column;
        }

        if ( $this->_has_upload ) {
            $upload_columns = ', ( SELECT name FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_name
                               , ( SELECT title FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_title';

            $options[ 'columns' ] .= $upload_columns;
        }

        $options['all'] = true;
        $options['binds'] = $binds;
        $options['order_by'] = $this->_order_by;

        if( count( $this->_where ) > 0 )
            $options['where'] = $this->_where;

        $output = $this->table()->find( $options );

        if ( count( $output ) > 0 ) {
            $this->attributes['data'] = $output;

            return $output;
        } else

            return false;
    }

    /**
     * Sets the main tables columns for any queries
     *
     * @return $this;
     */
    private function set_up_source_columns()
    {
        foreach ( $this->table()->columns($this->full_table()) as $column ) {
            if( $column != "" )
                $this->_columns[] = $this->full_table().'.'.$column;
        }

        return $this;
    }

    /**
     * Builds up the additional JOIN data for a get query
     *
     * @return string $joins - the string of additional joins
     */
    private function build_up_has_many_association()
    {
        if ( property_exists( get_class($this), 'has_many' ) ) {
            $assocs = $this->has_many;
            $joins = array();

            foreach ($assocs as $assoc) {
                $match_column = $source.'_id';
                $second_match = 'id';

                // See if the search column has been set
                $split = explode(":", $assoc);

                // If it has the match columns change to that of the set column
                if ( count( $split ) > 0 ) {
                    $match_column = $split[1];
                    $second_match = $match_column;
                    $assoc = $split[0];
                }

                $table = strtolower(DB_SUFFIX.'_'.$assoc);
                $source = strtolower($this->clean_table());

                $joins[] = 'LEFT JOIN '.$table.' ON '.$table.'.'.$match_column.' = '.DB_SUFFIX.'_'.$source.'.'.$second_match;

                $columns = $this->table()->columns(DB_SUFFIX.'_'.$assoc);

                foreach ($columns as $col) {
                    $this->_columns[] = DB_SUFFIX.'_'.$assoc.'.'.$col.' as '.$assoc.'_'.$col;
                }

                /*
                $mod = $assoc.'_model';
                $mod = new $mod();

                $mod->where( $match_column.' = :'.$second_match );
                die ( print_r ( $this ));
                $this->attributes[$assoc] = $mod->all( array( $second_match => $this->{$second_match} ) );
                */
            }

            $joins = implode( ' ', $joins );

            return $joins;
        }
    }

    /**
     * Builds up the additional JOIN data for a get query
     *
     * @return string $joins - the string of additional joins
     */
    private function build_up_has_many_association_find()
    {
        if ( property_exists( get_class($this), 'has_many' ) ) {
            $assocs = $this->has_many;
            $joins = array();

            foreach ($assocs as $assoc) {
                $match_column = $source.'_id';
                $second_match = 'id';

                // See if the search column has been set
                $split = explode(":", $assoc);

                // If it has the match columns change to that of the set column
                if ( count( $split ) > 0 ) {
                    $match_column = $split[1];
                    $second_match = $match_column;
                    $assoc = $split[0];
                }

                $table = strtolower(DB_SUFFIX.'_'.$assoc);
                $source = strtolower($this->clean_table());

                $mod = $assoc.'_model';
                $mod = new $mod();

                $mod->where( $match_column.' = :'.$second_match );

                $this->attributes[$assoc] = $mod->all( array( $second_match => $this->{$second_match} ) );
            }

            $joins = implode( ' ', $joins );

            return $joins;
        }
    }

    /**
     * Builds up the JOINS for a has_one association
     * The difference here is that the query looks for a match
     * within the id of the main tables column to an id in the target table
     *
     * @return string $joins
     */
    public function build_has_one_association()
    {
        if ( property_exists( get_class($this), 'has_one' ) ) {
            $has_one = $this->has_one;

            $joins = array();

            foreach ($has_one as $one) {
                $table = strtolower(DB_SUFFIX.'_'.$one);
                $source = DB_SUFFIX.'_'.strtolower($this->clean_table());

                $clean_one = str_replace( DB_SUFFIX .'_', '', $table );

                $joins[] = 'LEFT JOIN '.$table.' ON '.$source.'.'.$clean_one.'_id = '.$table.'.id';

                $columns = $this->table()->columns($table);

                foreach ($columns as $col) {
                    $this->_columns[] = $table.'.'.$col.' as '.$assoc.'_'.$col;
                }
            }
        }

        $joins = implode( ' ', $joins );

        return $joins;
    }

     /**
     * Handles the inserting/updating of a record
     *
     * @return bool/int - the output of the insert of update method
     */
    public function save( $attributes = array(), $validate = true )
    {

        if ( count( $attributes ) > 0 ) {
            $this->set_attributes( $attributes );
            $this->_take_over = true;
        }

        $valid = true;

        if ($validate) {
            $valid = $this->validate_attributes_to_save();
        }

        $method = 'insert';

        if (!!$this->attributes['id']) {
            $method = 'update';
        }

        if ($valid) {
            $output = $this->{$method}();
        }

        if ($output == NULL) {
            $output = false;
        } else
            $output = true;

        return $output;
    }

    /**
     * Arranges all the data to be saved - by cross referencing the table columns
     * to those set within the attributes array
     *
     * @param  optional string $table - sort a specific tables data
     * @return array           $data
     */
    public function cleanup_save_data( $table = "" )
    {
        $assocs = $this->has_many;

        if ( property_exists(get_class($this), 'has_one') ) {
            $has_one = $this->has_one;
        }

        if ( !isset( $table ) ) {
            $table = $this->full_table();
        } else {
            $table = $table;
        }

        $columns = $this->table()->columns( $table );

        $data = array();

        foreach ($this->attributes as $att => $value) {
            for ( $i=0; $i<=count($columns); $i++ ) {
                if ($columns[$i] == $att) {
                    $data[$att] = $value;
                }

                if ($att == $assocs[$i]) {
                    $this->_assoc_data[$att] = $value;
                }

                if ($att == $has_one[$i]) {
                    $this->_assoc_data[$att] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Inserts a record and any associated table data
     *
     * @return int/bool $output of the insert call
     */
    public function insert()
    {
        $data = $this->cleanup_save_data();

        $output = $this->table()->insert( $data );

        $this->_take_over = true;

        $this->find( $output )->handle_saving_of_associations();

        $this->find( $output )->handle_saving_of_has_one_association();

        $this->find( $output );

        return $output;
    }

    /**
     * Updates a record and all assocated data tables
     *
     * @return bool - output of the update
     */
    public function update()
    {
        $data = $this->attributes;

        unset( $data['id'] );
        unset( $data['create_date'] );

        $output = $this->table()->update( $this->cleanup_save_data(), $this->attributes['id'] );

        $this->_take_over = true;

        $this->find( $this->attributes['id'] )->handle_saving_of_associations();
        $this->find( $this->attributes['id'] );

        return $output;
    }

    /**
     * Handles the saving (inserting/updating) of any associated table data
     *
     * @return bool/int $output of update/insert
     */
    private function handle_saving_of_associations()
    {
        foreach ($this->has_many as $assoc) {
            if (!!$assoc && $assoc != "") {
                $data = $this->_assoc_data[$assoc];

                if (!!$data) {
                    if( $this->attributes['id'] )
                        $data[$this->clean_table().'_id'] = $this->attributes['id'];

                    $assoc = explode(':', $assoc);
                    if (count($assoc) > 0) {
                        $assoc = $assoc[0];
                    }

                    $table = DB_SUFFIX.'_'.$assoc;

                    $sql_query = new operations();
                    $sql_query->table( $table );

                    // Validate associations data
                    if ( $this->validate_attributes_to_save( $table ) ) {
                        if (!!$data['id']) {
                            $output = $sql_query->update( $data, $data['id'] );
                        } else {
                            $output = $sql_query->insert( $data );
                        }
                    }
                }
            }
        }

        return $output;
    }

    /**
     * This needs to be thoroughly tested
     * This will loop through the single inheritance and just save them to the database
     * however on the first insertion updating the main source table to the ID of the item aved
     * which builds up the 'has_one' relationship
     *
     * @return bool
     */
    private function handle_saving_of_has_one_association()
    {
        if ( property_exists(get_class($this), 'has_one') ) {
            foreach ($this->has_one as $one) {
                if (!!$one) {

                    $data = $this->_assoc_data[$one];

                    if (!!$data) {
                        $table = DB_SUFFIX.'_'.$one;
                        $source = get_class($this);

                        $sql_query = new operations();
                        $sql_query->table( $table );
                        
                        // Validate associations data
                        if ( $this->validate_attributes_to_save( $table ) ) {

                            if (!!$data['id']) {

                                $output = $sql_query->update( $data, $data['id'] );
                            } else {
                                // We only want to do something if we actually have properties!
                                if ( count( $data ) > 0 ) {

                                    $output = $sql_query->insert( $data );

                                    // On the first insertion and save - we update the main record with the id
                                    // of the main item that was saved

                                    $this->attributes[$one.'_id'] = $output;

                                    $output = $this->update();
                                }
                            }
                        }
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Validates the attribues before saving - making sure they do not error before saving
     * and all values are not missing or in correct format. Using the validation library
     *
     * @param  optional string $table - use table if not source table
     * @return int             - where the validation passed or not
     */
    private function validate_attributes_to_save( $table = "" )
    {
        if( $table == "" )
            $table = $this->full_table();
        else
            $table = $table;

        $validation = new Validation();

        $rules = $this->validates;

        $columns = $this->table()->columns( $table );

        foreach ($columns as $column) {
            for ( $i=0; $i<=count($rules); $i++ ) {
                $rule = $rules[$i][0];
                $col = $rules[$i][1];

                if ($column == $col) {
                    switch ($rule) {
                        case 'not_empty':
                            $validation->not_empty( $col, $this->attributes[$col], $rules[$i][2] );
                            break;

                        case 'valid_email':
                            $validation->valid_email( $this->attributes[$col], $rules[$i][1] );
                            break;
                    }
                }
            }
        }

        $this->errors = $validation->errors;

        return $validation->pass;
    }

    public function delete( $items = array(), $foreign = array() )
    {
        if (!!$items) {
            $delete_items = array ();

            // Convert the input to an array if not, so we can perform a loop
            if ( !is_array ( $items ) )
                $delete_items[] = $items;
            else
                $delete_items = $items;

            $count = 0;
            foreach ($delete_items as $id) {
                // If the foreign key array is set - loop through
                if ( count( $foreign ) > 0 ) {
                    foreach ($foreign as $t) {
                        // First query the column to get the FK
                        $fork_table = $this->table()->query->getObj( 'SELECT id FROM ' . $this->table . ' WHERE id = :id', array ( 'id' => $id ) );

                        // Delete the instance from the foreign key table
                        $this->table()->query->plain( 'DELETE FROM '. DB_SUFFIX.'_'.$t.'s' . ' WHERE id = :id', array ( 'id' => $fork_table->{'id'} ) );
                    }
                }

                $this->table()->query->plain( 'DELETE FROM '. $this->full_table() . ' WHERE id = :id', array ( 'id' => $id ) );

                $count++;
            }

            return $count;
        } else {
            $this->table()->query->plain( 'DELETE FROM '. $this->full_table() . ' WHERE id = :id', array ( 'id' => $this->attributes['id'] ) );

            return TRUE;
        }
    }

    /**
     * @return table name without DB_SUFFIX
     */
    protected function clean_table()
    {
        $class = get_class( $this );

        $class = str_replace( "_model", "", $class );

        return strtolower($class);
    }

    /**
     * @return table with DB_SUFFIX prepended
     */
    protected function full_table()
    {
        $class = $this->clean_table();
        $class = strtolower(DB_SUFFIX . '_' . str_replace( "_model", "", $class ));

        return $class;
    }

    /**
     * Holds direct port to the table class for manipulating a table
     */
    protected function table()
    {
        $class = $this->full_table();

        return Table::load( $class );
    }

    /**
     * Dynamically set a property of the class
     * @param  string $k - the property to set
     * @param  string $v - the value to set the property to
     * @return string
     */
    public function __set( $k, $v )
    {
        if ( isset($this->attributes[$k]) ) {
            $this->_data[$k] = $v;

            return;
        }

        $this->{$k} = $v;
    }

    /**
     * Dynmically retreive a given property
     * @return mixed
     */
    public function __get( $k )
    {
        if ( isset($this->attributes[$k]) ) {
            return $this->attributes[$k];
        }

        return $this->{$k};
    }

    /**
     * Builds up the where clause given by the dynamically _call method
     * @return bool
     */
    private function arrange_missing_where( $where )
    {
        $where = explode( '_', $where );

        if( $where[0] != 'where' )

            return false;

        $where[0] = "";
        for ( $i=0; $i<=count($where); $i++ ) {
            $column = $where[$i];

            if ($column != "") {
                $opera = ' = ';

                if ($where[$i+1] == 'like') {
                    $opera = ' LIKE ';
                    // Set the next one to be blank, as we don't wish to use this again
                    // As we know that this is the like operator
                    $where[$i+1] = "";
                }

                if ($where[$i+1] == 'id') {
                    $column .= '_id';
                    $where[$i+1] = "";
                }

                $this->where( $this->full_table().'.'.$column.$opera.':'.$column );
            }
        }

        return true;
    }


    /**
     * Dynamically called for building a query
     *
     * @param  string $method     - the query to build such as: find_where_title_like
     * @param  array  $parameters - the binds to be passed in to the query for the where clause
     * @return array
     */
    public function __call($method, $parameters)
    {
        $binds = $parameters[0];

        if ( starts_with( $method ) == 'find' ) {
            $where = $this->arrange_missing_where( substr($method, 5) );

            $this->find( $binds );

            return $this->attributes;
        } elseif ( starts_with( $method ) == 'get' ) {
            $where = $this->arrange_missing_where( substr($method, 4) );

            if ($where) {
                return $this->all( $binds );
            }
        }
    }

    /**
     * Method to delete rows by something other than the id
     *
     * @param string $column
     * @param string $value
     *
     * @return bool
     *
     * @access public
     */
    public function delete_by_column ( $column = "", $value = "" )
    {
        if (!!$column && !!$value) {
            $this->table()->query->plain( 'DELETE FROM '. $this->full_table() . ' WHERE ' . $column . ' = :' . $column, array ( $column => $value ) );

            return TRUE;
        } else
            throw new Exception ( "A column and a value is needed to use the 'delete_by_column' method." );
    }

}

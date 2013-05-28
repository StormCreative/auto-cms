<?php
/***
/
/ Active Record Class
/ -------------------
/ The handler of all connections to the databse
/ This is the sole point of the application that
/ can access the Database.
/
/ All data from the database is set/get passed through here
/ Based entirely on using objects ( besides the all which is for arrays )
/
/ @author Ashley Banks
/ @version 1.2
/
***/

class Active_record
{
    /**
     * Operations database class instance
     * @access protected
     */
    protected $o;

    /**
     * Query database class instane
     * @access protected
     */
    protected $q;

    /**
     * The table set to hold queries
     * Changed this to public as it made sense to make it accessible
     * @access public
     */
    public $table;

    /**
     * The table without the prepended DB_SUFFIX
     * @access public
     */
    public $raw_table;

    /**
     * Array of columns to include in query
     * @access protected
     */
    protected $_columns = array();

    /**
     * Holds an array of where clauses
     * @access private
     */
    private $_where = array ();

    /**
     * Column to order by - defaults to the date
     * @access private
     */
    private $_order_by = 'create_date DESC';

    /**
     * Where a model has an image assocated to it
     * @access protected
     */
    protected $_has_image = FALSE;
    protected $_has_upload = FALSE;

    /**
     * What to split the multiple where clauses by
     * @access private
     */
    private $_where_split = ' AND ';

    /**
     * Private instantiation of the valiation class for checking models
     * @access private
     */
    private $_validation = NULL;

    /**
     * Protected set of rules for validating a model
     * @access protected
     */
    protected $_rules = array();

    /**
     * Publicly set errors returned by validation library
     * @access public
     */
    public $errors = array();

    /**
     * Has many relationship - set to table name to associate
     * @access protected
     */
    protected $_has_many;
    protected $_has_image_many;
    protected $_has_one;

    /**
     * Associative data for saving many data associations at once
     * @access private
     */
    private $_assoc_data = array();

    /**
     * Sets the limit of a query
     * @access private
     */
    private $_limit;

    /**
     * Sets the offset of a query
     * @access private
     */
    private $_offset;

    protected $_binded = array();

    public $_data = array();

    public function __Construct ()
    {
        // Load and set Database classes
        $this->o = new operations();
        $this->q = new query();

        $this->setTable();

        // Load in validation
        $this->_validation = new validation();
    }

    /**
     * Sets the @table by the class that is loaded.
     * @access private
     */
    private function setTable ()
    {
        // Setting operations and query classes to the active record properties
        $class = get_class ( $this );

        $class = str_replace ( "_model", "", $class );
        $this->table = strtolower ( DB_SUFFIX . '_' . str_replace ( "_model", "", $class ) );
        $this->raw_table = $class;
    }

    private function set_table_columns_blank()
    {
        $table_columns = $this->q->describe_table( $this->table );

        foreach ( $table_columns as $col )
            if ( $col != 'id' && $col != 'create_date' )
            $this->{$col} = "";

        return $this;
    }

    /**
     * Sets items into the where array
     * @param string $where - the where clause eg. tree = hello
     */
    public function where ( $where )
    {
        $this->_where[] = $where;

        return $this;
    }

    /**
     * Split the where clauses by either AND or OR
     * @param string $split - either AND or OR
     */
    public function where_split ( $split )
    {
        $this->_where_split = $split;

        return $this;
    }

    /**
     * Return the concatinated where clause
     * @access private
     */
    private function getWhere ()
    {
        return ( sizeof($this->_where) > 0 ? ' WHERE ' .implode ( ' ' . $this->_where_split . ' ', $this->_where ) : '' );
    }

    /**
     * Set the column to order by
     * @param string $order - the string to order
     */
    public function order_by ( $col, $order = 'ASC' )
    {
        $this->_order_by = $col . ' ' . $order;

        return $this;
    }

    /**
     * Set columns specifically for a query
     * If the paramter is an array it just merges the columns
     * so it can take it either one column or many
     * @param mixed $column - takes in either string or an array
     */
    public function columns ( $column )
    {
        if ( is_array ( $column ) ) {
            $this->_columns = $column;
        } else
            $this->_columns[] = $column;

        return $this;
    }

    /**
     * Set the query limit
     * @param string $limit
     */
    public function limit ( $limit )
    {
        $this->_limit = $limit;

        return $this;
    }

    /**
     * Set the query offset
     * @param string $offset
     */
    public function offset( $offset )
    {
        $this->_offset = $offset;

        return $this;
    }

    private function get_limit ()
    {
        if ( !!$this->_limit && !!$this->_offset )
            return 'LIMIT ' . $this->_offset .', '. $this->_limit;
        elseif( !isset($this->_offset) && !!$this->_limit )
            return 'LIMIT ' . $this->_limit;
    }

    /**
     * Return the columns for the query
     * @access private
     */
    private function getCols ()
    {
        // If theres columns in the array return imploded other return all columns (*)
        return (int) sizeof( $this->_columns ) > 0 ? implode(', ', $this->_columns ) : '*';
    }

    /**
     * Get all items from the set table
     * @param  array $bind - anything that needs binding for where clauses eg. title => 'baanan'
     * @return assoc array - the results data
     */
    public function all ( $binds = array () )
    {
        if (!!$this->table) {

            // I'm putting this here because a few items wil have images
            // I could put them in their own model with additonal queries
            // But this way makes it just flow...
            if ($this->_has_image) {
                $image_column = ', (SELECT imgname FROM '.DB_SUFFIX.'_image WHERE id = image_id) as image';
            }

            $query = 'SELECT ' . $this->getCols() . ''.$image_column.' FROM ' . $this->table . $this->getWhere() . ' ORDER BY '.$this->_order_by . ' ' . $this->get_limit ();

            $data = $this->q->getAssoc( $query, $binds );

            $i = 0;

            foreach ($data as $item) {
                if (!!$this->_has_many) {

                    $table = str_replace( DB_SUFFIX.'_', '', $this->table );

                    /*
                    // Using a JOIN query is going to take some additional workarounds
                    // Mainly to the methods within the active record to be more intelligent
                    // on picking up the columns

                    // As I spent all of 15 minutes on it I settled for the additional query
                    // But rework into making this a query of itself will be undergone
                    $union = 'JOIN SELECT * FROM '.DB_SUFFIX.'_'.$this->_has_many.' WHERE '.$table.'_id = :'.$table.'_id';
                    $binds[$table.'_id'] = $id;
                    */
                    $id = $data[$i]['id'];

                    foreach ($this->_has_many as $many) {

                        preg_match('/:/', $many, $matches, PREG_OFFSET_CAPTURE);
                        if (!!$matches[0][0]) {
                            $add_select = explode(':', $many);

                            if( $add_select[1] == 'image' )
                                $add_query = ', (SELECT imgname FROM ' . DB_SUFFIX . '_image WHERE id = image_id) as image';

                            $many = $add_select[0];

                        }

                        // FOR NOW - I've copped out and created a seperate query
                        $query = 'SELECT *'.$add_query.' FROM '.DB_SUFFIX.'_'.$many.' WHERE '.$table.'_id = :id';

                        // Unset the add query for the next loop
                        $add_query = "";

                        // Always going to be an associated array - as its relational!
                        $method = 'getAssoc';

                        $has_many = $this->q->{$method}( $query, array ( 'id' => $id ) );

                        // If theres a joint query we set it in the array
                        if ( !!$has_many )
                            $data[$i][$many] = $has_many;
                    }
                }

                $i++;
            }

            if ( count( $data ) > 0 )
                return $data;
            else
                return FALSE;
        } else {
            throw new Exception ( 'A table must be set to use ActiveRecord::all' );
        }
    }

    /**
     * Creates a simple JOIN query
     * @param  array      $tables - the first table is the key the second is the join
     * @param  array      $binds  - the array of binds values to pass over
     * @return assoc/bool
     */
    public function join( $tables, $binds )
    {
        $where = implode( ' AND ', $this->_where );

        $sql = 'SELECT '.$this->getCols().' FROM '.$tables[0].' JOIN '.$tables[1].' ON '.$where;

        $data = $this->q->getAssoc( $sql, $binds );

        if( count( $data ) > 0 )

            return $data;
        else
            return FALSE;
    }

    /**
     * Returns back a result from a table and given id
     * @param int $id
     * @param return obj
     */
    public function find ( $id, $field = 'id', $type = 'object' )
    {

        if (!!$this->table) {
            if ( !is_array($id) ) {
                $binds = array( $field => $id );
            } else {
                $binds = $id;
            }

            if ( !!$this->getWhere() )
                $where = $this->getWhere();
            else
                $where = 'WHERE ' . $field . '=:'.$field.'';

            // I'm putting this here because a few items wil have images
            // I could put them in their own model with additonal queries
            // But this way makes it just flow...
            if ($this->_has_image) {
                $image_column = ', (SELECT imgname FROM ' . DB_SUFFIX . '_image WHERE id = image_id) as image';
            }

            if ($this->_has_upload) {
                $image_column .= ', ( SELECT title FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_title';
                $image_column .= ', ( SELECT name FROM ' . DB_SUFFIX . '_uploads WHERE id = uploads_id ) as upload_name';
            }

            if ( $type == 'object' )
                $data = $this->q->getObj ( 'SELECT ' . $this->getCols() . ''.$image_column.' FROM ' . $this->table . ' ' .$where . ' '.$union, $binds );

            else {
                $data = $this->q->getAssoc ( 'SELECT ' . $this->getCols() . ''.$image_column.' FROM ' . $this->table . ' ' .$where . ' '.$union, $binds );
                $this->data = $data;
            }

            if ($data) {
                if (!!$this->_has_many) {

                    $table = str_replace( DB_SUFFIX.'_', '', $this->table );

                    if( is_object( $data ) )
                        $id = $data->id;
                    else
                        $id = $data[0]['id'];

                    if ( !is_array ( $this->_has_many ) ) {
                        $has_many = array();
                        $has_many[] = $this->_has_many;

                        $this->_has_many = $has_many;
                    }


                    foreach ($this->_has_many as $many) {

                        preg_match('/:/', $many, $matches, PREG_OFFSET_CAPTURE);
                        if (!!$matches[0][0]) {
                            $add_select = explode(':', $many);

                            if( $add_select[1] == 'image' )
                                $add_query = ', (SELECT imgname FROM ' . DB_SUFFIX . '_image WHERE id = image_id) as image';

                            $many = $add_select[0];

                        }

                        // FOR NOW - I've copped out and created a seperate query
                        $query = 'SELECT *'.$add_query.' FROM '.DB_SUFFIX.'_'.$many.' WHERE '.$table.'_id = :id';

                        // Unset the add query for the next loop
                        $add_query = "";

                        // Always going to be an associated array - as its relational!
                        $method = 'getAssoc';

                        $has_many = $this->q->{$method}( $query, array('id' => $id ) );

                        if ($type == 'object') {
                            // Set the has_many association if if its set
                            if ( !!$has_many ) $this->{$many} = $has_many;
                        } else {
                            // If theres a joint query we set it in the array
                            if ( !!$has_many ) $data[0][$many] = $has_many;
                        }
                    }
                }

                if (!!$data && $type == 'object') {
                    foreach ($data as $field => $value) {
                        $this->_data[$field] = $value;
                    }

                    return $this;
                }

                //return $data[0];
                return $this;
            } else {
                return FALSE;
            }
        } else {
            throw new Exception ( 'A table must be set to use ActiveRecord::find() ' );
        }

    }

    /**
     * Sorts the properties set in the object to an assoc array
     * to be able to be saved into the database!
     * @return assoc array
     */
    public function sortProps ( $params = array() )
    {
        // If POST array not passed in we fall back to properties set within controllers
        if ( count($params) <= 0 ) {
            // Get the dynamically added properties
            $props = get_object_vars( $this );
        } else
            $props = $params;

        $columns = $this->q->describe_table ( $this->table );

        $data = array ();

        // Loop through and set up an array of the data for saving within DB
        foreach ($props as $key => $value) {
            if ( $key != $this->_has_many && !in_array($key, $this->_has_many) && $key != $this->_has_one ) {

                if ( in_array ( $key, $columns ) ) {
                    $data[$key] = $value;

                    if ( count( $params ) > 0 ) {
                        $this->_data[$key] = $params[$key];
                    }
                }
            } else {

                $this->_assoc_data[$key] = $value;
            }
        }

        if ( count ( $data ) > 0 )
            return $data;
    }

    /**
     * Build up the array of columns to validate
     * @param $arguments - as many argumnets as you want!
     */
    public function validate ( $arguments )
    {
        $rules = array();

        // Loop through the number of arguments provided
        for ( $i = 0; $i < func_num_args(); $i++ ) {
            $rules[] = func_get_arg( $i );
        }

        $this->_rules[] = $rules;

        return $this;
    }

    /**
     * Check the validation provided by models matches the values set
     * @access private
     */
    private function check_validation()
    {
        $model = str_replace ( DB_SUFFIX.'_', '', $this->table );

        foreach ($this->_rules as $rule) {
            $msg = (isset($rule[2])?$rule[2]:"");
            $value = (!!$this->{$rule[1]}?$this->{$rule[1]}:"");

            switch ($rule[0]) {
                case 'valid_email':
                    $this->_validation->valid_email( $value, $msg );
                    break;

                case 'exists_db':
                    $this->_validation->exists_db( $this->table, $rule[1], $value, $msg );
                    break;

                default:
                    $this->_validation->{$rule[0]}( $rule[1], $value, $msg );
                    break;
            }

        }

        $this->errors = $this->_validation->errors;

        return $this;
    }

    /**
     * Saves - determine update or save
     * return bool
     */
    public function save ( $params = array(), $validate = TRUE )
    {
        $data = $this->sortProps ( $params );

        // Set the table - this needs to be more 'dynamic' but here for the min
        $this->o->table( $this->table );

        // If there is an id set we update - otherwise we insert
        if ( isset ( $this->_data['id'] ) && $this->_data['id'] != "" ) {
            if( $validate )
                $this->check_validation();

            if ($this->_validation->pass) {
                if ( !!$data && count($data) > 0 ) {
                    $this->o->update ( $data, $this->_data['id'] );
                    $output = TRUE;
                }
            } else {
                $output = FALSE;
            }
        } else {
            if( $validate )
                $this->check_validation();

            if ($this->_validation->pass) {
                if (!!$data) {
                    // Setting the class id to what was just set!
                    $this->_data['id'] = $this->o->insert ( $data );

                    $output = TRUE;
                }
            } else {
                $output = FALSE;
            }
        }

        $this->assocSave();

        return $output;
    }

    /**
     * Saves associative data passed through the save params
     * @access private
     */
    private function assocSave()
    {

        if ( count($this->_assoc_data) > 0 ) {

            foreach ($this->_assoc_data as $key => $value) {

                // If the key or value is the object itself handle differently
                if ( is_object($value) ) {
                    $this->saveAssocObject($value);
                } else {

                    $class = $key.'_model';

                    $mod = new $class();

                    $mod->sortProps ( $value );

                    // If there is an id value within the array we know to update
                    if ( isset($value['id']) ) {

                        $mod->check_validation();

                        if ( count($mod->errors) == 0 ) {
                            $mod->find($value['id'])->save($value);

                        } else {
                            array_merge($this->errors, $mod->errors);
                            $this->errors = $mod->errors;
                            // Put the errors into the main
                        }
                    } else {

                        $mod->check_validation();

                        if ( count($mod->errors) == 0 ) {

                            $value[$this->raw_table.'_id'] = $this->_data['id'];

                            $mod->save($value);

                        } else {
                            //array_merge($this->errors, $mod->errors[0]);
                            $this->errors = $mod->errors;

                            // Put the errors into the main
                        }
                    }
                }

            }
        }

        return $this;
    }

    /**
     * Arranges the associative values for the object assoc save method
     * @param object $value - the object to arrange
     * @access private
     */
    private function arrangeAssocValues( $value )
    {
        $cols = $this->q->describe_table($value->table);

        $save_values = array();
        foreach ($cols as $col) {
            if( !!$value->$col )
                $save_values[$col] = $value->$col;
        }

        return $save_values;
    }

    /**
     * Saves the associated object
     * @param object $value
     * @access private
     */
    private function saveAssocObject( $value )
    {
        $save_values = $this->arrangeAssocValues($value);

        $table = $value->raw_table;

        $table = $table.'_model';
        $mod = new $table;

        if (!!$value->id) {
            $mod->check_validation();

            if( $mod->validation->pass )
                $mod->find($value->id)->save($save_values);
            else {
                array_merge($this->errors, $mod->errors);
                // Put the errors into the main
            }
        } else {

            $mod->check_validation();
            if ($mod->validation->pass) {
                $save_values[$this->raw_table.'_id'] = $this->_data['id'];
                $mod->save($save_values);
            } else {
                array_merge($this->errors, $mod->errors);
                // Put the errors into the main
            }
        }

        return $this;
    }

    /**
     * Method to insert multiple enteries into the database without setting the ID property of the object
     * If we set the ID property we will be over writing it with every iteration
     *
     * @param  array $values
     * @return bool
     * @access public
     */
    public function raw_insert ( $values, $field = "" )
    {
        if ( count ( $values ) > 0 ) {
            // Set the table - this needs to be more 'dynamic' but here for the min
            $this->o->table( $this->table );

            $return_ids = array ();

            $array = array ();

            foreach ($values as $key => $item) {
                if ( is_numeric ( $key ) ) {
                    $array[ !!$field ? $field : $key ] = $item;
                    $return_ids[] = $this->o->insert ( $array );
                    unset ( $array[ !!$field ? $field : $key ] );
                } else {
                    $array[ !!$field ? $field : $key ] = $item;
                }
            }

            if ( !!$array )
                $return_ids[] = $this->o->insert ( $array );

            return $return_ids;
        } else
            throw new Exception ( 'The raw_insert method needs a array of at least one value and a field.' );
    }

    /**
     * Delete - deletes a given array by the set table
     * @param array $items   - the array of id's to remove
     * @param array $foreign - an array of foreign columns to also delete
     *						   this will loop through an grab each assigned FK and remove
     *						   from the relevent table too
     * @return int $count - the total number of items deleted from the array
     */
    public function delete ( $items = array (), $foreign = array () )
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
                        $fork_table = $this->q->getObj( 'SELECT id FROM ' . $this->table . ' WHERE id = :id', array ( 'id' => $id ) );

                        // Delete the instance from the foreign key table
                        $this->q->plain( 'DELETE FROM '. DB_SUFFIX.'_'.$t.'s' . ' WHERE id = :id', array ( 'id' => $fork_table->{'id'} ) );
                    }
                }

                $this->q->plain( 'DELETE FROM '. $this->table . ' WHERE id = :id', array ( 'id' => $id ) );

                $count++;
            }

            return $count;
        } else {
            $this->q->plain( 'DELETE FROM '. $this->table . ' WHERE id = :id', array ( 'id' => $this->_data['id'] ) );

            return TRUE;
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
            $this->q->plain( 'DELETE FROM '. $this->table . ' WHERE ' . $column . ' = :' . $column, array ( $column => $value ) );

            return TRUE;
        } else
            throw new Exception ( "A column and a value is needed to use the 'delete_by_column' method." );
    }

    public function __get ($k)
    {
        if (isset($this->_data[$k])) {
            return $this->_data[$k];
        }

        return $this->{$k};
    }


    public function __set ($k, $v)
    {
        if (isset($this->_data[$k])) {
            $this->_data[$k] = $v;

            return;
        }

        $this->{$k} = $v;
    }


    /**
     * Interpret the missing method call and build it up
     * @param  string $method     - the missing method name input by the programmer
     * @param  array  $parameters - the second argument provided
     * @return void
     */
    public function __call($method, $parameters)
    {
        $method = explode( '_', $method );

        $_method = $method[0];
        unset($method[0]);

        // Interpret the method to a method that exists
        if ($_method == 'where') {
            $where_string = $this->buildMissingWhere( $method );

            $this->{$_method}( $where_string );

        } elseif ($_method == 'find') {
            $action = $method[1];
            unset($method[1]);

            if ($action == 'all') {
                if ($method[2] == 'where') {

                    unset($method[2]);

                    $where_string = $this->buildMissingWhere( $method );


                    $this->where( $where_string );
                }

                return $this->all( $parameters[0] );
            } elseif ($action == 'one') {
                if ($method[2] == 'where') {
                    unset($method[2]);

                    $where_string = $this->buildMissingWhere( $method );

                    $this->where( $where_string );


                }

                $row = $this->find( $parameters[0] );

                return $row;
            }
        } else {
            throw new Exception( "Method provided does not access or can not be used within the ActiveRecord" );

            return FALSE;
        }

        return $this;
    }


    /**
     * Builds up the missing where clause for the __call method
     * @param array $items - the items to build a where from
     * @access private
     * @return string $where_string
     */
    private function buildMissingWhere( $items )
    {

        $where_string = '';

        $sorted = array();

        // VERY MESSY!
        // First we sort the array and check if theres a double underscore
        // This is done to check undersocre columns facebook_id for example
        // is called with a double facebook__id

        // Start the count at 3 - as thtas the first item of the array ( as we have unset a couple by now )
        $c = 3;
        foreach ($items as $item) {

            if ($item == "") {

                if ($items[$c-1] != "" && $items[$c+1] != "") {
                    // Throw the ready made column into the sorted
                    $sorted[] = $items[$c-1].'_'.$items[$c+1];

                    // Remove this item we built from the sorted array ( not sure this does anything anymore )
                    unset($sorted[$c-1]);
                    unset($sorted[$c+1]);

                    // remove the items completely from the items array - as we don't need them any more
                    // as we are using them within the sorted...for larer!
                    unset($items[$c]);
                    unset($items[$c-1]);
                    unset($items[$c+1]);
                }
            }

            $c++;
        }

        // We then  loop through the left over items in the array and put them within sorted
        // as we know the ones with double underscores were removed from this array and can be put
        // within the sorted array ok.
        foreach ($items as $item) {
            if ( $item != "" )
                $sorted[] = $item;
        }

        foreach ($sorted  as $m) {
            if( $m != 'and' && $m != 'or' )
                $where_string .= $m .' = :'.$m . ' ';

            if( $m == 'and' || $m == 'or' )
                $where_string .= ' AND ';

            $c++;
        }

        return $where_string;
    }

}

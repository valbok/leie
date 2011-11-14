<?php


abstract class leieDBObject
{
    /**
     * @var
     */
    protected $FieldList = array();

    /**
     *
     */
    public static function definition()
    {
        return array();
    }

    public function __construct( array $fieldList = array() )
    {
        $this->FieldList = $fieldList;
    }

    public function hasAttribute( $name )
    {
        return isset( $this->FieldList[$name] );
    }

    protected function getDefinitionAttribute( $name )
    {
        $definition = $this->definition();
        if ( !isset( $definition[$name] ) )
        {
            throw new leieInvalidArgumentException( "'$name' definition attribute was not found" );
        }

        return $definition[$name];
    }

    protected function hasDefinitionAttribute( $name )
    {
        $definition = $this->definition();
        return isset( $definition[$name] );
    }

    public function getAttribute( $name )
    {
        if ( !$this->hasAttribute( $name ) )
        {
            throw new leieInvalidArgumentException( $this->getDefinitionAttribute( 'table' ) . ": Requested attribute '$name' does not exist" );
        }

        return $this->FieldList[$name];
    }

    public function setAttribute( $name, $value )
    {
        $this->FieldList[$name] = $value;
    }

    /**
     * Creates select query object to fetch the data
     *
     * @param (array|string) $cond Conditions
     * @param (string)
     * @param (string)
     * @param (string)
     * @return (ezcQuerySelect)
     */
    public function createSelect( $cond = false, $limit = false, $offset = false, $orderBy = false )
    {
        $db = ezcDbInstance::get();

        $q = $db->createSelectQuery();
        $e = $q->expr;
        $def = $this->definition();
        $table = $def['table'];
        $q->select( "$table.*" )->from( $table );

        if ( is_string( $cond ) )
        {
            $q->where( $cond );
        }
        elseif ( is_array( $cond ) )
        {
            if ( is_array( $cond[0] ) )
            {
                foreach ( $cond as $key => $valueList )
                {
                    // If it is an array like array( 'id', 'eq', 1 )
                    $method = $valueList[1];
                    $field = $valueList[0];
                    $value = $valueList[2];
                    $q->where( $e->$method( $field, $q->bindValue( $value ) ) );
                }
            }
            else
            {
                $method = $cond[1];
                $field = $cond[0];
                $value = $cond[2];
                $q->where( $e->$method( $field, $q->bindValue( $value ) ) );
            }

        }

        if ( $orderBy )
        {
            $q->orderBy( $orderBy );
        }

        if ( $limit !== false )
        {
            $q->limit( $limit, $offset ? $offset : '' );
        }

        return $q;
    }


    /**
     * Fetches object list from database
     *
     * @param (ezcQuerySelect) $q Query
     * @return (array)
     */
    public function fetchObjectList( $q = false )
    {
        if ( !$q )
        {
            $q = $this->createSelect();
        }

        $query = $q->getQuery();

        $stmt = $q->prepare();
        $stmt->execute();

        $result = array();
        $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
        $class = get_class( $this );
        foreach ( $rows as $row )
        {
            $result[] = new $class( $row );
        }

        return $result;
    }

    /**
     * Fetches the object from database
     *
     * @param (array|string) $cond
     *
     * @return (__CLASS__)
     */
    public function fetchObject( $cond )
    {
        $list = $this->fetchObjectList( $this->createSelect( $cond ) );

        return isset( $list[0] ) ? $list[0] : false;
    }

    /**
     * Begins a transaction
     *
     * @return (void)
     */
    public static function begin()
    {
        $db = ezcDbInstance::get();
        $db->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return (void)
     */
    public static function commit()
    {
        $db = ezcDbInstance::get();
        $db->commit();
    }

    /**
     * Updates current object
     *
     * @return (void)
     */
    public function update()
    {
        $db = ezcDbInstance::get();
        $q = $db->createUpdateQuery();
        $q->update( $this->getDefinitionAttribute( 'table' ) );
        $keyList =  $this->getDefinitionAttribute( 'keys' );
        foreach ( $keyList as $key )
        {
            $q->where( $q->expr->eq( $key, $q->bindValue( $this->getAttribute( $key ) ) ) );
        }

        $this->setQueryFields( $q );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Inserts current object to database
     *
     * @return (void)
     */
    public function insert()
    {
        $db = ezcDbInstance::get();
        $q = $db->createInsertQuery();
        $q->insertInto( $this->getDefinitionAttribute( 'table' ) );

        $this->setQueryFields( $q );

        $stmt = $q->prepare();
        $stmt->execute();

        if ( $this->hasDefinitionAttribute( 'increment_key' ) )
        {
            $this->FieldList[$this->getDefinitionAttribute( 'increment_key' )] = $db->lastInsertId();
        }
    }

    /**
     * Sets field list to a query
     *
     * @return (void)
     */
    protected function setQueryFields( $q )
    {
        foreach ( $this->FieldList as $name => $value )
        {
            if ( $this->hasDefinitionAttribute( 'increment_key' ) and $name == $this->getDefinitionAttribute( 'increment_key' ) )
            {
                continue;
            }

            $q->set( $name, $q->bindValue( $value ) );
        }
    }

    /**
     * Removes current object from database
     *
     * @return (void)
     */
    public function delete()
    {
        $db = ezcDbInstance::get();
        $q = $db->createDeleteQuery();
        $q->deleteFrom( $this->getDefinitionAttribute( 'table' ) );
        $keyList =  $this->getDefinitionAttribute( 'keys' );
        foreach ( $keyList as $key )
        {
            $q->where( $q->expr->eq( $key, $q->bindValue( $this->getAttribute( $key ) ) ) );
        }

        $stmt = $q->prepare();
        $stmt->execute();
    }

}


?>

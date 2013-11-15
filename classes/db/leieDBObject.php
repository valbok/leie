<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Base database object.
 */
abstract class leieDBObject
{
    /**
     * Lisf of database fields
     *
     * @var []
     */
    protected $FieldList = array();

    /**
     * Returns a definition of database table
     *
     * @return []
     */
    public static function definition()
    {
        return array();
    }

    /**
     * @reimp
     */
    public function __construct( array $fieldList = array() )
    {
        $this->FieldList = $fieldList;
    }

    /**
     * Checks if requested attribute exists
     *
     * @return bool
     */
    public function hasAttribute( $name )
    {
        return isset( $this->FieldList[$name] );
    }

    /**
     * Returns attribute from definition
     *
     * @return []
     */
    protected function getDefinitionAttribute( $name )
    {
        $definition = $this->definition();
        if ( !isset( $definition[$name] ) )
        {
            throw new leieInvalidArgumentException( "'$name' definition attribute was not found" );
        }

        return $definition[$name];
    }

    /**
     * Checks if definition attribute exists
     *
     * @return bool
     */
    protected function hasDefinitionAttribute( $name )
    {
        $definition = $this->definition();
        return isset( $definition[$name] );
    }

    /**
     * Returns field value
     *
     * @return string|number
     */
    public function getAttribute( $name )
    {
        if ( !$this->hasAttribute( $name ) )
        {
            throw new leieInvalidArgumentException( $this->getDefinitionAttribute( 'table' ) . ": Requested attribute '$name' does not exist" );
        }

        return $this->FieldList[$name];
    }

    /**
     * Sets field value
     *
     * @return $this
     */
    public function setAttribute( $name, $value )
    {
        $this->FieldList[$name] = $value;

        return $this;
    }

    /**
     * Creates select query object to fetch the data
     *
     * @param array|string $cond Conditions
     * @param string
     * @param string
     * @param string
     * @return ezcQuerySelect
     */
    public function createSelect( $cond = false, $limit = false, $offset = false, array $orderByList = array() )
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

        foreach ( $orderByList as $orderBy => $orderByType )
        {
            $q->orderBy( $orderBy, $orderByType );
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
     * @param ezcQuerySelect $q Query
     * @return array
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
     * @param array|string $cond
     *
     * @return __CLASS__
     */
    public function fetchObject( $cond )
    {
        $list = $this->fetchObjectList( $this->createSelect( $cond ) );

        return isset( $list[0] ) ? $list[0] : false;
    }

    /**
     * Begins a transaction
     *
     * @return $this
     */
    public static function begin()
    {
        $db = ezcDbInstance::get();
        $db->beginTransaction();

        return $this;
    }

    /**
     * Commits a transaction
     *
     * @return $this
     */
    public static function commit()
    {
        $db = ezcDbInstance::get();
        $db->commit();

        return $this;
    }

    /**
     * Updates current object
     *
     * @return $this
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

        return $this;
    }

    /**
     * Inserts current object to database
     *
     * @return int
     */
    public function insert()
    {
        $db = ezcDbInstance::get();
        $q = $db->createInsertQuery();
        $q->insertInto( $this->getDefinitionAttribute( 'table' ) );

        $this->setQueryFields( $q );

        $stmt = $q->prepare();
        $stmt->execute();
        $id = $db->lastInsertId();
        if ( $this->hasDefinitionAttribute( 'increment_key' ) )
        {
            $this->FieldList[$this->getDefinitionAttribute( 'increment_key' )] = $id;
        }

        return $id;
    }

    /**
     * Sets field list to a query
     *
     * @return void
     */
    protected function setQueryFields( $q )
    {
        foreach ( $this->FieldList as $name => $value )
        {
            if ( $this->hasDefinitionAttribute( 'increment_key' ) and $name == $this->getDefinitionAttribute( 'increment_key' ) )
            {
                continue;
            }

            // TODO: check if $value is hex
            $q->set( $name, strpos( $value, '0x' ) === false ? $q->bindValue( $value ) : $value );
        }
    }

    /**
     * Removes current object from database
     *
     * @return $this;
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

        return $this;
    }
}


?>

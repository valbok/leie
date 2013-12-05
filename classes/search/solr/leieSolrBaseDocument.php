<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Solr base document
 *
 * @todo Add num of rows
 */
abstract class leieSolrBaseDocument
{
    /**
     * Document field list
     *
     * @var (array)
     */
    protected $FieldList = array();

    /**
     * List of found elements with highlighting
     *
     * @var (array)
     */
    protected $HighlighList = array();

    /**
     * @reimp
     */
    protected function __construct( $def, $fieldList = array() )
    {
        foreach ( $def['fields'] as $fieldName => $fieldDef )
        {
            $found = false;
            foreach ( $fieldList as $key => $value )
            {
                if ( $key == $fieldName )
                {
                    $found = true;
                    $this->FieldList[$key] = $value;
                }
            }

            if ( !$found and $fieldDef['required'] )
            {
                throw new leieInvalidArgumentException( "Field '$fieldName' is required: " . print_r( $fieldList, true ) );
            }
        }
    }

    /**
     * Provides a definition of this document
     *
     * @return (array)
     */
    public static function definition()
    {
        return array( 'fields' => array() );
    }

    /**
     * @return (SolrClient)
     */
    protected static function getClient()
    {
        static $client;
        if ( $client )
        {
            return $client;
        }

        $client = new SolrClient( self::getOptions() );
        return $client;
    }

    /**
     * Provides solr server options
     *
     * @return (array)
     */
    public static function getOptions( $host = 'localhost', $port = '8983' )
    {
        return array( 'hostname' => $host,
                      'port'     => $port,
                    );
    }

    /**
     * Returns provided attribute value
     *
     * @return (mixed)
     */
    public function getAttribute( $name )
    {
        switch ( $name )
        {
            default:
            {
               $result = isset( $this->FieldList[$name] ) ? $this->FieldList[$name] : false;
            } break;
        }

        return $result;
    }

    /**
     * Checks for existance of attribute
     *
     * @return (bool)
     */
    public function hasAttribute( $name )
    {
        return isset( $this->FieldList[$name] );
    }

    /**
     * Stores current object
     *
     * @return (void)
     */
    public function store()
    {
        $doc = new SolrInputDocument();
        $client = self::getClient();
        foreach ( $this->FieldList as $name => $value )
        {
            $doc->addField( $name, $value );
        }

        try
        {
            @$client->addDocument( $doc );
            @$client->commit();
        }
        catch ( Exception $e )
        {
            throw new leieSolrException( $e->getMessage() );
        }
    }

    /**
     * Deletes all data from index
     *
     * @return (void)
     */
    public static function deleteAll()
    {
        try
        {
            $client = self::getClient();
            $client->deleteByQuery( '*' );
            $client->commit();
        }
        catch ( Exception $e )
        {
            throw new leieSolrException( $e->getMessage() );
        }
    }

    /**
     * Deletes object by ID
     *
     * @return (void)
     */
    public static function deleteByID( $id )
    {
        try
        {
            $client = self::getClient();
            @$client->deleteById( $id );
            @$client->commit();
        }
        catch ( Exception $e )
        {
            throw new leieSolrException( $e->getMessage() );
        }
    }

    /**
     * Deletes current object
     *
     * @return (void)
     */
    public function delete()
    {
        self::deleteByID( $this->getAttribute( 'id' ) );
    }

    /**
     * Sets highlighting
     *
     * @param (SolrObject)
     * @return (array)
     */
    protected function setHighlighting( $object )
    {
        $result = array();
        foreach ( $object as $fieldName => $itemList )
        {
            $result = array_merge( $result, $itemList );
        }

        $this->HighlighList = $result;
    }

    /**
     * Returns found text with highlights
     *
     * @return (array)
     */
    public function getHighlightList()
    {
        return $this->HighlighList;
    }

    /**
     * Parses response and returns a list of objects
     *
     * @return (array)
     */
    private static function parseResponse( $def, $response )
    {
        if ( !isset( $response->response->docs ) or !$response->response->docs )
        {
            return array();
        }

        $result = array();
        foreach ( $response->response->docs as $item )
        {
            $o = new $def['class']( $def, $item );
            $o->setHighlighting( $response->highlighting[$item->id] );
            $result[] = $o;
        }

        return $result;
    }

    /**
     * Makes a searching
     *
     * @param (string)
     * @param (array)
     * @param (int)
     * @param (int)
     *
     * @return (array)
     */
    protected static function fetchObjectList( $def, $field, $text, $filterList = array(), $limit = false, $offset = false, array $orderByList = array() )
    {
        $text = preg_replace( '/[~!@#$%^&*\(\)=\{\}\[\]:;\\\\\/\|]/', '', $text );
        $text = htmlspecialchars( $text );
        if ( empty( $text ) )
        {
            return array();
        }

        if ( !$limit )
        {
            $limit = 1000;
        }

        $query = new SolrQuery();
        $query->setQuery( $field . ':' . $text );

        if ( $offset !== false )
        {
            $query->setStart( $offset );
        }

        if ( $limit )
        {
            $query->setRows( $limit );
        }

        /**
         * AND: array( 'field1' => 'value1',
         *             'field2' => 'value2' );
         * OR:  array( array( 'field1' => 'value1',
         *                    'field2' => 'value2' ),
         *             array( 'field1' => 'value3' )
         *            );
         */
        foreach ( $filterList as $key => $item )
        {
            if ( $item === false )
            {
                continue;
            }

            if ( is_array( $item ) )
            {
                $str = '';
                foreach ( $item as $subList )
                {
                    foreach ( $subList as $subKey => $subItem )
                    {
                        $str .= $subKey . ':' . $subItem . ' ';
                    }
                }

                if ( $str )
                {
                    $query->addFilterQuery( $str );
                }
            }
            else
            {
                $query->addFilterQuery( $key . ':' . $item );
            }
        }

        $query->setHighlight( true )->setHighlightSnippets( 100 );
        foreach ( $orderByList as $orderBy => $orderByType )
        {
            $query->addSortField( $orderBy, $orderByType );
        }

        try
        {
            $queryResponse = self::getClient()->query( $query );
        }
        catch ( Exception $e )
        {
            throw new leieSolrException( $e->getMessage() );
        }

        return self::parseResponse( $def, $queryResponse->getResponse() );
    }
}
?>

<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 */

/**
 * Class to handle images
 */
class leieImage
{

    protected $Path = '';

    /**
     * @param (string) $dir Where templates are located
     */
    public function __construct( $path )
    {
        if ( !file_exists( $path ) )
        {
            throw new leieInvalidArgumentException( "File '$path' does not exist" );
        }

        $this->Path = $path;
    }

    public static function get( $path )
    {
        return new self( $path );
    }

    public function analyze()
    {
        try
        {
            $image = new ezcImageAnalyzer( $this->Path );
        }
        catch ( Exception $e )
        {
            throw new leieInvalidArgumentException( $e->getMessage() );
        }

        return $image;
    }

    public function getFilterList()
    {
        static $result = false;
        if ( $result )
        {
            return $result;
        }

        $result = array(

            'filledThumbnail' =>
                new ezcImageFilter(
                    'filledThumbnail',
                    array(
                        'width'  => 100,
                        'height' => 100,
                        'color'  => array(
                            200,
                            200,
                            200,
                        ),
                    )
                ),
            'scale'=>
                new ezcImageFilter(
                    'scale',
                    array(
                        'width'     => 320,
                        'height'    => 240,
                        'direction' => ezcImageGeometryFilters::SCALE_DOWN,
                    )
                ),
            'colorspace' =>
                new ezcImageFilter(
                    'colorspace',
                    array(
                        'space' => ezcImageColorspaceFilters::COLORSPACE_GREY,
                    )
                ),
            'border'=>
                new ezcImageFilter(
                    'border',
                    array(
                        'width' => 5,
                        'color' => array( 240, 240, 240 ),
                    )
                ),
        );

        return $result;
    }

    public static function getConverter()
    {
        static $converter = false;
        if ( $converter )
        {
            return $converter;
        }

        $settings = new ezcImageConverterSettings(
            array(
                //new ezcImageHandlerSettings( 'GD',          'ezcImageGdHandler' ),
                new ezcImageHandlerSettings( 'ImageMagick', 'ezcImageImagemagickHandler' ),
            )
            /*, array(
                'image/gif' => 'image/png',
            )*/
        );

        $converter = new ezcImageConverter( $settings );
        return $converter;
    }

    public function transform( $name, $filterList = array() )
    {
        if ( !is_array( $filterList ) )
        {
            $filterList = array( $filterList );
        }

        $converter = self::getConverter();
        $fullFilterList = self::getFilterList();
        $filters = array();
        foreach ( $filterList as $item )
        {
            if ( !isset( $fullFilterList[$item] ) )
            {
                throw new leieRunTimeException( 'Wrong filter' );
            }

            $filters[] = $fullFilterList[$item];
        }

        $converter->createTransformation( $name, $filters, array( 'image/jpeg', 'image/png' ) );

        $info = pathinfo( $this->Path );
        $dir = $info['dirname'];
        $filename = $info['filename'] . '_' . $name;
        $ext = $info['extension'];
        $result = $dir . '/' .  $filename . '.' . $ext;

        try
        {
            $converter->transform(
                $name,
                $this->Path,
                $result
            );
        }
        catch ( ezcImageTransformationException $e)
        {
            throw new leieInvalidArgumentException( $e->getMessage() );
        }

        return $result;
    }
}
?>
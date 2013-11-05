<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2013 VaL::bOK
 * @license GNU GPL v2
 * @package leie::image
 * @version 1.0Beta
 */

/**
 * Class to handle images
 */
class leieImage
{
    /**
     * @var string
     */
    protected $Path = '';

    /**
     * @var ezcImageAnalyzer
     */
    protected $Analyzer = false;

    /**
     * @param string
     */
    public function __construct( $path )
    {
        if ( !file_exists( $path ) )
        {
            throw new leieInvalidArgumentException( "File '$path' does not exist" );
        }

        $this->Path = $path;
    }

    /**
     * @param string
     **/
    public static function get( $path )
    {
        return new self( $path );
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->Path;
    }

    /**
     * @return ezcImageAnalyzer
     * @throws leieInvalidArgumentException
     */
    public function getAnalyzer()
    {
        if ( $this->Analyzer )
        {
            return $this->Analyzer;
        }

        try
        {
            $image = new ezcImageAnalyzer( $this->Path );
        }
        catch ( Exception $e )
        {
            throw new leieInvalidArgumentException( $e->getMessage() );
        }

        $this->Analyzer = $image;
        return $image;
    }

    /**
     * @return ezcImageConverter
     */
    public static function getConverter()
    {
        $settings = new ezcImageConverterSettings(
            array(
                //new ezcImageHandlerSettings( 'GD',          'ezcImageGdHandler' ),
                new ezcImageHandlerSettings( 'ImageMagick', 'ezcImageImagemagickHandler' ),
            )
        );

        return new ezcImageConverter( $settings );
    }

    /**
     * Returns transformation path
     *
     * @param string
     * @return string Path to transformed image
     */
    protected function getTransformationPath( $name )
    {
        $info = pathinfo( $this->Path );
        $dir = $info['dirname'];
        $filename = $info['filename'] . '_' . $name;
        $ext = isset( $info['extension'] ) ? '.' . $info['extension'] : '';
        $result = $dir . '/' .  $filename . $ext;

        return $result;
    }

    /**
     * @param int
     * @param int
     * @return __CLASS__
     */
    protected function transform( $filters = array(), $path = false )
    {
        $name = md5( serialize( $filters ) );
        $path = !$path ? $this->getTransformationPath( $name ) : $path;
        if ( file_exists( $path ) )
        {
            return new self( $path );
        }

        $mt = array( 'image/jpeg', 'image/png' );

        try
        {
            self::getConverter()
                ->createTransformation( $name, $filters, $mt )
                ->transform( $this->Path, $path );
        }
        catch ( ezcBaseException $e )
        {
            throw new leieInvalidArgumentException( $e->getMessage() );
        }

        return new self( $path );
    }

    /**
     * Scales the image and keeping proportions
     *
     * @param int
     * @param int
     * @param string
     * @return __CLASS__
     */
    public function scale( $width, $height, $path = false )
    {
        $filter = new ezcImageFilter(
                    'scale',
                    array(
                        'height' => $height,
                        'width' => $width,
                        'direction' => ezcImageGeometryFilters::SCALE_DOWN,
                    )
                );

        return $this->transform( array( $filter ), $path );
    }

    /**
     * Scales the image
     *
     * @param int
     * @param int
     * @return string Path to scalled image
     */
    public function resize( $width, $height, $path = false )
    {
        $filter = new ezcImageFilter(
                    'scaleExact',
                    array(
                        'height' => $height,
                        'width' => $width,
                    )
                );

        return $this->transform( array( $filter ), $path );
    }

    /**
     * @param string
     * @return __CLASS__
     */
    public function convertToGrayscale( $path = false )
    {
        $filter = new ezcImageFilter(
                        'colorspace',
                        array(
                            'space' => ezcImageColorspaceFilters::COLORSPACE_GREY,
                        )
                );

        return $this->transform( array( $filter ), $path );
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->getAnalyzer()->data->width;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->getAnalyzer()->data->height;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->getAnalyzer()->mime;
    }

    /**
     * @return string
     */
    public function getAverageHash()
    {
        $i = $this->convertToGrayscale()->resize( 8, 8 );
        $averageValue = $i->getAveragePixelValue();
        $result = "";
        for ( $y = 0; $y < 8; $y++ )
        {
            for ( $x = 0; $x < 8; $x++ )
            {
                $result .= ( $i->getPixel( $x, $y ) >= $averageValue ) ? "1" : "0";
            }
        }

        // Inteher val exceeded PHP_MAX_INT
        $a = gmp_init( '0x' . dechex( bindec( $result ) ) );

        return gmp_strval( $a );
    }

    /**
     * @return string
     */
    public function getDifferenceHash()
    {
        $i = $this->convertToGrayscale()->resize( 8, 8 );
        $previousPixel = $i->getPixel( 7, 7 );
        $result = "";
        for ( $y = 0; $y < 8; $y = $y + 2 )
        {
            for ( $x = 0; $x < 8; $x++ )
            {
                $pixel = $i->getPixel( $x, $y );
                $result .= ( $pixel >= $previousPixel ) ? "1" : "0";
                $previousPixel = $pixel;
            }

            $y += 1;
            for ( $x = 7; $x >= 0; $x-- )
            {
                $pixel = $i->getPixel( $x, $y );
                $result .= ( $pixel >= $previousPixel ) ? "1" : "0";
                $previousPixel = $pixel;
            }
        }

        $a = gmp_init( '0x'.dechex( bindec( $result ) ) );

        return gmp_strval( $a );
    }

    /**
     * @return [r,g,b]
     */
    public function getPixelColor( $x, $y )
    {
        $im = new Imagick( $this->Path );

        return $im->getImagePixelColor( $x, $y )->getColor();
    }

    /**
     * @return double
     */
    protected function getPixel( $x, $y )
    {
        $rgb = $this->getPixelColor( $x, $y );

        return ( $rgb['r'] + $rgb['g'] + $rgb['b'] ) / 3;
    }

    /**
     * @return double
     */
    public function getAveragePixelValue()
    {
        $w = $this->getWidth();
        $h = $this->getHeight();

        $result = $c = 0;
        for ( $y = 0; $y < $h; $y++ )
        {
            for ( $x = 0; $x < $w; $x++ )
            {
                $result += $this->getPixel( $x, $y );
            }
        }

        return $result / ( $w * $h );
    }
}
?>

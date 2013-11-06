<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2013 VaL::bOK
 * @license GNU GPL v2
 * @package leie::image
 * @version 1.0Beta
 * @uses imagemagick, ezcomponents
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
        $filename = $info['filename'] . '-' . $name;
        $ext = isset( $info['extension'] ) ? '.' . $info['extension'] : '';
        $result = $dir . '/' .  $filename . $ext;

        return $result;
    }

    /**
     * @param [] How to transform
     * @param string To store transformed file to
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
     * @return string 16 bytes hex
     */
    public function getAverageHash()
    {
        $i = $this->resize( 8, 8 )->convertToGrayscale();
        $averageValue = $i->getAveragePixelValue();
        $result = "";
        for ( $y = 0; $y < 8; $y++ )
        {
            for ( $x = 0; $x < 8; $x++ )
            {
                $result .= ( $i->getPixel( $x, $y ) >= $averageValue ) ? "1" : "0";
            }
        }

        $hex = self::bin2hex( $result );
        $len = strlen( $hex );
        if ( $len < 16 )
        {
            $hex = str_repeat( '0', 16 - $len ) . $hex;
        }

        return $hex;
    }

    /**
     * dechex( bindec( "1111111111110000111000001110000011000000111010001111100011110000" ) )
     * sometimes returns not actual value looks like because of converting to double
     *
     * @param string binary
     * @return string hex
     */
    public static function bin2hex( $data )
    {
        $result = '';
        $len = strlen( $data );
        $s = '';
        $li = 0;
        for ( $i = $len - 1; $i >= 0; $i-- )
        {
            $li++;
            $s = $data[$i] . $s;
            if ( $li >= 4 )
            {
                $result = dechex( bindec( $s ) ) . $result;
                $li = 0;
                $s = '';
            }
        }

        if ( $s )
        {
            $result = dechex( bindec( $s ) ) . $result;
        }

        return $result;
    }

    /**
     * @return string 16 bytes hex
     */
    public function getDifferenceHash()
    {
        $i = $this->resize( 8, 8 )->convertToGrayscale();
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

        $hex = self::bin2hex( $result );
        $len = strlen( $hex );
        if ( $len < 16 )
        {
            $hex = str_repeat( '0', 16 - $len ) . $hex;
        }

        return $hex;
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
        if ( $this->getMime() =='image/gif' ) return 0;
        $w = $this->getWidth();
        $h = $this->getHeight();

        $result = 0;
        for ( $y = 0; $y < $h; $y++ )
        {
            for ( $x = 0; $x < $w; $x++ )
            {
                $result += $this->getPixel( $x, $y );
            }
        }

        return $result / ( $w * $h );
    }

    /**
     * @param string HEX
     * @param string HEX
     * @return int
     * @todo check how it works on 64 bits
     */
    public static function getHammingDistance( $hash1, $hash2 )
    {
        return self::getPopCount( hexdec( $hash1 ) ^ hexdec( $hash2 ) );
    }

    /**
     * @return int
     */
    public static function getPopCount( $value )
    {
        $result = 0;
        while( $value )
        {
            $result += ($value & 1);
            $value = $value >> 1;
        }

        return $result;
    }

    /**
     * @return []
     * @copyright http://stackoverflow.com/questions/14106984/how-to-calculate-discrete-cosine-transform-dct-in-php
     */
    protected static function calculateDCT( $in )
    {
        $results = array();
        $N = count( $in );
        for ( $k = 0; $k < $N; $k++ )
        {
            $sum = 0;
            for ( $n = 0; $n < $N; $n++ )
            {
                 $sum += $in[$n] * cos( $k * pi() * ( $n + 0.5 ) / ( $N ) );
            }

            $sum *= sqrt( 2 / $N );
            if ( $k == 0 )
            {
                $sum *= 1 / sqrt( 2 );
            }

            $results[$k] = $sum;
        }
        
        return $results;
    }

    /**
     * @return []
     * @copyright http://stackoverflow.com/questions/14106984/how-to-calculate-discrete-cosine-transform-dct-in-php
     */
    public function getDCT()
    {
        $result = array();
        $rows = array();
        $row = array();

        $width = $this->getWidth();
        $height = $this->getHeight();

        for ( $j = 0; $j < $height; $j++ )
        {
            for ( $i = 0; $i < $width; $i++ )
            {
                $row[$i] = $this->getPixel( $i, $j );
            }

            $rows[$j] = self::calculateDCT( $row );
        }

        for ( $i = 0; $i < $width; $i++ )
        {
            for ( $j = 0; $j < $height; $j++ )
            {
                $col[$j] = $rows[$j][$i];
            }

            $result[$i] = self::calculateDCT( $col );
        }

        return $result;
    }

    /**
     * @return []
     */
    protected static function cropArray( $array, $offset1, $offset2, $length1, $length2 )
    {
        $result = array();
        foreach ( array_slice( $array, $offset1, $length1 ) as $item )
        {
            $result[] = array_slice( $item, $offset2, $length2 );
        }

        return $result;
    }

    /**
     * @return double
     */
    protected static function getAverageValueInArray( $array )
    {
        $h = count( $array );
        $result = 0;
        $c = 0;
        foreach ( $array as $item )
        {
            foreach ( $item as $x )
            {
                $c++;
                $result += $x;
            }
        }

        return $result / $c;
    }

    /**
     * @return string HEX
     */
    public function getPerceptualHash()
    {
        $i = $this->resize( 32, 32 )->convertToGrayscale();
        $dct = self::cropArray( $i->getDCT(), 0, 0, 8, 8 );
        $median = self::getAverageValueInArray( $dct );

        $result = "";
        for ( $y = 0; $y < 8; $y++ )
        {
            for ( $x = 0; $x < 8; $x++ )
            {
                $result .= ( $dct[$y][$x] > $median ) ? "1" : "0";
            }
        }

        $hex = self::bin2hex( $result );
        $len = strlen( $hex );
        if ( $len < 16 )
        {
            $hex = str_repeat( '0', 16 - $len ) . $hex;
        }

        return $hex;
    }
}
?>

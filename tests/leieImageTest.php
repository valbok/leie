<?php


class leieImageTest extends PHPUnit_Framework_TestCase
{
    public function testAnalyzeJPG()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' );
        $this->assertEquals( 'image/jpeg', $r->getMime() );
    }

    public function testAnalyzeImagePHP()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/morke.php' );
        $this->assertEquals( 'image/jpeg', $r->getMime() );
    }

    /**
     * @expectedException leieException
     */
    public function testAnalyzeNotExisting()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/NO-EX.gif' );
    }

    /**
     * @expectedException leieException
     */
    public function testAnalyzePHP()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/shell.php' )->getAnalyzer();
    }

    public function testScaleDown()
    {
        $p = dirname( __FILE__ ) . '/img/morke.jpg';
        $i = leieImage::get( $p )->scale( 100, 100 );

        $this->assertEquals( 100, $i->getWidth() );
        $this->assertEquals( 67, $i->getHeight() );
    }

    public function testResizeDown()
    {
        $p = dirname( __FILE__ ) . '/img/morke.jpg';
        $i = leieImage::get( $p )->resize( 100, 100 );

        $this->assertEquals( 100, $i->getWidth() );
        $this->assertEquals( 100, $i->getHeight() );
    }

    public function testConvertToGrayscale()
    {
        $p = dirname( __FILE__ ) . '/img/morke.jpg';
        $rp = dirname( __FILE__ ) . '/img/morke_grayscaled.jpg';
        $i = leieImage::get( $p );
        $r = $i->convertToGrayscale( $rp );
        $this->assertEquals( 'image/jpeg', $r->getMime() );
        $p = $r->getPixelColor( 0, 0 );
        $this->assertTrue( is_array($p) );
        $this->assertEquals( 4, count( $p ) );
    }

    public function testGetPixelColor()
    {
        $p = dirname( __FILE__ ) . '/img/morke.jpg';
        $r = leieImage::get( $p );
        $p = $r->getPixelColor( 0, 0 );
        $this->assertTrue( is_array($p) );
        $this->assertEquals( 4, count( $p ) );
    }

    public function testGetAveragePixelValue()
    {
        $p = dirname( __FILE__ ) . '/img/morke.jpg';
        $r = leieImage::get( $p );
        $p = $r->resize( 8, 8 )->getAveragePixelValue();
        $this->assertTrue( is_double( $p ) );
    }

    public function testAverageHash()
    {
        $h = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' )->getAverageHash();
	//todo
    }

    public function testDIfferenceHash()
    {
        $h = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' )->getDifferenceHash();
	//todo
    }

}


?>

<?php


class leieImageTest extends PHPUnit_Framework_TestCase
{
    public function testAnalyzeJPG()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' )->analyze();
        $this->assertEquals( 'image/jpeg', $r->mime );
    }

    public function testAnalyzeImagePHP()
    {
        $r = leieImage::get( dirname( __FILE__ ) . '/img/morke.php' )->analyze();
        $this->assertEquals( 'image/jpeg', $r->mime );
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
        $r = leieImage::get( dirname( __FILE__ ) . '/img/shell.php' )->analyze();
    }

    public function testTransform()
    {
        $path = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' )->transform( 'thumb', 'filledThumbnail' );
        $r = leieImage::get( $path )->analyze();
        $this->assertEquals( 'image/jpeg', $r->mime );
    }

    public function testTransformComplex()
    {
        $path = leieImage::get( dirname( __FILE__ ) . '/img/morke.jpg' )->transform( 'complex', array( 'scale', 'colorspace', 'border' ) );
        $r = leieImage::get( $path )->analyze();
        $this->assertEquals( 'image/jpeg', $r->mime );
    }

}


?>

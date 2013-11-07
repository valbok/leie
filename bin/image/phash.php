<?php
require 'autoload.php';

$fn1 = isset( $_SERVER['argv'][1] ) ? $_SERVER['argv'][1] : false;
if ( !$fn1 )
{
    exit;
}

$fn2 = isset( $_SERVER['argv'][2] ) ? $_SERVER['argv'][2] : false;
$h1 = leieImage::get( $fn1 )->getPerceptualHash();
print $h1 . "\n";
if ( $fn2 )
{
    $h2 = @leieImage::get( $fn2 )->getPerceptualHash();
    print $h2 . "\n";
    print $fn1 . " and " . $fn2 + " distance: " + leieImage::getHammingDistance( $h1, $h2 ) . " and " . ( ( ( 64 - leieImage::getHammingDistance( $h1, $h2 ) ) * 100.0 ) / 64.0 ) . "% similar.\n";
}
?>

#!/usr/bin/env php
<?php
/**
 * Use this script to generate autoload file
 *
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie
 * @see ../autoload.php
 * @todo Clean up the code
 */

require 'Base/src/base.php';

/**
 * Autoload ezc classes
 *
 * @param string $className
 */
function __autoload( $className )
{
    ezcBase::autoload( $className );
}

$list = fetchFiles();
$phpClasses = getClassFileList( $list );
writeAutoloadFiles( $phpClasses );


/**
 * Builds a filelist of all PHP files in $path.
 *
 * @param string $path
 * @param array $extraFilter
 * @return array
 */
function buildFileList( $path, $extraFilter = null )
{
    $dirSep = preg_quote( DIRECTORY_SEPARATOR );
    $exclusionFilter = array( "@^{$path}{$dirSep}(var|settings|bin|var{$dirSep}autoload|classes{$dirSep}tests|lib{$dirSep}ezc){$dirSep}@" );
    if ( !empty( $extraFilter ) and is_array( $extraFilter ) )
    {
        foreach( $extraFilter as $filter )
        {
            $exclusionFilter[] = $filter;
        }
    }

    if ( !empty( $path ) )
    {
        return findRecursive( $path, array( '@\.php$@' ), $exclusionFilter );
    }

    return false;
}


/**
 * Uses the walker in ezcBaseFile to find files.
 *
 * This also uses the callback to get progress information about the file search.
 *
 * @param string $sourceDir
 * @param array $includeFilters
 * @param array $excludeFilters
 * @param eZAutoloadGenerator $gen
 * @return array
 */
function findRecursive( $sourceDir, array $includeFilters = array(), array $excludeFilters = array() )
{
    echo( "Scanning for PHP-files.\n" );

    // create the context, and then start walking over the array
    $context = new stdClass();
    ezcBaseFile::walkRecursive( $sourceDir, $includeFilters, $excludeFilters,
            'findRecursiveCallback', $context );

    // return the found and pattern-matched files
    sort( $context->elements );

    echo( "\nScan complete. Found {$context->count} PHP files." );
    return $context->elements;
}

/**
 * Callback used ezcBaseFile
 *
 * @param string $ezpAutoloadFileFindContext
 * @param string $sourceDir
 * @param string $fileName
 * @param string $fileInfo
 * @return void
 */
function findRecursiveCallback( $context, $sourceDir, $fileName, $fileInfo )
{
    // update the statistics
    $context->elements[] = $sourceDir . DIRECTORY_SEPARATOR . $fileName;
    $context->count++;

    echo ".";
}

/**
 * Extracts class information from PHP sourcecode.
 * @return array (className=>filename)
 */
function getClassFileList( $fileList )
{
    $retArray = array();
    echo( "\nSearching for classes (tokenizing).\n" );
    $statArray = array( 'nFiles' => count( $fileList ),
                        'classCount' => 0,
                        'classAdded' => 0,
                       );

    foreach( $fileList as $file )
    {
        $tokens = @token_get_all( file_get_contents( $file ) );
        foreach( $tokens as $key => $token )
        {
            if ( is_array( $token ) )
            {
                switch( $token[0] )
                {
                    case T_CLASS:
                    case T_INTERFACE:
                        // Increment stat for found class.
                        //$this->incrementProgressStat( self::OUTPUT_PROGRESS_PHASE2, 'classCount' );
                        echo ".";
                        // CLASS_TOKEN - WHITESPACE_TOKEN - TEXT_TOKEN (containing class name)
                        $className = $tokens[$key+2][1];

                        $filePath = $file;

                        $retArray[$className] = $filePath;
                        break;
                }
            }
        }
    }
    echo( "\nFound " . count($retArray) . " classes\n");
    ksort( $retArray );
    return $retArray;
}

function writeAutoloadFiles( $list, $file = 'var/autoload.php' )
{
    $directorySeparators = "/\\";
    $content = '<?php return array( ';
    foreach( $list as $class => $path )
    {
        $content .= "\n    '$class' => '$path',";
    }

    $content .= "\n);?>";
    $result = file_put_contents( $file, $content );
}

/**
 * Returns an array indexed by location for classes and their filenames.
 *
 * @param string $path The base path to start the search from.
 * @return array
 */
function fetchFiles()
{
    $path = $_SERVER['PWD'];
    $retFiles = buildFileList( $path );

    //Make all the paths relative to $path
    foreach ( $retFiles as $key => &$file )
    {
        $retFiles[$key] = ezcBaseFile::calculateRelativePath( $file, $path );
    }

    return $retFiles;
}

?>

<?php
/**
 * @author VaL
 * @copyright Copyright (C) 2011 VaL::bOK
 * @license GNU GPL v2
 * @package leie::mail
 */

/**
 * Class to handle sending emails
 */
class leieMtaTransport
{
    /**
     * Sends email using mail()
     *
     * @return void
     * @exception leieRunTimeException
     */
    public static function send( $emailFrom, $emailToList, $subject, $content )
    {
        if ( !is_array( $emailToList ) )
        {
            $emailToList = array( $emailToList );
        }

        try
        {
            $mail = new ezcMail();
            $mail->from = new ezcMailAddress( $emailFrom );
            foreach ( $emailToList as $email )
            {
                $mail->addTo( new ezcMailAddress( $email ) );
            }

            $mail->subject = $subject;
            $textPart = new ezcMailText( $content, 'UTF-8' );
            $textPart->subType = 'html';
            $mail->body = $textPart;
            $transport = new ezcMailMtaTransport();
            $transport->send( $mail );
        }
        catch ( Exception $e )
        {
            throw new leieRunTimeException( $e->getMessage() );
        }
    }

}

/**
 * To send mails using SMTP
 */
class leieSmtpTransport
{

    /**
     * @var string
     */
    protected $Host = false;

    /**
     * @var string
     */
    protected $Username = false;

    /**
     * @var string
     */
    protected $Password = false;

    /**
     * @var string
     */
    protected $Port = false;

    /**
     * @reimp
     */
    public function __construct( $host, $username, $password, $port )
    {
        $this->Host = $host;
        $this->Username = $username;
        $this->Password = $password;
        $this->Port = $port;
    }

    /**
     * Sends email using mail()
     *
     * @return void
     * @exception leieRunTimeException
     */
    public function send( $emailFrom, $emailToList, $subject, $content )
    {
        if ( !is_array( $emailToList ) )
        {
            $emailToList = array( $emailToList );
        }

        try
        {
            $mail = new ezcMail();
            $mail->from = new ezcMailAddress( $emailFrom );
            foreach ( $emailToList as $email )
            {
                $mail->addTo( new ezcMailAddress( $email ) );
            }

            $mail->subject = $subject;
            $textPart = new ezcMailText( $content, 'UTF-8' );
            $textPart->subType = 'html';
            $mail->body = $textPart;

            $options = new ezcMailSmtpTransportOptions();
            $options->connectionType = ezcMailSmtpTransport::CONNECTION_SSLV3;

            $transport = new ezcMailSmtpTransport( $this->Host, $this->Username, $this->Password, $this->Port, $options );
            $transport->options->connectionType = ezcMailSmtpTransport::CONNECTION_SSLV3;

            // Use the SMTP transport to send the created mail object
            $transport->send( $mail );
        }
        catch ( Exception $e )
        {
            throw new leieRunTimeException( $e->getMessage() );
        }
    }

}

?>

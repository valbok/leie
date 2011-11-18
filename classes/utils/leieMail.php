<?php


class leieMail
{
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

?>

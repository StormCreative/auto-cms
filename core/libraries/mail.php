<?php
/**
* A very simple class for sending the email
*/
define ( 'SUBJECT', 'subject' );
define ( 'FROM', 'from' );
define ( 'RECIPIENT', 'recipent' );

class mail
{
    private $_recipient,
            $_subject,
            $_from,
            $_message,
            $_headers,
            $_attachment_name,
            $_attachment_content,
            $_mime_uid;

    public function __Construct ( $to, $template, $subject, $from = '', $data = array () )
    {
        $this->_recipient = $to;
        $this->_template = $template;
        $this->_subject = $subject;
        $this->_from = $from;

        $this->setBody( $template, $data );

        if ( $this->send() )
            return TRUE;
    }

    public function setter ( $field, $value )
    {
        $this->{'_'. $field } = $value;

        return $this;
    }

    public function setBody ( $email, $columns = "" )
    {
        if (!!$columns) {
            if ( file_exists ( PATH . 'assets/emails/' . $email . '.txt' ) ) {
                $message = file_get_contents( PATH . 'assets/emails/' . $email . '.txt' );

                foreach ( $columns as $col => $key )
                    $message = str_replace( '{' . strtoupper( $col ) . '}', $key, $message );

                $this->_message = $message;

            } else {
                throw new \Exception( 'Can not find template email ' . $email . ' specified within setBody Mail library' );
            }
        } else
            $this->_message = $email;

        return $this;
    }

    /**
     * Set an attachment to the email
     * @param $att - the content of the attachment file
     * @param $att_name - the name of the attachment
     */
    public function setAttachment ( $att, $att_name )
    {
        $this->_mime_uid = md5 ( uniqid( time() ) );
        $this->_attachment_name = $att;
        $this->_attachment_content = $att_content;

        return $this;
    }

    private function setHeaders ()
    {
        $this->_headers = 'From: '. $this->_from . "\n" .
                          'Reply-To: '. $this->_from . "\n";

        $this->_headers .= "Message-ID: <".gettimeofday()." TheSystem@".$_SERVER['SERVER_NAME'].">"."\n";

        if (!!$this->_attachment_name && $this->_mine_uid) {
            $ext = strrchr( $this->_attachment_name , '.' );

            $ftype = "";
            if ( $ext == ".doc" ) $ftype = "application/msword";
            if ( $ext == ".jpg" ) $ftype = "image/jpeg";
            if ( $ext == ".gif" ) $ftype = "image/gif";
            if ( $ext == ".zip" ) $ftype = "application/zip";
            if ( $ext == ".pdf" ) $ftype = "application/pdf";
            if  ( $ftype==" " ) $ftype = "application/octet-stream";

            // build the headers for attachment and html
            $this->_headers = 'From: '. $this->_from . "\n" .
                                'Reply-To: '. $this->_from . "\n";
            $this->_headers .= 'MIME-Version: 1.0' . "\n";
            $this->_headers .= "Content-Type: multipart/mixed; boundary=\"".$this->_mime_uid."\"\n\n";
            $this->_headers .= "This is a multi-part message in MIME format.\n";
            $this->_headers .= "--".$this->_mime_uid."\n";
            $this->_headers .= "Content-type:text/html; charset=iso-8859-1\n";
            $this->_headers .= "Content-Transfer-Encoding: 7bit\r\n\n";
            $this->_headers .= $this->_message."\n\n";
            $this->_headers .= "--".$uid."\n";
            $this->_headers .= "Content-Type: ".$ftype."; name=\"".$this->_attachment_name."\"\n";
            $this->_headers .= "Content-Transfer-Encoding: base64\n";
            $this->_headers .= "Content-Disposition: attachment; filename=\"".$this->_attachment_name."\"\n\n";
            $this->_headers .= $this->_attachment_content."\n\n";
            $this->_headers .= "--".$this->_mime_uid."--";
        } else {
            $this->_headers .= "Content-type: text/html; charset=utf-8\n";
        }

        return $this;
    }

    public function send ()
    {
        $this->setHeaders();

        if ( mail( $this->_recipient, $this->_subject, $this->_message, $this->_headers ) )
            return TRUE;
    }

}

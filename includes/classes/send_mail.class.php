<?php
include_once(DIR_COMMON_PHP.'general.php');
require_once DIR_WS_CLASSES.'Pear/Mail/mimePart.php';

class send_mail {
    private $_fromName;
    private $_fromEmail;
    private $_fromAddress;
    private $_replyTo;
    private $_recipients;
    private $_ccRecipients;
    private $_bccRecipients;
    private $_userAgent = '';
    private $_subject = '';
    private $_txtbody = '';
    private $_htmlbody = '';
    private $_headers = array();
    private $_transferParams = array();
    private $_attachments = array();
    private $_images = array();
	private $_wildcard_values = '';
	private $_build_params = array();
	private $sep = "\r\n";

    public function setFrom($name, $email) {
        $this->_fromName = $name;
        $this->_fromEmail = $email;
    }

	public function getFromEmail() {
        return $this->_fromEmail;
    }

	public function getFromName() {
        return $this->_fromName;
    }

    public function setFullFromAddress($from) {
        $this->_fromAddress = $from;
    }

    public function setReplyTo($replyto) {
        $this->_replyTo = $replyto;
    }
	
	function _setTXTBody($data, $append = false)
    {
        if (!$append)
            $this->_txtbody = $data;
        else
            $this->_txtbody .= $data;
        return true;
	}

	function _setHTMLBody($data)
    {
        $this->_htmlbody = $data;
        return true;
    }
    
	function _addTextPart(&$obj, $text)
    {
        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->_build_params['text_encoding'];
        $params['charset']      = $this->_build_params['text_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($text, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($text, $params);
            return $ret;
        }
    }
	
	function _addAlternativePart(&$obj)
    {
        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            $ret = new Mail_mimePart('', $params);
            return $ret;
        }
    }
	
	function _addHtmlPart(&$obj)
    {
        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->_build_params['html_encoding'];
        $params['charset']      = $this->_build_params['html_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($this->_htmlbody, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($this->_htmlbody, $params);
            return $ret;
        }
    }

	function get_body()
    {
        if (isset($this->_headers['From'])){
            $domain = @strstr($this->_headers['From'],'@');
            //Bug #11381: Illegal characters in domain ID
            $domain = str_replace(array("<", ">", "&", "(", ")", " ", "\"", "'"), "", $domain);
            $domain = urlencode($domain);
        }
        
        $null        = null;
        $attachments = false;
        $html_images = false;
        $html        = strlen($this->_htmlbody)             ? true : false;
        $text        = (!$html AND strlen($this->_txtbody)) ? true : false;
		
		
        switch (true) {
			case $text:
				$message =& $this->_addTextPart($null, $this->_txtbody);
				break;

			case $html:
				if (isset($this->_txtbody)) {
					$message =& $this->_addAlternativePart($null);
					$this->_addTextPart($message, $this->_txtbody);
					$this->_addHtmlPart($message);
				} else {
					$message =& $this->_addHtmlPart($null);
				}
				break;
		}
        if (isset($message)) {
            $output = $message->encode();
            
            $this->_headers = array_merge($this->_headers, $output['headers']);
            $body = $output['body'];
            return $body;

        } else {
            $ret = false;
            return $ret;
        }
		
    }
	
	function _sanitizeHeaders(&$headers)
    {
        foreach ($headers as $key => $value) {
            $headers[$key] =
                preg_replace('=((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*=i',
                             null, $value);
        }
    }
	
	function prepareHeaders($headers)
    {
        $lines = array();
        $from = null;
        foreach ($headers as $key => $value) {
            if (strcasecmp($key, 'Received') === 0) {
                $received = array();
                if (is_array($value)) {
                    foreach ($value as $line) {
                        $received[] = $key . ': ' . $line;
                    }
                }
                else {
                    $received[] = $key . ': ' . $value;
                }
                // Put Received: headers at the top.  Spam detectors often
                // flag messages with Received: headers after the Subject:
                // as spam.
                $lines = array_merge($received, $lines);
            } else {
                // If $value is an array (i.e., a list of addresses), convert
                // it to a comma-delimited string of its elements (addresses).
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $lines[] = $key . ': ' . $value;
            }
        }

        return array($from, join($this->sep, $lines));
    }

	function send_body($recipients, $headers, $body)
    {
		
        $this->_sanitizeHeaders($headers);
		
        // If we're passed an array of recipients, implode it.
        if (is_array($recipients)) {
            $recipients = implode(', ', $recipients);
        }
		
        // Get the Subject out of the headers array so that we can
        // pass it as a seperate argument to mail().
        $subject = '';
        if (isset($headers['Subject'])) {
            $subject = $headers['Subject'];
            unset($headers['Subject']);
        }
        
        // * Also remove the To: header.  The mail() function will add its own
        // * To: header based on the contents of $recipients.
        //
        unset($headers['To']);
		
        // Flatten the headers out.
        $headerElements = $this->prepareHeaders($headers);
		
        list(, $text_headers) = $headerElements;
		
        $result = @mail($recipients, $subject, $body, $text_headers);//, $this->_params);
        
		return $result;
    }

    // * Set email recipients
    // *
    // * @param string $recipients comma separated list of mail recipients
    //
    public function setRecipients($recipients) {
        $this->_recipients = $recipients;
    }
	
	public function getRecipients() {
        return $this->_recipients;
    }
    
    // * Set Carbon copy recipients
    // *
    // * @param string $recipients comma separated list of carbon copy recipients
	
    public function setCcRecipients($recipients) {
        $this->_ccRecipients = $recipients;
    }

     // * Set Bcc recipients
     // *
     // * @param string $recipients comma separated list of carbon copy recipients
     
    public function setBccRecipients($recipients) {
        $this->_bccRecipients = $recipients;
    }

     // * Set User Agent header value
     // *
     // * @param string $agent
     // 
    public function setUserAgent($agent) {
        $this->_userAgent = $agent;
    }

     // * Set subject
     // *
     // * @param string $subject
	 
    public function setSubject($subject) {
        $this->_subject = make_synonyms($subject, 3, '', '');
    }
	
	public function getSubject() {
        return $this->_subject;
    }

     // * Set Text body of mail
     // *
     // * @param string $body
     
    public function setTxtBody($body) {
        $this->_txtbody = make_synonyms($body, 3, '', '');
    }

     // * Set Html body of mail
     // *
     // * @param string $body
     
    public function setHtmlBody($body) {
        $this->_htmlbody = make_synonyms($body, 3, '', '');
    }
	
	public function getHtmlBody() {
        return $this->_htmlbody;
    }
	
	// this is a string with wildcard names and values in form of <name>"\t"<value>"\r\n"
	public function setBodyValues($values) {
        $this->_wildcard_values = $values;
    }
	
     // * Set transfer params
     // *
     // * @param array $params
     
    public function setTransferParams($params) {
        $this->_transferParams = $params;
    }

     // * Add attachment to mail
     // *
     // * @param string $filename file name of file
     // * @param string $filetype mime type of file
     // * @param string $content content of file
     
    function addAttachment($filename, $filetype, $content) {
        $this->_attachments[] = array('filename'=>$filename, 'filetype' => $filetype, 'content'=>$content);
    }

     // * Add image to mail
     // *
     // * @param string $filename
     // * @param string $content
     
    function addImage($filename, $content) {
        $this->images[] = array('filename'=>$filename, 'filetype' => $filetype, 'content'=>$content);
    }

	// by PYsoft
	protected function ChunkText($text, $max_len = 60, $str_delimeter = "\r\n")
	{
		$text = str_replace('">', '" >', $text);
		$text = str_replace(');', ' );', $text);
		$text = str_replace('url(', 'url( ', $text);
		$text = str_replace('="', '= "', $text);
			
		$new_text = '';
		//$strings = explode("\r\n", $text);
		$strings = preg_split('/$\R?^/m', $text);
		foreach($strings as $ss) { 
			$words = explode(" ", $ss);
			$new_str = '';
			foreach($words as $ww) { 
				if ( strlen($new_str) + strlen($ww) > $max_len ) {
					$new_text = $new_text.$str_delimeter.$new_str;
					$new_str = $ww;
				}
				else
					$new_str = $new_str.' '.$ww;
			}
			$new_text = $new_text.$str_delimeter.$new_str;
		}
		return $new_text;
	}
	// <--
   
     // * Send mail
     // *
     // * @return boolean
     
    public function send($userid = '', $save_to_db = true, $sender_userid = '', $dont_send_email = false ) 
	{
		if (!strlen($this->_recipients))
            throw new Exception($this->_("Recipients empty"));
		
        $this->_build_params['html_charset'] = 'UTF-8';
        $this->_build_params['text_charset'] = 'UTF-8';
        $this->_build_params['head_charset'] = 'UTF-8';
		
		if ( strlen($this->_wildcard_values) ) {
			$this->_wildcard_values = str_replace("=", "%3D", $this->_wildcard_values);
			$this->_wildcard_values = str_replace("\t", "=", $this->_wildcard_values);
			$this->_wildcard_values = str_replace("&", "&amp;", $this->_wildcard_values);
			$this->_wildcard_values = str_replace("\r\n", "&", $this->_wildcard_values);
			parse_str($this->_wildcard_values, $wildcards);
		}
		
		foreach ($wildcards as $key => $value)
			$this->_subject = str_replace('{$'.$key.'}', hex2bin($value), $this->_subject);
		
		if ( strlen($this->_htmlbody) ) {
			if ( strlen($this->_wildcard_values) ) {
				$s = $this->_htmlbody;
				foreach ($wildcards as $key => $value) {
					$s = str_replace('{$'.$key.'}', hex2bin($value), $s);
    			}
				$this->_htmlbody = $s;
			}
			
			$this->_htmlbody = $this->ChunkText($this->_htmlbody)."\r\n".' -';
			
            if (strlen($this->_txtbody)) {
				if ( strlen($this->_wildcard_values) ) {
					$s = $this->_txtbody;
					foreach ($wildcards as $key => $value) {
						$s = str_replace('{$'.$key.'}', $value, $s);
	    			}
					$this->_txtbody = $s;
				}
                $this->_setTXTBody($this->_txtbody);
            } 
			else {
				$s = convert_html2text($this->_htmlbody, false);
				$this->_setTXTBody($s);
            }
        } else if (strlen($this->_txtbody)) {
            $this->_setTXTBody($this->_txtbody);
        } else {
            throw new Exception($this->_("Body of mail not specified"));
        }
		
		if( !$this->initHeaders() )
       		throw new Exception($this->_('Failed to init mail headers'));
        
        $body = $this->get_body();
		foreach ($this->_headers as $name => $header) {
			$headers .= "Reply-To: ". strip_tags($_POST['req-email']) . "\r\n";
		}

		if ( !$dont_send_email ) {
			$ret = $this->send_body($this->_recipients, $this->_headers, $body);
			if ( !$ret )
	            throw new Exception('Mail cannot be sent');
		}
		if ( $save_to_db ) {
			$insert_values = array(
				'subject' => tep_sanitize_string($this->_subject, 0, true),
				'body_text' => tep_sanitize_string($this->_txtbody, 0, true),
				'body_html' => tep_sanitize_string(preg_replace('/\'/', '"', $this->_htmlbody), 0, true, false, ' ', false),
				'_created' => 'NOW()',
				'from_mail' => tep_sanitize_string($this->_fromAddress, 256, true),
  				'to_recipients' => tep_sanitize_string($this->_recipients, 256, true),
  				'cc_recipients' => tep_sanitize_string($this->_ccRecipients, 256, true),
  				'bcc_recipients' => tep_sanitize_string($this->_bccRecipients, 256, true)
			);
			if ( !empty($userid) )
				$insert_values['userid'] = $userid;
			
			if ( !empty($sender_userid) )
				$insert_values['sender_userid'] = $sender_userid;
			
			make_api_request('email_save_to_db', '', $insert_values);
		}
		return true;
    }

    private function initHeaders() {
		
		if(!strlen($this->_fromEmail) && !strlen($this->_fromAddress)) {
            throw new Exception($this->_("From address is empty"));
        }
		
        $this->addHeader('Date', date('j M Y H:i:s O'));
		
        if (!strlen($this->_fromAddress)) {
            $from = "";
            if(strlen($this->_fromName)) {
                $from = '"' . $this->_fromName . '" ';
            }
            $this->_fromAddress = $from . '<' . $this->_fromEmail . '>';
        }
        $this->addHeader('From', $this->_fromAddress);
        $this->addHeader('Reply-To',  $this->_replyTo);

        if(!$this->addHeader('To',  $this->_recipients)) {
            throw new Exception($this->_("Recipients empty"));
        }

        $this->addHeader('Cc',  $this->_ccRecipients);
        $this->addHeader('Bcc',  $this->_bccRecipients);
        $this->addHeader('User-Agent', $this->_userAgent);
        $this->addHeader('Subject', $this->_subject);
		
        return true;
    }

    private function addHeader($name, $value) {
        if(!strlen(trim($value))) {
            return false;
        }
        $this->_headers[$name] = trim($value);
        return true;
    }

     // * Return image content type depending on extension of file
     // *
     // * @param string $filename
     // * @return string mime type of image
     
    function getImageContentType($filename) {
        $path = pathinfo($filename);
        switch(strtolower($path['extension'])) {
            case 'gif':
                $type = IMAGETYPE_GIF;
                break;
            case 'png':
                $type = IMAGETYPE_PNG;
                break;
            default:
                $type = IMAGETYPE_JPEG;
                break;
        }
        return image_type_to_mime_type($type);
    }

     // * Explode mail address
     // *
     // * @param string $inValue
     // * @return array
     
    function prepareEmail($inValue) {
        
    }

     // * Return clean mail address
     // *
     // * @param array $email
     // * @param int $index
     // * @return string
     
    function getEmailAddress($email, $index = 0) {
        if (is_array($email) &&
        count($email) > 0 &&
        isset($email[$index]->mailbox) &&
        isset($email[$index]->host) &&
        strlen($email[$index]->mailbox) &&
        strlen($email[$index]->host)) {
            return trim($email[$index]->mailbox) . '@' . trim($email[$index]->host);
        } else {
            return '';
        }
    }

     // * Return person name from mail address format
     // *
     // * @param array $email
     // * @param int $index
     // * @return string
     
    function getPersonalName($email, $index = 0) {
        if (is_array($email) &&
        count($email) > 0 &&
        isset($email[$index]->personal) &&
        strlen($email[$index]->personal)) {
            return str_replace('"', '', $email[$index]->personal);
        } else {
            return '';
        }
    }
}

?>
<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Model;

use StdClass;
use DateTime;

/**
 * IMAP letter model
 * 
 * @category    Application
 * @package     Model
 */
class Letter
{
    /**
     * UID
     *
     * @var string
     */
    protected $uid;

    /**
     * Message ID
     *
     * @var string
     */
    protected $mid;

    /**
     * Date
     *
     * @var DateTime
     */
    protected $date;

    /**
     * From
     *
     * @var string
     */
    protected $from;

    /**
     * To
     *
     * @var string
     */
    protected $to;

    /**
     * Subject
     *
     * @var string
     */
    protected $subject;

    /**
     * HTML part of the message
     *
     * @var string
     */
    protected $htmlMessage;

    /**
     * Plain text part of the message
     *
     * @var string
     */
    protected $textMessage;

    /**
     * Attachments
     *
     * @var array
     */
    protected $attachments;
 
    /**
     * Message headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Raw headers
     *
     * @var string
     */
    protected $rawHeaders;
    
    /**
     * Raw body
     *
     * @var string
     */
    protected $rawBody;

    /**
     * Boundary
     *
     * @var string
     */
    protected $boundary;
    
    /**
     * Sections
     *
     * @var array
     */
    protected $sections;

    /**
     * Parsed body
     *
     * @var string
     */
    protected $parsedBody;

    /**
     * Is the message successfully parsed?
     *
     * @var boolean
     */
    protected $error = false;

    /**
     * Log of message parsing
     *
     * @var string
     */
    protected $log = null;

    /**
     * Constructor
     *
     * @param integer $uid
     * @param StdClass $overview   Optional imap_rfc822_parse_headers() output
     */
    public function __construct($uid, StdClass $overview = null)
    {
        $this->uid = $uid;

        if (!$overview)
            return;

        $this->mid = $overview->message_id;
        $this->date = new DateTime($overview->date);
        if (isset($overview->fromaddress))
            $this->from = @iconv_mime_decode($overview->fromaddress, 0, "UTF-8");
        if (isset($overview->toaddress))
            $this->to = @iconv_mime_decode($overview->toaddress, 0, "UTF-8");
        if (isset($overview->subject))
            $this->subject = @iconv_mime_decode($overview->subject, 0, "UTF-8");
    }

    /**
     * UID setter
     *
     * @param integer $uid
     * @return Letter
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * UID getter
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Message ID setter
     *
     * @param string $mid
     * @return Letter
     */
    public function setMid($mid)
    {
        $this->mid = $mid;
        return $this;
    }

    /**
     * Message ID getter
     *
     * @return string
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Date setter
     *
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Date getter
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * From field setter
     *
     * @param string $from
     * @return Letter
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * From field getter
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * To field setter
     *
     * @param string $to
     * @return Letter
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * To field getter
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Subject setter
     *
     * @param string $subject
     * @return Letter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Subject getter
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Return raw letter (headers + body)
     *
     * @return string
     */
    public function getSource()
    {
        return $this->rawHeaders . "\n\n" . $this->rawBody;
    }

    /**
     * Retrieves headers
     * 
     * @return  array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get particular header
     *
     * Result is an array as there could be multiple headers with the
     * same name
     *
     * @param   string $name
     * @return  array
     */
    public function getHeader($name)
    {
        foreach ($this->getHeaders() as $key => $value) {
            if (strtolower($key) == strtolower($name))
                return $value;
        }

        return [];
    }

    /**
     * Get HTML part of the letter
     *
     * @return string
     */
    public function getHtmlMessage()
    {
        return $this->htmlMessage;
    }

    /**
     * Get text part of the letter
     *
     * @return string
     */
    public function getTextMessage()
    {
        return $this->textMessage;
    }

    /**
     * Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Get raw headers
     *
     * @return string
     */
    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    /**
     * Get raw body
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * Parsed headers
     *
     * @return string
     */
    public function getParsedHeaders()
    {
        $headers = "";
        $preferences = array(
            "scheme" => "Q",
            "input-charset" => "UTF-8",
            "output-charset" => "UTF-8",
            "line-length" => 76,
            "line-break-chars" => "\n"
        );

        $mimeVersion = join(" ", $this->getHeader('MIME-Version'));
        if (strlen($mimeVersion) > 0) {
            $headers .= "MIME-Version: $mimeVersion";
            $headers .= "\n";
        }

        $headers .= "Content-Type: ";
        $headers .= join(" ", $this->getHeader('Content-Type'));
        $headers .= "\n";

        $encoding = join(" ", $this->getHeader('Content-Transfer-Encoding'));
        $headers .= "Content-Transfer-Encoding: " . (strlen($encoding) > 0 ? $encoding : "8bit");
        $headers .= "\n";

        $headers .= "Message-ID: " . $this->getMid();
        $headers .= "\n";

        $headers .= @iconv_mime_encode('Subject', $this->getSubject(), $preferences);
        $headers .= "\n";

        $headers .= @iconv_mime_encode('From', $this->getFrom(), $preferences);
        $headers .= "\n";

        $headers .= @iconv_mime_encode('To', $this->getTo(), $preferences);
        $headers .= "\n";

        return $headers;
    }

    /**
     * Parsed body
     *
     * @return string
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Get parsing status
     *
     * @return boolean
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * Get parsing log
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Loads and analyzes the message
     *
     * @param string        $rawHeaders
     * @param string        $rawBody
     * @param Parser|null   $parser
     * @return boolean      Success or not
     */
    public function load($rawHeaders, $rawBody, $parser = null)
    {
        $this->rawHeaders = $rawHeaders;
        $this->rawBody = $rawBody;
        if ($parser)
            $this->parsedBody = "";

        $this->htmlMessage= '';
        $this->textMessage = '';
        $this->attachments = array();
        $this->log = "";

        $this->error = false;
        $this->log = "== Analyzing headers ==\n";

        $this->headers = array();
        $key = false;
        $value = "";
        foreach (explode("\n", $this->rawHeaders) as $line) {
            if (($line = rtrim($line)) == "")
                continue;
            if ($key && preg_match('/^\s+(.+)\s*$/', $line, $matches)) {
                $value[] = $matches[1];
                continue;
            }
            if (!preg_match('/^([^:]+)\s*:\s*(.*)\s*$/', $line, $matches)) {
                $this->log .= "Malformed line: $line\n";
                $this->error = true;
                break;
            }
            if ($key)
                $this->headers[$key] = $value;
            $key = $matches[1];
            $value = [ $matches[2] ];
        }
        if ($key)
            $this->headers[$key] = $value;

        if ($this->error) {
            $this->log .= "= Analysis aborted =\n";
            return false;
        }

        $this->log .= "= Success =\n";

        if (count($this->getHeader('MIME-Version'))) {
            $type = false;
            $this->boundary = null;
            $typeHeader = $this->getHeader('Content-Type');
            $typeValue = join(' ', $typeHeader);
            if (count($typeHeader) == 0) {
                $this->error = true;
                $this->log .= "Content-Type is not set\n";
                $this->log .= "= Analysis aborted =\n";
                return false;
            } else if (preg_match('/^text\/plain/', $typeValue, $matches)) {
                $this->log .= "Content-Type: text/plain\n";
                $type = "plain";
            } else if (preg_match('/^text\/html/', $typeValue, $matches)) {
                $this->log .= "Content-Type: text/html\n";
                $type = "html";
            } else if (preg_match('/^multipart\/related/', $typeValue, $matches)) {
                $this->log .= "Content-Type: multipart/related\n";
                $type = "related";
            } else if (preg_match('/^multipart\/mixed/', $typeValue, $matches)) {
                $this->log .= "Content-Type: multipart/mixed\n";
                $type = "related";
            } else if (preg_match('/^multipart\/alternative/', $typeValue, $matches)) {
                $this->log .= "Content-Type: multipart/alternative\n";
                $type = "alternative";
            } else if (preg_match('/^multipart\/report/', $typeValue, $matches)) {
                $this->log .= "Content-Type: multipart/report\n";
                $type = "report";
                $match = $this->lookupKey('report-type', $typeValue);
                if ($match)
                    $this->log .= "Report-Type: " . $match . "\n";
                else
                    $this->log .= "Report-Type is not set\n";
            }

            if (!$type) {
                $this->error = true;
                $this->log .= "Content-Type is unknown: " . $typeValue . "\n";
                $this->log .= "= Analysis aborted =\n";
                return false;
            }
            
            $match = $this->lookupKey('boundary', $typeValue);
            if ($match) {
                $this->boundary = $match;
                $this->log .= "Boundary: " . $this->boundary . "\n";
            } else {
                $this->boundary = null;
                $this->log .= "Boundary is not set\n";
            }
        } else {
            $this->log .= "This is not a MIME message, guess it's plain text.\n";
            $type = "plain";
            $this->boundary = null;
            $this->headers['Content-Type'] = [ "text/plain" ];
        }

        $this->log .= "== Analyzing body ==\n";

        $this->sections = $this->loadSections($this->rawBody, $this->boundary);
        if ($this->error) {
            $this->error = true;
            $this->log .= "= Analysis aborted =\n";
            return false;
        }

        $this->log .= "= Success =\n";

        if (in_array($type, [ "plain", "html" ])) {
            $type = $this->getHeader('Content-Type');
            $encoding = $this->getHeader('Content-Transfer-Encoding');
            $this->sections[0]['headers'] = [];
            if (count($type))
                $this->sections[0]['headers']['Content-Type'] = $type;
            if (count($encoding))
                $this->sections[0]['headers']['Content-Transfer-Encoding'] = $encoding;
        }

        $this->log .= "Structure:\n";
        $this->parseSections(1, $this->sections, $parser);
        if ($this->error) {
            $this->error = true;
            $this->log .= "= Analysis aborted =\n";
            return false;
        }

        $this->log .= "== All done ==\n";
        return true;
    }

    /**
     * Load MIME parts
     *
     * @param string            $body
     * @param string            $boundary
     * @return array
     */
    protected function loadSections($body, $boundary)
    {
        $sections = array();
        $buffer = '';
        if ($boundary) {
            $started = false;
            foreach (explode("\n", $body) as $line) {
                $line = rtrim($line);
                if ($line == "--" . $boundary . "--") {
                    if (strlen($buffer)) {
                        if (strlen($buffer) > 2
                                && $buffer[strlen($buffer) - 1] == "\n"
                                && $buffer[strlen($buffer) - 2] == "\n") {
                            $buffer = substr($buffer, 0, strlen($buffer) - 2);
                        }
                        $sections[] = $buffer;
                    }
                    break;
                }
                if ($line == "--" . $boundary) {
                    $started = true;
                    if (strlen($buffer)) {
                        if (strlen($buffer) > 2
                                && $buffer[strlen($buffer) - 1] == "\n"
                                && $buffer[strlen($buffer) - 2] == "\n") {
                            $buffer = substr($buffer, 0, strlen($buffer) - 2);
                        }
                        $sections[] = $buffer;
                    }
                    $buffer = '';
                    continue;
                }
                if ($started)
                    $buffer .= $line . "\n";
            }
        } else {
            $sections[] = $body;
        }

        $result = array();
        foreach ($sections as $section) {
            $headers = array();
            $body = '';
            if ($boundary) {
                $key = false;
                $value = "";
                $dataPart = false;
                foreach (explode("\n", $section) as $line) {
                    $line = rtrim($line);
                    if ($dataPart) {
                        if ($key) {
                            $headers[$key] = $value;
                            $key = false;
                        }
                        $body .= $line . "\n";
                    } else {
                        if ($line == "") {
                            $dataPart = true;
                            continue;
                        }
                        if ($key && preg_match('/^\s+(.+)\s*$/', $line, $matches)) {
                            $value[] = $matches[1];
                            continue;
                        }
                        if (!preg_match('/^([^:]+)\s*:\s*(.*)\s*$/', $line, $matches)) {
                            $this->log .= "Malformed line: $section\n";
                            $this->error = true;
                            return array();
                        }
                        if ($key)
                            $headers[$key] = $value;
                        $key = $matches[1];
                        $value = [ $matches[2] ];
                    }
                }
            } else {
                $body = $section;
            }
            $part = array(
                'headers'   => $headers,
                'body'      => $body,
                'boundary'  => $boundary
            );
            $contentType = null;
            foreach ($headers as $headerKey => $headerValue) {
                if (strtolower($headerKey) == 'content-type')
                    $contentType = join(' ', $headerValue);
            }
            $subsectionAlternative = preg_match('/^multipart\/alternative/', $contentType);
            $subsectionRelated = preg_match('/^multipart\/related/', $contentType);
            $subsectionMixed = preg_match('/^multipart\/mixed/', $contentType);
            $subsectionBoundary = $this->lookupKey('boundary', $contentType);
            if (($subsectionAlternative || $subsectionRelated || $subsectionMixed)
                     && $subsectionBoundary) {
                $part['sections'] = $this->loadSections($body, $subsectionBoundary);
            }
            $result[] = $part;
        }

        return $result;
    }

    /**
     * Parse sections
     *
     * @param integer       $level
     * @param array         $sections
     * @param Parser|null   $parser
     * @return boolean      False on error
     */
    protected function parseSections($level, $sections, $parser = null)
    {
        $prevBoundary = null;
        foreach ($sections as $section) {
            if ($section['boundary']) {
                if ($prevBoundary && $prevBoundary != $section['boundary'] && $parser)
                    $this->parsedBody .= '--' . $prevBoundary . "--\n";

                if ($parser) {
                    $this->parsedBody .= '--' . $section['boundary'] . "\n";
                    foreach ($section['headers'] as $headerKey => $headerValue) {
                        $this->parsedBody .= "$headerKey: ";
                        $this->parsedBody .= join("\n    ", $headerValue);
                        $this->parsedBody .= "\n";
                    }
                    $this->parsedBody .= "\n";
                }
                $prevBoundary = $section['boundary'];
            }

            $contentType = null;
            foreach ($section['headers'] as $headerKey => $headerValue) {
                if (strtolower($headerKey) == 'content-type')
                    $contentType = join(' ', $headerValue);
            }
            if (!$contentType) {
                $this->log .= "Content-Type is not set\n";
                $this->error = true;
                return false;
            }
            $type = explode(';', $contentType);
            $this->log .= str_repeat("  ", $level) . "- " . $type[0];

            if (isset($section['sections'])) {
                $this->log .= "\n";
                if (!$this->parseSections($level+1, $section['sections'], $parser))
                    return false;
                continue;
            }

            $this->log .= " (";
            $encoding = null;
            foreach ($section['headers'] as $headerKey => $headerValue) {
                if (strtolower($headerKey) == 'content-transfer-encoding')
                    $encoding = join(' ', $headerValue);
            }
            if ($encoding) {
                switch ($encoding) {
                    case 'base64':
                        $body = imap_base64($section['body']);
                        if (preg_match('/^text\/plain/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, false, false)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= imap_binary($parsed);
                            }
                        } else if (preg_match('/^text\/html/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, true, true)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= imap_binary($parsed);
                            }
                        } else if ($parser) {
                            $this->parsedBody .= $section['body'];
                        }
                        $this->log .= \Application\Tool\Text::sizeToStr(strlen($body));
                        break;
                    case 'quoted-printable':
                        $body = imap_qprint($section['body']);
                        if (preg_match('/^text\/plain/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, false, false)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= imap_8bit($parsed);
                            }
                        } else if (preg_match('/^text\/html/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, true, true)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= imap_8bit($parsed);
                            }
                        } else if ($parser) {
                            $this->parsedBody .= $section['body'];
                        }
                        $this->log .= \Application\Tool\Text::sizeToStr(strlen($body));
                        break;
                    case '7bit':
                    case '8bit':
                    case 'binary':
                        $body = $section['body'];
                        if (preg_match('/^text\/plain/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, false, false)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= $parsed;
                            }
                        } else if (preg_match('/^text\/html/', $contentType)) {
                            $charset = $this->lookupKey('charset', $contentType);
                            if ($charset) {
                                $converted = iconv($charset, 'UTF-8', $body);
                                if ($converted !== false)
                                    $body = $converted;
                            }
                            if ($parser) {
                                $parsed = $body;
                                if (!$parser->parse($body, $parsed, true, true)) {
                                    $this->error = true;
                                    return false;
                                }
                                $this->parsedBody .= $parsed;
                            }
                        } else if ($parser) {
                            $this->parsedBody .= $section['body'];
                        }
                        $this->log .= \Application\Tool\Text::sizeToStr(strlen($body));
                        break;
                    default:
                        $this->log .= "Unknown encoding: " . $encoding . ")";
                        $this->error = true;
                        return false;
                }
            } else {
                $body = $section['body'];
                if (preg_match('/^text\/plain/', $contentType)) {
                    $charset = $this->lookupKey('charset', $contentType);
                    if ($charset) {
                        $converted = iconv($charset, 'UTF-8', $body);
                        if ($converted !== false)
                            $body = $converted;
                    }
                    if ($parser) {
                        $parsed = $body;
                        if (!$parser->parse($body, $parsed, false, false)) {
                            $this->error = true;
                            return false;
                        }
                        $this->parsedBody .= $parsed;
                    }
                } else if (preg_match('/^text\/html/', $contentType)) {
                    $charset = $this->lookupKey('charset', $contentType);
                    if ($charset) {
                        $converted = iconv($charset, 'UTF-8', $body);
                        if ($converted !== false)
                            $body = $converted;
                    }
                    if ($parser) {
                        $parsed = $body;
                        if (!$parser->parse($body, $parsed, true, true)) {
                            $this->error = true;
                            return false;
                        }
                        $this->parsedBody .= $parsed;
                    }
                } else if ($parser) {
                    $this->parsedBody .= $section['body'];
                }
                $this->log .= \Application\Tool\Text::sizeToStr(strlen($body));
            }
            $this->log .= ")\n";
            if ($parser)
                $this->parsedBody .= "\n";

            if (preg_match('/^text\/plain/', $contentType)
                    || preg_match('/^message\/delivery-status/', $contentType)
                    || preg_match('/^message\/rfc822/', $contentType))
                $this->textMessage .= $body;
            else if (preg_match('/^text\/html/', $contentType))
                $this->htmlMessage .= $body;
            else if (!preg_match('/^multipart\/alternative/', $contentType)
                    && !preg_match('/^multipart\/related/', $contentType)
                    && !preg_match('/^multipart\/mixed/', $contentType)) {
                $disposition = null;
                foreach ($section['headers'] as $headerKey => $headerValue) {
                    if (strtolower($headerKey) == 'content-disposition')
                        $disposition = join(' ', $headerValue);
                }
                if ($disposition) {
                    if (!preg_match('/^inline/', $disposition) && !preg_match('/^attachment/', $disposition)) {
                        $this->log .= "Unknown content disposition: " . $disposition;
                        $this->error = true;
                        return false;
                    }
                    //if (!preg_match('/filename\s*=\s*"([^"]+)"/', $disposition, $matches)) {
                    $name = $this->lookupKey('filename', $disposition);
                    if (!$name) {
                        $this->log .= "Could not get filename: " . $disposition;
                        $this->error = true;
                        return false;
                    }
    
                    $cid = null;
                    $attType = null;
                    foreach ($section['headers'] as $headerKey => $headerValue) {
                        if (strtolower($headerKey) == 'content-id')
                            $cid = join(' ', $headerValue);
                        if (strtolower($headerKey) == 'content-type') {
                            $attType = join(' ', $headerValue);
                            $pos = strpos($attType, ';');
                            if ($pos != false)
                                $attType = substr($attType, 0, $pos);
                        }
                    }

                    $this->attachments[] = array(
                        'cid'   => $cid,
                        'name'  => $name,
                        'type'  => $attType ? $attType : 'application/octet-stream',
                        'data'  => $body
                    );
                }
            }
        }

        $boundary = $sections[count($sections) -1]['boundary'];
        if ($boundary && $parser)
            $this->parsedBody .= '--' . $boundary . "--\n";

        return true;
    }

    /**
     * Find key=value in string which might be in quotes
     *
     * @param string $key
     * @param string $string
     * @return string
     */
    protected function lookupKey($key, $string)
    {
        if (preg_match('/' . $key . '\s*=\s*"([^"]+)/i', $string, $matches))
            return $matches[1];
        if (preg_match('/' . $key . '\s*=\s*([^;\s]+)/i', $string, $matches))
            return $matches[1];

        return null;
    }
}

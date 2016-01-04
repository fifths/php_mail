<?php


namespace Mail;

/**
 * User: lee
 * Date: 16-1-4
 * Time: 上午9:12
 */
class Smtp
{
    public $host;
    public $port = 25;
    public $nickname = '';
    public $user;
    public $pass;
    public $type = 'text';
    public $debug = false;
    private $socket;
    private $conn;
    private $to;
    private $cc;
    private $bcc;
    private $subject;
    private $body;

    public function __construct()
    {
    }

    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->conn = socket_connect($this->socket, $this->host, $this->port);
    }


    public function sendMail($to, $subject, $body, $cc = '', $bcc = '')
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        if ($cc != '') {
            $this->cc = $cc;
        }
        if ($bcc != '') {
            $this->bcc = $bcc;
        }
        return $this->exec();
    }

    public function exec()
    {
        $this->connect();
        $msg = '';
        if ($this->conn) {
            $msg = "succeed connect to " . $this->host . ":" . $this->port . "\n";
            $msg .= $this->read();
            $this->write("HELO " . $this->host . "\r\n");
            $msg .= $this->read();
            $this->write("AUTH LOGIN " . base64_encode($this->user) . "\r\n");
            $msg .= $this->read();
            $this->write(base64_encode($this->pass) . "\r\n");
            $msg .= $this->read();
            if (stripos($msg, '235 Authentication successful') !== FALSE) {
                $this->write("MAIL FROM:<{$this->user}>\r\n");
                $msg .= $this->read();

                if (is_array($this->to)) {
                    foreach ($this->to as $v) {
                        $this->write("RCPT TO:<{$v}>\r\n");
                        $msg .= $this->read();
                    }
                } else {
                    $this->write("RCPT TO:<{$this->to}>\r\n");
                    $msg .= $this->read();
                }

                if (isset($this->cc)) {
                    if (is_array($this->cc)) {
                        foreach ($this->cc as $v) {
                            $this->write("RCPT TO:<{$v}>\r\n");
                            $msg .= $this->read();
                        }
                    } else {
                        $this->write("RCPT TO:<{$this->cc}>\r\n");
                        $msg .= $this->read();
                    }
                }


                if (isset($this->bcc)) {
                    if (is_array($this->bcc)) {
                        foreach ($this->bcc as $v) {
                            $this->write("RCPT TO:<{$v}>\r\n");
                            $msg .= $this->read();
                        }
                    } else {
                        $this->write("RCPT TO:<{$this->bcc}>\r\n");
                        $msg .= $this->read();
                    }
                }

                $this->write("DATA\r\n");
                $msg .= $this->read();

                $data = $this->buildHeader() . "\r\n" . $this->buildBody();
                $this->write($data);

                $this->write("\r\n.\r\n");
                $msg .= $this->read();

                $this->write("QUIT\r\n");
                $msg .= $this->read();
            }
        }

        return $msg;
    }

    private function buildHeader()
    {
        $header = "MIME-Version:1.0\r\n";
        if ($this->type == 'HTML') {
            $header .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        } else {
            $header .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        }
        $header .= "Subject: =?utf-8?B?" . base64_encode($this->subject) . "?=\r\n";
        $header .= "From: " . $this->nickname . " <" . $this->user . ">\r\n";
        if (is_array($this->to)) {
            $to = implode(',', $this->to);
        } else {
            $to = $this->to;
        }
        $header .= "To: " . $to . "\r\n";
        if (isset($this->cc)) {
            if (is_array($this->cc)) {
                $cc = implode(',', $this->cc);
            } else {
                $cc = $this->cc;
            }
            $header .= "Cc: " . $cc . "\r\n";
        }
        if (isset($this->bcc)) {
            if (is_array($this->bcc)) {
                $bcc = implode(',', $this->bcc);
            } else {
                $bcc = $this->bcc;
            }
            $header .= "Bcc: " . $bcc . "\r\n";
        }
        $header .= "Date: " . date("r") . "\r\n";
        list($msec, $sec) = explode(" ", microtime());
        $header .= "Message-ID: <Fifths_" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $this->user . ">\r\n";
        return $header;
    }

    private function buildBody()
    {
        $body = preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $this->body);
        return $body;
    }

    private function write($data)
    {
        socket_write($this->socket, $data);
    }

    private function read()
    {
        return socket_read($this->socket, 1024);
    }

    public function close()
    {
        if (is_resource($this->socket)) {
            unset($this->socket);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

}
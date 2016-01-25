<?php
namespace Fifths\Mail;


class SmtpMailer
{
    private $connection;

    private $host;

    private $port;

    private $username;

    private $password;

    private $secure;

    private $timeout;

    private $persistent;

    public function __construct(array $options = [])
    {
        if (isset($options['host'])) {
            $this->host = $options['host'];
            $this->port = isset($options['port']) ? (int)$options['port'] : NULL;
        } else {
            $this->host = ini_get('SMTP');
            $this->port = ini_get('smtp_port');
        }
        $this->username = isset($options['username']) ? $options['username'] : '';
        $this->password = isset($options['password']) ? $options['password'] : '';
        $this->secure = isset($options['secure']) ? $options['secure'] : '';
        $this->timeout = isset($options['timeout']) ? (int)$options['timeout'] : 20;
        if (!$this->port) {
            $this->port = $this->secure === 'ssl' ? 465 : 25;
        }
        $this->persistent = !empty($options['persistent']);
    }

    public function connect()
    {
        $this->connection = @stream_socket_client(
            ($this->secure === 'ssl' ? 'ssl://' : '') . $this->host . ':' . $this->port,
            $errno, $error, $this->timeout
        );
        stream_set_timeout($this->connection, $this->timeout, 0);
        $this->read();
        $this->write("HELO " . $this->host);
        if ($this->username != NULL && $this->password != NULL) {
            $this->write('AUTH LOGIN');
            $this->write(base64_encode($this->username));
            $this->write(base64_encode($this->password));
        }
    }

    protected function disconnect()
    {
        if (is_resource(($this->connection))) {
            fclose($this->connection);
        }
        $this->connection = NULL;
    }

    protected function write($data)
    {
        fwrite($this->connection, $data . "\r\n");
        $this->read();
    }

    protected function read()
    {
        $s = '';
        while (($line = fgets($this->connection, 1e3)) != NULL) { // intentionally ==
            $s .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        echo $s;
        return $s;
    }

}
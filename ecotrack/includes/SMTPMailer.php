<?php

class SMTPMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $debug = false;

    public function __construct($host, $port, $username, $password) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function send($to, $subject, $body, $fromName = 'EcoTrack Admin') {
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Connect Failed: $errstr ($errno)");
            return false;
        }

        $this->read($socket); // banner

        // Hello
        $this->cmd($socket, 'EHLO ' . $this->host);

        // STARTTLS if needed (port 587)
        if ($this->port == 587) {
            $this->cmd($socket, 'STARTTLS');
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->cmd($socket, 'EHLO ' . $this->host);
        }

        // Auth
        $this->cmd($socket, 'AUTH LOGIN');
        $this->cmd($socket, base64_encode($this->username));
        $this->cmd($socket, base64_encode($this->password));

        // Mail
        $this->cmd($socket, "MAIL FROM: <{$this->username}>");
        $this->cmd($socket, "RCPT TO: <$to>");

        // Data
        $this->cmd($socket, 'DATA');

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: {$fromName} <{$this->username}>";
        $headers[] = "To: <$to>";
        $headers[] = "Subject: $subject";
        $headers[] = "Date: " . date('r');

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        
        $result = $this->cmd($socket, $message); // Sending data

        $this->cmd($socket, 'QUIT');
        fclose($socket);

        // Simple check: if the last command (DATA end) was 250, success
        // Note: cmd() returns the response, looking for "250"
        return strpos($result, '250') !== false;
    }

    private function cmd($socket, $cmd) {
        if ($this->debug) echo "C: $cmd<br>";
        fputs($socket, $cmd . "\r\n");
        return $this->read($socket);
    }

    private function read($socket) {
        $response = '';
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == ' ') { break; }
        }
        if ($this->debug) echo "S: $response<br>";
        return $response;
    }
}
?>

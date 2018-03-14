<?php

namespace suffi\MailReader;

/**
 * Класс для подключения к серверу и чтения данных
 * Class Connection
 * @package suffi\MailReader
 */
class Connection
{
    private $socket = null;

    /**
     * Сервер
     * @var string
     */
    public $server = '';

    /**
     * Порт
     * @var string
     */
    public $port = '';

    /**
     * Логин
     * @var string
     */
    public $login = '';

    /**
     * Пароль
     * @var string
     */
    public $password = '';

    /**
     * Соединение
     * @return bool
     * @throws Exception
     */
    public function connect():bool
    {
        $this->socket = fsockopen($this->server, $this->port, $errno, $errstr, 10);
        if (!$this->socket) {
            throw new Exception('fsockopen() failed: ' . $errstr);
        }

        $read = $this->read();

        if ($this->login) {
            $this->write('USER ' . $this->login);
            $read = $this->read();
        }

        if ($this->password) {
            $this->write('PASS ' . $this->password);
            $read = $this->read();
        }

        return ($read == '+OK User successfully logged on.');
    }

    /**
     * Чтение данных
     * @return string
     */
    public function getData():string
    {
        $data = "";
        while (!feof($this->socket)) {
            $buffer = chop(fgets($this->socket, 1024));
            $data .= "$buffer\r\n";
            if (trim($buffer) == ".") {
                break;
            }
        }
        return $data;
    }

    /**
     * Функция для чтения ответа сервера.
     * @param bool $top Флаг чтения заголовков
     * @return bool|string
     * @throws Exception
     */
    public function read($top = false)
    {
        $read = fgets($this->socket);
        if ($top) {
            $line = $read;
            while (!preg_match("#^\.\r\n#", $line)) {
                $line = fgets($this->socket);
                $read .= $line;
            }
        }

        if ($read{0} != '+') {
            if (!empty($read)) {
                throw new Exception('POP3 failed: ' . $read);
            } else {
                throw new Exception('Unknown error');
            }
        }

        return trim($read);
    }

    /**
     * Функция для отправки запроса серверу
     * @param $msg
     */
    public function write($msg)
    {
        $msg = $msg . "\r\n";
        fwrite($this->socket, $msg);
    }
}

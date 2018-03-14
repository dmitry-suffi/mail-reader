<?php

namespace suffi\MailReader\Messages;

/**
 * Абстрактный класс сообщения
 * Class Message
 * @package suffi\MailReader\Messages
 */
abstract class Message implements MessageInterface
{
    protected $headers = [];

    protected $body = '';

    public function __construct(array $headers, string $body)
    {
        $this->headers = $headers;
        $this->body = $body;

        $this->parseBody();
    }

    /**
     * Заголовки
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Заголовок
     * @param $headerName
     * @return mixed|null
     */
    public function getHeader($headerName)
    {
        return $this->headers[$headerName] ?? null;
    }

    /**
     * Тело письма
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Метод разбора тела письма. Должен быть определен в реализациях класса
     * @return mixed
     */
    abstract protected function parseBody();

    /**
     * Перекодировщик тела письма.
     * Само письмо может быть закодировано и данная функция приводит тело письма в нормальный вид.
     * Так же и вложенные файлы будут перекодироваться этой функцией.
     * @param $enctype
     * @param $ctype
     */
    public function compileBody($enctype, $ctype)
    {
        $enctype = explode(" ", $enctype);
        $enctype = $enctype[0];
        if (strtolower($enctype) == "base64") {
            $this->body = base64_decode($this->body);
        } elseif (strtolower($enctype) == "quoted-printable") {
            $this->body = quoted_printable_decode($this->body);
        }
        if (preg_match("#koi?8#", $ctype)) {
            $this->body = iconv("KOI8-R", "UTF-8//IGNORE", $this->body);
        }
    }
}

<?php

namespace suffi\MailReader;

use suffi\MailReader\Messages\AttachmentMessage;
use suffi\MailReader\Messages\MailMessage;
use suffi\MailReader\Messages\MessageInterface;
use suffi\MailReader\Messages\PartMessage;

/**
 * Класс для парсинга письма
 * Class Parser
 * @package suffi\MailReader
 */
class Parser
{
    /**
     * Разбирает текст, возвращает объект сообщения
     * @param string $message
     * @param string $body
     * @param bool $isPart
     * @return MessageInterface
     */
    public static function parse(string $message, string $body = '', bool $isPart = false): MessageInterface
    {
        $separator = "\r\n\r\n";
        $header = trim(substr($message, 0, strpos($message, $separator)));
        $bodyPos = strlen($header) + strlen($separator);

        if (!$body) {
            $body = substr($message, $bodyPos, strlen($message) - $bodyPos);
        }

        $headers = self::parseHeaders($header);

        if (isset($headers["content-type"])) {
            $contentType = $headers["content-type"];
            $types = explode(';', $contentType);

            $rctype = strtolower($types[0]);

            // проверяем, является ли эта часть прикрепленным файлом
            if (isset($headers["content-id"]) && isset($headers["content-type"])
                && isset($headers["content-disposition"])) {
                if (preg_match("#name=#", $headers["content-disposition"] . $headers["content-type"])
                    || $headers["content-id"] != "" || $rctype == "message/rfc822") {
                    return new AttachmentMessage($headers, $body);
                }
            }
        }

        return ($isPart) ? new PartMessage($headers, $body) : new MailMessage($headers, $body);
    }

    /**
     * Разбор заголовков
     * @param $header
     * @return array
     */
    protected static function parseHeaders($header)
    {
        $headers = explode("\r\n", $header);
        $parsedHeaders = [];
        $lasthead = "";
        for ($i = 0; $i < count($headers); $i++) {
            $thisheader = trim($headers[$i]);
            if (!empty($thisheader)) {
                if (!preg_match('#^[A-Z0-9a-z_-]+:#', $thisheader)) {
                    if (isset($parsedHeaders[$lasthead])) {
                        $parsedHeaders[$lasthead] .= " $thisheader";
                    } else {
                        $parsedHeaders[$lasthead] = " $thisheader";
                    }
                } else {
                    $dbpoint = strpos($thisheader, ":");
                    $headname = strtolower(substr($thisheader, 0, $dbpoint));
                    $headvalue = trim(substr($thisheader, $dbpoint + 1));
                    if (isset($parsedHeaders[$headname])) {
                        $parsedHeaders[$headname] .= "; $headvalue";
                    } else {
                        $parsedHeaders[$headname] = $headvalue;
                    }
                    $lasthead = $headname;
                }
            }
        }

        foreach ($parsedHeaders as $key => $value) {
            $parsedHeaders[$key] = self::decode($value);
        }
        return $parsedHeaders;
    }

    /**
     * Перекодирование
     * @todo необходимо рефаторить
     * @param $text
     * @return bool|string
     */
    public static function decode($text)
    {
        $string = $text;
        if (($pos = strpos($string, "=?")) === false) {
            return $string;
        }
        $newResult = '';
        while (!($pos === false)) {
            if (isset($newResult)) {
                $newResult .= substr($string, 0, $pos);
            } else {
                $newResult = substr($string, 0, $pos);
            }

            $string = substr($string, $pos + 2, strlen($string));
            $intpos = strpos($string, "?");
            $enctype = strtolower(substr($string, $intpos + 1, 1));
            $string = substr($string, $intpos + 3, strlen($string));
            $endpos = strpos($string, "?=");
            $mystring = substr($string, 0, $endpos);
            $string = substr($string, $endpos + 2, strlen($string));
            if ($enctype == "q") {
                $mystring = quoted_printable_decode(preg_replace("#_#", " ", $mystring));
            } elseif ($enctype == "b") {
                $mystring = base64_decode($mystring);
            }
            $newResult .= $mystring;
            $pos = strpos($string, "=?");
        }

        $result = $newResult . $string;

        if (preg_match("#.*koi8.*#", $text) || preg_match("#.*KOI8.*#", $text)) {
            $result = iconv("KOI8-R", "UTF-8//IGNORE", $result);
        }
        return $result;
    }
}

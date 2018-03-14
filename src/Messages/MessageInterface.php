<?php

namespace suffi\MailReader\Messages;

/**
 * Интерфейс сообщения
 * Interface MessageInterface
 * @package suffi\MailReader\Messages
 */
interface MessageInterface
{

    /**
     * Заголовки
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Заголовок по имени
     * @param $headerName
     * @return mixed|null
     */
    public function getHeader($headerName);

    /**
     * Тело сообщения
     * @return string
     */
    public function getBody();
}

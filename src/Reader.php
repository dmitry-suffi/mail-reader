<?php

namespace suffi\MailReader;

use suffi\MailReader\Messages\MessageInterface;

/**
 * Класс для чтения писем
 * Class Reader
 * @package suffi\MailReader
 */
class Reader
{
    /** @var  Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Получение количества и общего размера в байтах
     * @return array [$totalCount, $totalSize]
     */
    public function getStats():array
    {
        // Определяем кол-во сообщений в ящике и общий размер
        $this->connection->write('STAT');
        $answer = $this->connection->read(); // ответ сервера

        preg_match('!([0-9]+)[[:space:]]([0-9]+)!is', $answer, $matches);
        $totalCount = $matches[1];
        $totalSize = $matches[2];

        return [$totalCount, $totalSize];
    }

    /**
     * Получение письма по номеру
     * @param int $i
     * @return MessageInterface
     */
    public function getMessage(int $i):MessageInterface
    {

        $this->connection->write('TOP ' . $i . ' 0');
        $answer = $this->connection->read(true);

        $this->connection->write('RETR ' . $i);
        $body = $this->connection->getData();

        $message = Parser::parse($answer, $body);

        return $message;
    }

    /**
     * Итератор по письмам с конца
     * @return \Generator
     */
    public function getMessages()
    {
        $count = $this->getStats()[0];

        for ($i = $count; $i > 0; $i--) {
            yield $this->getMessage($i);
        }
    }

    /**
     * Удаление письма
     * @param $index
     */
    public function deleteMessageById($index)
    {
        $this->connection->write('DELE ' . $index);
    }
}

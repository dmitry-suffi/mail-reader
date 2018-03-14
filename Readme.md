Библиотека для чтения писем
===========================

Установка
---------

Предпочтительный способ установить это расширение через [composer](http://getcomposer.org/download/).

Необходимо добавить строку

```
"suffi/mail-reader": "*"
```

в раздел  ```require``` файла `composer.json` Вашего приложения.

Использование
-------------

```php

$conn = new \suffi\MailReader\Connection();

$conn->login = '***@xxx.ru';
$conn->password = '******';
$conn->server = 'ssl://outlook.office365.com';
$conn->port = '995';

$conn->connect();

$reader = new \suffi\MailReader\Reader($conn);

$message = $reader->getMessage(1273);

$message->getBody(); //Текст
 
$message->getHeader('date'); //Заголовки 
$message->getHeader('message-id'); //Заголовки 
$message->getHeader('subject'); //Заголовки 

/** Обход писем за последний день */
foreach ($reader->getMessages() as $message) {
    $curentDate = new DateTime();
    $date = new DateTime($message->getHeader('date'));

    if ($date->diff($curentDate)->d > 0) {
        break;
    }

    $message->getHeader('subject');    

}


```
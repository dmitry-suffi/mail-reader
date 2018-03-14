<?php

namespace suffi\MailReader\Messages;

use suffi\MailReader\Parser;

/**
 * Сообщение - вложение в письмо
 * Class AttachmentMessage
 * @package suffi\MailReader\Messages
 */
class AttachmentMessage extends Message
{
    /**
     * Имя файла
     * @var string
     */
    public $filename = '';

    /**
     * @inheritdoc
     */
    protected function parseBody()
    {
        // Имя файла можно выдернуть из заголовков Content-Type или Content-Disposition
        $cdisp = $this->getHeader("content-disposition");
        if (!$cdisp) {
            return;
        }
        $ctype = $this->getHeader("content-type");

        $disp = explode(";", $cdisp);
        if (isset($disp[1])) {
            $fname = $disp[1];
            $filename = '';
            if (preg_match("/filename=(.*)/", $fname, $regs)) {
                $filename = $regs[1];
            }
            if ($filename == "" && preg_match("/name=(.*)/", $ctype, $regs)) {
                $filename = $regs[1];
            }
            $filename = preg_replace("/\"(.*)\"/", "\1", $filename);

            // как получили имя файла, теперь его нужно декодировать
            $filename = trim(Parser::decode($filename));

            // теперь читаем файл в переменную.
            $encoding = $this->getHeader("content-transfer-encoding");
            if ($encoding) {
                $this->compileBody($encoding, $ctype);
            }

            $this->filename = $filename;
        }
    }
}

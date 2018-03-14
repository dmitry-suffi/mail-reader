<?php

namespace suffi\MailReader\Messages;

use suffi\MailReader\Parser;

/**
 * Сообщение, представляющее полное письмо
 * Class MailMessage
 * @package suffi\MailReader\Messages
 */
class MailMessage extends Message
{

    protected $attachments = [];

    protected $parts = [];

    /**
     * @inheritdoc
     */
    protected function parseBody()
    {
        $contentType = $this->getHeader('content-type');
        $contentTransferEncoding = $this->getHeader('content-transfer-encoding');

        /** Несколько частей */
        if (preg_match('/boundary[ |"]?=[ ]?(["]?.*)/i', $contentType, $regs)) {
            $boundary = trim($regs[1], '"');
            $boundary =  trim("--" . $boundary);

            $startPos = strpos($this->body, $boundary) + strlen($boundary) + 2;
            $length = strpos($this->body, "\r\n$boundary--") - $startPos;
            $body = substr($this->body, $startPos, $length);
            $parts = explode($boundary . "\r\n", $body);

            foreach ($parts as $part) {
                $message = Parser::parse($part, '', true);
                $this->parts[] = $message;

                if ($message instanceof AttachmentMessage) {
                    $this->attachments[$message->filename] = $message->getBody();
                } else {
                    $this->body .= $message->getBody();
                }
            }
        } else {
            if ($contentTransferEncoding) {
                $this->compileBody($contentTransferEncoding, $contentType);
            }
        }
    }
}

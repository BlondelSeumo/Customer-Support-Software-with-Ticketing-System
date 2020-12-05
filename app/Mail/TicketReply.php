<?php namespace App\Mail;

use App;
use App\Reply;
use App\Services\Mail\TicketReferenceHash;
use App\Ticket;
use Common\Files\FileEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Message;

class TicketReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * @var Reply
     */
    public $reply;

    /**
     * @var string
     */
    public $reference;

    /**
     * @param Ticket $ticket
     * @param Reply $reply
     */
    public function __construct(Ticket $ticket, Reply $reply)
    {
        $this->reply = $reply;
        $this->ticket = $ticket;
        $this->reference = App::make(TicketReferenceHash::class)->makeEmbedForEmail($reply);
    }

    /**
     * @return $this
     */
    public function build()
    {
        $this->to($this->ticket->user->email)
             ->subject("RE: {$this->ticket->subject}")
             ->view('tickets.ticket-reply.ticket-reply')
             ->text('tickets.ticket-reply.ticket-reply-plain');

        $this->addHeaders();
        $this->addAttachments();

        return $this;
    }

    /**
     * Add ticket reference hash as email x-header.
     */
    private function addHeaders()
    {
        $this->withSwiftMessage(function(Swift_Message $swiftMessage) {
            $messageId = App::make(TicketReferenceHash::class)->makeMessageIdForEmail($this->reply);
            $swiftMessage->setId($messageId);
        });
    }

    /**
     * Add uploads from latest ticket reply to email.
     */
    private function addAttachments()
    {
        if ($this->reply->uploads->isEmpty()) return;

        $basePath = rtrim($this->reply->uploads->first()->getDisk()->getAdapter()->getPathPrefix());

        $this->reply->uploads->each(function(FileEntry $upload) use($basePath) {
            $this->attach($basePath.'/'.$upload->getStoragePath(), [
                'as'   => $upload->name,
                'mime' => $upload->mime,
            ]);
        });
    }
}

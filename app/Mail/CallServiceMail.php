<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CallServiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $callService;

    public function __construct($callService)
    {
        $this->callService = $callService;
    }

    public function build()
    {
        return $this->subject('Pemanggilan Service Baru dari ' . $this->callService->user->name)
                    ->markdown('emails.call_service.notification');
    }
}

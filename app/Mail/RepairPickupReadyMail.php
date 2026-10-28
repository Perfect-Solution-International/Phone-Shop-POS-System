<?php

namespace App\Mail;

use App\Models\RepairJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepairPickupReadyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public RepairJob $repair)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $shopName = $this->repair->shop->name ?? 'Phone Shop';

        return new Envelope(subject: "Your Device is Ready for Collection! [Ticket #{$this->repair->job_number}] - {$shopName}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.repair-pickup-ready');
    }
}


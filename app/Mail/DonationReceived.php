<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $donation;
    public $campaign;
    public $donor;

    /**
     * Create a new message instance.
     */
    public function __construct($donation, $campaign, $donor)
    {
        $this->donation = $donation;
        $this->campaign = $campaign;
        $this->donor = $donor;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Your Donation!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.donation_received',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}


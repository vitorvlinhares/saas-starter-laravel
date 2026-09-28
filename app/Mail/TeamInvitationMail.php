<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $token  Plain token. Only its SHA-256 hash is stored.
     */
    public function __construct(public TeamInvitation $invitation, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->invitation->team->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-invitation',
            with: [
                'teamName' => $this->invitation->team->name,
                'role' => $this->invitation->role->label(),
                'acceptUrl' => route('invitations.accept', $this->token),
                'expiresAt' => $this->invitation->expires_at->toFormattedDayDateString(),
            ],
        );
    }
}

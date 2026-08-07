<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Membuat instance pesan mailable baru.
     *
     * @param  User  $user  Model pengguna yang baru dibuat.
     * @param  string  $plainPassword  Password mentah sebelum di-hash.
     */
    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    /**
     * Mendapatkan amplop pesan (subject dan pengirim).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Akun & Kredensial Akses Sistem',
        );
    }

    /**
     * Mendapatkan definisi konten template email (Markdown).
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.users.created',
        );
    }

    /**
     * Mendapatkan lampiran untuk pesan email.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

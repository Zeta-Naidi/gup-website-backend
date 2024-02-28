<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMfaByMail extends Mailable
{
  use Queueable, SerializesModels;

  private $username;
  private $otp;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($username, $otp)
  {
    $this->username = $username;
    $this->otp = $otp;
  }

  /**
   * Get the message envelope.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope()
  {
    return new Envelope(
      subject: 'Otp Mfa Ermetix',
    );
  }

  /**
   * Get the message content definition.
   */
  public function build()
  {
    return $this->view('mailOtpMfaByMail', [
      "username" => $this->username,
      "otp" => $this->otp,
    ]);
  }

  /**
   * Get the attachments for the message.
   *
   * @return array
   */
  public function attachments()
  {
    return [];
  }
}

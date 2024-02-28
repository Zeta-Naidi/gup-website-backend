<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionCatchedMail extends Mailable
{
    use Queueable, SerializesModels;
    private $host;
    private $description;
    private $file;
    private $line;
    private $backtrace;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($infosExceptions = array())
    {
      $this->host = $infosExceptions['host'] ?? null;
      $this->description = $infosExceptions['description'] ?? null;
      $this->line = $infosExceptions['line'] ?? null;
      $this->backtrace = $infosExceptions['backtrace'] ?? null;
      $this->file = $infosExceptions['file'] ?? null;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mailException',[
          "host" => $this->host,
          "description" => $this->description,
          "line" => $this->line,
          "backtrace" => $this->backtrace,
          "file" => $this->file,
        ]);
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $formData;

    /**
     * Create a new message instance.
     *
     * @param $formData
     */
    public function __construct($formData)
    {
        $this->formData = $formData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('博客联系我们表单：' . $this->formData['name'])
            ->view('emails.contact', ['data' => $this->formData]);
    }
}

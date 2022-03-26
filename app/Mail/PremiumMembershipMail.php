<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PremiumMembershipMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $content;
	
    public function __construct($content)
    {
		$this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject($this->content->subject)
                    ->markdown('backend.email.general_template')
					->with('content',$this->content);
        if(isset($this->content->fileName))
            $email->attachData($this->content->file, $this->content->fileName);
        return $email;
    }
}

<?php

namespace App\Jobs;

use App\Mail\sendMailRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailWhenRegiterAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */


    public $data;
    public $mail_to;
    public function __construct($mail_to, $data)
    {
        $this->data = $data;
        $this->mail_to = $mail_to;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return  Mail::to($this->mail_to)->send(new sendMailRegister($this->data));
    }
}

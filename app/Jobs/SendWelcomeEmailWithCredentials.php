<?php

namespace App\Jobs;
use App\Mail\WelcomeMailWithCredentials;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmailWithCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $password;
    /**
     * Create a new job instance.
     */
     public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }


    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new WelcomeMailWithCredentials($this->user, $this->password));
    }
}

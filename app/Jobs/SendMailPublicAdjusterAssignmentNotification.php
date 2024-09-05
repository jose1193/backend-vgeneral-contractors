<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\PublicAdjusterAssignmentNotification;

class SendMailPublicAdjusterAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected object $user,protected object $claim)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         Mail::to($this->user->email)->send(new PublicAdjusterAssignmentNotification($this->user,$this->claim));
    }
}

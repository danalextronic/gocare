<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Import;
use App\Order;
use Mail;
use App\User;

class SendImportOrdersEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $email_message;
    protected $order;

    /**
     * Create a new job instance.
     * @param string $email_message
     * @param Order $order
     */
    public function __construct($email_message, $order)
    {
        $this->order = $order;
        $this->email_message = $email_message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::findOrFail($this->order->user_id);

        Mail::send('emails.order_import_failed', ['user' => $user, 'email_message' => $this->email_message, 'order' => $this->order], function ($m) use ($user) {
            $m->from('no-reply@gocare.com', 'GoCare');

            $m->to($user->email, $user->name)->subject('Trouble with your recent order import.');
        });
    }
}

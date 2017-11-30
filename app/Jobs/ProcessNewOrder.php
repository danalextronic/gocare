<?php

namespace App\Jobs;

use App\Order;
use App\Import;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Library\Magento;

use App\Jobs\SendImportOrdersEmail;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ProcessNewOrder extends Job implements ShouldQueue
{
    protected $magento;

    use InteractsWithQueue, SerializesModels;
    use DispatchesJobs;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Create a new job instance.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $import = null;
        $magento = $this->getMagentoInstance();
        if ($magento->createOrder($this->order)) {
            $this->order->status = 'complete';
        } else {
            $this->order->status = 'failed';
        }
        $this->order->save();
        $email_message = null;

        // if the order was part of an import, update the import status too
        if ($this->order->import_id) {
            $email_message = null;
            $status_message = null;
            $import = Import::where('id', '=', $this->order->import_id)->first();
            // first see if we're done, if not, just update the x out of x message
            $orders = Order::where('import_id', '=', $import->id)->get();
            $non_pending_orders = Order::where('import_id', '=', $import->id)->where('status', '!=', 'pending')->get();
            if ($orders->count() !== $non_pending_orders->count()) { // we're not done yet
                $status_message = $non_pending_orders->count() . ' of ' . $orders->count() . ' processed';
                $import->status = 'processing';
            } else {
                // if we're done, see if there are errors, if so, fire the email and update the message
                $failed_orders = Order::where('import_id', '=', $import->id)->where('status', '=', 'failed')->get();
                // if no errors, just send an email and say things are completed
                if ($failed_orders->count() === 0) {
                    $import->status = 'completed';
                    $status_message = 'Your import has been processed with zero errors.';
                    $email_message = $status_message;
                } else {
                    $import->status = 'completed_with_errors';
                    $status_message = 'Your import has been processed, there are (' . $failed_orders->count() . ') errors';
                    $status_message .= ($failed_orders->count() !== 1) ? 's ' : ' ';
                    $status_message .= 'for your review.';
                    $email_message = $status_message;
                }
            }
            if ($status_message) {
                $import->status_message = $status_message;
                $import->save();
            }
            if ($email_message) {
                $this->dispatch(new SendImportOrdersEmail($email_message, $this->order));
            }
        } else {
            if ($this->order->status === 'failed') {
                $email_message = 'We had a problem processing your order. The reason was ' . $this->order->failed_message . '. Please login [here] to correct the issue.';
                $this->dispatch(new SendImportOrdersEmail($email_message, $this->order));
            }
        }
    }

    private function getMagentoInstance()
    {
        if(null === $this->magento){
            $this->magento = new Magento();
        }
        return $this->magento;
    }
}

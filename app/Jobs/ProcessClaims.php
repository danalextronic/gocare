<?php

namespace App\Jobs;

use App\Claim;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;
use App\Library\Magento;

class ProcessClaims extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $claim;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Claim $claim)
    {
        $this->claim = $claim;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $magento = new Magento();
        if ($magento->createClaim($this->claim)) {
            $this->claim->status = 'complete';
        } else {
            $this->claim->status = 'failed';
        }
        $this->claim->save();
    }
}

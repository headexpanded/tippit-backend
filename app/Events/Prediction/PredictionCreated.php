<?php

namespace App\Events\Prediction;

use App\Models\Prediction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PredictionCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Prediction $prediction;

    /**
     * @param  Prediction  $prediction
     */
    public function __construct(Prediction $prediction)
    {
        $this->prediction = $prediction;
    }
}

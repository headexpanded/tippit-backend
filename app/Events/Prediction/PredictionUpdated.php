<?php

namespace App\Events\Prediction;

use App\Models\Prediction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PredictionUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Prediction $prediction;
    public array $oldValues;

    /**
     * @param  Prediction  $prediction
     * @param  array  $oldValues
     */
    public function __construct(Prediction $prediction, array $oldValues)
    {
        $this->prediction = $prediction;
        $this->oldValues = $oldValues;
    }
}

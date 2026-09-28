<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Property;
use Throwable;
use App\Services\ReviewSummaryService;
use Illuminate\Support\Facades\Log;

class GenerateReviewSummaryJob implements ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 300; // Lock expires after 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(public int $propertyId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ReviewSummaryService $reviewSummaryService): void
    {
        $property=Property::find($this->propertyId);

        if(! $property){
            return;
        }

        $reviewSummaryService->generate($property);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to generate property review summary', [
            'property_id' => $this->propertyId,
            'error' => $exception->getMessage(),
        ]);
    }
}

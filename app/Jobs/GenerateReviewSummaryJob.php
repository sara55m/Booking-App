<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Property;
use Throwable;
use App\Services\ReviewSummaryService;
use Illuminate\Support\Facades\Log;

class GenerateReviewSummaryJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $propertyId)
    {
        //
    }
    //generate a unique key for jobs fired for a specific property
    public function uniqueId(): string
    {
        return 'property-review-summary-' . $this->propertyId;
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

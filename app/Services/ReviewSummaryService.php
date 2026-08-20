<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

class ReviewSummaryService
{
    private const REVIEW_THRESHOLD = 5;

    public function __construct(
        private GroqService $groqService
    ) {
    }

    public function shouldRegenerateForReview(Review $review): bool
    {
        $property=$review->property;
        if (! $property) {
            return false;
        }

        $oldStatus = $review->getRawOriginal('status');
        $newStatus = $review->status;

        //handle review status transitions

        //1.approved->rejected regenerate the summary if a previously approved review gets rejected
        if ($oldStatus === ReviewStatus::Approved->value && $newStatus === ReviewStatus::Rejected)
        {
            return true;
        }

        //2.pending/rejected->approved
        //apply the threshold logic
        if($newStatus===ReviewStatus::Approved)
        {
            return $this->shouldRegenerate($property);
        }

        return false;
    }

    public function shouldRegenerate(Property $property): bool{
        // No summary exists yet
        if (! $property->ai_review_summary_generated_at) {
            return $property->reviews()
                ->where('status', ReviewStatus::Approved)
                ->exists();
        }

        //threshold logic->regenerate the ai summary after 5 or more reviews are approved
        $aiReviewsCount=$property->ai_review_summary_review_count;
        $currentReviewsCount=$property->reviews()
        ->where('status',ReviewStatus::Approved)
        ->count();

        return ($currentReviewsCount - $aiReviewsCount) >= self::REVIEW_THRESHOLD;
    }

    public function generate(Property $property): array
    {
        //build review context
        $context = $this->buildReviewContext($property);

        $reviewsCount=$context['total_reviews'];

        if ($reviewsCount === 0) {
            $emptySummary = [
                'summary' => null,
                'positive_points' => [],
                'negative_points' => [],
                'notable_features' => [],
            ];

            $property->update([
                'ai_review_summary' => $emptySummary,
                'ai_review_summary_generated_at' => now(),
                'ai_review_summary_review_count'=>$reviewsCount
            ]);

            return $emptySummary;
        }

        $systemPrompt = $this->buildSystemPrompt();

        $userPrompt = $this->buildUserPrompt($context);

        //call groq to generate the ai summary
        $summary= $this->groqService->chatJson(
            $systemPrompt,
            $userPrompt
        );

        //save the summary to the property
        $property->update([
            'ai_review_summary'=>$summary,
            'ai_review_summary_generated_at'=>now(),
            'ai_review_summary_review_count'=>$reviewsCount
        ]);

        return $summary;
    }

    public function buildReviewContext(Property $property): array
    {

        $reviews=$property->reviews()
        ->where('status',ReviewStatus::Approved)
        ->with(['tags','categories'])
        ->latest()
        ->get();

        return [
            'total_reviews' => $reviews->count(),

            'overall_rating' => $property->average_rating,

            'tag_statistics' => $this->getTagStatistics($reviews),

            'category_statistics' => $this->getCategoryStatistics($reviews),

            'reviews' => $this->getReviews($reviews)
        ];

    }

    public function getReviews(Collection $reviews): array
    {
        return $reviews
            ->map(function ($review) {
                return [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                ];
            })
            ->values()
            ->all();
    }

    public function getTagStatistics(Collection $reviews): array
    {
        return $reviews
            ->flatMap(function ($review) {
                return $review->tags;
            })
            ->groupBy('type')  //['positive' => [...],'negative' => [...],'neutral' => [...],]
            ->map(function ($tags) {
                return $tags
                    ->groupBy('id')
                    ->map(function ($group) {
                        $tag = $group->first(); //get the tag only one time by its id as it may be repeated across multiple reviews

                        return [
                            'name' => $tag->name,
                            'count' => $group->count(),//how many approved reviews contain that tag
                        ];
                    })
                    ->sortByDesc('count')
                    ->values()
                    ->all();
            })
            ->all();
    }

    public function getCategoryStatistics(Collection $reviews): array
    {
        return $reviews
            ->flatMap(function ($review) {
                return $review->categories->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'rating' => $category->pivot->rating,
                    ];
                });
            })
            ->groupBy('name')
            ->map(function ($ratings, $name) {
                return [
                    'name' => $name,
                    'average_rating' => round(
                        $ratings->avg('rating'),
                        1
                    ),
                    'count' => $ratings->count(),/*[
                        [
                            'name' => 'Cleanliness',
                            'average_rating' => 4.5,
                            'count' => 2,
                        ],
                        [
                            'name' => 'Location',
                            'average_rating' => 4.5,
                            'count' => 2,
                        ],
                    ]*/
                ];
            })
            ->values()
            ->all();
    }

    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
            You are an AI assistant for a hotel booking platform.

            Your task is to summarize approved guest reviews for a property.

            Use ONLY the information provided in the review data.

            Rules:
            - Do not invent facts.
            - Do not make assumptions.
            - Do not mention individual guests.
            - Do not mention guest names.
            - Do not exaggerate isolated opinions.
            - Give more importance to frequently mentioned themes.
            - Positive tags are already classified as positive.
            - Negative tags are already classified as negative.
            - Neutral tags describe notable features and should not be treated as positive or negative.
            - Use category ratings as supporting evidence.
            - Keep the summary concise and useful to someone deciding whether to book the property.

            Return JSON with exactly these fields:

            {
                "summary": "A concise overall summary",
                "positive_points": [
                    "Most important positive point"
                ],
                "negative_points": [
                    "Most important negative point"
                ],
                "notable_features": [
                    "Relevant neutral feature"
                ]
            }

            Return only valid JSON.
            PROMPT;
    }

    private function buildUserPrompt(array $context): string
    {
        return <<<PROMPT
            Analyze the following approved guest review data.

            Overall rating:
            {$context['overall_rating']}

            Total approved reviews:
            {$context['total_reviews']}

            Category statistics:
            {$this->encodeJson($context['category_statistics'])}

            Tag statistics:
            {$this->encodeJson($context['tag_statistics'])}

            Guest reviews:
            {$this->encodeJson($context['reviews'])}
            PROMPT;
    }

    private function encodeJson(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

}






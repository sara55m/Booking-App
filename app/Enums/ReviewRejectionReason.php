<?php

namespace App\Enums;

enum ReviewRejectionReason: string
{
    case InappropriateContent = 'inappropriate_content';
    case PersonalInformation = 'personal_information';
    case IrrelevantContent = 'irrelevant_content';
    case SpamOrPromotionalContent = 'spam_or_promotional_content';
    case FakeOrSuspiciousReview = 'fake_or_suspicious_review';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

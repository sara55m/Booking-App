<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'check_in' => [
                'from' => $this->check_in_from->format('g:i A'),
                'until' => $this->check_in_until->format('g:i A'),
            ],

            'check_out' => [
                'from' => $this->check_out_from->format('g:i A'),
                'until' => $this->check_out_until->format('g:i A'),
            ],

            'pets_allowed' => $this->pets_allowed,

            'children_allowed' => $this->children_allowed,

            'smoking_allowed' => $this->smoking_allowed,

            'minimum_check_in_age' => $this->minimum_check_in_age,

            'cancellation_policy' => $this->cancellation_policy,

            'important_information' => $this->important_information,

        ];
    }
}

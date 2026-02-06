<?php

namespace App\Services;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationCategory;
use App\Models\Amenity;

class FilterService
{
    public function getAmenityFilters()
    {
        $amenities = Amenity::where('is_highlighted', true)
            ->get();

        $filters = [];

        foreach ($amenities as $amenity) {
            $amenityFilter = $this->formFilterOption($amenity->slug, $amenity->name, $amenity->icon);
            array_push($filters, $amenityFilter);
        }

        return [
            'value' => 'amenities',
            'title' => __('filters.amenities'),
            'options' => $filters
        ];
    }

    public function getAccommodationCategoryFilters()
    {
        $accommodationCategories = collect(AccommodationCategory::cases())
            ->map->toArray()
            ->values();

        $filters = [];

        foreach ($accommodationCategories as $accommodationCategory) {
            $amenityFilter = $this->formFilterOption($accommodationCategory['value'], $accommodationCategory['name'], $accommodationCategory['icon']);
            array_push($filters, $amenityFilter);
        }

        return [
            'value' => 'accommodation_categories',
            'title' => __('filters.accommodation_category'),
            'options' => $filters
        ];
    }

    public function getAccommodationOccupationFilters()
    {
        $accommodationOccupations = collect(AccommodationOccupation::cases())
            ->map->toArray()
            ->values();

        $filters = [];

        foreach ($accommodationOccupations as $accommodationOccupation) {
            $amenityFilter = $this->formFilterOption($accommodationOccupation['value'], $accommodationOccupation['name'], null);
            array_push($filters, $amenityFilter);
        }

        return [
            'value' => 'accommodation_occupations',
            'title' => __('filters.accommodation_occupation'),
            'options' => $filters
        ];
    }

    private function formFilterOption($value, $title, $icon): array
    {
        return [
            'value' => $value,
            'title' => $title,
            'icon' => $icon,
        ];
    }
}

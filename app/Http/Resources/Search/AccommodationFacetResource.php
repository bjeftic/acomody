<?php


namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Lang;
use App\Models\Amenity;

class AccommodationFacetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $counts = [];
        foreach ($this->resource['counts'] as $filter) {
            if($this->resource['field_name'] === 'amenities') {
                $amenity = Amenity::where('slug', $filter['value'])->first();
                $filter['title'] = $amenity->name ?? $filter['value'];
                $filter['category'] = __('amenity_category.'.$amenity->category) ?? null;
            } else {
                if (Lang::has('enums/'.$this->resource['field_name'].'.'.$filter['value'])) {
                    $filter['title'] = __('enums/'.$this->resource['field_name'].'.'.$filter['value']);
                } else {
                    $filter['title'] = $filter['value'];
                }
            }

            array_push($counts, $filter);
        }

        return [
            'counts' => $counts,
            'field_name' => $this->resource['field_name'],
            'sampled' => $this->resource['sampled'],
            'stats' => $this->resource['stats'],
            'title' => __('filters.'.$this->resource['field_name']),
        ];
    }
}

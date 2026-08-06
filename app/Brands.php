<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Return list of brands for a business
     *
     * @param  int  $business_id
     * @param  bool  $show_none = false
     * @param  bool  $filter_use_for_repair = false
     * @param  bool  $only_pos_visible When true, hide brands with show_in_pos = 0
     * @return \Illuminate\Support\Collection
     */
    public static function forDropdown($business_id, $show_none = false, $filter_use_for_repair = false, $only_pos_visible = false)
    {
        $query = Brands::where('business_id', $business_id);

        if ($filter_use_for_repair) {
            $query->where('use_for_repair', 1);
        }

        if ($only_pos_visible) {
            $query->where(function ($q) {
                $q->where('show_in_pos', 1)->orWhereNull('show_in_pos');
            });
        }

        $brands = $query->orderBy('name', 'asc')
                    ->pluck('name', 'id');

        if ($show_none) {
            $brands->prepend(__('lang_v1.none'), '');
        }

        return $brands;
    }
}

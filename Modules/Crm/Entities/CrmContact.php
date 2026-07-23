<?php

namespace Modules\Crm\Entities;

use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CrmContact extends Contact
{
    protected $table = 'contacts';

    public function leadUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crm_lead_users', 'contact_id', 'user_id')
            ->withTimestamps();
    }

    public function scopeLeadsOnly($query)
    {
        return $query->where('contacts.type', 'lead');
    }

    public function scopeCustomersOnly($query)
    {
        return $query->whereIn('contacts.type', ['customer', 'both']);
    }

    public static function getSourcesDropdown(int $business_id): array
    {
        return \App\Category::forDropdown($business_id, 'crm_source')->toArray();
    }

    public static function getLifeStagesDropdown(int $business_id): array
    {
        return \App\Category::forDropdown($business_id, 'crm_life_stage')->toArray();
    }
}

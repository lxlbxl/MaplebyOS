<?php

namespace Modules\CRM\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'secondary_phone',
        'address',
        'city',
        'state_province',
        'country',
        'postal_code',
        'tax_id',
        'notes',
        'source',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setCompanyNameAttribute($value)
    {
        $this->attributes['company_name'] = $value ? ucwords(strtolower($value)) : null;
    }

    public function setContactPersonAttribute($value)
    {
        $this->attributes['contact_person'] = $value ? ucwords(strtolower($value)) : null;
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function quotes()
    {
        // return $this->hasMany(\Modules\Quoting\Entities\Quote::class);
    }

    public function shipments()
    {
        return $this->hasMany(\Modules\Operations\Entities\Shipment::class);
    }

    public function jobs()
    {
        // return $this->hasMany(\Modules\Operations\Entities\Job::class);
    }

    public function getNameAttribute()
    {
        return $this->company_name ?? $this->contact_person;
    }

    public function communications()
    {
        return $this->morphMany(\Modules\Communications\Entities\Communication::class, 'communicable');
    }
}

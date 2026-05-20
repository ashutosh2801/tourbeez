<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class UserSupplier extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id', 'business_name', 'supplier_type', 'business_registration_number', 'year_established', 'website_url', 
        'designation', 'secondary_contact', 
        'address', 'operating_locations', 
        'insurance_details', 'license_file', 'certifications', 'payment_method',
        'bank_details', 'currency', 'company_logo', 'service_images', 'promotional_offers', 'consent_info',
        'consent_terms', 'digital_signature', 'submitted_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('UserSupplier')
            ->setDescriptionForEvent(fn(string $eventName) => "UserSupplier {$eventName}")
            ->logAll(); // 🔥 important
    }
}

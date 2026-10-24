<?php

namespace App\Models\Platform;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'country',
        'business_reg_number',
        'invoice_header_note',
        'invoice_footer_note',
        'warranty_terms',
    ];

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function mainOutlet(): HasOne
    {
        return $this->hasOne(Outlet::class)->where('is_main', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trial'])
            ->latestOfMany();
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}

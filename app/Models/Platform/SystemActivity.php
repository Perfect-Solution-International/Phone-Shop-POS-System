<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'system_activities';

    protected $fillable = [
        'user_id',
        'shop_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * Safely record an administrative or system event.
     * Guaranteed never to log passwords, tokens, or payment secrets.
     */
    public static function log(
        string $action,
        string $description,
        ?int $shopId = null,
        ?int $userId = null,
        array $properties = []
    ): self {
        $sanitizedProperties = static::sanitizeProperties($properties);

        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'shop_id' => $shopId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ? substr(request()->userAgent(), 0, 255) : null,
            'properties' => ! empty($sanitizedProperties) ? $sanitizedProperties : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Redact any sensitive information from properties before storing.
     */
    protected static function sanitizeProperties(array $properties): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'access_token',
            'refresh_token', 'remember_token', 'api_key', 'secret',
            'card_number', 'cvv', 'cvc', 'pin', 'ssn',
        ];

        $cleaned = [];
        foreach ($properties as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $sensitiveKeys, true)) {
                $cleaned[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $cleaned[$key] = static::sanitizeProperties($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}


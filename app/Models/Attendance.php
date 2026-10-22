<?php

namespace App\Models;

use App\Support\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToShop, HasFactory;

    protected $fillable = ['employee_id', 'work_date', 'clocked_in_at', 'clocked_out_at'];

    protected $casts = ['work_date' => 'date:Y-m-d', 'clocked_in_at' => 'immutable_datetime', 'clocked_out_at' => 'immutable_datetime'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }
}

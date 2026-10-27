<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class HourMovement extends Model
{
    use BelongsToInstitution;

    protected $fillable = ['institution_id', 'hour_request_id', 'hours', 'description', 'expires_on', 'created_by'];

    protected function casts(): array
    {
        return ['hours' => 'decimal:2', 'expires_on' => 'date'];
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'business_unit_id', 'kind', 'title', 'signed_on', 'expires_on', 'amount',
        'file_path', 'file_name', 'external_url', 'sha256',
    ];

    protected function casts(): array
    {
        return ['signed_on' => 'date', 'expires_on' => 'date', 'amount' => 'decimal:2'];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deliverable extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'project_id', 'business_unit_id', 'title', 'kind', 'version', 'phase_label',
        'file_path', 'file_name', 'file_size', 'external_url', 'sha256', 'status', 'requires_approval',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Los borradores no se muestran al cliente */
    public function scopeClientVisible(Builder $q): Builder
    {
        return $q->where('status', '!=', 'draft');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }
}

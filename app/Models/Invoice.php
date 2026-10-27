<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'number', 'concept', 'issued_on', 'due_on', 'amount', 'currency', 'status',
        'paid_on', 'pdf_path', 'xml_path',
    ];

    protected function casts(): array
    {
        return ['issued_on' => 'date', 'due_on' => 'date', 'paid_on' => 'date', 'amount' => 'decimal:2'];
    }

    /** Estado que ve el cliente: vencida se calcula, no se guarda */
    public function displayStatus(): string
    {
        if ($this->status === 'pending' && $this->due_on && $this->due_on->isPast() && ! $this->due_on->isToday()) {
            return 'overdue';
        }

        return $this->status;
    }
}

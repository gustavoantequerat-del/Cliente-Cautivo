<?php

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schedule;

/*
| En cPanel se configura UN solo cron cada minuto:
|   * * * * * cd /home/USUARIO/cliente-cautivo && php artisan schedule:run >> /dev/null 2>&1
| No hay procesos persistentes: la cola se procesa en lotes y se detiene sola.
*/
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()->withoutOverlapping();

// Mantiene acotada la base de datos (espacio limitado)
Schedule::call(fn () => AuditLog::where('created_at', '<', now()->subMonths(18))->delete())->weekly();
Schedule::command('queue:prune-failed --hours=720')->weekly();

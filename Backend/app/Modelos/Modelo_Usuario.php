<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Usuario_Autenticable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Las columnas email, password y remember_token mantienen el nombre que exige el login de Laravel.
 */
class Modelo_Usuario extends Usuario_Autenticable
{
    use Notifiable;

    public const Rol_Staff = 'staff';                                       // Staff Blockfinity con acceso al CMS
    public const Rol_Administrador_Institucion = 'administrador_institucion';
    public const Rol_Funcionario = 'funcionario';

    protected $table = 'usuarios';

    protected $fillable = [
        'institucion_id', 'nombre', 'email', 'password', 'rol', 'area', 'cargo', 'telefono',
        'enlace_agenda', 'esta_activo', 'numero_credencial',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'fecha_emision_credencial' => 'datetime',
            'fecha_ultimo_ingreso' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean',
        ];
    }

    /** Todo usuario de una institución recibe credencial con código QR al crearse */
    protected static function booted(): void
    {
        static::creating(function (Modelo_Usuario $Usuario) {
            if ($Usuario->rol !== self::Rol_Staff && ! $Usuario->codigo_credencial) {
                $Usuario->codigo_credencial = (string) Str::uuid();
                $Usuario->fecha_emision_credencial = now();
            }
        });
        static::created(function (Modelo_Usuario $Usuario) {
            if ($Usuario->codigo_credencial && ! $Usuario->numero_credencial) {
                $Usuario->numero_credencial = str_pad((string) $Usuario->id, 4, '0', STR_PAD_LEFT);
                $Usuario->saveQuietly();
            }
        });
    }

    public function Institucion(): BelongsTo
    {
        return $this->belongsTo(Modelo_Institucion::class, 'institucion_id');
    }

    public function Es_Staff(): bool
    {
        return $this->rol === self::Rol_Staff;
    }

    public function Es_Administrador_Institucion(): bool
    {
        return $this->rol === self::Rol_Administrador_Institucion;
    }

    public function Obtener_Iniciales(): string
    {
        $Palabras = preg_split('/\s+/', trim($this->nombre));

        return Str::upper(Str::substr($Palabras[0] ?? '', 0, 1).Str::substr($Palabras[1] ?? '', 0, 1));
    }
}

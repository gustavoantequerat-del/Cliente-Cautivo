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

    // Personal de Blockfinity (entra por /admin/login)
    public const Rol_Superadmin = 'superadmin';                          // Todo el CMS
    public const Rol_Usuario_Ejecutivo = 'usuario_ejecutivo';    // Solo las instituciones que tiene a su cargo

    // Personal de la institución cliente (entra por /login)
    public const Rol_Administrador_Delegado = 'administrador_delegado'; // Facturación, horas, aprobaciones y su equipo
    public const Rol_Usuario_Institucional = 'usuario_institucional';               // Formación, research, ecosistema y su credencial

    public const Roles = [
        self::Rol_Usuario_Institucional => 'Usuario institucional',
        self::Rol_Administrador_Delegado => 'Administrador delegado institucional',
        self::Rol_Usuario_Ejecutivo => 'Usuario ejecutivo',
        self::Rol_Superadmin => 'Superadmin',
    ];

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
            if (! $Usuario->Es_Personal_Blockfinity() && ! $Usuario->codigo_credencial) {
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

    public function Es_Superadmin(): bool
    {
        return $this->rol === self::Rol_Superadmin;
    }

    public function Es_Usuario_Ejecutivo(): bool
    {
        return $this->rol === self::Rol_Usuario_Ejecutivo;
    }

    /** Superadmin o usuario ejecutivo: usa el CMS y no el portal */
    public function Es_Personal_Blockfinity(): bool
    {
        return $this->Es_Superadmin() || $this->Es_Usuario_Ejecutivo();
    }

    public function Es_Administrador_Delegado(): bool
    {
        return $this->rol === self::Rol_Administrador_Delegado;
    }

    /** Instituciones que puede gestionar en el CMS (null = todas, para el superadmin) */
    public function Obtener_Ids_Instituciones_A_Cargo(): ?array
    {
        if ($this->Es_Superadmin()) {
            return null;
        }

        return Modelo_Institucion::where('ejecutivo_cuenta_id', $this->id)->pluck('id')->all();
    }

    public function Obtener_Iniciales(): string
    {
        $Palabras = preg_split('/\s+/', trim($this->nombre));

        return Str::upper(Str::substr($Palabras[0] ?? '', 0, 1).Str::substr($Palabras[1] ?? '', 0, 1));
    }
}

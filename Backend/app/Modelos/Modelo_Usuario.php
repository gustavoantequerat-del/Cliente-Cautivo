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
    public const Rol_Staff = 'staff';                          // Todo el CMS
    public const Rol_Ejecutivo_Cuenta = 'ejecutivo_cuenta';    // Solo las instituciones que tiene a su cargo

    // Personal de la institución cliente (entra por /login)
    public const Rol_Cliente_Institucional = 'cliente_institucional'; // Facturación, horas, aprobaciones y su equipo
    public const Rol_Cliente_Basico = 'cliente_basico';               // Formación y los proyectos donde lo inscribieron

    public const Roles = [
        self::Rol_Cliente_Basico => 'Cliente básico',
        self::Rol_Cliente_Institucional => 'Cliente institucional',
        self::Rol_Ejecutivo_Cuenta => 'Ejecutivo/a de cuenta',
        self::Rol_Staff => 'Staff Blockfinity (todo el CMS)',
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

    public function Es_Staff(): bool
    {
        return $this->rol === self::Rol_Staff;
    }

    public function Es_Ejecutivo_Cuenta(): bool
    {
        return $this->rol === self::Rol_Ejecutivo_Cuenta;
    }

    /** Staff o ejecutivo/a: usa el CMS y no el portal */
    public function Es_Personal_Blockfinity(): bool
    {
        return $this->Es_Staff() || $this->Es_Ejecutivo_Cuenta();
    }

    public function Es_Cliente_Institucional(): bool
    {
        return $this->rol === self::Rol_Cliente_Institucional;
    }

    /** Instituciones que puede gestionar en el CMS (null = todas, para el staff) */
    public function Obtener_Ids_Instituciones_A_Cargo(): ?array
    {
        if ($this->Es_Staff()) {
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

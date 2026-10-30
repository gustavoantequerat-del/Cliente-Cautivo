<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Contrato;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Servicios\Servicio_Archivos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

class Controlador_Facturacion
{
    public function Mostrar_Facturacion(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion;
        $Formatear_Monto = fn ($Monto) => number_format((float) $Monto, 2, ',', '.');

        $Facturas = Modelo_Factura::Visibles_Para_Usuario($Usuario)->orderByDesc('fecha_emision')->get();
        $Facturas_Pendientes = $Facturas->where('estado', 'pendiente');
        $Facturas_Del_Anio = $Facturas->filter(fn ($Factura) => $Factura->fecha_emision->year === now()->year && $Factura->estado !== 'anulada');
        $Proximo_Vencimiento = $Facturas_Pendientes->sortBy('fecha_vencimiento')->first()?->fecha_vencimiento;

        return Inertia::render('Portal/Pagina_Facturacion', [
            'Indicadores' => [
                ['Etiqueta' => 'Pendiente de pago', 'Valor' => 'Bs '.$Formatear_Monto($Facturas_Pendientes->sum('monto')), 'Detalle' => $Facturas_Pendientes->count().' factura(s)'],
                ['Etiqueta' => 'Facturado '.now()->year, 'Valor' => 'Bs '.$Formatear_Monto($Facturas_Del_Anio->sum('monto')), 'Detalle' => $Facturas_Del_Anio->count().' factura(s)'],
                ['Etiqueta' => 'Próximo vencimiento', 'Valor' => $Proximo_Vencimiento?->translatedFormat('j M Y') ?? '—', 'Detalle' => ''],
            ],
            'Facturas' => $Facturas->map(fn (Modelo_Factura $Factura) => [
                'Id' => $Factura->id,
                'Numero' => $Factura->numero,
                'Concepto' => $Factura->concepto,
                'Fecha_Emision' => $Factura->fecha_emision->translatedFormat('j M Y'),
                'Fecha_Vencimiento' => $Factura->fecha_vencimiento?->translatedFormat('j M Y'),
                'Monto' => $Formatear_Monto($Factura->monto),
                'Moneda' => $Factura->moneda,
                'Estado' => $Factura->Obtener_Estado_Visible(),
                'Tiene_Pdf' => (bool) $Factura->ruta_pdf,
                'Tiene_Xml' => (bool) $Factura->ruta_xml,
            ])->values(),
            'Contratos' => Modelo_Contrato::Visibles_Para_Usuario($Usuario)->with('Unidad_Negocio')->orderByDesc('fecha_firma')->get()
                ->map(fn (Modelo_Contrato $Contrato) => [
                    'Id' => $Contrato->id,
                    'Tipo' => $Contrato->tipo,
                    'Titulo' => $Contrato->titulo,
                    'Codigo_Unidad' => $Contrato->Unidad_Negocio?->codigo,
                    'Fecha_Firma' => $Contrato->fecha_firma?->translatedFormat('j M Y'),
                    'Fecha_Vencimiento' => $Contrato->fecha_vencimiento?->translatedFormat('j M Y'),
                    'Monto' => $Contrato->monto ? $Formatear_Monto($Contrato->monto) : null,
                    'Huella_Sha256' => $Contrato->huella_sha256,
                    'Tiene_Archivo' => (bool) ($Contrato->ruta_archivo || $Contrato->enlace_externo),
                ]),
            'Datos_Facturacion' => [
                ['Etiqueta' => 'Razón social', 'Valor' => $Institucion->razon_social ?? $Institucion->nombre],
                ['Etiqueta' => 'NIT', 'Valor' => $Institucion->nit ?? '—'],
                ['Etiqueta' => 'Correo de facturación', 'Valor' => $Institucion->correo_facturacion ?? '—'],
                ['Etiqueta' => 'Dirección', 'Valor' => $Institucion->direccion_facturacion ?? '—'],
            ],
        ]);
    }

    public function Descargar_Archivo_Factura(Request $Peticion, Modelo_Factura $Factura, string $Tipo_Archivo): Respuesta_Http
    {
        abort_unless($Factura->institucion_id == $Peticion->user()->institucion_id, 404);
        Modelo_Registro_Auditoria::Registrar_Evento('factura.descargar', $Factura, "Descargó factura {$Factura->numero} ({$Tipo_Archivo})");

        $Ruta_Archivo = $Tipo_Archivo === 'pdf' ? $Factura->ruta_pdf : $Factura->ruta_xml;

        return Servicio_Archivos::Descargar_Archivo($Ruta_Archivo, "factura-{$Factura->numero}.{$Tipo_Archivo}");
    }

    public function Descargar_Contrato(Request $Peticion, Modelo_Contrato $Contrato): Respuesta_Http
    {
        abort_unless($Contrato->institucion_id == $Peticion->user()->institucion_id, 404);
        Modelo_Registro_Auditoria::Registrar_Evento('contrato.descargar', $Contrato, 'Descargó «'.$Contrato->titulo.'»');

        if ($Contrato->ruta_archivo) {
            return Servicio_Archivos::Descargar_Archivo($Contrato->ruta_archivo, $Contrato->nombre_archivo);
        }
        abort_unless($Contrato->enlace_externo, 404);

        return redirect()->away($Contrato->enlace_externo);
    }
}

<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\Invoice;
use App\Support\Files;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $institution = $user->institution;
        $money = fn ($v) => number_format((float) $v, 2, ',', '.');

        $invoices = Invoice::visibleTo($user)->orderByDesc('issued_on')->get();
        $pending = $invoices->where('status', 'pending');
        $year = $invoices->filter(fn ($i) => $i->issued_on->year === now()->year && $i->status !== 'void');

        return Inertia::render('Portal/Billing', [
            'kpis' => [
                ['label' => 'Pendiente de pago', 'value' => 'Bs '.$money($pending->sum('amount')), 'sub' => $pending->count().' factura(s)'],
                ['label' => 'Facturado '.now()->year, 'value' => 'Bs '.$money($year->sum('amount')), 'sub' => $year->count().' factura(s)'],
                ['label' => 'Próximo vencimiento', 'value' => $pending->sortBy('due_on')->first()?->due_on?->translatedFormat('j M Y') ?? '—', 'sub' => ''],
            ],
            'invoices' => $invoices->map(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'concept' => $i->concept,
                'issued' => $i->issued_on->translatedFormat('j M Y'),
                'due' => $i->due_on?->translatedFormat('j M Y'),
                'amount' => $money($i->amount),
                'currency' => $i->currency,
                'status' => $i->displayStatus(),
                'hasPdf' => (bool) $i->pdf_path,
                'hasXml' => (bool) $i->xml_path,
            ]),
            'contracts' => Contract::visibleTo($user)->with('unit')->orderByDesc('signed_on')->get()->map(fn (Contract $c) => [
                'id' => $c->id,
                'kind' => $c->kind,
                'title' => $c->title,
                'unit' => $c->unit?->code,
                'signed' => $c->signed_on?->translatedFormat('j M Y'),
                'expires' => $c->expires_on?->translatedFormat('j M Y'),
                'amount' => $c->amount ? $money($c->amount) : null,
                'sha256' => $c->sha256,
                'hasFile' => (bool) ($c->file_path || $c->external_url),
            ]),
            'billing' => [
                ['k' => 'Razón social', 'v' => $institution->legal_name ?? $institution->name],
                ['k' => 'NIT', 'v' => $institution->tax_id ?? '—'],
                ['k' => 'Correo de facturación', 'v' => $institution->billing_email ?? '—'],
                ['k' => 'Dirección', 'v' => $institution->billing_address ?? '—'],
            ],
        ]);
    }

    public function invoiceFile(Request $request, Invoice $invoice, string $type): HttpResponse
    {
        abort_unless($invoice->institution_id == $request->user()->institution_id, 404);
        $path = $type === 'pdf' ? $invoice->pdf_path : $invoice->xml_path;
        AuditLog::record('invoice.download', $invoice, "Descargó factura {$invoice->number} ({$type})");

        return Files::download($path, "factura-{$invoice->number}.{$type}");
    }

    public function contractFile(Request $request, Contract $contract): HttpResponse
    {
        abort_unless($contract->institution_id == $request->user()->institution_id, 404);
        AuditLog::record('contract.download', $contract, 'Descargó «'.$contract->title.'»');

        if ($contract->file_path) {
            return Files::download($contract->file_path, $contract->file_name);
        }
        abort_unless($contract->external_url, 404);

        return redirect()->away($contract->external_url);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Resources;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BusinessUnit;
use App\Models\Institution;
use App\Support\Files;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * CRUD genérico del CMS a partir de App\Admin\Resources.
 */
class ResourceController extends Controller
{
    public function index(Request $request, string $resource): Response
    {
        $def = Resources::get($resource);
        $query = $def['model']::query();

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($def, $q) {
                foreach ($def['search'] as $col) {
                    $w->orWhere($col, 'like', '%'.$q.'%');
                }
            });
        }
        $filterCol = $def['filter'] ?? null;
        if ($filterCol && $request->filled('f')) {
            $query->where($filterCol, $request->query('f'));
        }
        [$orderCol, $orderDir] = $def['order'];
        $query->orderBy($orderCol, $orderDir)->orderByDesc('id');

        $columns = array_values(array_filter($def['fields'], fn ($f) => $f['table'] ?? false));
        $options = $this->relationOptions($def['fields']);

        $rows = $query->paginate(25)->withQueryString()->through(function (Model $m) use ($columns, $options) {
            $row = ['id' => $m->getKey()];
            foreach ($columns as $f) {
                $row[$f['name']] = $this->display($m, $f, $options);
            }

            return $row;
        });

        $filterField = $filterCol ? collect($def['fields'])->firstWhere('name', $filterCol) : null;

        return Inertia::render('Admin/Resource/Index', [
            'resource' => Arr::only($def, ['key', 'label', 'singular']),
            'columns' => array_map(fn ($f) => Arr::only($f, ['name', 'label', 'type']), $columns),
            'rows' => $rows,
            'filter' => $filterField ? [
                'label' => $filterField['label'],
                'options' => $options[$filterCol] ?? [],
                'value' => $request->query('f'),
            ] : null,
            'q' => $request->query('q'),
        ]);
    }

    public function create(string $resource): Response
    {
        $def = Resources::get($resource);
        $values = [];
        foreach ($def['fields'] as $f) {
            $values[$f['name']] = $f['default'] ?? ($f['type'] === 'boolean' ? false : ($f['type'] === 'units' ? [] : null));
        }

        return $this->form($def, $values, null);
    }

    public function edit(string $resource, int $id): Response
    {
        $def = Resources::get($resource);
        $model = $def['model']::findOrFail($id);
        $values = [];
        foreach ($def['fields'] as $f) {
            $values[$f['name']] = match ($f['type']) {
                'password' => '',
                'units' => $model->units->mapWithKeys(fn ($u) => [$u->id => ['on' => true, 'note' => $u->pivot->note,
                    'active_since' => $u->pivot->active_since]])->all(),
                'date' => $model->{$f['name']} ? Carbon::parse($model->{$f['name']})->format('Y-m-d') : null,
                'datetime' => $model->{$f['name']} ? Carbon::parse($model->{$f['name']})->format('Y-m-d\TH:i') : null,
                'boolean' => (bool) $model->{$f['name']},
                default => $model->{$f['name']},
            };
        }

        return $this->form($def, $values, $model);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $def = Resources::get($resource);
        $model = new $def['model'];
        $this->fill($request, $def, $model);
        AuditLog::record('cms.create', $model, 'Creó '.$def['singular'].' #'.$model->getKey());

        return redirect()->route('admin.resources.index', $resource)->with('success', ucfirst($def['singular']).' creado/a.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $def = Resources::get($resource);
        $model = $def['model']::findOrFail($id);
        $this->fill($request, $def, $model);
        AuditLog::record('cms.update', $model, 'Editó '.$def['singular'].' #'.$model->getKey());

        return redirect()->route('admin.resources.index', $resource)->with('success', 'Cambios guardados.');
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $def = Resources::get($resource);
        $model = $def['model']::findOrFail($id);
        abort_if($model->is($request->user()), 422, 'No puede eliminar su propio usuario.');

        foreach ($def['fields'] as $f) {
            if ($f['type'] === 'file') {
                Files::delete($model->{$f['name']});
            }
        }
        AuditLog::record('cms.delete', null, 'Eliminó '.$def['singular'].' #'.$model->getKey());
        $model->delete();

        return redirect()->route('admin.resources.index', $resource)->with('success', 'Registro eliminado.');
    }

    /** Descarga para el staff de cualquier archivo cargado en el CMS */
    public function file(string $resource, int $id, string $field): HttpResponse
    {
        $def = Resources::get($resource);
        $f = collect($def['fields'])->firstWhere('name', $field);
        abort_unless($f && $f['type'] === 'file', 404);
        $model = $def['model']::findOrFail($id);

        return Files::download($model->{$field}, $f['nameTo'] ?? null ? $model->{$f['nameTo']} : null);
    }

    // ---------------------------------------------------------------------

    private function form(array $def, array $values, ?Model $model): Response
    {
        $fields = array_map(function ($f) use ($model, $def) {
            $out = Arr::except($f, ['model', 'where']);
            if ($f['type'] === 'file' && $model && $model->{$f['name']}) {
                $out['current'] = [
                    'name' => isset($f['nameTo']) ? $model->{$f['nameTo']} : basename($model->{$f['name']}),
                    'url' => route('admin.resources.file', [$def['key'], $model->getKey(), $f['name']]),
                ];
            }

            return $out;
        }, $def['fields']);

        return Inertia::render('Admin/Resource/Form', [
            'resource' => Arr::only($def, ['key', 'label', 'singular']),
            'fields' => $fields,
            'values' => $values,
            'options' => $this->relationOptions($def['fields']) + [
                'units' => BusinessUnit::orderBy('sort')->get(['id', 'code', 'name']),
            ],
            'id' => $model?->getKey(),
            'providers' => VideoEmbed::PROVIDERS,
        ]);
    }

    private function fill(Request $request, array $def, Model $model): void
    {
        $rules = [];
        foreach ($def['fields'] as $f) {
            if ($f['readonly'] ?? false) {
                continue;
            }
            $rules[$f['name']] = $this->rulesFor($f, $model, $def);
            if ($f['type'] === 'units') {
                $rules['units.*.on'] = ['nullable', 'boolean'];
                $rules['units.*.note'] = ['nullable', 'string', 'max:255'];
                $rules['units.*.active_since'] = ['nullable', 'date'];
            }
        }
        $data = $request->validate($rules);

        $units = null;
        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            if ($f['readonly'] ?? false) {
                continue;
            }
            switch ($f['type']) {
                case 'units':
                    $units = $data['units'] ?? [];
                    break;
                case 'password':
                    if (! empty($data[$name])) {
                        $model->{$name} = $data[$name];
                    }
                    break;
                case 'file':
                    if ($request->hasFile($name)) {
                        Files::delete($model->{$name});
                        $stored = Files::store($request->file($name), $f['folder'] ?? 'archivos');
                        $model->{$name} = $stored['path'];
                        foreach (['nameTo' => 'name', 'sizeTo' => 'size', 'hashTo' => 'sha256'] as $key => $attr) {
                            if (isset($f[$key])) {
                                $model->{$f[$key]} = $stored[$attr];
                            }
                        }
                    } elseif ($request->boolean($name.'_remove')) {
                        Files::delete($model->{$name});
                        $model->{$name} = null;
                    }
                    break;
                case 'hash':
                    // Si se subió archivo nuevo en este request, manda la huella calculada en el servidor
                    $fileWithHash = collect($def['fields'])->first(fn ($x) => ($x['hashTo'] ?? null) === $name);
                    if (! ($fileWithHash && $request->hasFile($fileWithHash['name']))) {
                        $model->{$name} = isset($data[$name]) ? strtolower($data[$name]) : null;
                    }
                    break;
                case 'boolean':
                    $model->{$name} = $request->boolean($name);
                    break;
                default:
                    $model->{$name} = $data[$name] ?? ($f['type'] === 'number' && isset($f['default']) ? $f['default'] : null);
            }
        }

        $model->save();

        if ($units !== null && $model instanceof Institution) {
            $sync = [];
            foreach ($units as $unitId => $u) {
                if (! empty($u['on'])) {
                    $sync[$unitId] = ['note' => $u['note'] ?? null, 'active_since' => $u['active_since'] ?? null];
                }
            }
            $model->units()->sync($sync);
        }
    }

    private function rulesFor(array $f, Model $model, array $def): array
    {
        $req = ($f['required'] ?? false) ? 'required' : 'nullable';
        $rules = match ($f['type']) {
            'email' => [$req, 'email', 'max:255'],
            'url', 'video_url' => [$req, 'url', 'max:500'],
            'textarea' => [$req, 'string', 'max:10000'],
            'number' => [$req, 'integer', 'min:'.($f['min'] ?? -1000000), 'max:'.($f['max'] ?? 1000000)],
            'decimal' => [$req, 'numeric', 'between:-999999999,999999999'],
            'date', 'datetime' => [$req, 'date'],
            'boolean' => ['nullable', 'boolean'],
            'select' => [$req, Rule::in(array_keys($f['options']))],
            'belongsTo' => [$req, 'integer', Rule::exists((new $f['model'])->getTable(), 'id')],
            'file' => ['nullable', 'file', 'max:'.config('portal.max_upload_kb'),
                'mimes:'.($f['type'] === 'file' && isset($f['accept']) ? ltrim(str_replace('.', '', $f['accept']), ',') : 'pdf,doc,docx,ppt,pptx,xls,xlsx,xml,zip,png,jpg')],
            'password' => [$model->exists ? 'nullable' : 'required', Password::min(10)->letters()->numbers()],
            'hash' => ['nullable', 'regex:/^[a-fA-F0-9]{64}$/'],
            'units' => ['nullable', 'array'],
            default => [$req, 'string', 'max:255'],
        };

        if ($f['unique'] ?? false) {
            $rules[] = Rule::unique($model->getTable(), $f['name'])->ignore($model->getKey());
        }
        if ($f['type'] === 'video_url') {
            $rules[] = function ($attr, $value, $fail) {
                if ($value && ! VideoEmbed::parse($value)) {
                    $fail('El enlace no es de Google Drive, YouTube ni Vimeo reconocible.');
                }
            };
        }

        return $rules;
    }

    private function relationOptions(array $fields): array
    {
        $options = [];
        foreach ($fields as $f) {
            if ($f['type'] === 'belongsTo') {
                $q = $f['model']::query()->orderBy($f['labelColumn']);
                if (isset($f['where'])) {
                    $q->where($f['where'][0], $f['where'][1]);
                }
                $options[$f['name']] = $q->get(['id', $f['labelColumn']])
                    ->map(fn ($m) => ['value' => $m->id, 'label' => $m->{$f['labelColumn']}])->all();
            } elseif ($f['type'] === 'select') {
                $options[$f['name']] = collect($f['options'])->map(fn ($l, $v) => ['value' => $v, 'label' => $l])->values()->all();
            }
        }

        return $options;
    }

    private function display(Model $m, array $f, array $options): mixed
    {
        $v = $m->{$f['name']};

        return match ($f['type']) {
            'belongsTo', 'select' => collect($options[$f['name']] ?? [])->firstWhere('value', $v)['label'] ?? ($v ?? '—'),
            'boolean' => $v ? 'Sí' : 'No',
            'date' => $v ? Carbon::parse($v)->format('d/m/Y') : '—',
            'datetime' => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '—',
            'textarea' => $v ? \Illuminate\Support\Str::limit($v, 80) : '—',
            default => $v ?? '—',
        };
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\ProformaPlano;
use App\Models\EspacioPrograma;
use App\Models\CategoriaPrograma;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProgramaArquitectonico;

class ProgramaArquitectonicoController extends Controller
{
    public function show($tipo, $proformaId)
    {
        $proforma = ProformaPlano::find($proformaId);
        $title_page = 'Programa Arquitectonico';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => route('proformas.tipo', $tipo)],
            ['name' => $title_page, 'url' => '']
        ];
        // 1. Busca si ya existe un programa para esta proforma
        $programa = ProgramaArquitectonico::where('proforma_id', $proforma->id)->first();
        // 2. Si no existe, lo creamos a partir de la plantilla
        if (!$programa) {
            $programa = $this->cloneFromTemplate($proforma);
        }

        $categorias = CategoriaPrograma::orderBy('orden')->get();
        $espaciosExistentes = $programa->espacios->groupBy('categoria_id');

        return view('proformas.programa_arquitectonico', compact('title_page', 'breadcrumbs', 'proforma', 'tipo', 'programa', 'categorias', 'espaciosExistentes'));
    }

    private function cloneFromTemplate(ProformaPlano $proforma)
    {
        // Busca la plantilla maestra. Aún usamos firstOrFail para asegurar que exista.
        // Si no existe, es una condición excepcional que debe ser resuelta (ej. corriendo los seeders).
        $template = ProgramaArquitectonico::whereNull('proforma_id')->firstOrFail();
        // Clona el programa principal (replicate() copia los atributos).
        $newPrograma = $template->replicate();
        // Asigna los nuevos valores específicos para esta instancia.
        $newPrograma->proforma_id = $proforma->id;
        $newPrograma->plantilla_id = $template->id;
        $newPrograma->nombre = "Programa para Proforma #{$proforma->numero}";

        // Guarda el nuevo programa en la base de datos.
        $newPrograma->save();

        return $newPrograma;
    }

    public function store(Request $request, $tipo, $proformaId, ProgramaArquitectonico $programa)
    {
        // 1. VALIDACIÓN DE ENTRADA
        // Es el primer filtro de seguridad y garantiza la integridad de los datos.
        $validated = $request->validate([
            'urls_referencias' => 'nullable|string',
            'estilo' => 'nullable|string|max:255',
            'espacios' => 'nullable|array',
            'espacios.*.espacio' => 'nullable|string|max:255',
            'espacios.*.cantidad' => 'nullable|integer|min:0',
            'espacios.*.actividades' => 'nullable|string',
            'espacios.*.mobiliario' => 'nullable|string',
            'espacios.*.usuario' => 'nullable|integer|min:0',
            'espacios.*.m2' => 'nullable|numeric|min:0',
            'espacios.*.observaciones' => 'nullable|string',
            'espacios.*.link_ref' => 'nullable|string',
            // Se requiere 'categoria_id' solo si la fila tiene contenido.
            // Si el usuario añade una fila y la deja vacía, no se validará.
            'espacios.*.categoria_id' => 'required_with:espacios.*.espacio|integer|exists:categorias_programa,id',
        ]);

        try {
            // 2. INICIO DE LA TRANSACCIÓN
            // Garantiza que todas las operaciones se realicen con éxito, o ninguna.
            DB::beginTransaction();

            $espaciosExistentes =  $programa->espacios()->pluck('id')->toArray();
            // 3. ACTUALIZACIÓN DEL MODELO PRINCIPAL (PROGRAMA)
            $programa->update(Arr::only($validated, ['estilo', 'urls_referencias']));

            $submittedEspacios = $validated['espacios'] ?? [];

            $existingIds = []; // Almacenará los IDs de las filas existentes que se deben conservar.

            // 4. BUCLE PARA PROCESAR CADA ESPACIO
            foreach ($submittedEspacios as $id => $data) {

                // Lógica para evitar guardar filas completamente vacías enviadas por el formulario
                $camposRellenables = Arr::except($data, ['categoria_id', 'cantidad']);
                $camposConContenido = array_filter($camposRellenables, fn($value) => !is_null($value) && $value !== '');

                // Si no hay contenido significativo, saltamos esta fila.
                if (empty($camposConContenido)) {
                    continue;
                }

                // Si el ID de la fila empieza con "new_", es un nuevo registro.
                if (str_starts_with($id, 'new_')) {
                    // Aseguramos valores por defecto para campos numéricos para evitar errores de base de datos.
                    $data['cantidad'] = $data['cantidad'] ?? 1;
                    $data['m2'] = $data['m2'] ?? 0.00;

                    // Creamos el nuevo espacio usando la relación para asignar automáticamente 'programa_id'.
                    $programa->espacios()->create($data);
                }
                // Si el ID es numérico, es un registro existente que hay que actualizar.
                else {
                    // Buscamos el espacio dentro de la relación del programa actual para seguridad.
                    if ($espacio = $programa->espacios()->find($id)) {
                        $espacio->update($data);
                        // Añadimos el ID al array de los que se deben conservar.
                        $existingIds[] = $id;
                    }
                }
            }

            $espaciosEliminar = array_diff($espaciosExistentes, $existingIds);

            // 5. LÓGICA DE ELIMINACIÓN
            // Elimina los espacios que pertenecen a este programa pero cuyos IDs no fueron enviados en el formulario.
            // Esto cubre el caso en que un usuario elimina una fila existente en la interfaz.
            $programa->espacios()->whereIn('id', $espaciosEliminar)->delete();

            // 6. CONFIRMACIÓN DE LA TRANSACCIÓN
            // Si el código llega hasta aquí, todas las operaciones fueron exitosas.
            DB::commit();

            // 7. RESPUESTA DE ÉXITO
            return back()->with('success', 'Programa arquitectónico guardado con éxito.');
        } catch (\Exception $e) {
            // 8. MANEJO DE ERRORES
            // Si ocurre cualquier error dentro del bloque 'try', se ejecuta este código.
            DB::rollBack();
            return $e;
            // Registra el error detallado en los logs de Laravel para que el desarrollador pueda depurarlo.
            Log::error('Error al actualizar el programa arquitectónico: ' . $e->getMessage(), [
                'programa_id' => $programa->id,
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            // Devuelve al usuario a la página anterior con un mensaje de error genérico y amigable.
            return back()->with('error', 'Ocurrió un error inesperado al guardar los cambios. Por favor, inténtelo de nuevo o contacte a soporte.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropiedadesVentaStoreRequest;
use App\Http\Requests\PropiedadesVentaUpdateRequest;
use Illuminate\Http\Request;
use App\Models\VentaPropiedad;
use App\Models\ImagenPropiedad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VentaPropiedadController extends Controller
{

    private $path_files;

    public function __construct()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            $this->path_files = 'propiedades';
        } else {
            $this->path_files = 'pruebas/propiedades';
        }
    }

    public function index()
    {
        $title_page = 'Propiedades';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Gerencia', 'url' => route('gerencia.index')],
            ['name' => 'Propiedades', 'url' => '']
        ];

        $propiedades = VentaPropiedad::orderBy('created_at', 'desc')->paginate(15);

        return view('gerencia.propiedades.index', compact('propiedades', 'title_page', 'breadcrumbs'));
    }

    public function create()
    {
        $title_page = 'Nuevo Propiedad';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Propiedades', 'url' => route('gerencia.propiedades.index')],
            ['name' => 'Nuevo Propiedad', 'url' => '']
        ];

        $propiedad = new VentaPropiedad();

        return view('gerencia.propiedades.create', compact('propiedad', 'title_page', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $files = $request->file('files') ?? [];
        try {
            DB::beginTransaction();
            $propiedad = VentaPropiedad::create([
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'area' => $request->area,
                'telefono' => $request->telefono,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'precio_venta' => $request->precio_venta,
                'precio_por_metros_cuadrados' => $request->precio_mt2,
                'estado' => $request->estado,
                'frente' => $request->frente,
                'fondo' => $request->fondo,
                'tipo_propiedad_id' => $request->tipo,
                'observaciones' => $request->observaciones,
            ]);

            if ($propiedad) {
                foreach ($files as $index => $file) {
                    ImagenPropiedad::create([
                        'venta_propiedad_id' => $propiedad->id,
                        'file_name' => 'imagen_' . $index . '.' . $file->getClientOriginalExtension(),
                        'path_file' => Storage::disk('digitalocean')->put($this->path_files, $file),
                    ]);
                }
                DB::commit();
                return redirect()->route('gerencia.propiedades.index')->with('success', 'La información se guardó correctamente.');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'No es posible guardar la información, por favor vuelva a intentarlo.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($files as $file) {
                if (Storage::disk('digitalocean')->exists($this->path_files, $file)) {
                    Storage::disk('digitalocean')->delete($this->path_files, $file);
                }
            }

            return redirect()->back()->with('error', 'Ocurrio un error por favor vuelva a intentarlo.');
        }
    }

    public function edit(VentaPropiedad $propiedad)
    {
        $title_page = 'Editar Propiedad';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Propiedades', 'url' => route('gerencia.propiedades.index')],
            ['name' => 'Editar Propiedad', 'url' => '']
        ];


        return view('gerencia.propiedades.edit', compact('propiedad', 'title_page', 'breadcrumbs'));
    }

    public function update(Request $request, VentaPropiedad $propiedad)
    {
        $filesNuevos = $request->file('files') ?? [];
        $imagenesAntiguas = $propiedad->imagenes_propiedades->whereNotIn('id', $request->imagenes_propiedades ?? []);

        try {
            DB::beginTransaction();
            $propiedad->update([
                'nombre' => $request->nombre,
                'direccion' => $request->direccion,
                'area' => $request->area,
                'telefono' => $request->telefono,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'precio_venta' => $request->precio_venta,
                'precio_por_metros_cuadrados' => $request->precio_mt2,
                'estado' => $request->estado,
                'frente' => $request->frente,
                'fondo' => $request->fondo,
                'tipo_propiedad_id' => $request->tipo,
                'observaciones' => $request->observaciones,
            ]);

            if ($request->hasFile('files')) {
                // Guardar las nuevas imágenes
                foreach ($filesNuevos as $index => $file) {
                    ImagenPropiedad::create([
                        'venta_propiedad_id' => $propiedad->id,
                        'file_name' => 'imagen_' . $index . '.' . $file->getClientOriginalExtension(),
                        'path_file' => Storage::disk('digitalocean')->put($this->path_files, $file),
                    ]);
                }
            }

            // Eliminar las imágenes antiguas
            foreach ($imagenesAntiguas as $imagen) {
                if (Storage::disk('digitalocean')->exists($this->path_files . '/' . $imagen->file_name)) {
                    Storage::disk('digitalocean')->delete($this->path_files . '/' . $imagen->file_name);
                }
                $imagen->delete();
            }

            DB::commit();
            return redirect()->route('gerencia.propiedades.index')->with('success', 'La información de la propiedad se actualizó correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Eliminar las imágenes nuevas si hubo un error
            foreach ($filesNuevos as $file) {
                if (Storage::disk('digitalocean')->exists($this->path_files, $file)) {
                    Storage::disk('digitalocean')->delete($this->path_files, $file);
                }
            }

            return redirect()->back()->with('error', 'Ocurrió un error al actualizar la propiedad: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $propiedad = VentaPropiedad::findOrFail($id);
        $imagenes = $propiedad->imagenes_propiedades;
        $path = $this->path_files;
        // Eliminar las imágenes asociadas a la propiedad
        foreach ($imagenes as $imagen) {
            if (Storage::disk('digitalocean')->exists($path . '/' . $imagen->path_file)) {
                Storage::disk('digitalocean')->delete($path . '/' . $imagen->path_file);
            }
            $imagen->delete();
        }
        // Eliminar la propiedad
        $propiedad->delete();

        return response()->json(['status' => true, 'message' => 'La propiedad se eliminó correctamente.']);
    }

    public function buscar(Request $request)
    {
        $buscar = $request->input('text');
        $rows = '';

        $propiedades = VentaPropiedad::where('nombre', 'like', '%' . $buscar . '%')
            ->orWhere('direccion', 'like', '%' . $buscar . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($propiedades as $propiedad) {
            $estadoBadge = $propiedad->estado == 'VENDIDO' ? 'badge-warning' : 'badge-success';
            $compartir = "<a href='javascript:void(0);' class='dropdown-item compartir-ubicacion' data-ubicacion='" . json_encode(['lat' => $propiedad->latitud, 'lng' => $propiedad->longitud]) . "' data-nombre='" . $propiedad->nombre . "'>Compartir ubicacón</a>";
            $edit = "<a href='" . route('gerencia.propiedades.edit', $propiedad->id) . "' class='dropdown-item'>Detalle</a>";
            $destroy = "<a href='javascript:void(0);' class='dropdown-item eliminar-propiedad' id='" . $propiedad->id . "'>Eliminar</a>";

            $rows .= '<tr id="' . $propiedad->id . '">';
            $rows .= '<td class="align-middle">' . $propiedad->created_at_formatted . '</td>';
            $rows .= '<td class="align-middle">' . $propiedad->nombre . '</td>';
            $rows .= '<td class="align-middle">' . $propiedad->area_formatted . '</td>';
            $rows .= '<td class="align-middle">$ ' . $propiedad->precio_venta_formatted . '</td>';
            $rows .= '<td class="align-middle">' . $propiedad->telefono . '</td>';
            $rows .= '<td class="align-middle"><span class="badge ' . $estadoBadge . '">' . $propiedad->estado . '</span></td>';
            $rows .= '<td class="align-middle text-right">';
            $rows .= '<div class="btn-group dropleft">';
            $rows .= '<button type="button" class="btn btn-outline-dark dropdown-toggle" data-container="body" data-toggle="dropdown" aria-expanded="false"> Opciones </button>';
            $rows .= '<div class="dropdown-menu">';
            $rows .= $compartir . $edit . $destroy;
            $rows .= '</div>';
            $rows .= '</div>';
            $rows .= '</td>';
            $rows .= '</tr>';
        }

        return response()->json($rows);
    }

    public function mapa()
    {
        $title_page = 'Mapa de propiedades';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Propiedades', 'url' => route('gerencia.propiedades.index')],
            ['name' => 'Mapa de propiedades', 'url' => '']
        ];

        $propiedades = VentaPropiedad::orderBy('created_at', 'desc')->get();

        return view('gerencia.propiedades.mapa', compact('title_page', 'propiedades', 'breadcrumbs'));
    }
}

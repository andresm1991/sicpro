<?php

namespace App\Http\Controllers;

use App\Models\PushNotificationUser;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $title_page = 'Notificaciones';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Notificaciones', 'url' => '']
        ];
        // Obtener las notificaciones no leídas del usuario autenticado
        $notificaciones = PushNotificationUser::where('user_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('notificacion.index', compact('title_page', 'breadcrumbs', 'notificaciones'));
    }

    public function leerNotificacion(Request $request)
    {
        // Marcar la notificación como leída
        $notificacion = PushNotificationUser::find($request->id);
        if ($notificacion) {
            $notificacion->leido = true;
            $notificacion->save();
        }

        return response()->json(['success' => true]);
    }

    public function leerTodasNotificacion(Request $request)
    {
        // Marcar todas las notificaciones como leídas
        PushNotificationUser::where('user_id', auth()->user()->id)
            ->where('leido', false)
            ->update(['leido' => true]);

        return response()->json(['success' => true]);
    }
}
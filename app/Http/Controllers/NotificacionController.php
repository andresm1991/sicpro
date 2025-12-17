<?php

namespace App\Http\Controllers;

use App\Models\PushNotificationUser;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $title_page = 'Notificaciones';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Notificaciones', 'url' => '']
        ];
        if ($request->tipo == 1) {
            $notificaciones = PushNotificationUser::where('user_id', auth()->user()->id)
                ->where('tipo', 'operativo')
                ->orderBy('created_at', 'desc')
                ->paginate(50);
        } elseif ($request->tipo == 2) {
            $notificaciones = PushNotificationUser::where('user_id', auth()->user()->id)
                ->where('tipo', 'administrativo')
                ->orderBy('created_at', 'desc')
                ->paginate(50);
        } else {
            return redirect('home');
        }

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
            ->where('is_new', true)
            ->update(['is_new' => false]);

        return response()->json(['success' => true]);
    }
}

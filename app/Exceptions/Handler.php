<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Un rango de fechas invalido es un error de entrada, no una falla del
        // servidor: los reportes que no atrapan la excepcion (los PDF y los
        // excel) avisan por flash en lugar de responder 500.
        $this->renderable(function (RangoFechasInvalidoException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });
    }
}

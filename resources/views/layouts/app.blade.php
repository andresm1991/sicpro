<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '') }} - @yield('title')</title>
    <!-- Favicon -->
    {!! Html::favicon('images/icon_app.png') !!}
    <!-- Fontawesome -->
    <link href="{{ asset('plugins/fontawesome-pro/css/all.min.css') }}" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Selected -->
    <link href="{{ asset('plugins/bootstrap-select/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <!-- Selected2 -->
    <link href="{{ asset('plugins/select2-4.0.13/css/select2.min.css') }}" rel="stylesheet">
    <!-- DatePicker-->
    {!! Html::style('plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') !!}
    <!-- daterange picker -->
    {!! Html::style('plugins/daterangepicker/daterangepicker.css') !!}
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <!-- Animate Css -->
    <link rel="stylesheet" href="{{ asset('css/animate.min.css') }}">
    <!-- Datables Css -->
    <link rel="stylesheet" href="{{ asset('plugins/dataTables/datatables.min.css') }}">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>

<body>
    <div id="app">
        <div class="overlay"></div>
        @yield('content')
    </div>
</body>

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<!-- Bootstrap-Select -->
<script src="{{ asset('plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('plugins/select2-4.0.13/js/select2.full.min.js') }}"></script>
<!-- DatePicker js -->
{!! Html::script('plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') !!}
{!! Html::script('plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js') !!}
<!-- daterange picker -->
{!! Html::script('plugins/daterangepicker/daterangepicker.js') !!}
<!-- SweetAlert2 -->
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('plugins/dataTables/datatables.min.js') }}"></script>

@yield('scripts')

<script type="text/javascript">
    /*====== SweetAlert2 ======*/
    const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
</script>

</html>

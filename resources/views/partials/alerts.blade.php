@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>{{ session('success') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@elseif(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>{{ session('error') }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@elseif(session('toast_error'))
    <script type="text/javascript">
        window.onload = function() {
            Toast.fire({
                icon: 'error',
                title: '{{ session('toast_error') }}'
            });
        }
    </script>
@elseif(session('toast_success'))
    <script type="text/javascript">
        window.onload = function() {
            Toast.fire({
                icon: 'success',
                title: '{{ session('toast_success') }}'
            });
        }
    </script>
@elseif (session('sweetalert'))
    <script type="text/javascript">
        window.onload = function() {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-dark",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: '{{ session('title') }}',
                text: '{{ session('message') }}',
                icon: '{{ session('icon') }}',
                confirmButtonText: "Aceptar",
                //width: '600px'
            });
        }
    </script>
@endif

    <script src="{{ asset('/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            @if (Session::has('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: "{{Session::get('error')}}"
                });
            @endif
            @if (Session::has('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    html: "{{Session::get('success')}}"
                });
            @endif
        });
    </script>
    </body>

    </html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @section('title'){{ $title or 'GoCare' }}@endsection
    <title>@yield('title')</title>
    @include('layouts.includes.head')

    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".datatable").DataTable();

            $(".confirm").click(function (e) {
                var c = confirm("This action cannot be undone! Are you sure you want to do this?");
                if (c) {
                    return true;
                } else {
                    return false;
                }
            });
        });
    </script>
</head>
<body id="app-layout">
@if (Session::has('error'))
    <div class="alert alert-danger">
        {{ Session::get('error') }}
    </div>
@endif
@if (Session::has('info'))
    <div class="alert alert-info">
        {{ Session::get('info') }}
    </div>
@endif
@section('nav')
    @include('layouts.includes.nav')
@show

@yield('content')

</body>
</html>

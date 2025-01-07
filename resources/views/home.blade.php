<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
            .container-form {
                max-width: 500px;
                margin: 0 auto;
                padding: 20px;
            }
        </style>
    </head>
    <body class="antialiased mt-5">

        <form action="{{ route('proceed') }}" method="POST">
            @csrf
            <section class="container-form">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-3 w-100">
                        <label for="user_id" class="form-label">User ID:</label>
                        <input type="number" name="user_id" id="user_id" placeholder="Enter Your User Id" required class="form-control">
                    </div>
        
                    <button type="submit" class="btn btn-primary w-100">Proceed</button>
                </div>
            </section>
        </form>

        <script src="{{ asset('js/app.js') }}" defer></script>

    </body>
</html>

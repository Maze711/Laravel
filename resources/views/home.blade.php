<!DOCTYPE html>
<html>

<head>
    <title>Registraion</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Dashboard</div>
                    <div class="card-body">
                        Welcome {{ auth()->user()->name }}, you are logged in!
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
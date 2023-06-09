<!DOCTYPE html>
<html>

<head>
    <title>Registraion</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container col-md-4 mt-5">
        @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        <div class="card">
            <div class="card-header text-center font-weight-bold">
                <h2>REGISTER FORM</h2>
            </div>
            <div class="card-body">
                <form name="employee" id="employee" method="post" action="{{url('store-form')}}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name</label>
                        <input type="text" id="name" name="name" class="@error('name') is-invalid @enderror form-control">
                        @error('name')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email</label>
                        <input type="email" id="email" name="email" class="@error('email') is-invalid @enderror form-control">
                        @error('email')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">password</label>
                        <input type="password" id="password" name="password" class="@error('password') is-invalid @enderror form-control">
                        @error('password')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div><br>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a class="btn btn-success" href="{{ route('login') }}">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
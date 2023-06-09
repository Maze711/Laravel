<!DOCTYPE html>
<html>

<head>
    <title>Edit</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header h2">Edit User</div>
            <div class="card-body">
                <form action="{{ route('users.update', ['user' => $user->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="{{ $user->name }}" class="@error('name') is-invalid @enderror form-control">
                        @error('name')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="{{ $user->email }}" class="@error('email') is-invalid @enderror form-control">
                        @error('email')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div><br>

                    <button type="submit" class="btn btn-primary">Edit</button>
                    <a href="{{route('home')}}" class="btn btn-danger">Return Home</a>

                </form>
            </div>
        </div>
    </div>
</body>

</html>
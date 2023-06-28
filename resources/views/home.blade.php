<!DOCTYPE html>
<html>

<head>
    <title>DashBoard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header h2">Dashboard</div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        Welcome {{ auth()->user()->name }}, you are logged in!
                        <div class="mt-4">
                            <table class="table text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Email Verified</th>
                                        <!-- <th>Password</th> -->
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->email_verified_at ? 'Yes' : 'No' }}</td>
                                        <!-- <td>********</td> -->
                                        <td>{{ $user->created_at }}</td>
                                        <td>{{ $user->updated_at }}</td>
                                        <td>
                                            <div class="row">
                                                <form class="col-md-6" action="{{route('users.edit', $user->id)}}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">EDIT</button>
                                                </form>
                                                <form class="col-md-6" action="{{route('users.destroy', $user->id)}}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">DELETE</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <a href="{{route('users.add')}}" class="btn btn-success">Add User</a>
                            <a href="{{route('export.excel')}}" class="btn btn-warning">Export</a>
                            <a href="{{route('view')}}" class="btn btn-secondary">Import</a>
                            <a href="{{route('logout')}}" class="btn btn-dark">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
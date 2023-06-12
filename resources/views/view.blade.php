<!DOCTYPE html>
<html>

<head>
    <title>Excel File Uploader</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header h2">Excel Uploader</div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                    <tr>
                                        <td>{{ $row[0] }}</td>
                                        <td>{{ $row[1] }}</td>
                                        <td>{{ $row[2] }}</td>
                                        <td>{{ $row[3] }}</td>
                                        <!-- <td>********</td> -->
                                        <td>{{ $row[4] }}</td>
                                        <td>{{ $row[5] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="container mt-4">
                                <div class="row justify-content-between">
                                    <div class="col-md-6">
                                        <a href="{{ route('users.add') }}" class="btn btn-success">Add User</a>
                                        <a href="{{ route('logout') }}" class="btn btn-dark">Logout</a>
                                    </div>
                                    <div class="col-md-6">
                                        <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="input-group">
                                                <input class="form-control" type="file" name="excel_file" accept=".xls,.xlsx">
                                                <button type="submit" class="btn btn-warning">Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
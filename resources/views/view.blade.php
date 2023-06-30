@extends('layouts.mainlayout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="container mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            @if (session('match'))
                                <div class="alert alert-success">
                                    {{ session('match') }}
                                </div>
                            @elseif(session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @elseif(session('success'))
                                <div class="alert alert-danger">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Filter modal -->
                            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="filterModalLabel">Filter Catalog</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Add your filter form or inputs here -->
                                            <!-- Example: -->
                                            <div class="mb-3">
                                                <label for="filterName" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="filterName">
                                            </div>
                                            <div class="mb-3">
                                                <label for="filterCategory" class="form-label">Category</label>
                                                <input type="text" class="form-control" id="filterCategory">
                                            </div>
                                            <!-- Add more filter inputs as needed -->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary">Apply Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="container mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="h2 w-50">ADD CATALOG</div>
                                            <div class="input-group d-grid gap-4 d-md-flex justify-content-md-end">
                                                <form action="{{ route('catalog.export') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="hidden_columns[]" id="hiddenColumnsInput">
                                                    <button type="submit" class="rounded-pill p-2 fs-6 btn btn-secondary"
                                                        style="width: 250px;"><i class="fa-solid fa-file-arrow-down"></i>
                                                        DOWNLOAD TEMPLATE</button>
                                                </form>
                                                <form method="POST" id="myForm" action="{{ route('import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input id="file_input" style="display:none" type="file"
                                                        name="excel_file" accept=".csv,.xls,.xlsx">
                                                    <button type="button" onclick="selectFile()"
                                                        class="rounded-pill p-2 fs-6 btn btn-secondary"
                                                        style="width: 250px;"><i class="fa-solid fa-file-arrow-up"></i>
                                                        UPLOAD A NEW FILE</button>
                                                </form>
                                            </div>
                                        </div>
                                        <button id="filterButton" class="rounded-pill fs-6 btn btn-secondary" type="button"
                                            style="width: 250px;">
                                            <i class="fa-solid fa-filter"></i> FILTER
                                        </button>
                                    </div>
                                    @if (isset($empty))
                                        <p class="text-center fs-3 mt-4">{{ $empty }}</p>
                                    @else
                                        <div class="h-75 table-responsive mt-4">
                                            <table id="catalogTable"
                                                class="vh-100 table table-bordered border-dark text-justify">
                                                <thead>
                                                    <tr>
                                                        @foreach ($columns as $column)
                                                            <th>{{ $column }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($rows as $row)
                                                        <tr>
                                                            @foreach ($row->toArray() as $value)
                                                                <td class="text-truncate ellipsis"
                                                                    style="max-width: 250px;">{{ $value }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function selectFile() {
            document.getElementById('file_input').click();
        }

        document.getElementById('file_input').addEventListener('change', function() {
            document.getElementById('myForm').submit();
        });

        $(document).ready(function() {
            // Initialize DataTables
            $('#catalogTable').DataTable();

            // Filter modal functionality
            $('#filterButton').click(function() {
                $('#filterModal').modal('show');
            });
        });
    </script>

@endsection

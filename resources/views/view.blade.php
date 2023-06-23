@extends('layouts.mainlayout')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="container mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            @if (session('match'))
                                <div class="alert alert-success">
                                    {{ session('match') }}
                                </div>
                            @endif
                            <div class="modal fade" id="filterModal" tabindex="-1" role="dialog"
                                aria-labelledby="filterModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="filterModalLabel">Toggle Columns</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" id="columnSearch" class="form-control mb-2"
                                                placeholder="Search columns">
                                            @foreach ($columns as $column)
                                                <div class="form-check column-check">
                                                    <input type="checkbox" name="columns[]" value="{{ $column }}"
                                                        class="form-check-input column-toggle">
                                                    <label class="form-check-label">{{ $column }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="container mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="h2">CATALOG</div>
                                            <div class="input-group d-grid gap-2 d-md-flex justify-content-md-end">
                                                <button class="rounded-pill p-2 fs-6 btn btn-secondary"
                                                    style="width: 180px;" data-toggle="modal" data-target="#filterModal"><i
                                                        class="fa fa-filter" aria-hidden="true"></i> FILTER</button>
                                                <form action="{{ route('catalog.export') }}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="hidden_columns[]" id="hiddenColumnsInput">
                                                    <button type="submit" class="rounded-pill p-2 fs-6 btn btn-secondary"
                                                    style="width: 190px;"><i class="fa-solid fa-file-arrow-down"></i> Download
                                                        Template</button>
                                                </form>
                                                <form method="POST" id="myForm" action="{{ route('import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <input class="" id="file_input" style="display:none"
                                                        type="file" name="excel_file" accept=".csv,.xls,.xlsx">
                                                    <button type="button" onclick="selectFile()"
                                                        class="rounded-pill p-2 fs-6 btn btn-secondary"
                                                        style="width: 210px;"><i class="fa-solid fa-file-arrow-up"></i>
                                                        UPLOAD A NEW FILE</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @if (isset($empty))
                                        <p class="text-center fs-3 mt-4">{{ $empty }}</p>
                                    @else
                                        <div class="table-responsive mt-4">
                                            <table class="table table-bordered text-justify">
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
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                            @if ($rows->previousPageUrl())
                                                <a href="{{ $rows->previousPageUrl() }}"
                                                    class="rounded-pill btn btn-secondary" style="width: 150px;"><i
                                                        class="fas fa-arrow-left"></i> Previous Page</a>
                                            @endif

                                            @if ($rows->hasMorePages())
                                                <a href="{{ $rows->nextPageUrl() }}"
                                                    class="rounded-pill fs-6 btn btn-secondary" style="width: 150px;">Next
                                                    Page <i class="fas fa-arrow-right"></i></a>
                                            @endif
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
            // Update hidden column input when checkboxes are toggled
            $('.column-toggle').change(function() {
                updateHiddenColumns();
            });

            // Filter columns based on search input
            $('#columnSearch').keyup(function() {
                var searchValue = $(this).val().toLowerCase();
                $('.column-check').each(function() {
                    var columnLabel = $(this).find('label').text().toLowerCase();
                    if (columnLabel.indexOf(searchValue) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Update hidden column input on modal close
            $('#filterModal').on('hidden.bs.modal', function() {
                updateHiddenColumns();
            });

            // Function to update hidden columns input
            function updateHiddenColumns() {
                var hiddenColumns = [];
                $('.column-toggle').each(function() {
                    if (!$(this).is(':checked')) {
                        hiddenColumns.push($(this).val());
                    }
                });
                $('#hiddenColumnsInput').val(hiddenColumns);
            }
        });
    </script>
@endsection

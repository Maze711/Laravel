@extends('layouts.mainlayout')
@section('content')
    <style>
        /* Active column visibility button */
        .dt-button {
            background-color: #6c757d;
            color: #fff;
            border: #6c757d;
            margin-top: 10px;
            border-radius: 30px;
            width: 190px;
        }

        .dt-button:hover {
            background-color: #5c636a;
            color: #fff;
            transition: 0.3s;
            border: #6c757d;

        }

        /* Inactive column visibility button */
        #catalogTable_wrapper .dt-button-collection {
            position: absolute;
            z-index: 1;
            background: #eee;
            margin: 0 !important;
            top: 40px !important;
            padding: 10px;
            left: 0 !important;
        }

        .dt-buttons {
            position: relative;
        }

        #catalogTable_wrapper .dt-button-background {
            display: none !important;
        }


        #catalogTable_wrapper .dt-button-collection .dt-button {
            background: #0d6efd;
            margin: 2px;
            font-size: 12px;
            width: 12%;
            color: #fff;
            border: 1px solid #0d6efd;
        }

        #catalogTable_wrapper .dt-button-collection .dt-button.active {
            background: #95c0ff;
            border: 1px solid #579bff;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-10">
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
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <!-- Filter modal -->
                            <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog ">
                                    <div class="modal-content" style="width: 200%; left: -50%;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="filterModalLabel">Filter Catalog</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                        </div>
                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="container mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="h2 w-50">STH CATALOG</div>
                                            <div class="input-group d-grid gap-4 d-md-flex justify-content-md-end">
                                                <button id="filterButton" class="rounded-pill fs-6 btn btn-secondary"
                                                    type="button" style="width: 250px;">
                                                    <i class="fa-solid fa-filter"></i> FILTER
                                                </button>
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
                                    </div>
                                    @if (isset($empty))
                                        <p class="text-center fs-3 mt-4">{{ $empty }}</p>
                                    @else
                                        <table id="catalogTable" class="table table-bordered border-dark text-justify">
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
                                                            <td class="text-truncate ellipsis py-3"
                                                                style="max-width: 250px">
                                                                {{ $value }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <p>Showing rows {{ $startRow }} to {{ $endRow }} of {{ $totalRows }}
                                        </p>
                                        <div id="paginationContainer">
                                            <ul class="pagination justify-content-md-end mt-3">
                                                @if ($rows->currentPage() != 1)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $rows->url(1) }}"
                                                            aria-label="First Page">
                                                            <span aria-hidden="true">First Page</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                
                                                @if ($rows->currentPage() > 1)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $rows->previousPageUrl() }}"
                                                            aria-label="Previous">
                                                            <span aria-hidden="true">&laquo; Previous</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                @foreach ($rows->getUrlRange($rows->currentPage(), $rows->currentPage()) as $page => $url)
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @endforeach

                                                @if ($rows->hasMorePages())
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $rows->nextPageUrl() }}"
                                                            aria-label="Next">
                                                            <span aria-hidden="true">Next &raquo;</span>
                                                        </a>
                                                    </li>
                                                @endif

                                                @if ($rows->lastPage() > $rows->currentPage())
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $rows->url($rows->lastPage()) }}"
                                                            aria-label="Last Page">
                                                            <span aria-hidden="true">Last Page</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
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
            function fetchData(page) {
                $.ajax({
                    url: '/get-data/' + page,
                    type: 'GET',
                    success: function(response) {
                        // Update the data container with the received data
                        $('#catalogTable tbody').html(response.data);
                    }
                });
            }

            $('#filterButton').click(function() {
                $('#filterModal').modal('show');
            });

            var importantColumns = [0, 1, 2]; // Define the indices of the important columns
            var searchFilters = {}; // Object to store search filters

            var dataTable = $('#catalogTable').DataTable({
                "dom": 'Bfrtip',
                "buttons": [{
                        "extend": 'copyHtml5',
                        "exportOptions": {
                            "columns": [0, ':visible']
                        }
                    },
                    {
                        "extend": 'excelHtml5',
                        "exportOptions": {
                            "columns": ':visible'
                        }
                    },
                    {
                        "extend": 'pdfHtml5',
                        "exportOptions": {
                            "columns": [0, 1, 2, 5]
                        }
                    },
                    {
                        "extend": 'colvis',
                        "text": 'Column Visibility',
                        "className": 'btn-column-visibility'
                    },
                    {
                        "text": 'Hide All',
                        "action": function(e, dt, button, config) {
                            if (button.text() === 'Hide All') {
                                dataTable.columns().visible(false);
                                button.text('Show All');
                            } else {
                                dataTable.columns().visible(true);
                                button.text('Hide All');
                            }
                        }
                    }
                ],
                "scrollX": true,
                "columnDefs": [{
                    "targets": '_all',
                    "orderable": true // Enable sorting for all columns
                }],
                "paging": false, // Disable the built-in pagination
                "info": false
            });

            // Event listener for Filter button
            $('#filterButton').on('click', function() {
                var modalContent = $('<div></div>');
                var form = $('<form class="row g-4"></form>'); // Add class "row g-3" to the form

                dataTable.columns().every(function() {
                    var column = this;
                    var title = $(column.header()).text();

                    var input = $('<input type="text" class="form-control" placeholder="Search ' +
                            title + '" />')
                        .on('click', function(e) {
                            e.stopPropagation(); // Prevent sorting when clicking on the input
                        })
                        .on('keyup change clear', function() {
                            searchFilters[column.index()] = this
                                .value; // Store the search filter value
                        });

                    var label = $('<label for="filter_' + column.index() + '">' + title +
                        '</label>');

                    var formGroup = $(
                            '<div class="col-md-4"></div>') // Set the width of the input column
                        .append(label, input);

                    form.append(formGroup);
                });

                var applyFilterButton = $(
                        '<button type="button" class="btn btn-primary">Apply Filter</button>')
                    .on('click', function() {
                        for (var index in searchFilters) {
                            var value = searchFilters[index];

                            var column = dataTable.column(parseInt(index));
                            column.search(value).draw();
                        }

                        $('#filterModal').modal('hide');
                    });

                form.append(applyFilterButton);
                modalContent.append(form);

                $('#filterModal .modal-body').empty().append(modalContent);
            });

            // Event listener for pagination links
            $('#catalogTable_wrapper .pagination').on('click', 'a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                fetchData(page);
            });

            function fetchData(page) {
                $.ajax({
                    url: '/get-data/' + page,
                    type: 'GET',
                    success: function(response) {
                        // Update the data container with the received data
                        $('#catalogTable tbody').html(response.data);

                        $('#catalogTable_wrapper .pagination').html(response.pagination);

                    }
                });
            }
        });
    </script>

@endsection

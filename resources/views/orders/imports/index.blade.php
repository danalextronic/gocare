@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Imported CSVs</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a href="{{url('/orders/imports/create')}}" class="btn btn-primary">Import CSV</a>
                                </p>

                            </div>
                        </div>


                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($imports as $import)
                                <tr>
                                    <td>{{ $import->id }}</td>
                                    <td>{{ $import->file_name }}</td>
                                    <td>{{ $import->status }}</td>
                                    <td>{{ $import->status_message }}</td>
                                    <td>
                                        <a href="{{url('/orders/imports/download/', [$import->id])}}" class="btn btn-info">Download</a>
                                        @if ($import->status == 'failed')
                                        <a href="#">Errors</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

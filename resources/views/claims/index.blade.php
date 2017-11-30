@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Claims</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a href="/orders/download/failed" class="btn btn-primary">Download Failed Orders CSV</a>
                                </p>

                            </div>
                        </div>

                        <table class="table table-bordered table-striped table-hover datatable">
                            <thead>
                            <tr>
                                <th>Email</th>
                                <th>Warranty Sku</th>
                                <th>Sku</th>
                                <th>Serial Number</th>
                                <th>Activation Date</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($claims as $claim)
                                <tr>
                                    <td>{{ $claim->email }}</td>
                                    <td>{{ $claim->warranty_sku }}</td>
                                    <td>{{ $claim->sku }}</td>
                                    <td>{{ $claim->serial_number }}</td>
                                    <td>{{ $claim->start_date }}</td>
                                    <td>{{ $claim->created_at }}</td>
                                    <td>{{ $claim->updated_at }}</td>
                                    <td>{{ ($claim->status == "failed") ? "FAILED: " . $claim->failed_reason : $claim->status }}</td>
                                    <td nowrap>
                                        @if ($claim->status == 'failed')
                                            <form action="/claims/{{ $claim->id }}" method="post">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="_method" value="DELETE">
                                                <a href="/claims/{{ $claim->id }}/edit" class="btn btn-info btn-sm">Edit</a>
                                                <input type="submit" value="Delete" class="confirm btn btn-danger btn-sm">

                                            </form>

                                        @else
                                            <a href="#" class="btn btn-info">View</a>
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

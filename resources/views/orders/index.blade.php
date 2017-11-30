@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Orders</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a href="{{url('/orders/download/failed')}}" class="btn btn-primary">Download Failed Orders CSV</a>
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
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->email }}</td>
                                    <td>{{ $order->warranty_sku }}</td>
                                    <td>{{ $order->sku }}</td>
                                    <td>{{ $order->serial_number }}</td>
                                    <td>{{ $order->start_date }}</td>
                                    <td>{{ $order->created_at }}</td>
                                    <td>{{ $order->updated_at }}</td>
                                    <td>{{ ($order->status === 'failed') ? 'FAILED: ' . $order->failed_reason : $order->status }}</td>
                                    <td nowrap>
                                        @if ($order->status === 'failed')
                                            <form action="{{url('/orders/', [$order->id])}}" method="post">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="_method" value="DELETE">
                                                <a href="{{url('/orders/', [$order->id])}}" class="btn btn-info btn-sm">Edit</a>
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

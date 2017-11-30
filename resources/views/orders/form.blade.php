@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ (Request::is('*orders/*/edit') ? 'Edit' : 'Create New') }} Order</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">
                                <form role="form" action="{{url('/orders', [isset($order->id)? '': $order->id])}}" method="post">
                                    {!! csrf_field() !!}

                                    {!! (Request::is('*orders/*/edit') ? '<input name="_method" type="hidden" value="PUT">' : '') !!}
                                    @if ($order->status === 'failed')
                                        <div class="alert alert-danger">
                                            {{ $order->failed_code }} : {{ $order->failed_reason }}
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Customer's Email</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{ $order->email }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Warranty SKU</label>
                                        <input type="text" name="warranty_sku" id="warranty_sku" class="form-control" value="{{ $order->warranty_sku }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>SKU</label>
                                        <input type="text" name="sku" id="sku" class="form-control" value="{{ $order->sku }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Serial Number</label>
                                        <input type="text" name="serial_number" id="serial_number" class="form-control" value="{{ $order->serial_number }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Activation Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ (isset($order->start_date) ? date('Y-m-d', strtotime($order->start_date)) : '') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <button href="#" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

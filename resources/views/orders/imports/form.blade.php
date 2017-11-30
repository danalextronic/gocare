@extends('layouts.app')

@section('content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Import CSV</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-5">
                                <form action="{{url('/orders/imports')}}" method="POST" enctype="multipart/form-data">

                                    {!! csrf_field() !!}

                                    <div class="form-group">
                                        <label>CSV</label>
                                        <input type="file" name="file" class="form-control">
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

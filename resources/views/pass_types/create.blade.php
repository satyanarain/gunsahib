@extends('layouts.master')
@section('header')
<h1>{{headingBold()}}</h1>
{{BreadCrumb()}}
@stop
@section('content')
<div class="row">
    <div class="col-xs-12">
   
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{{headingMain()}}</h3>
           </div>
            <!-- /.box-header -->
            <div class="box-body">
                {!! Form::open([
                'route' => 'pass_types.store',
                'files'=>true,
                'enctype' => 'multipart/form-data'
                 ]) !!}
                @include('pass_types.form', ['submitButtonText' => Lang::get('user.headers.create_submit')])

                {!! Form::close() !!}
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>

@stop


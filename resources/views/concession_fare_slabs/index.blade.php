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
             {{ createButton('create','Add') }}
            </div>
           @include('partials.message')
            <!-- /.box-header -->
            <div class="box-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                         <tr>
                            <th>@lang('Service Name')</th>
                           <th>@lang('Percetage')</th>
                            <th>@lang('From Stage')</th>
                            <th>@lang('To Stage')</th>
                            <th>@lang('Fare')</th>
                            {{  actionHeading('Action', $newaction='') }}
                        </tr>
                    </thead>
                    <tbody>
                         @foreach($concessionFareSlabs as $value)
                        <tr class="nor_f">
                            <td>{{$value->name}}</td>
                             <td>{{$value->percentage}}</td>
                            <td>{{$value->stage_from}}</td>
                            <td>{{$value->stage_to}}</td>
                            <td>{{$value->fare}}</td>
                             {{ actionEdit('edit',$value->id)}}
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->

@include('partials.concession_fare_slabsheader')
@include('partials.table_script')
@stop
@extends('app')
@section('title', 'Редактирование переменной')
@section('content')
@section('controller', 'VariablesForm')
<div class="row">
    <div class="col-sm-12">
        @include('variables._form')
        @include('modules.edit_button')
    </div>
</div>
@stop

@extends('communications::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('communications.name') !!}</p>
@endsection

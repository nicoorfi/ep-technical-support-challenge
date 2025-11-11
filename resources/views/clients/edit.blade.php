@extends('layouts.app')

@section('content')
<div class="container">
    <client-form :client="{{ $client }}" :is-edit="true"></client-form>
</div>
@endsection

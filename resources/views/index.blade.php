@extends('layouts.app')

@section('content')
<div class="container mt-3">
@include('flash::message')
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">id</th>
      <th scope="col">name</th>
      <th scope="col">status code</th>
    </tr>
  </thead>
  <tbody>
  @foreach ($domains as $domain)
    <tr>  
      <td>{{$domain->id}}</td>
      <td><a href="{{route('show', $domain->id)}}"> {{$domain->name}}</a></td>
      <td>{{$domain->created_at}}</td>
      <td>{{$domain->updated_at}}</td>
    </tr>
    @endforeach
  </tbody>
</table>
</div>
@endsection

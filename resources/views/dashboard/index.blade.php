@extends('layouts.dashboard')
@section('judul', 'Dashboard')
@section('isi')
<h1 class="text-3xl font-extrabold">Halo, {{ auth()->user()->name }}!</h1>
<p class="mt-2 text-slate-500">Dashboard {{ auth()->user()->peran }} dibuat pada tahap berikutnya.</p>
@endsection

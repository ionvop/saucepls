@extends('layouts.app')

@section('title', $title . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-7xl">
        <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
        <div class="prose prose-invert mt-4 max-w-none text-sm text-gray-400">
            {!! $contentHtml !!}
        </div>
    </div>
@endsection
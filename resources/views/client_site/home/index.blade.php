@extends('layouts.client_site_app')

@section('title', 'Home')

@section('content')
    @include('client_site.home.sections.hero')
    @include('client_site.home.sections.menu')
    @include('client_site.home.sections.offers')
    @include('client_site.home.sections.about_us')
@endsection

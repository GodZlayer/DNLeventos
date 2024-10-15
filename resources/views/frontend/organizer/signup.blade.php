@extends('frontend.layout')
@section('pageHeading')
  @if (!empty($pageHeading))
    
  @else
    CADASTRO ORGANIZADOR
  @endif
@endsection
@php
  $metaKeywords = !empty($seo->meta_keyword_organizer_login) ? $seo->meta_keyword_organizer_login : '';
  $metaDescription = !empty($seo->meta_description_organizer_login) ? $seo->meta_description_organizer_login : '';
@endphp
@section('meta-keywords', "{{ $metaKeywords }}")
@section('meta-description', "$metaDescription")
@section('hero-section')
  <!-- Page Banner Start -->
  <section class="page-banner overlay pt-120 pb-125 rpt-90 rpb-95 lazy"
    data-bg="{{ asset('assets/admin/img/' . $basicInfo->breadcrumb) }}">
    <div class="container">
      <div class="banner-inner">
        <h2 class="page-title">
          PARA CADASTRO COMO ORGANIZADOR ENTRE EM CONTATO COM GODZLAYER
        </h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            @GodZlayer no instagram ou pelo Email santoss.cog@gmail.com
          </ol>
        </nav>
      </div>
    </div>
  </section>
  <!-- Page Banner End -->
@endsection
@section('content')
  <!-- LogIn Area Start -->
  <div class="login-area pt-115 rpt-95 pb-120 rpb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
<img src="https://dnleventos.com/public/assets/admin/img/670d810be219d.gif" alt="Loading" />
        </div>
      </div>
    </div>
  </div>
  <!-- LogIn Area End -->
@endsection

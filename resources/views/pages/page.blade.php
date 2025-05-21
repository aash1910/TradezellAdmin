@extends('layouts.home')
@section('content')

<main id="primary" class="site-main">

<!-- start: Page Headings -->
<section class="page-headings" data-bg-image="{{ url(@$header_image ?? '')}}">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="page_heading_content text-center">
          <h2 class="title">{{@$title}}</h2>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Page Headings -->

<!-- start: Page Navigation -->
<section class="page-navigation">
  <div class="container">
    <div class="row">
      <div class="col">
        <ul class="page_navigations">
          <li><a href="/">Home</a></li>
          <li>{{@$title}}</li>
        </ul>
      </div>
    </div>
  </div>
</section>
<!-- end: Page Navigation -->

    <!-- start: Haor Details -->
    <section class="haor-details">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-7">
            <div class="details_content">

              {!! @$content !!}

            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end: Haor Details -->

</main>

@stop
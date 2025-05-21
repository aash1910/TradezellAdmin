@extends('layouts.home')
@section('content')

<main id="primary" class="site-main">

<!-- start: Hero Section -->
<section class="hero-section">

  <div class="hero_video_wrap" data-bg-image="{{ url(@$home_top_hero_image ?? '') }}">
    <video src="{{@$home_top_hero_video_url}}" id="topVideo" loop autoplay muted>
    </video>
    <button class="muteVideoBtn" style="position: absolute;
    width: 40px;
    height: 40px;
    right: 10px;
    bottom: 10px;
    background-color: var(--ss-color-theme-primary);
    z-index: 10;
    border-radius: 6px;"><i class="muteVideoBtnIcon fa-light fa-volume fa-volume-mute" style="color: #fff;
    font-weight: bold;"></i></button>

    <button class="playVideoBtn" style="position: absolute;
        width: 40px;
        height: 40px;
        right: 60px;
        bottom: 10px;
        background-color: var(--ss-color-theme-primary);
        z-index: 10;
        border-radius: 6px;"><i class="playVideoBtnIcon fa-light fa-pause" style="color: #fff;
        font-weight: bold;"></i></button>
  </div>

  <div class="scroll_down">
    <a href="#explore" class="scroll_text">Scroll down</a>
  </div>
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="hero_content">
          <div class="hero_content_title">
            <h1 class="title">{{@$home_top_hero_title}}</h1>
          </div>
          <div class="hero_content_desc">
            <p>{{@$home_top_hero_text}}</p>
          </div>
          <div class="hero_content_search">
            <form action="#" class="hero_search">
              
              <select name="district" id="district">
                <option selected value="">District</option>
                @foreach ($district_list ?? array() as $key => $name)
                  <option value="{{ @$key }}">{{ @$name }}</option>
                @endforeach
              </select>
              <select name="thana" id="thana">
                <option selected value="">Thana</option>
                @foreach ($upazila_list ?? array() as $key => $item)
                  <!--option value="{{ @$item['id'] }}" data-did="{{ @$item['district_id'] }}">{{ @$item['name'] }}</option-->
                @endforeach
              </select>
              <input list="haor-list" type="text" name="haor" id="haor" placeholder="Type haor or wetland name" autocomplete="off">
              <datalist id="haor-list">
                @foreach ($haor_list ?? array() as $key => $item)
                  <option data-id="{{ @$item['id'] }}">{{ @$item['name'] }}</option>
                @endforeach
              </datalist>

              <button type="submit" id="haor_search_form"><i class="fa-light fa-search"></i>Find</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Hero Section -->
<script>
  let district_list = <?php echo json_encode($district_list);?>;
  let upazila_list = <?php echo json_encode($upazila_list);?>;
  let haor_list = <?php echo json_encode($haor_list);?>;
</script>

<!-- start: Exploring Section -->
<section class="exploring-section" id="explore">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="section_title text-center">
          <h3 class="title">{{@$home_exploring_title}}</h3>
          <p>{{@$home_exploring_text}}
          </p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="explore_haors grid">
          <div class="gutter-sizer"></div>

          @foreach ($home_exploring_items ?? array() as $key => $item)
          <div class="explore_card grid-item 
            @if($key%3 == 0)
            grid-item--width2
            @else
            grid-item--width1
            @endif">
            <a href="{{ @$item['url'] }}" class="explore_card_inner">
              <div class="explore_card_img">
                <img src="{{ url(@$item['image'] ?? '')}}" alt="">
              </div>
              <div class="explore_card_content">
                <h4 class="title">{{ @$item['title'] }}</h4>
              </div>
            </a>
          </div>
          @endforeach

        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Exploring Section -->

<!-- start: Statistics Section -->
<section class="statistics_section overflow-hidden">
  <div class="container-fluid">
    <div class="row">
      <div class="col">
        <div class="statistic_wrap">
          <div class="statistic_wrap_heading text-center">
            <h6 class="title">STATISTICS</h6>
          </div>

          <div class="statistics_info">
            <div class="statistics_info_item">
              <div class="statistics_info_item_inner">
                <h4 class="title">Haors</h4>
                <div class="stat">{{ @$home_statistics_total_haors }}</div>
              </div>
            </div>
            <div class="statistics_info_item">
              <div class="statistics_info_item_inner">
                <h4 class="title">Area</h4>
                <div class="stat">{{ @$home_statistics_total_area }}</div>
              </div>
            </div>
            <div class="statistics_info_item">
              <div class="statistics_info_item_inner">
                <h4 class="title">Projects</h4>
                <div class="stat">{{ @$home_statistics_total_projects }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Statistics Section -->

<!-- stat: Featured Haors -->
<section class="featured-haors">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="section_title text-center">
          <h6 class="subtitle">{{ @$home_featured_haors_sub_title }}</h6>
          <h3 class="title">{{ @$home_featured_haors_title }}</h3>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="featured_haors_carousel swiper-container swiper">
          <div class="swiper-wrapper">
          @foreach ($home_featured_haors_items ?? array() as $item)
            <div class="swiper-slide featured_haor">
              <div class="featured_haor_img" data-bg-image="{{ url(@$item['image'] ?? '')}}"></div>
              <a href="{{ @$item['url'] }}" class="featured_link"></a>
              <div class="featured_haor_content">
                <h4 class="haor_name">{{ @$item['title'] }}</h4>
                <p class="haor_address">{{ @$item['subtitle'] }}</p>
              </div>
            </div>
          @endforeach
          </div>

          <!-- Add Pagination -->
          <div class="featured_haors_nav">
            <span class="featured_haors_nav-prev"><i class="fas fa-arrow-left"></i></span>
            <span class="featured_haors_nav-next"><i class="fas fa-arrow-right"></i></span>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="view_all">
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="view_all_btn text-center">
            <a href="{{ @$home_featured_haors_view_all_url }}" class="btn">view all</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Featured Haors -->

<!-- start: Haor Map -->
<section class="haor-map">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="section_title text-center">
          <h3 class="title">{{ @$home_haor_map_title }}</h3>
          <p>{{ @$home_haor_map_text }}</p>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="haor_map">
          <img src="assets/images/haor-map/map.jpg" alt="">

          <div class="haor_map_hover">
          @foreach ($home_haor_map_items ?? array() as $item)
            
            @if($item['district'] == "Netrokona")
            <a href="{{ @$item['url'] }}" class="netrakona"><img src="assets/images/haor-map/netrakona.png" alt=""
                title="Netrakona"></a>
            @endif
            @if($item['district'] == "Sunamgang")
            <a href="{{ @$item['url'] }}" class="sunamganj"><img src="assets/images/haor-map/sunamganj.png" alt=""
                title="Sunamganj"></a>
            @endif
            @if($item['district'] == "Sylhet")
            <a href="{{ @$item['url'] }}" class="sylhet"><img src="assets/images/haor-map/sylhet.png" alt=""
                title="Sylhet"></a>
            @endif
            @if($item['district'] == "Kishoreganj")
            <a href="{{ @$item['url'] }}" class="kishoreganj"><img src="assets/images/haor-map/kishoreganj.png" alt=""
                title="Kishoreganj"></a>
            @endif
            @if($item['district'] == "Habiganj")
            <a href="{{ @$item['url'] }}" class="habiganj"><img src="assets/images/haor-map/habiganj.png" alt=""
                title="Habiganj"></a>
            @endif
            @if($item['district'] == "Maulvibazar")
            <a href="{{ @$item['url'] }}" class="moulavibazar"><img src="assets/images/haor-map/moulavibazar.png" alt=""
                title="Moulavibazar"></a>
            @endif
            @if($item['district'] == "Brahmanbaria")
            <a href="{{ @$item['url'] }}" class="brahmanbaria"><img src="assets/images/haor-map/brahmanbaria.png" alt=""
                title="Brahmanbaria"></a>
            @endif

          @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Haor Map -->

<!-- start: Conversion Effect -->
<section class="conversion-effect-section">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="section_title text-center">
          <h3 class="title">{{ @$home_conservation_effects_title }}</h3>
          <p>{{ @$home_conservation_effects_text }}
          </p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="conversion_effects">
        @foreach ($home_conservation_effects_items ?? array() as $key => $item)
          <div class="conversion_effects_item 
            @if($key%5 == 0)
            item_big
            @endif">
            <a href="{{ @$item['url'] }}" class="conversion_effects_item_inner">
              <div class="effect_image">
                <img src="{{ url(@$item['image'] ?? '')}}" alt="">
              </div>
              <div class="effect_content">
                <h4 class="title">{{ @$item['title'] }}</h4>
                <p>{{ @$item['text'] }}</p>
              </div>
            </a>
          </div>
        @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Conversion Effect -->

<!-- start: Report Section -->
<section class="report-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-xl-4 col-lg-5 col-md-7">
        <div class="section_title">
          <h3 class="title">{{ @$home_summary_report_title }}</h3>
          <p>{{ @$home_summary_report_sub_title }}</p>
          @if($home_summary_report_view_all_url != "")
          <a href="{{ @$home_summary_report_view_all_url }}" class="btn">VIEW ALL</a>
          @endif
        </div>
      </div>
      <div class="col-xl-8">
        <div class="row">
        @foreach ($home_summary_report_items ?? array() as $item)
          <div class="col-md-4">
            <div class="report">
              <div class="report_img">
                <img src="{{ url(@$item['image'] ?? '')}}" alt="">
              </div>
              <div class="report_content">
                <h4 class="title"><a href="{{ @$item['url'] }}" target="_blank">{{ @$item['title'] }}</a></h4>
                <p>{{ @$item['subtitle'] }}</p>
                <a href="{{ @$item['url'] }}" target="_blank" class="btn-inline">DOWNLOAD <i class="fa-regular fa-arrow-right-long"></i></a>
              </div>
            </div>
          </div>
        @endforeach

        </div>
      </div>
    </div>
  </div>
</section>
<!-- end: Report Section -->

<!-- start: Tourism Section -->
<section class="tourism-section">
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="section_title text-center">
          <h3 class="title">{{ @$home_recreation_tourism_title }}</h3>
        </div>
      </div>
    </div>
    <div class="row">
    @foreach ($home_recreation_tourism_items ?? array() as $item)
      <div class="col-lg-4">
        <div class="tourism">
          <a href="{{ @$item['url'] }}" class="tourism_inner">
            <div class="tourism_img">
              <img src="{{ url(@$item['image'] ?? '')}}" alt="">
            </div>
            <div class="tourism_content">
              <h4 class="title">{{ @$item['title'] }}</h4>
              <p class="desc">{{ @$item['subtitle'] }}</p>
            </div>
          </a>
        </div>
      </div>
    @endforeach

    </div>
  </div>
</section>
<!-- end: Tourism Section -->

<!-- start: Gallery Section -->
<section class="gallery-section">
  <div class="gallery-section_inner owl-carousel">
  @foreach ($home_gallery_items ?? array() as $item)
    <a class="gallery_img" href="{{ url(@$item['image2'] ?? '')}}">
      <img src="{{ url(@$item['image'] ?? '')}}" alt="">
    </a>
  @endforeach
  </div>
</section>
<!-- end: Gallery Section -->
</main>

@stop
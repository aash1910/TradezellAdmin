<div class="body-overlay d-md-none"></div>

<!-- start: Hamburger Menu -->
<div class="hamburger-area d-md-none">
  <div class="hamburger_bg"></div>
  <div class="hamburger_wrapper">
    <div class="hamburger_top d-flex align-items-center justify-content-between">
      <div class="hamburger_logo">
        <a href="/"><img src="{{ url(@$topbar_logo ?? '')}}" alt="LOGO"></a>
      </div>
      <div class="hamburger_close">
        <button class="hamburger_close_btn"><i class="fa-thin fa-times"></i></button>
      </div>
    </div>

    <div class="hamburger_menu">
      <div class="mobile_menu"></div>
    </div>
  </div>
</div>
<!-- end: Hamburger Menu -->

<!-- start: Header Area -->
<header class="site-header header-absolute">
  <div class="header_topbar">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="topbar_content">
            <div class="topbar_content_left">
              <ul class="contact_list">
                <li class="haor-gov-logo"><img src="/assets/images/logos/db-logo.png" alt=""></li>
                <li class="haor-dep-name">Department of Bangladesh Haor and Wetlands Development</li>
                <li><a href="tel:{{@$topbar_telephone}}"><i class="fa-light fa-phone"></i>{{@$topbar_telephone}}</a></li>
                <li><a href="mailto:{{@$topbar_email}}"><i class="fa-light fa-envelope"></i>Contact us</a></li>
              </ul>
            </div>
            <div class="topbar_content_right">
              <ul class="social_list">
                @foreach ($social_media_menu_items ?? array() as $item)
                    <li><a href="{{ @$item['url'] }}"><i class="fa-brands fa-{{ @$item['icon'] }}"></i></a></li>
                @endforeach
              </ul>
              <div class="header_lang">
                <span>ENG</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="main_header">
    <div class="container">
      <div class="row">
        <div class="col">
          <div class="main_header_wrapper">

            <div class="site_logo">
              <a href="/" class="logo"><img src="{{ url(@$topbar_logo ?? '')}}" alt=""></a>
            </div>

            <nav class="primary_menu d-none d-md-inline-flex">
              <ul>
                @foreach ($topbar_menu_items ?? array() as $item)
                    <li><a href="{{ @$item['url'] }}">{{ @$item['text'] }}</a></li>
                @endforeach
              </ul>
            </nav>

            <div class="mega_menu_bars d-none d-md-inline-flex">
              <div class="mega_menu_bars_inner">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>

            <div class="menu_bar d-md-none">
              <a href="javascript:void(0)" class="bars"><i class="fa-light fa-bars"></i></a>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mega_menus d-none d-md-block">
    <div class="mega_menu_title">
      <h6 class="title">LIST OF HAORS</h6>
    </div>

    <div class="container">
      <div class="row">
        <div class="col">
          <div class="mega_menu_wrap">
            @foreach ($mega_menu_items ?? array() as $item)
            <div class="mega_menu_wrap_item">
              <a href="{{ @$item['url'] }}" class="mega_menu_wrap_item_inner">
                <div class="haor_image">
                  <img src="{{ url(@$item['image'] ?? '')}}" alt="">
                </div>
                <div class="haor_name">
                  <h6 class="name">{{ @$item['title'] }}</h6>
                </div>
              </a>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <div class="mega_menu_button">
      <a href="/haors" class="btn-inline">VIEW ALL</a>
    </div>
  </div>
</header>
<!-- end: Header Area -->
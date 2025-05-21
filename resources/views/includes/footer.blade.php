  <!-- start: Footer Area -->
  <footer class="footer-area">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6">
          <div class="footer_widget">
            <div class="site_info">
              <div class="footer_logo">
                <a href="/" class="logo"><img src="{{ url(@$footer_logo ?? '')}}" alt=""></a>
              </div>
              <p class="desc">{{@$footer_text}}</p>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="footer_widget">
            <h6 class="widget_title">QUICK LINKS</h6>

            <nav class="widget_menu">
              <ul>
                @foreach ($footer_link_items ?? array() as $key=>$item)
                  <li><a href="{{ @$item['url'] }}">{{ @$item['text'] }}</a></li>
                @endforeach
              </ul>
            </nav>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="footer_widget">
            <h6 class="widget_title">QUICK LINKS</h6>

            <nav class="widget_menu">
              <ul>
                @foreach ($footer_link_items_section2 ?? array() as $key=>$item)
                  <li><a href="{{ @$item['url'] }}">{{ @$item['text'] }}</a></li>
                @endforeach
              </ul>
            </nav>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="footer_widget">
            <h6 class="widget_title">QUICK LINKS</h6>

            <ul class="socials">
                @foreach ($social_media_menu_items ?? array() as $item)
                  <li><a href="{{ @$item['url'] }}"><i class="fa-brands fa-{{ @$item['icon'] }}"></i></a></li>
                @endforeach
            </ul>
          </div>
          <div class="footer_widget">
            <h6 class="widget_title">ADDRESS</h6>
            <p class="address">{{@$footer_contact_address}}</p>
            <p><a target="_blank" href="https://play.google.com/store/apps/details?id=com.amarhaor.app" style="max-width: 200px;
    display: inline-block;
    border: 1px solid #fff;
    border-radius: 12px;
    overflow: hidden;
    width: 100%;"><img src="/assets/images/google-play-download-android-app-logo-png-transparent.png" alt=""></a></p>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <div class="footer_bottom">
            <div class="copyright_text">
              {!! @$footer_copyright_text !!}
            </div>
            <nav class="footer_menu">
              <ul>
                <li><a href="/privacy-policy">Privacy policy</a></li>
                <li><a href="/cookies">Cookie settings</a></li>
                <li><a href="/terms-of-use">Terms & Conditions</a></li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <!-- end: Footer Area -->
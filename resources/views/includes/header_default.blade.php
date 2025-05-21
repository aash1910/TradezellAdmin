<main class="position-relative">
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="navbar navbar-expand-lg navbar-light" id="navbar_top">
                        <div class="container">
                            <a class="navbar-brand" href="/">
                                <img src="{{ url(@$topbar_logo ?? '')}}" class="logo top-logo" class="img-fluid" />
                                <img src="{{ url(@$topbar_logo ?? '')}}" class="logo logo-topfix" class="img-fluid" />
                            </a>
                            
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
                            </button>
                            
                            
                            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav m-auto mb-2 mb-lg-0">
                                  @foreach ($topbar_menu_items ?? array() as $item)
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ @$item['url'] }}">
                                            {{ @$item['text'] }}
                                        </a>
                                    </li>
                                  @endforeach
                                </ul>
                                <ul class="socialmedia-icons">
                                  @foreach ($social_media_menu_items ?? array() as $item)
                                    <li>
                                        <a href="{{ @$item['url'] }}" target="_blank"><i class="fab fa-{{ @$item['icon'] }}"></i></a>
                                    </li>
                                  @endforeach
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <section>
        <div class="slider-container">
          <figure style="background-image: url({{ url(@$home_top_img2 ?? '')}});">
            <div id="compare" style="background-image: url({{ url(@$home_top_img ?? '')}});"></div>
            <div class="banner-text">
                <h1 class="banner-title">{{@$home_top_title}}</h1>
                <p>{{@$home_top_text}}</p>
            </div>
          </figure>
          <input oninput="beforeAfter()" onchange="beforeAfter()" type="range" min="0" max="100" value="50" id="slider"/>
        </div>
    </section>
</main>
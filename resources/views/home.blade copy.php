
<!doctype html>
<html lang="en">
    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-212160370-2"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-212160370-2');
        </script>

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-NLZRN93');</script>
        <!-- End Google Tag Manager -->

        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Title-->
        <title>Level Renovation Landing Page</title>

        <!-- Favicon-->
        <!-- <link rel="icon" href="assets/img/" type="image/gif"> -->

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <!--Fontawesome-->
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <!--Font Family-->
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@700&family=Inter:wght@700&display=swap" rel="stylesheet"> 
        <!-- Slicknav CSS -->
        <link rel="stylesheet" href="assets/vendor/slicknav/css/slicknav-1.0.10.min.css">
        <!--Lightbox2-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" integrity="sha512-ZKX+BvQihRJPA8CROKBhDNvoc2aDMOdAlcm7TUQY+35XYtrd3yh95QOOhsPDQY9QnKE0Wqag9y38OIgEvb88cA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Style CSS -->
        <link rel="stylesheet" href="assets/css/style.css">
        <!-- Responsive CSS -->
        <link rel="stylesheet" href="assets/css/responsive.css">

    </head>

    <body>

        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NLZRN93"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

        <!-- Header Area Start -->
        <header>
           <div class="top-bar">
               <div class="container">
                   <div class="row">
                       <div class="col-md-12">
                           <div class="logo">
                               <a href="{{ url('/') }}">
                                <img src="{{ url(@$topbar_logo ?? '')}}" alt="">
                            </a>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
        </header>
        <!-- Header Area End -->

        <!-- Hero Section Start -->
        <section id="hero-section" style="background-image: url( {{ url(@$hero_background_img ?? '')}} );">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="header-primary">
                            {!! @$hero_content !!}
                            <a class="btn button-primary" href="javascript:;" data-toggle="modal" data-target="#getQuoteModal">{{@$hero_btn_text}}</a>
                            <a class="btn button-secondary" href="tel:{{@$topbar_tel_number}}"><img src="{{ url(@$topbar_tel_icon ?? '')}}" class="icon-left" alt=""> {{@$topbar_tel_number}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Hero Section End -->

        <!-- Feature Section Start -->
        <section id="feature-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 d-flex flex-wrap align-items-center justify-content-center">
                        @foreach ($features ?? array() as $item)
                            <div class="item d-flex align-items-center">
                                <div class="image-box">
                                    <img src="{{ url(@$item['image'] ?? '') }}" alt="">
                                </div>
                                <div class="text-box">
                                    <p>{{ @$item['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!-- Feature Section End -->

        <!-- Rating Section Start -->

        <section id="rating-section" class="first">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 d-flex flex-wrap text-center justify-content-around">
                    @foreach ($rating_items_first ?? array() as $item)
                        <div class="rating-box">
                            <img src="{{ url(@$item['image'] ?? '') }}" class="icon" alt="">
                            <h3>{{ @$item['title'] }}</h3>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

        </section>

        <section id="rating-section" class="pt-0">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 d-flex flex-wrap text-center justify-content-around">
                    @foreach ($rating_items ?? array() as $item)
                        <div class="rating-box">
                            <img class="five-star" src="{{ url(@$item['image2'] ?? '') }}" alt="">
                            <p>{{ @$item['title'] }}</p>
                            <img class="company-logo" src="{{ url(@$item['image'] ?? '') }}" alt="">
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

        </section>
        <!-- Rating Section End -->

        <!-- Choose Us Section Start -->
        <section id="choose-us-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="left-area">
                            <img src="{{ url(@$choose_us_left_img ?? '')}}" alt="">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="right-area">
                            <h2>{{@$choose_us_title}}</h2>
                        <ul>
                        @foreach ($choose_us_why ?? array() as $item)
                            <li class="d-flex">
                                <div class="icon-box">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="text-box">
                                    <p>{{ @$item['text'] }}</p>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Choose Us Section End -->

        <!-- Discover Section Start -->
        <section id="discover-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="left-area">
                            {!! @$discover_content !!}
                            <ul>
                            @foreach ($discover_items ?? array() as $key => $item)
                                <li>
                                    <a href="{{ @$item['url'] }}" data-index="{{$key}}">
                                        <img src="{{ url(@$item['image'] ?? '') }}" alt="">
                                        {{ @$item['text'] }}
                                    </a>
                                </li>
                            @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="right-area">
                        @foreach ($discover_items ?? array() as $key => $item)
                            <div class="image-box" id="image-{{$key}}"><img src="{{ url(@$item['image2'] ?? '') }}" alt=""></div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Discover Section End -->

        <!-- About Us Section Start -->
        <section id="about-us-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 d-flex flex-wrap align-items-center">
                        <div class="video-box">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{@$about_us_video_id}}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                        <div class="text-box">
                            {!! @$about_us_content !!}
                            <div class="inner-box d-flex flex-wrap">
                            @foreach ($about_us_items ?? array() as $item)
                                <div class="item">
                                    <span>{{ @$item['number'] }}</span>
                                    <p>{{ @$item['text'] }}</p>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- About Us Section End -->


        <!--Renovation Section Two Start-->
        <section id="renovation-section-2">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h2>{{@$renovation_title}}</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="left-area">
                            <div class="renovation-items">
                                <ul>
                                @foreach ($renovation_items ?? array() as $key=>$item)
                                    <li data-index="{{ $key }}">
                                        {{ @$item['title'] }}
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        </div> 
                    </div>

                    <div class="col-md-6">
                        <div class="right-area d-flex align-items-center">
                        @foreach ($renovation_items ?? array() as $key=>$item)
                            <div class="text-box" id="text-box-{{ $key }}">
                                <ul>
                                @foreach (json_decode(@$item['list'] ?? '[]', true) ?? array() as $list)
                                    <li>
                                        <p>{{ @$list['text'] }}</p>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </section>
        <!--Renovation Section Two End-->

        <!-- Reviews Section Start -->
        <section id="reviews-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h2>{{@$review_title}}</h2>
                    </div>
                </div>
                <div class="row">
                @foreach ($review_items ?? array() as $item)
                    <div class="col-md-4">
                        <div class="reviews-box">
                            <div class="reviews-item">
                                <img src="{{ url(@$item['image'] ?? '') }}" class="five-starts-icon" alt="">
                                <h4>{{ @$item['title'] }}</h4>
                                <p>{{ @$item['content'] }}</p>

                                <span>{{ @$item['author'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </section>
        <!-- Reviews Section End -->

        <!-- Gallery Section start -->
        <section id="gallery-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h2>{{@$gallery_title}}</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="gallery-box">
                        @foreach ($gallery_items ?? array() as $item)
                            <div class="gallery-item">
                                <a href="{{ url(@$item['image'] ?? '') }}" data-lightbox="gallery">
                                    <img src="{{ url(@$item['image'] ?? '') }}" alt="">
                                </a>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Gallery Section End -->

        <!-- Consultation Section Start -->
        <section id="consultation-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        {!! @$consultation_content !!}
                        <a class="btn button-primary" href="javascript:;" data-toggle="modal" data-target="#getQuoteModal">{{@$consultation_btn_text}}</a>
                        <a class="btn button-third" href="tel:{{@$topbar_tel_number}}"><img src="{{ url(@$topbar_tel_icon ?? '')}}" class="icon-left" alt=""> {{@$topbar_tel_number}}</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Consultation Section End -->


        <!-- Footer Area Start -->
        <footer>
            <div class="footer-top">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 d-flex flex-wrap justify-content-between">
                        @foreach ($footer_items ?? array() as $item)
                            <div class="footer-item">
                                {!! @$item['content'] !!}
                            </div>
                        @endforeach

                            <div class="footer-item">
                                <div class="social-icon">
                                    <h3>{{@$footer_social_media_title}}</h3>
                                    <ul class="d-flex flex-wrap">
                                    @foreach ($footer_social_media_items ?? array() as $item)
                                        <li>
                                            <a href="{{ @$item['url'] }}"><i class="{{ @$item['icon'] }}"></i></a>
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            {!! @$footer_copyright_content !!}
                        </div>
                    </div>
                </div>
            </div>
            
        </footer>
        <!-- Footer Area End -->

        <!-- Modal Form -->

        <div class="modal fade" id="getQuoteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
             <form class="send_quote_form" id="sendQuoteForm">
              <div class="modal-header border-0">
                <h5 class="modal-title" id="exampleModalLabel">{{@$hero_btn_text}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body border-0">
                <input type="hidden" name="to" value="{{@$hero_btn_url}}" required>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="name" class="col-form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="cell" class="col-form-label">Cell</label>
                    <input type="text" class="form-control" id="cell" name="cell" required>
                  </div>
                </div>
                  <div class="form-group">
                    <label for="email" class="col-form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                      <label for="budget">Budget</label>
                      <select id="budget" class="form-control" name="budget" required>
                        <option selected value="">Choose...</option>
                        <option value="$15,000-$20,000">$15,000-$20,000</option>
                        <option value="$20,000-$26,000">$20,000-$26,000</option>
                        <option value="$26,000+">$26,000+</option>
                      </select>
                  </div>
                  <div class="form-group col-md-6">
                      <label for="start_time">When you want to start</label>
                      <select id="start_time" class="form-control" name="start_time" required>
                        <option selected value="ASAP">ASAP</option>
                        <option value="2-3 months">2-3 months</option>
                        <option value="4-6 months">4-6 months</option>
                        <option value="6-12 months">6-12 months</option>
                        <option value="I am not sure yet">I am not sure yet</option>
                      </select>
                  </div>
                </div>
                  <div class="form-group">
                    <label for="description" class="col-form-label">Project Description</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                  </div>
                
              </div>
              <div class="modal-footer border-0">
                <button type="submit" class="button-primary">Send quote</button>
              </div>
             </form>
            </div>
          </div>
        </div>
        <!-- Modal Form -->
        
        

        <!-- Font Awesome JS -->
        <script src="https://kit.fontawesome.com/91badfcfce.js" crossorigin="anonymous"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
        <!-- Popper JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <!-- Bootstrap JS -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <!-- Slicknav JS -->
        <script src="assets/vendor/slicknav/js/jquery.slicknav-1.0.10.min.js"></script>
        <!-- Masonry -->
        <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
        <!--Lightbox2-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js" integrity="sha512-k2GFCTbp9rQU412BStrcD/rlwv1PYec9SNrkbQlo6RZCf75l6KcC3UwDY8H5n5hl4v77IDtIPwOk9Dqjs/mMBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <!-- Main JS -->
        <script src="assets/js/main.js"></script>

        <script type="text/javascript">
            $(function() {

                $('#sendQuoteForm').on('submit', function(e) {
                    e.preventDefault();

                    $.ajax({
                        type: "GET",
                        url: "/api/send_email_quote",
                        data: $('form.send_quote_form').serialize(),
                        success: function(response) {
                            if(response && response['response'] == 'success'){
                                //alert("Email sent successfully");
                                $('#getQuoteModal').modal('hide');
                                window.location.href = "/thank-you";
                            }
                            
                        },
                        error: function() {
                            alert('Error');
                        }
                    });
                    return false;
                });
            });
        </script>

    </body>
</html>

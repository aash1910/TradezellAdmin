
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
        <title>Thank You</title>

        <!-- Favicon-->
        <!-- <link rel="icon" href="assets/img/" type="image/gif"> -->

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <!--Fontawesome-->
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <!--Font Family-->
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@700&family=Inter:wght@700&display=swap" rel="stylesheet"> 

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

        <!-- Consultation Section Start -->
        <section id="consultation-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h2>Contact Us</h2>
                        <a href="{{ url('/') }}">
                            <img src="{{ url(@$topbar_logo ?? '')}}" alt="">
                        </a>
                        <p>Thank you for contacting us, we will be in touch soon!</p>
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

    </body>
</html>

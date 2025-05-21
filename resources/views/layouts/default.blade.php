<!DOCTYPE html>
<html lang="en">

  <head>
      @include('includes.head')
  </head>

  <body>
      @include('includes.header_default')

      @yield('content')

      @include('includes.footer')

      @include('includes.footer_js')

  </body>

</html>
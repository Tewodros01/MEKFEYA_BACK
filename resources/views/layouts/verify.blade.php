<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Scripts - Loads React! -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles - Loads Compiled Css (Bootstrap) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <title>IMSV2</title>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Verify Your Email Address</div>
                      <div class="card-body">
                       @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                               {{ __('A fresh verification link has been sent to your email address.') }}
                           </div>
                       @endif
                       <a href="http://customlaravelauth.co/{{$token}}/reset-password">Click Here</a>.
                   </div>
               </div>
           </div>
       </div>
   </div>
</body>

</html>
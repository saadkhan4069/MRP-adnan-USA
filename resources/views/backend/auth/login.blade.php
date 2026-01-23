<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <link rel="manifest" href="{{url('manifest.json')}}">
    @if(!config('database.connections.saleprosaas_landlord'))
    <link rel="icon" type="image/png" href="{{url('logo', $general_setting->site_logo)}}" />
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="preload" href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet"></noscript>
    <!-- login stylesheet-->
    <link rel="stylesheet" href="<?php echo asset('css/auth.css') ?>" id="theme-stylesheet" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" rel="stylesheet"></noscript>
    @else
    <link rel="icon" type="image/png" href="{{url('../../logo', $general_setting->site_logo)}}" />
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    <!-- Font Awesome CSS-->
    <link rel="preload" href="<?php echo asset('../../vendor/font-awesome/css/font-awesome.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet"></noscript>
    <!-- login stylesheet-->
    <link rel="stylesheet" href="<?php echo asset('../../css/auth.css') ?>" id="theme-stylesheet" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" rel="stylesheet"></noscript>
    @endif
  </head>
  <body>
    <style>
      .professional-login-wrapper {
        display: flex;
        min-height: 100vh;
        width: 100%;
      }
      .login-image-section {
        flex: 1;
        background: linear-gradient(135deg, rgba(124, 92, 196, 0.9), rgba(156, 39, 176, 0.9)),
                    url('{{ asset("images/login.png") }}');
        background-size: cover;
        background-position: center;
        background-blend-mode: overlay;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 186px;
        position: relative;
      }
      .login-image-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        /* background: rgba(0, 0, 0, 0.3); */
      }
      .login-image-content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
        max-width: 500px;
      }
      .login-image-content h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      }
      .login-image-content p {
        font-size: 1.1rem;
        line-height: 1.6;
        opacity: 0.95;
      }
      .login-form-section {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        background: #f7f2fc;
      }
      .professional-form-inner {
        width: 100%;
        max-width: 450px;
        background: #fff;
        border-radius: 12px;
        /*padding: 50px 40px;*/
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      }
      .professional-logo {
        text-align: center;
        margin-bottom: 40px;
      }
      .professional-logo img {
        max-width: 150px;
        height: auto;
      }
      .professional-logo span {
        font-size: 2rem;
        font-weight: 700;
        color: #7c5cc4;
      }
      .professional-form-inner h2 {
        text-align: center;
        color: #333;
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 10px;
      }
      .professional-form-inner .subtitle {
        text-align: center;
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 30px;
      }
      .form-group-material {
        position: relative;
        margin-bottom: 25px;
      }
      .input-material {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fff;
      }
      .input-material:focus {
        outline: none;
        border-color: #7c5cc4;
        box-shadow: 0 0 0 3px rgba(124, 92, 196, 0.1);
      }
      .label-material {
        position: absolute;
        left: 15px;
        top: 12px;
        color: #999;
        font-size: 1rem;
        pointer-events: none;
        transition: all 0.3s ease;
        background: #fff;
        padding: 0 5px;
      }
      .label-material.active {
        top: -10px;
        left: 10px;
        font-size: 0.85rem;
        color: #7c5cc4;
        font-weight: 500;
      }
      .btn-primary {
        background: linear-gradient(135deg, #845be3, #b6c1ef);
        border: none;
        padding: 14px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(124, 92, 196, 0.3);
      }
      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(124, 92, 196, 0.4);
      }
      .forgot-pass {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #7c5cc4;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease;
      }
      .forgot-pass:hover {
        color: #9c27b0;
        text-decoration: none;
      }
      .register-section {
        text-align: center;
        margin-top: 25px;
        color: #666;
        font-size: 0.9rem;
      }
      .register-section a {
        color: #7c5cc4;
        font-weight: 600;
        text-decoration: none;
      }
      .register-section a:hover {
        color: #9c27b0;
        text-decoration: underline;
      }
      #togglePassword {
        right: 15px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        color: #999;
        font-size: 1.1rem;
      }
      #togglePassword:hover {
        color: #7c5cc4;
      }
      .copyrights {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
        color: #999;
        font-size: 0.85rem;
      }
      @media (max-width: 768px) {
        .professional-login-wrapper {
          flex-direction: column;
        }
        .login-image-section {
          min-height: 300px;
        }
        .login-image-content h1 {
          font-size: 1.8rem;
        }
        .professional-form-inner {
          padding: 40px 30px;
        }
      }
      .alert {
        border-radius: 8px;
        margin-bottom: 20px;
      }
    </style>
    <div class="page login-page">
      <div class="professional-login-wrapper">
        <div class="login-image-section">
          <div class="login-image-content">
            
          </div>
        </div>
        <div class="login-form-section">
          <div class="professional-form-inner">
            <div class="professional-logo">
                @if($general_setting->site_logo)
                <!--<img src="{{url('logo', $general_setting->site_logo)}}" alt="Logo">-->
                @else
                <!--<span>{{$general_setting->site_title}}</span>-->
                @endif
            </div>
            <h2>Sign In</h2>
            <p class="subtitle">Enter your credentials to continue</p>
            
            @if(session()->has('delete_message'))
            <div class="alert alert-danger alert-dismissible text-center">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              {{ session()->get('delete_message') }}
            </div>
            @endif
            @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible text-center">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              {!! session()->get('message') !!}
            </div>
            @endif
            @if(session()->has('not_permitted'))
            <div class="alert alert-danger alert-dismissible text-center">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              {{ session()->get('not_permitted') }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" id="login-form">
              @csrf
              <div class="form-group-material">
                <input id="login-username" type="text" name="name" required class="input-material" value="">
                <label for="login-username" class="label-material">{{__('db.UserName')}}</label>
                @if(session()->has('error'))
                    <p style="color: #dc3545; font-size: 0.85rem; margin-top: 5px;">
                        <strong>{{ session()->get('error') }}</strong>
                    </p>
                @endif
              </div>

              <div class="form-group-material">
                <input id="login-password" type="password" name="password" required class="input-material" value="">
                <label for="login-password" class="label-material">{{__('db.Password')}}</label>
                <span id="togglePassword" class="position-absolute" style="right: 0; top: 50%; transform: translateY(-50%); cursor: pointer;">
                    <i class="fa fa-eye-slash"></i>
                </span>
                @if(session()->has('error'))
                    <p style="color: #dc3545; font-size: 0.85rem; margin-top: 5px;">
                        <strong>{{ session()->get('error') }}</strong>
                    </p>
                @endif
              </div>
              
              <button type="submit" class="btn btn-primary btn-block">{{__('db.LogIn')}}</button>
            </form>
            
            <a href="{{ route('password.request') }}" class="forgot-pass">{{__('db.Forgot Password?')}}</a>
            
            <p class="register-section">
              {{__('db.Do not have an account?')}}
              <a href="{{url('register')}}" class="signup register-section">{{__('db.Register')}}</a>
            </p>
            
           
          </div>
        </div>
      </div>

     
    </div>
  </body>
</html>
@if(!config('database.connections.saleprosaas_landlord'))
<script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.min.js') ?>"></script>
@else
<script type="text/javascript" src="<?php echo asset('../../vendor/jquery/jquery.min.js') ?>"></script>
@endif
<script>
    @if(config('database.connections.saleprosaas_landlord'))
        if(localStorage.getItem("message")) {
            alert(localStorage.getItem("message"));
            localStorage.removeItem("message");
        }
        numberOfUserAccount = <?php echo json_encode($numberOfUserAccount)?>;
        $.ajax({
            type: 'GET',
            async: false,
            url: '{{route("package.fetchData", $general_setting->package_id)}}',
            success: function(data) {
                if(data['number_of_user_account'] > 0 && data['number_of_user_account'] <= numberOfUserAccount) {
                    $(".register-section").addClass('d-none');
                }
            }
        });
    @endif

    $("div.alert").delay(4000).slideUp(800);

    //switch theme code
    var theme = <?php echo json_encode($theme); ?>;
    if(theme == 'dark') {
        $('body').addClass('dark-mode');
        $('#switch-theme i').addClass('dripicons-brightness-low');
    }
    else {
        $('body').removeClass('dark-mode');
        $('#switch-theme i').addClass('dripicons-brightness-max');
    }

    $('#togglePassword').click(function() {
        var passwordField = $("#login-password"); // Select password input
        var icon = $(this).find("i"); // Select eye icon inside #togglePassword

        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text"); // Show password
            icon.removeClass("fa-eye-slash").addClass("fa-eye"); // Change icon
        } else {
            passwordField.attr("type", "password"); // Hide password
            icon.removeClass("fa-eye").addClass("fa-eye-slash"); // Change back icon
        }
    });

    $('.demo-btn').on('click', function(e) {
        e.preventDefault();
        $("input[name='name']").focus().val('admin');
        $("input[name='password']").focus().val('admin');
        let form = $('#login-form');
        form.attr('action', $(this).attr('href'));
        form.submit();
    });


    if ('serviceWorker' in navigator ) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/salepro/service-worker.js').then(function(registration) {
                // Registration was successful
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
                // registration failed :(
                console.log('ServiceWorker registration failed: ', err);
            });
        });
    }

    $('.admin-btn').on('click', function(){
        $("input[name='name']").focus().val('admin');
        $("input[name='password']").focus().val('admin');
        $('#login-form').submit();
    });

    $('.staff-btn').on('click', function(){
        $("input[name='name']").focus().val('staff');
        $("input[name='password']").focus().val('staff');
        $('#login-form').submit();
    });

    $('.customer-btn').on('click', function(){
        $("input[name='name']").focus().val('james');
        $("input[name='password']").focus().val('james');
        $('#login-form').submit();
    });
  // ------------------------------------------------------- //
    // Material Inputs
    // ------------------------------------------------------ //

    var materialInputs = $('input.input-material');

    // activate labels for prefilled values
    materialInputs.filter(function() { return $(this).val() !== ""; }).siblings('.label-material').addClass('active');

    // move label on focus
    materialInputs.on('focus', function () {
        $(this).siblings('.label-material').addClass('active');
    });

    // remove/keep label on blur
    materialInputs.on('blur', function () {
        $(this).siblings('.label-material').removeClass('active');

        if ($(this).val() !== '') {
            $(this).siblings('.label-material').addClass('active');
        } else {
            $(this).siblings('.label-material').removeClass('active');
        }
    });
</script>

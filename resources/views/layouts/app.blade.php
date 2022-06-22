<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') | @endif {{ 'TakeoffLite' }}</title>
    <meta name="description" content="">
    <meta name="author" content="Dee-hony">

    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">
    <link rel="icon" type="image/png" href="{{asset('favicon.png')}}">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css">

    <script src="{{asset('js/jquery-3.5.1.js')}}"></script>
    <script src="{{asset('js/popper1.16.0.min.js')}}"></script>
    <script src="{{asset('js/bootstrap4.5.2.min.js')}}"></script>

    {{-- updated jquery ui link --}}
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="{{asset('js/jquery-ui1.12.1.min.js')}}"></script>

    <!-- form validation -->
    <script src="{{asset('js/jquery.validate.1.19.2.min.js')}}"></script>

    <!-- loading overlay -->
    <script src="{{asset('js/loadingoverlay.min.js')}}"></script>

    <link rel="stylesheet" href="{{asset('/css/preloader.css')}}"/>
    <script src="{{asset('js/jquery.preloader.js')}}"></script>

    <!-- sweet alert notification -->
    <script src="{{asset('js/sweetalert.2.1.2.min.js')}}"></script>

    <!-- global css -->
    <link rel="stylesheet" href="{{ asset('css/styles.css?v=0.2') }}"/>
    <link rel="stylesheet" href="{{ asset('css/layout.css?v=0.23') }}"/>
    <link rel="stylesheet" href="{{ asset('css/tkl_icon.css?v=0.2') }}"/>

    {{-- draw2d js CDN --}}
    <script src="{{asset('js/TKL_draw2d.js')}}"></script>
    {{--<script src="{{asset('js/draw2d.1.0.38.min.js')}}"></script>--}}

    {{-- toastr notification --}}
    <link rel="stylesheet" type="text/css"
          href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true
        };
    </script>

    <!-- Global site tag (gtag.js) - Google Ads: 1071000134 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-1071000134"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-1071000134');
    </script>

    {{--    email collection script--}}
    <script type="text/javascript">
        !function () {
            var geq = window.geq = window.geq || [];
            if (geq.initialize) return;
            if (geq.invoked) {
                if (window.console && console.error) {
                    console.error("GE snippet included twice.");
                }
                return;
            }
            geq.invoked = true;
            geq.methods = ["page", "suppress", "trackOrder", "identify", "addToCart"];
            geq.factory = function (method) {
                return function () {
                    var args = Array.prototype.slice.call(arguments);
                    args.unshift(method);
                    geq.push(args);
                    return geq;
                };
            };
            for (var i = 0; i < geq.methods.length; i++) {
                var key = geq.methods[i];
                geq[key] = geq.factory(key);
            }
            geq.load = function (key) {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.async = true;
                if (location.href.includes("vge=true")) {
                    script.src = "https://s3-us-west-2.amazonaws.com/jsstore/a/" + key + "/ge.js?v=" + Math.random();
                } else {
                    script.src = "https://s3-us-west-2.amazonaws.com/jsstore/a/" + key + "/ge.js";
                }
                var first = document.getElementsByTagName("script")[0];
                first.parentNode.insertBefore(script, first);
            };
            geq.SNIPPET_VERSION = "1.5.1";
            geq.load("150H822");
        }();
    </script>
    <script>geq.page()</script>

    <!-- Start of LiveChat (www.livechatinc.com) code -->
    <script>
        window.__lc = window.__lc || {};
        window.__lc.license = 12939171;
        ;(function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
    </script>
    <noscript><a href="https://www.livechatinc.com/chat-with/12939171/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechatinc.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>
    <!-- End of LiveChat code -->

    {{-- guided tour--}}
    <script src="//app.helphero.co/embed/9Ef5n0IAEA"></script>
    <script>
        HelpHero.anonymous();
    </script>
    {{-- end guided tour --}}

    <!-- custom css -->
    @yield('custom_css')
</head>

<body>
<div>
    <div class="someBlock"></div>
    @include('partials.header')

    @include('partials.nav')

    @yield('content')

    @include('partials.footer')

    @yield('script')
</div>

</body>

</html>
@inject('TemplateHelper', 'Lambda\Template\Helper\TemplateHelper')
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{!! csrf_token() !!}">
    <meta name="ws_url" content="{{ env('WS_URL') }}">

    <title>{{ $title ? $title : 'Paper template' }}</title>
    <link href="{{$TemplateHelper->favicon}}" rel="icon"/>
    <link rel="stylesheet" href="/assets/lambda/fonts/roboto/roboto.css?family=Roboto:400,300,100,100italic,300italic,400italic,700,700italic">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/vendor.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/paper.css') }}">
    {{-- paper.js is a static (non-lazy) entry, so its Vue components' own <style> blocks (e.g.
    UserControl.vue's `.avatar img { max-height: 32px }`) land in this separate components/paper.css
    bundle instead of getting Vite's automatic async-chunk CSS injection — without this link, avatar
    images and other component-scoped styling render at unconstrained natural size. --}}
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/components/paper.css') }}">
    @yield('meta')
    @stack('styles')
</head>
<body>
<noscript>To run this application, JavaScript is required to be enabled.</noscript>
@yield('app')
<script type="module" src="{{ mix('assets/lambda/js/paper.js') }}"></script>
<script>
    window.app_logo = "{{$TemplateHelper->logo}}";
</script>
@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html>
<head>
	@include('includes.meta')
	<title>@yield('title')</title>
	<link rel="icon" type="image/png" sizes="32x32" href="{!! URL::asset('images/favicons/favicon-32x32.png') !!}"
          sizes="32x32">
  <link rel="icon" type="image/png" sizes="16x16" href="{!! URL::asset('images/favicons/favicon-16x16.png') !!}"
          sizes="32x32">
  @include('includes.styles')
</head>
<body>
	@include('includes.header')
	@yield('content')
	@include('includes.footer')
	@include('includes.scripts')
</body>
</html>
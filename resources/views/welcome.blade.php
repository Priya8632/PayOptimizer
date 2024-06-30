@extends('shopify-app::layouts.default')
<link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@6.0.1/dist/styles.css" />
@section('style')
@endsection

@section('content')
    <!-- You are: (shop domain name) -->
    {{-- <p>You are: {{ $shopDomain ?? Auth::user()->name }}</p> --}}
    <script type="text/javascript">
        const userString = '<?php echo Auth::user(); ?>';
        const hostName = "<?php echo app('request')->input('host'); ?>";

        window.auth = JSON.parse(userString);
        window.host_name = hostName; //host name used in activate plan
    </script>
    <div id="app" data-shop="{{ Auth::user()->name }}"></div>
@endsection

@section('scripts')
    @parent
    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        // actions.TitleBar.create(app, {
        //     title: 'Welcome'
        // });
        const settingsLink = actions.AppLink.create(app, {
            label: 'Settings',
            destination: '/settings',
        })
        const supportLink = actions.AppLink.create(app, {
            label: 'Support',
            destination: '/support',
        })

        const navigationMenu = actions.NavigationMenu.create(app, {
            items: [supportLink,settingsLink],
        })
    </script>
@endsection

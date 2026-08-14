@extends('template::paper', ['title' => 'Тэнцвэртэй эцэг эх - CMS'])

@push('styles')
    <link rel="stylesheet" href="/assets/lambda/fonts/flaticons/flaticons.css">
    <link rel="stylesheet" href="/assets/lambda/fonts/themify/themify-icons.css">
    <link rel="stylesheet" href="/assets/lambda/fonts/iconsax/style.css">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/moqup.css') }}">
    {{--    <link rel="stylesheet" href="{{ mix('assets/lambda/css/report.css') }}">--}}
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/dataform.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/datagrid.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/datasource.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/agent.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/krud.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/puzzle.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/lambda/css/krud.css') }}">
    <link rel="stylesheet" href="/vendor/ol/ol.css">
    <script src="/vendor/tinymce/tinymce.min.js"></script>
@endpush

@section('app')
    <div id="puzzle" class="app-wrapper"></div>
@endsection

@push('scripts')
    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyCZCNSSfKeatvY1-QpSc_ShPyWmk7lEx4M&sensor=false&language=mn"></script>
    <script type="text/javascript" src="/vendor/echart/echarts-en.js"></script>
    <script type="text/javascript" src="/vendor/ckeditor/ckeditor.js"></script>
    <script src="/vendor/ol/ol.js"></script>
    {{-- Old CoffeeScript-generated UMD bundle with a labeled-function-statement syntax error under
    ES module strict mode — can't be imported/bundled by Rollup like datasource.js's other deps, so
    it loads as a classic script and sets window.SQLParser itself. --}}
    <script src="/vendor/sql-parser/sql-parser.js"></script>
    {{-- jquery-extendext, jQuery-QueryBuilder, and its locale file all detect their environment via
    `typeof module === 'object' && module.exports` and otherwise fall back to `factory(root.jQuery)`
    where root is the UMD wrapper's own `this` — that's `window` in a classic script, but `undefined`
    in an ES module, and Rollup's CJS/ESM interop for these specific files doesn't reliably take the
    module.exports branch either (jquery-extendext also vendors its own separate nested copy of
    jquery, which the datasource.js bundle's own jquery import doesn't share — see vite.config.js).
    Loading this whole family as classic scripts, in dependency order, is what actually keeps a
    single shared window.jQuery instance flowing through jQuery -> extendext -> query-builder -> its
    locale file, matching how they were designed to be loaded before any bundler was in the picture. --}}
    <script src="/vendor/jquery-legacy/jquery.js"></script>
    <script src="/vendor/jquery-legacy/jquery-extendext.js"></script>
    <script src="/vendor/jquery-legacy/query-builder.js"></script>
    <script src="/vendor/jquery-legacy/queryBuilder.i18.mn.js"></script>
    <script>
        window.init = {
            user: {!! json_encode(auth()->user()) !!},
            dbSchema: {!! json_encode($dbSchema) !!},
            gridList: {!! json_encode($gridList) !!},
            user_fields:{!! json_encode($user_fields) !!},
            email_templates:{!! json_encode($email_templates) !!},
            data_form_custom_elements: {!! json_encode(isset(config('lambda')['data_form_custom_elements']) ? config('lambda')['data_form_custom_elements'] : []) !!},
            data_grid_custom_elements: {!! json_encode(isset(config('lambda')['data_grid_custom_elements']) ? config('lambda')['data_grid_custom_elements'] : []) !!}
        };
        window.lambda = {!! json_encode(config('lambda')) !!};
    </script>

    <script type="module" src="{{ mix('assets/lambda/js/moqup.js') }}"></script>
    <script type="module" src="{{ mix('assets/lambda/js/dataform.js') }}"></script>
    <script type="module" src="{{ mix('assets/lambda/js/dataform-builder.js') }}"></script>

    <script type="module" src="{{ mix('assets/lambda/js/datagrid.js') }}"></script>
    <script type="module" src="{{ mix('assets/lambda/js/datagrid-builder.js') }}"></script>

    {{--    <script src="{{ mix('assets/lambda/js/report.js') }}"></script>--}}
    {{--    <script src="{{ mix('assets/lambda/js/report-builder.js') }}"></script>--}}

    <script type="module" src="{{ mix('assets/lambda/js/datasource.js') }}"></script>
    <script type="module" src="{{ mix('assets/lambda/js/krud.js') }}"></script>
    <script type="module" src="{{ mix('assets/lambda/js/agent.js') }}"></script>
{{--    <script src="{{ mix('assets/lambda/js/notification.js') }}"></script>--}}
    <script type="module" src="{{ mix('assets/lambda/js/puzzle.js') }}"></script>
@endpush

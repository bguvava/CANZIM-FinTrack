@extends('layouts.app')

@section('title', 'View Project')

@section('content')
    <div id="view-project-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import ViewProject from '../../js/pages/Projects/ViewProject.vue';

        const pinia = createPinia();
        const app = createApp(ViewProject);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#view-project-app');
    </script>
@endpush

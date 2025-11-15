@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
    <div id="add-project-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import AddProject from '../../js/pages/Projects/AddProject.vue';

        const pinia = createPinia();
        const app = createApp(AddProject);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#add-project-app');
    </script>
@endpush

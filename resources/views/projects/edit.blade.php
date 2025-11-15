@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <div id="edit-project-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import EditProject from '../../js/pages/Projects/EditProject.vue';

        const pinia = createPinia();
        const app = createApp(EditProject);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#edit-project-app');
    </script>
@endpush

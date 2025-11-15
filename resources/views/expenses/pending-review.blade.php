@extends('layouts.app')

@section('title', 'Pending Review')

@section('content')
    <div id="pending-review-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import PendingReview from '../../js/pages/Expenses/PendingReview.vue';

        const pinia = createPinia();
        const app = createApp(PendingReview);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#pending-review-app');
    </script>
@endpush

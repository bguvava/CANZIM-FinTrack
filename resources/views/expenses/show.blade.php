@extends('layouts.app')

@section('title', 'View Expense')

@section('content')
    <div id="view-expense-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import ViewExpense from '../../js/pages/Expenses/ViewExpense.vue';

        const pinia = createPinia();
        const app = createApp(ViewExpense);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#view-expense-app');
    </script>
@endpush

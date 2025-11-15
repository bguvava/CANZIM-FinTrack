@extends('layouts.app')

@section('title', 'Create Expense')

@section('content')
    <div id="create-expense-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import CreateExpense from '../../js/pages/Expenses/CreateExpense.vue';

        const pinia = createPinia();
        const app = createApp(CreateExpense);

        app.use(pinia);
        app.config.globalProperties.$swal = window.$swal;
        app.config.globalProperties.$toast = window.$toast;

        app.mount('#create-expense-app');
    </script>
@endpush

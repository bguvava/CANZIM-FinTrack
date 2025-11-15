@extends('layouts.app')

@section('title', 'Purchase Orders - Receiving')

@section('content')
    <div id="receiving-app"></div>
@endsection

@push('scripts')
    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';

        const pinia = createPinia();
        const app = createApp({
            template: `
                <div class="min-h-screen bg-gray-50">
                    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                        <div class="px-4 py-6 sm:px-0">
                            <div class="bg-white shadow-sm rounded-lg p-6">
                                <h1 class="text-3xl font-bold text-gray-900 mb-4">Item Receiving</h1>
                                <p class="text-gray-600">Item receiving interface coming soon...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `
        });

        app.use(pinia);
        app.mount('#receiving-app');
    </script>
@endpush

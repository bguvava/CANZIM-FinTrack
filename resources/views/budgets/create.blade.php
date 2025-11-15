<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Budget - FinTrack</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div id="app">
        <create-budget></create-budget>
    </div>

    <script type="module">
        import {
            createApp
        } from 'vue';
        import {
            createPinia
        } from 'pinia';
        import CreateBudget from '@/pages/Budgets/CreateBudget.vue';

        const pinia = createPinia();
        const app = createApp({
            components: {
                CreateBudget
            }
        });

        app.use(pinia);
        app.mount('#app');
    </script>
</body>

</html>

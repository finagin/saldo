<!doctype html>
<html lang="ru">
<head>
    <title>{{ trans($title ?? 'Saldo') }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    @stack('tailwindcss')
</head>
<body class="m-5 bg-white dark:bg-gray-900">
<div id="app">
    {{ $slot }}
</div>
<div id="scripts" class="hidden">
    @stack('scripts')
</div>
</body>
</html>

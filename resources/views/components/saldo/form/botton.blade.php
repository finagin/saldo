<button type="{{ $type ?? 'button' }}" {{ $attributes->merge(['class' => 'btn']) }}>
    {{ $slot }}
</button>

@push('tailwindcss')
    <style type="text/tailwindcss">
        @layer components {
            .btn {
                @apply w-full border focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center;
            }
            .btn.primary {
                @apply text-blue-700 border-blue-700 hover:bg-blue-800 focus:ring-blue-300 dark:border-blue-500 dark:text-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800 hover:text-white dark:hover:text-white;
            }
            .btn.disable {
                @apply text-gray-900 border-gray-800 hover:bg-gray-900 focus:ring-gray-300 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-600 dark:focus:ring-gray-800 hover:text-white dark:hover:text-white;
            }
        }
    </style>
@endpush

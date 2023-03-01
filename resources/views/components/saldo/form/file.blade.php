@php
    $id = \Illuminate\Support\Str::random();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center w-full']) }}>
    <label for="dropzone-file-{{ $id }}"
           class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
        <div id="dropzone-file-placeholder-{{ $id }}"
             class="text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center pt-5 pb-6">
            <svg aria-hidden="true" class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="mb-2 text-sm">
                <span class="font-semibold">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs">XLS, XLSX or CSV</p>
        </div>
        <div id="dropzone-file-name-{{ $id }}" class="hidden flex flex-col items-center justify-center pt-5 pb-6">
            <p class="m-10 text-sm text-gray-500 dark:text-gray-400"></p>
        </div>
        <input id="dropzone-file-{{ $id }}" type="file" class="hidden" name="{{$name}}" />
    </label>
</div>
@push('scripts')
    <script>
        (() => {
            const id = '{{ $id }}';
            const dropzone = document.getElementById('dropzone-file-' + id);
            const placeholder = document.getElementById('dropzone-file-placeholder-' + id);
            const name = document.getElementById('dropzone-file-name-' + id);

            const setVisible = (show, hide) => {
                show.classList.remove('hidden');
                hide.classList.add('hidden');
            };

            const setError = (hasError) => {
                const colors = {
                    true: ['text-red-500', 'dark:text-red-400'],
                    false: ['text-gray-500', 'dark:text-gray-400']
                };

                placeholder.classList.add(...colors[hasError]);
                placeholder.classList.remove(...colors[!hasError]);
            };

            dropzone.addEventListener("change", (event) => {
                if (/\.(xlsx?|csv)$/.test(event.target.files[0].name)) {
                    setVisible(name, placeholder);
                    name.querySelector('p').innerText = event.target.files[0].name;
                } else {
                    setError(true);
                    setVisible(placeholder, name);
                }
            });
        })();
    </script>
@endpush

@php
    if ($errors->isNotEmpty()) {
        dd($errors);
    }

    $filename = 'dropzone-file[]';
@endphp

<x-layout>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <form id="saldo-create" class="flex flex-col items-center justify-between" action="{{ route('saldo.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="w-full flex flex-row items-center justify-between p-10">
                <x-saldo.form.file :name="$filename" class="mr-10 last:mr-0"/>
                <x-saldo.form.file :name="$filename" class="mr-10 last:mr-0"/>
            </div>
            <div class="mb-5">
                @foreach(\App\Support\Enum\Saldo\CompareType::cases() as $compareType)
                    <div class="flex items-center">
                        <input id="type-{{ $compareType }}" name="compare_type[]" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $compareType }}">
                        <label for="type-{{ $compareType }}" class="ml-2 block text-sm text-gray-900">Проверка {{ $compareType->label() }}</label>
                    </div>
                @endforeach
            </div>
            <div class="w-full flex flex-row items-center justify-between w-full px-10 pb-10">
                <x-saldo.form.botton type="submit" class="primary">
                    {{ __('Upload') }}
                </x-saldo.form.botton>
            </div>
            @push('scripts')
                <script>
                    (() => {
                        const form = document.getElementById('saldo-create');

                        form.addEventListener("submit", (event) => {
                            event.preventDefault();
                            const formData = new FormData(form);
                            const files = formData.getAll('{{ $filename }}');

                            const hasError = files.length !== 2
                                || files.some((file) => !/\.(xlsx?|csv)$/.test(file.name));

                            if (hasError) {
                                alert('Please upload a valid files');
                                return;
                            }

                            form.submit();
                        });
                    })();
                </script>
            @endpush
        </form>
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Первая таблица
                </th>
                <th scope="col" class="px-6 py-3">
                    Вторя таблица
                </th>
                <th scope="col" class="px-6 py-3">
                    Тип проверки
                </th>
                <th scope="col" class="px-6 py-3">
                    Статус
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($saldos as $saldo)
                <x-saldo.task :$saldo />
            @endforeach
            </tbody>
        </table>
    </div>
</x-layout>

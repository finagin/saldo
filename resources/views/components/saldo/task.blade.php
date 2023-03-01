<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
    @foreach($saldo->files as $file)
        <td class="px-6 py-4">
            @if($saldo->status === \App\Support\Enum\Saldo\Status::COMPLETED)
                <a href="{{ route('saldo.download', $file->id) }}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-500">
                    {{ $file->name }}
                </a>
            @else
                {{ $file->name }}
            @endif
        </td>

    @endforeach
        <td class="px-6 py-4">
            Проверка {{ \App\Support\Enum\Saldo\CompareType::castString($saldo->compare_type, 'сумм') }}
        </td>
    <td class="px-6 py-4">
        {{ $saldo->status->label() }}
    </td>
</tr>

@php
    $files = $getRecord()->bai_giang_path ?? [];
@endphp

@if (!empty($files))
    <ul>
        @foreach ($files as $file)
            <li>
                <a href="{{ Storage::url($file) }}" target="_blank" class="text-primary-600 hover:underline">
                    📄 {{ basename($file) }}
                </a>
            </li>
        @endforeach
    </ul>
@else
    <span class="text-gray-400">Không có tài liệu</span>
@endif

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $disk = config('filesystems.default');
    $files = $getState() ?? [];
    if (!is_array($files)) {
        $files = [$files];
    }
@endphp

@if (empty($files))
    <span class="text-gray-500">Không có</span>
@else
    <ul class="space-y-1">
        @foreach ($files as $file)
            @php
                $label = basename($file);
                $url = '';

                if (is_string($file) && Str::startsWith($file, ['http://', 'https://'])) {
                    // Link tuyệt đối đã OK
                    $url = $file;
                } elseif (is_string($file)) {
                    // Luôn build ra URL tuyệt đối: /storage/bai-giang/...
                    $url = Storage::url($file);
                }

                // Icon theo loại file
                $ext = strtolower(pathinfo($label, PATHINFO_EXTENSION));
                $icon = '📄';
                if ($ext === 'pdf') $icon = '📕';
                elseif (in_array($ext, ['doc','docx'])) $icon = '📝';
                elseif (in_array($ext, ['jpg','jpeg','png','gif'])) $icon = '🖼️';
            @endphp

            @if (!empty($url))
                <li>
                    <a href="{{ $url }}" target="_blank" class="text-blue-600 hover:underline flex items-center space-x-1">
                        <span>{{ $icon }}</span>
                        <span>{{ $label }}</span>
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif

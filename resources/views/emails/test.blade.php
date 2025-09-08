{{-- resources/views/emails/test.blade.php --}}

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Email Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        h1 {
            color: #2c3e50;
            font-size: 20px;
        }
        p {
            line-height: 1.6;
            font-size: 15px;
        }
        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Xin chào, {{ $name ?? 'Người dùng' }}!</h1>
        <p>
            Đây là email test được gửi từ hệ thống <strong>Laravel</strong>.
        </p>
        <p>
            Nội dung test: <em>{{ $content ?? 'Email test mặc định' }}</em>
        </p>
        <div class="footer">
            <p>Trường Cao đẳng THACO<br>
            Hệ thống eLearning & Quản lý nội bộ</p>
        </div>
    </div>
</body>
</html>

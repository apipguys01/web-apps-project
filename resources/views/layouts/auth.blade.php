<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — UMKM Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        :root{--bg:#080A0F;--card:#181D27;--border:#232938;--border2:#2D3548;--text:#E8EDF5;--muted:#5A6480;--muted2:#8892AA;--green:#00D68F;--green-bg:rgba(0,214,143,0.08)}
        body{background:var(--bg);color:var(--text);font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}
        .bg-orb{position:fixed;border-radius:50%;pointer-events:none}
        .form-input{width:100%;background:#0E1118;border:1px solid var(--border2);border-radius:10px;padding:11px 14px;color:var(--text);font-size:0.88rem;font-family:inherit;transition:border-color 0.2s}
        .form-input:focus{outline:none;border-color:var(--green)}
        .form-input::placeholder{color:var(--muted)}
        .form-label{font-size:0.72rem;font-weight:600;color:var(--muted2);margin-bottom:6px;display:block;letter-spacing:0.05em;text-transform:uppercase}
        .btn-primary{width:100%;background:var(--green);color:#000;border:none;border-radius:10px;padding:12px;font-size:0.9rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.2s;letter-spacing:0.02em}
        .btn-primary:hover{background:#00f0a0;transform:translateY(-1px);box-shadow:0 8px 24px rgba(0,214,143,0.3)}
        .error-msg{background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.2);color:#FF4D6A;border-radius:8px;padding:10px 14px;font-size:0.78rem;margin-bottom:14px}
        .success-msg{background:var(--green-bg);border:1px solid rgba(0,214,143,0.2);color:var(--green);border-radius:8px;padding:10px 14px;font-size:0.78rem;margin-bottom:14px}
    </style>
    @stack('styles')
</head>
<body>
    <div class="bg-orb" style="width:600px;height:600px;top:-200px;left:-100px;background:radial-gradient(circle,rgba(0,214,143,0.05) 0%,transparent 70%)"></div>
    <div class="bg-orb" style="width:400px;height:400px;bottom:-100px;right:-50px;background:radial-gradient(circle,rgba(77,159,255,0.04) 0%,transparent 70%)"></div>

    @yield('content')

    @stack('scripts')
</body>
</html>

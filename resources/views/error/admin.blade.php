@extends('layouts.app_page')

@section('title', 'خطأ في البيانات الاساسيه للنظام')

@section('content')
    <style>
:root {
  --bg-color: #0e0e11;
  --card-bg: #16161a;
  --accent: #ffb84d;
  --accent-light: #ffe6a3;
  --text-main: #f5f5f5;
  --text-dim: #bfbfbf;
  --border-light: rgba(255,255,255,0.08);
}

/* الخلفية */
body {
  background: var(--bg-color);
  font-family: 'Cairo', sans-serif;
  margin: 0;
  padding: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  color: var(--text-main);
}

/* صندوق الخطأ */
.error-box {
  background: var(--card-bg);
  padding: 40px;
  border-radius: 18px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
  text-align: center;
  max-width: 600px;
  width: 100%;
  border: 1px solid var(--border-light);
  animation: pop 0.8s ease-out;
}

/* حركة الظهور */
@keyframes pop {
  0% { transform: scale(0.85); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

/* الأيقونة */
.error-icon {
  font-size: 80px;
  color: var(--accent);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.75; }
  100% { transform: scale(1); opacity: 1; }
}

/* الرسالة الرئيسية */
.error-message {
  margin-top: 20px;
  font-size: 22px;
  font-weight: 800;
  color: var(--accent-light);
}

/* ملاحظات عامة */
.system-note {
  margin-top: 15px;
  font-size: 16px;
  color: var(--text-dim);
}

/* ملاحظات المطور */
.dev-note {
  margin-top: 25px;
  padding: 15px;
  background: rgba(255, 184, 77, 0.1);
  color: var(--accent-light);
  border-radius: 10px;
  font-size: 14px;
  direction: ltr;
  text-align: left;
  border: 1px solid rgba(255, 184, 77, 0.3);
}

    </style>

    <div class="error-box">
        <div class="error-icon">⚠️</div>
        <div class="error-message">خطأ في البيانات الاساسيه</div>
        <div class="system-note">برجاء الرجوع الى فريق الصيانة: <b>01094619040</b></div>

        @if(!empty($error))
            <div class="dev-note">
                {{ $error }}
            </div>
        @endif
    </div>
@endsection

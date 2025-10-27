{{-- resources/views/auth/login-hive.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>تسجيل الدخول | Hive</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ====== Hive Theme Colors ====== */
        :root {
            --hive-yellow: #F3C94C;
            /* honey yellow */
            --hive-yellow-soft: #E0B93D;
            --hive-gray: #2C2C2C;
            --hive-light-gray: #bfbfbf;
            --card-bg: rgba(255, 255, 255, 0.03);
            --glass: rgba(255, 255, 255, 0.05);
            --accent: linear-gradient(90deg, var(--hive-yellow), var(--hive-yellow-soft));
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: radial-gradient(1200px 600px at 10% 10%, rgba(243, 201, 76, 0.05), transparent 10%),
                radial-gradient(1000px 500px at 90% 90%, rgba(243, 201, 76, 0.03), transparent 8%),
                #0b0b0c;
            /* deep black background */
            font-family: "Inter", system-ui, sans-serif;
            color: #e9edf0;
            -webkit-font-smoothing: antialiased;
        }

        /* container */
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }

        /* card */
        .card {
            width: 100%;
            max-width: 1100px;
            border-radius: 18px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 520px 1fr;
            background: var(--card-bg);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(6px);
        }

        @media (max-width: 880px) {
            .card {
                grid-template-columns: 1fr;
            }

            .left-hero {
                order: 2
            }

            .form-area {
                order: 1;
                padding: 2rem
            }
        }

        /* left hero */
        .left-hero {
            padding: 48px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-wrap img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);
            animation: buzz 5s ease-in-out infinite;
        }

        @keyframes buzz {

            0%,
            100% {
                transform: rotate(0deg)
            }

            50% {
                transform: rotate(1.5deg)
            }
        }

        .hero-title {
            font-weight: 800;
            font-size: 36px;
            background: var(--accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .hero-sub {
            color: rgba(233, 237, 240, 0.7);
            font-size: 14px;
            max-width: 380px;
        }

        /* honeycomb dots */
        .dots {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .dot {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(243, 201, 76, 0.18);
            box-shadow: 0 0 12px rgba(243, 201, 76, 0.1);
            animation: floatDot 7s ease-in-out infinite;
        }

        @keyframes floatDot {
            0% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-10px)
            }

            100% {
                transform: translateY(0)
            }
        }

        /* form area */
        .form-area {
            padding: 42px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            justify-content: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.015), rgba(255, 255, 255, 0.02));
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.015);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 8px 30px rgba(2, 6, 23, 0.6);
        }

        label {
            font-size: 13px;
            color: rgba(233, 237, 240, 0.8);
            margin-bottom: 6px;
            display: block;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: #e9edf0;
            outline: none;
            transition: all .15s ease;
        }

        input:focus {
            border-color: rgba(243, 201, 76, 0.4);
            box-shadow: 0 0 12px rgba(243, 201, 76, 0.1);
        }

        /* button */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            background: var(--accent);
            color: #111;
            box-shadow: 0 8px 30px rgba(243, 201, 76, 0.15);
            transition: all .12s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 35px rgba(243, 201, 76, 0.25);
        }

        .link-muted {
            color: rgba(233, 237, 240, 0.6);
            font-size: 13px;
            text-decoration: none
        }

        .muted {
            color: rgba(233, 237, 240, 0.65);
            font-size: 13px
        }

        @media (max-width:480px) {
            .hero-title {
                font-size: 28px
            }

            .left-hero {
                padding: 22px
            }

            .form-area {
                padding: 20px
            }

            .logo-wrap img {
                width: 60px;
                height: 60px
            }
        }
        /* ==== Extra Hive Animations ==== */

/* floating logo like honeybee */
@keyframes logoFloat {
  0% { transform: translateY(0) rotate(-2deg); }
  50% { transform: translateY(-5px) rotate(2deg); }
  100% { transform: translateY(0) rotate(-2deg); }
}
.logo-wrap img {
  animation: logoFloat 6s ease-in-out infinite;
}

/* glossy shine over hero */
.shine {
  position: absolute;
  left: -30%;
  top: -20%;
  width: 160%;
  height: 200%;
  background: linear-gradient(120deg,
      rgba(255,255,255,0.04) 0%,
      rgba(255,255,255,0.06) 20%,
      rgba(255,255,255,0) 40%);
  transform: rotate(-12deg);
  animation: slowShift 10s linear infinite;
  pointer-events: none;
}
@keyframes slowShift {
  0% { transform: translateX(-30%) rotate(-12deg); }
  50% { transform: translateX(0%) rotate(-12deg); }
  100% { transform: translateX(30%) rotate(-12deg); }
}

/* shiny button effect */
.btn-primary::after {
  content: "";
  position: absolute;
  top: -40%;
  left: -10%;
  width: 120%;
  height: 40%;
  background: linear-gradient(90deg,
      rgba(255,255,255,0.28),
      rgba(255,255,255,0.06),
      rgba(255,255,255,0));
  transform: rotate(-20deg);
  animation: btnShine 3s linear infinite;
  pointer-events: none;
  opacity: 0.7;
}
@keyframes btnShine {
  0% { transform: translateX(-100%) rotate(-20deg); }
  100% { transform: translateX(200%) rotate(-20deg); }
}

/* livelier dots */
@keyframes floatDot {
  0% { transform: translateY(0) translateX(0) scale(1); }
  50% { transform: translateY(-14px) translateX(6px) scale(1.1); }
  100% { transform: translateY(0) translateX(0) scale(1); }
}

    </style>
</head>

<body>
    <div class="login-wrap">
        <div class="card">

            {{-- LEFT HERO --}}
            <div class="left-hero">
              <div class="shine" aria-hidden="true"></div>

                <div class="logo-wrap">
                    <img src="https://drive.google.com/uc?export=view&id=1U38G-E4eXY0h3JXFNYP7FbkrGpwU9p5D"
                        alt="Hive Logo">
                    <div>
                        <div class="hero-title">Hive</div>
                        <div class="hero-sub">بيئة رقمية ذكية بطابع عصري مستوحى من خلية النحل.</div>
                    </div>
                </div>

                <div style="height:16px"></div>

                <div class="glass-card muted">
                    <div style="font-weight:700; font-size:13px; color:rgba(243,201,76,0.9)">ألوان مستوحاة من العسل
                        والنحل</div>
                    <div style="font-size:12px; color:rgba(233,237,240,0.6)">أسود أنيق، رمادي ناعم ولمسات صفراء دافئة.
                    </div>
                </div>

                <div class="dots">
                    <div class="dot" style="left:15%; top:18%; animation-delay:-1s"></div>
                    <div class="dot" style="left:70%; top:40%; animation-delay:-2s"></div>
                    <div class="dot" style="left:25%; top:80%; animation-delay:-3s"></div>
                    <div class="dot" style="left:80%; top:70%; animation-delay:-4s"></div>
                </div>
            </div>

            {{-- FORM AREA --}}
            <div class="form-area">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 style="margin:0; font-size:20px; font-weight:700">تسجيل دخول Hive</h2>
                        <div class="muted">أدخل بياناتك للوصول إلى الخلية.</div>
                    </div>
                </div>

                <div class="glass-card">
                    {{-- Session Status --}}
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div style="margin-bottom:14px">
                            <label for="email">البريد الإلكتروني</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autofocus autocomplete="username">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div style="margin-bottom:8px">
                            <label for="password">كلمة المرور</label>
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div
                            style="display:flex; align-items:center; justify-content:space-between; margin-top:10px; margin-bottom:18px">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" name="remember"
                                    style="width:18px;height:18px;border-radius:6px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.04)">
                                <span
                                    style="margin-inline-start:8px; color:rgba(233,237,240,0.7); font-size:13px">تذكرني</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="link-muted" href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
                            @endif
                        </div>

                        <div style="display:flex; justify-content:flex-end;">
                            <button type="submit" class="btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14M12 5l7 7-7 7" stroke="#111" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                تسجيل الدخول
                            </button>
                        </div>
                    </form>
                </div>

                <div style="display:flex; justify-content:center; margin-top:12px">
                    <div class="muted">Hive — عالم منظم مثل خلية النحل 🐝</div>
                </div>
            </div>

        </div>
    </div>
    <script>
(function() {
  const left = document.querySelector('.left-hero');
  if (!left) return;
  function move(evt) {
    if (window.matchMedia('(pointer: coarse)').matches) return;
    const rect = left.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;
    const dx = (evt.clientX - cx) / rect.width;
    const dy = (evt.clientY - cy) / rect.height;
    left.style.transform = `translate3d(${dx * 8}px, ${dy * 8}px, 0)`;
  }
  window.addEventListener('mousemove', move);
})();
</script>

</body>

</html>

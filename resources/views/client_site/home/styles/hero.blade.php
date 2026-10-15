@push('styles')
    <style>
        .hero {
            position: relative;
            height: 100vh;
            width: 100%;
            background: var(--olive-dark);
            overflow: hidden;
        }

        /* الصورة الأساسية التي ستظهر بعد انتهاء الانميشن */
        .hero-image-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards 1.8s;
            /* تم تقليل التأخير ليناسب السرعة الجديدة */
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: transparent;
            /* أو امسحه السطر كله */
            z-index: 1;
        }


        .hero-overlay {
            position: relative;
            z-index: 2;
            height: 100%;
            width: 100%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            color: var(--cream);
        }

        /* Brand */
        .brand {
            position: absolute;
            top: 30px;
            left: 30px;
            font-size: 28px;
            font-family: 'Playfair Display', serif;
            color: var(--gold-soft);
            opacity: 0;
            animation: fadeIn .6s ease forwards 2s;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-block;
        }

        .brand:hover {
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(203, 191, 154, 0.5);
        }

        /* Navigation */
        .hero-nav {
            position: absolute;
            top: 35px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 25px;
            opacity: 0;
            animation: fadeIn .6s ease forwards 2.2s;
        }

        .hero-nav a {
            color: var(--cream);
            text-decoration: none;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.4s ease;
            display: inline-block;
        }

        .hero-nav a:hover {
            transform: scale(1.2) rotate(3deg);
            color: var(--gold-soft);
            text-shadow: 0 0 15px var(--gold-soft);
        }
.hero-icons {
    position: absolute;
    top: 35px;
    right: 30px;
    display: flex;
    gap: 25px; /* المسافة بين الأيقونات */
    opacity: 0;
    animation: fadeIn .6s ease forwards 2.4s; /* نفس الأنيميشن القديم */
}

.hero-icon img {
    width: 25px; /* حجم الأيقونات */
    height: 25px;
    cursor: pointer;
    transition: transform 0.3s, filter 0.3s;

    /* لون أبيض باهت */
    filter: brightness(0) invert(1) opacity(0.7); /* أبيض باهت */
}

.hero-icon:hover img {
    transform: scale(1.2);
    filter: brightness(1) invert(1) opacity(1); /* أبيض كامل عند hover */
}



      

        /* Hero Title */
        .hero-title {
            position: absolute;
            bottom: 10%;
            left: 30px;
            max-width: 80%;
            font-family: 'Playfair Display', serif;
            font-size: clamp(32px, 8vw, 66px);
            line-height: 1.1;
            opacity: 0;
            animation: fadeUp .8s ease forwards 2.6s;
        }

        .hero-title span {
            color: var(--bg-soft);
            text-shadow: 2px 4px 10px rgba(43, 46, 38, 0.5);
        }

        /* --- Polygon Animation Logic --- */
        .polygon-reveal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            /* تختفي الحاوية بعد 2 ثانية لتسمح بالتفاعل */
            animation: fadeOut 0.2s forwards 2s;
        }

        .polygon-reveal img {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            transform: scale(0.1) rotate(-15deg);
            opacity: 0;
            /* إجمالي وقت الأنميشن 2 ثانية ليكون أسرع وأكثر حيوية */
            animation: complexReveal 2s cubic-bezier(.22, 1, .36, 1) forwards;
        }

        @keyframes complexReveal {
            0% {
                opacity: 0;
                transform: scale(0.1) rotate(-20deg);
                clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            }

            /* صدمة البداية أصبحت عند 7.5% بدلاً من 15% (أسرع بالضعف) */
            7.5% {
                opacity: 1;
                transform: scale(0.4) rotate(5deg);
                clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
            }

            30% {
                transform: scale(0.35) rotate(0deg);
                clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
            }

            100% {
                transform: scale(1) rotate(0deg);
                /* التمدد الكامل لمقاس الشاشة */
                clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 100%);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .hero-nav {
                display: none;
            }

            .brand {
                left: 20px;
                top: 20px;
                font-size: 24px;
            }

            .hero-btn {
                right: 20px;
                top: 18px;
                padding: 8px 18px;
                font-size: 12px;
            }

            .hero-title {
                bottom: 15%;
                left: 20px;
                width: 95%;
            }
        }

        .nav-btn {
            position: absolute;
            top: 40%;
            transform: translateY(-50%);
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

      /* --- تنسيق اللوجو المحدث --- */
        #scroll-logo {
            position: fixed;
            top: 15px; /* تقليل المسافة من الأعلى قليلاً للموبايل */
            left: 30px; /* مسافة ثابتة من اليسار لضمان عدم خروجه */
            width: 50px; /* حجم أصغر قليلاً للموبايل */
            height: auto;
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 100000;
            cursor: pointer;
            pointer-events: none; /* لمنع التفاعل معه وهو مخفي */
            transform: translateY(-10px); /* يبدأ من أعلى قليلاً */
        }

        #scroll-logo.show {
            opacity: 1;
            transform: translateY(0); /* يظهر في مكانه الطبيعي */
            pointer-events: auto;
        }

        #scroll-logo.hide {
            opacity: 0;
            transform: translateY(-20px);
            pointer-events: none;
        }

        /* تعديلات للديسكتوب والشاشات الكبيرة */
        @media (min-width: 769px) {
            #scroll-logo {
                top: 20px;
                left: 40px; /* مسافة أكبر قليلاً في الشاشات الكبيرة */
                width: 60px;
            }
        }
    </style>
@endpush

@push('styles')
<style>
    /* 🎨 Light Gray + Gold Theme */

    :root {
        --bg-soft: #a5a5a5;          /* خلفية فاتحة */
        --card-bg: #ffffff;           /* خلفية الكارد */
        --text-main: #2B2E26;         /* لون الكتابة */
        --cream: #E6E1D5;             /* لون ثانوي */
        --gold-soft: #CBBF9A;         /* ذهبي للعناوين والـ badges */
        --border-light: rgba(203, 191, 154, 0.2);
    }

    body {
        background: var(--bg-soft);
        color: var(--text-main);
        font-family: "Cairo", sans-serif;
        direction: rtl;
    }

    /* ====== Container & Card ====== */
    .about-us-section {
        position: relative;
        padding: 100px 0;
    }

    .about-content-card {
        padding: 40px;
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border-light);
        box-shadow: 0 0 25px rgba(0,0,0,0.1);
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }

    .about-us-section.visible .about-content-card {
        opacity: 1;
        transform: translateY(0);
    }

    .about-content-card h2 {
        font-size: clamp(28px, 4vw, 42px);
        font-weight: 800;
        margin: 15px 0;
        color: var(--text-main);
    }

    .about-content-card h2 span {
        color: var(--gold-soft);
    }

    .sub-title {
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 14px;
        color: var(--gold-soft);
        font-weight: 600;
    }

    .about-features {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        margin-top: 25px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: var(--text-main);
    }

    .feature-item i {
        color: var(--gold-soft);
        font-size: 1.3rem;
    }

    .about-visual {
        position: relative;
        height: 400px;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transform: scale(0.95);
        transition: all 0.8s ease-out 0.3s;
    }

    .about-us-section.visible .about-visual {
        opacity: 1;
        transform: scale(1);
    }

    .abstract-shape {
        width: 300px;
        height: 300px;
        background: rgba(203, 191, 154, 0.05);
        border: 2px dashed rgba(203, 191, 154, 0.2);
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        animation: morphing 10s infinite alternate;
    }

    .floating-badge {
        position: absolute;
        top: 15%;
        right: 10%;
        background: var(--gold-soft);
        color: var(--card-bg);
        padding: 20px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        animation: floating 3s ease-in-out infinite;
        z-index: 2;
        border: 2px solid var(--gold-soft);
    }

    .floating-badge .number {
        font-weight: 800;
        font-size: 20px;
    }

    /* Social links */
    .social-connect {
        margin-top: 35px;
        padding-top: 20px;
        border-top: 1px solid var(--border-light);
    }

    .connect-text {
        font-size: 14px;
        color: var(--gold-soft);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }

    .social-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        border-radius: 50px;
        background: rgba(203, 191, 154, 0.08);
        border: 1px solid var(--border-light);
        color: var(--text-main);
        text-decoration: none !important;
        transition: all 0.3s ease;
    }

    .social-btn i {
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .social-btn:hover {
        transform: translateY(-3px);
        color: var(--card-bg);
        background: var(--gold-soft);
    }

    .social-btn:hover i {
        transform: rotate(15deg) scale(1.2);
    }

    /* Media Queries */
    @media (max-width: 480px) {
        .about-features {
            flex-direction: column;
        }
        .social-links {
            flex-direction: column;
        }
        .social-btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animations */
    @keyframes morphing {
        0% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
        100% { border-radius: 50% 50% 20% 80% / 25% 80% 20% 75%; }
    }

    @keyframes floating {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }
</style>
@endpush

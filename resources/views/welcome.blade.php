<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareLink — نظام إدارة الرعاية الصحية | Backend System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0f766e;
            --primary-dark: #0b5a54;
            --primary-light: #14b8a6;
            --accent: #f0fdfa;
            --dark: #0f172a;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --white: #ffffff;
            --success: #22c55e;
            --shadow: 0 10px 30px rgba(15, 118, 110, 0.08);
        }

        body {
            font-family: 'Segoe UI', 'Tahoma', Arial, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--dark);
            line-height: 1.7;
        }

        /* ===== Header ===== */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        .logo-text span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 28px;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-badge {
            background: var(--light-gray);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
        }

        /* ===== Hero ===== */
        .hero {
            max-width: 1200px;
            margin: 0 auto;
            padding: 70px 24px 50px;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: var(--primary-dark);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid #99f6e4;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .hero h1 {
            font-size: 42px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 18px;
            line-height: 1.3;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 17px;
            color: var(--gray);
            max-width: 640px;
            margin: 0 auto 34px;
        }

        .hero-buttons {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 13px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.25);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--dark);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ===== Stats ===== */
        .stats {
            max-width: 1000px;
            margin: 50px auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 0 24px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px 16px;
            text-align: center;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray);
            font-weight: 500;
        }

        /* ===== Features ===== */
        .section-title {
            text-align: center;
            margin: 70px auto 40px;
            max-width: 600px;
            padding: 0 24px;
        }

        .section-title h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .section-title p {
            color: var(--gray);
            font-size: 15px;
        }

        .features {
            max-width: 1200px;
            margin: 0 auto 70px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            transition: all 0.25s;
        }

        .feature-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-4px);
            border-color: var(--primary-light);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .feature-card h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .feature-card p {
            font-size: 14px;
            color: var(--gray);
        }

        /* ===== System status card ===== */
        .status-section {
            max-width: 1200px;
            margin: 0 auto 70px;
            padding: 0 24px;
        }

        .status-card {
            background: linear-gradient(135deg, var(--dark), #1e293b);
            border-radius: 20px;
            padding: 36px;
            color: white;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 30px;
            align-items: center;
        }

        .status-card h3 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        .status-card p {
            color: #cbd5e1;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tech-tag {
            background: rgba(255,255,255,0.1);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .status-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,255,255,0.06);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
        }

        .status-ok {
            color: #4ade80;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ===== Footer ===== */
        footer {
            border-top: 1px solid var(--border);
            padding: 30px 24px;
            text-align: center;
            color: var(--gray);
            font-size: 13px;
        }

        footer strong {
            color: var(--dark);
        }

        /* ===== Responsive ===== */
        @media (max-width: 860px) {
            .nav-links { display: none; }
            .hero h1 { font-size: 30px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .features { grid-template-columns: 1fr; }
            .status-card { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo">
                <div class="logo-icon">C+</div>
                <div class="logo-text">Care<span>Link</span></div>
            </div>
            <ul class="nav-links">
                <li><a href="#features">المميزات</a></li>
                <li><a href="#status">حالة النظام</a></li>
                <li><a href="#about">عن المشروع</a></li>
            </ul>
            <div class="nav-badge">Backend v1.0</div>
        </div>
    </header>

    <section class="hero">
        <div class="hero-badge">
            <span class="dot"></span>
            النظام يعمل بشكل طبيعي — API Online
        </div>
        <h1>نظام <span>CareLink</span><br>لإدارة الرعاية الصحية</h1>
        <p>
            منصة خلفية (Backend) متكاملة لإدارة المرضى، المواعيد، السجلات الطبية، والتواصل بين الأطباء والمرضى،
            ضمن مشروع تخرج مبني على Laravel.
        </p>
        <div class="hero-buttons">
            <a href="/dashboard" class="btn btn-primary">لوحة التحكم</a>
            <a href="/api/documentation" class="btn btn-secondary">توثيق الـ API</a>
        </div>
    </section>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number">+12</div>
            <div class="stat-label">Endpoint API</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">4</div>
            <div class="stat-label">أدوار مستخدمين</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">99.9%</div>
            <div class="stat-label">استقرار النظام</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">Laravel</div>
            <div class="stat-label">إطار العمل</div>
        </div>
    </div>

    <div class="section-title" id="features">
        <h2>مميزات النظام</h2>
        <p>أهم الوحدات والخدمات التي يوفرها الـ Backend الخاص بمشروع CareLink</p>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">🩺</div>
            <h3>إدارة المرضى</h3>
            <p>تسجيل بيانات المرضى، السجلات الطبية، والتاريخ المرضي بشكل آمن ومنظم.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>إدارة المواعيد</h3>
            <p>حجز، تعديل، وإلغاء المواعيد بين المريض والطبيب مع إشعارات تلقائية.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👨‍⚕️</div>
            <h3>إدارة الأطباء والطاقم</h3>
            <p>ملفات تعريف للأطباء، التخصصات، وأوقات الدوام الخاصة بكل طبيب.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔐</div>
            <h3>المصادقة والصلاحيات</h3>
            <p>نظام تسجيل دخول آمن مع صلاحيات مختلفة (مريض، طبيب، إداري).</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💊</div>
            <h3>الوصفات الطبية</h3>
            <p>إصدار ومتابعة الوصفات الطبية إلكترونيًا وربطها بسجل المريض.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>تقارير وإحصائيات</h3>
            <p>لوحات تحكم تعرض إحصائيات حول المرضى، المواعيد، والأداء العام للنظام.</p>
        </div>
    </div>

    <div class="status-section" id="status">
        <div class="status-card">
            <div>
                <h3>عن المشروع</h3>
                <p id="about">
                    CareLink هو مشروع تخرج يهدف لبناء نظام خلفي (Backend) متكامل لإدارة الرعاية الصحية،
                    مبني باستخدام Laravel، بحيث يوفر واجهات برمجية (API) يمكن ربطها بأي تطبيق ويب أو موبايل
                    لإدارة المرضى والأطباء والمواعيد والسجلات الطبية.
                </p>
                <div class="tech-tags">
                    <span class="tech-tag">Laravel</span>
                    <span class="tech-tag">MySQL</span>
                    <span class="tech-tag">REST API</span>
                    <span class="tech-tag">Sanctum Auth</span>
                    <span class="tech-tag">Tailwind CSS</span>
                </div>
            </div>
            <div class="status-list">
                <div class="status-item">
                    <span>حالة الخادم</span>
                    <span class="status-ok">● يعمل</span>
                </div>
                <div class="status-item">
                    <span>قاعدة البيانات</span>
                    <span class="status-ok">● متصلة</span>
                </div>
                <div class="status-item">
                    <span>خدمة المصادقة</span>
                    <span class="status-ok">● نشطة</span>
                </div>
                <div class="status-item">
                    <span>الإصدار الحالي</span>
                    <span>v1.0.0</span>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <strong>CareLink Backend System</strong> — مشروع تخرج © {{ date('Y') }}
        <br>
        مبني باستخدام Laravel {{ app()->version() ?? '' }}
    </footer>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AutoShop - Premium Car Care</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --orange: #f97316;
            --dark: #1f2937;
            --light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            padding-top: 80px; /* Offset for fixed navbar */
        }

        /* Navbar */
        .navbar {
            background: rgba(31, 41, 55, 0.97) !important;
            backdrop-filter: blur(12px);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
            z-index: 1030;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand img {
            height: 50px;
            width: auto;
        }

        .navbar-brand h3 {
            color: white !important;
            margin-left: 10px;
            font-weight: 600 1.5rem 'Inter', sans-serif;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--orange) !important;
            background-color: rgba(249, 115, 22, 0.1);
        }

        .btn-login, .btn-register {
            padding: 0.5px 1.2rem !important;
            border-radius: 50px;
            font-weight: 600;
        }

        .btn-login {
            background: transparent;
            border: 2px solid var(--orange);
            color: var(--orange) !important;
        }

        .btn-login:hover {
            background: var(--orange);
            color: white !important;
        }

        /* Hero Section */
        .hero {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.8)),
                url('https://images.unsplash.com/photo-1503376780353-7e6692767b70') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 0;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--orange);
        }

        .hero p.lead {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 1.5rem auto;
        }

        .hero .btn {
            padding: 0.9rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 50px;
        }

        /* Service Card */
        .service-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.4s ease;
            border: none;
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 40px rgba(0,0,0,0.15);
        }

        .service-icon {
            font-size: 3.5rem;
            color: var(--orange);
            margin-bottom: 1.5rem;
        }

        /* CTA */
        .cta {
            background: linear-gradient(135deg, #ea580c, #f97316);
            color: white;
            padding: 5rem 0;
        }

        }

        /* Footer */
        footer {
            background: var(--dark);
            color: #ccc;
            padding: 2rem 0;
            font-size: 0.95rem;
        }

        footer a {
            color: var(--orange);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }

            .hero {
                min-height: 90vh;
                padding: 6rem 1rem 4rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .lead {
                font-size: 1.1rem;
            }

            .navbar-brand h3 {
                font-size: 1.3rem;
            }

            .navbar-toggler {
                border: none;
            }

            .navbar-toggler-icon {
                color: var(--orange);
            }

            .nav-link {
                text-align: center;
                padding: 0.8rem !important;
            }

            .btn-login, .btn-register {
                width: 100%;
                margin: 0.5rem 0;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 2.2rem;
            }

            .display-5 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Logo">
                <h3 class="mb-0">{{ setting_value('logo_text', 'AutoShop') }}</h3>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars fa-lg" style="color: var(--orange);"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>

                    @guest
                        <li class="nav-item mt-3 mt-lg-0 ms-lg-3">
                            <a href="{{ route('login') }}" class="btn btn-outline-warning btn-login">Login</a>
                        </li>
                        <li class="nav-item mt-2 mt-lg-0 ms-lg-2">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-register">Register</a>
                        </li>
                    @endguest

                    @auth
                        <li class="nav-item mt-3 mt-lg-0 ms-lg-3">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">Logout</button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="animate__animated animate__fadeInDown">Drive with Unmatched Confidence</h1>
            <p class="lead mt-4">
                Welcome to AutoShop, where your vehicle receives world-class care. Our expert team delivers precision diagnostics,
                reliable repairs, and exceptional service to keep you on the road with peace of mind.
            </p>
            <a href="#services" class="btn btn-warning btn-lg mt-4">Explore Our Services</a>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="py-5 bg-white">
        <div class="container">
            <h2 class="display-5 fw-bold text-center mb-4" style="color: var(--orange);" data-aos="fade-up">
                Our Premium Services
            </h2>
            <p class="lead text-muted text-center mx-auto mb-5" style="max-width: 800px;" data-aos="fade-up" data-aos-delay="100">
                Discover our comprehensive suite of automotive services designed to enhance your vehicle’s performance and longevity.
            </p>

            <div class="row g-4 g-lg-5">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
                    <div class="service-card text-center">
                        <i class="fa-solid fa-wrench service-icon"></i>
                        <h3 class="h4 fw-bold">Advanced Diagnostics</h3>
                        <p class="text-muted">Cutting-edge technology to pinpoint issues with unmatched accuracy.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="service-card text-center">
                        <i class="fa-solid fa-car-battery service-icon"></i>
                        <h3 class="h4 fw-bold">Battery & Electrical</h3>
                        <p class="text-muted">Full electrical system care from battery replacement to wiring repairs.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="service-card text-center">
                        <i class="fa-solid fa-oil-can service-icon"></i>
                        <h3 class="h4 fw-bold">Oil & Fluid Services</h3>
                        <p class="text-muted">Premium oil changes and fluid maintenance for optimal engine health.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center flex-column-reverse flex-md-row">
                <div class="col-md-6 text-center text-md-start" data-aos="fade-right">
                    <img src="https://images.unsplash.com/photo-1525609004556-c46c7d6cf023" class="img-fluid rounded shadow" alt="Workshop">
                </div>
                <div class="col-md-6 mb-5 mb-md-0" data-aos="fade-left">
                    <h2 class="display-5 fw-bold" style="color: var(--orange);">Why Choose AutoShop?</h2>
                    <p class="lead text-muted mt-4">
                        At AutoShop, we’re passionate about keeping your vehicle in top condition. With certified technicians,
                        state-of-the-art tools, and full transparency, we deliver care you can trust.
                    </p>
                    <ul class="list-unstyled mt-4">
                        <li class="d-flex mb-3"><i class="fas fa-check-circle text-orange me-3 mt-1"></i><strong>Certified Expert Technicians</strong></li>
                        <li class="d-flex mb-3"><i class="fas fa-check-circle text-orange me-3 mt-1"></i><strong>No Hidden Fees – Transparent Pricing</strong></li>
                        <li class="d-flex"><i class="fas fa-check-circle text-orange me-3 mt-1"></i><strong>24/7 Customer Support Available</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta text-center">
        <div class="container">
            <h2 class="display-5 fw-bold">Need Immediate Assistance?</h2>
            <p class="lead mt-4 mx-auto" style="max-width: 800px;">
                Whether it’s a breakdown or routine maintenance, our team is ready 24/7 to help.
            </p>
            <a href="#contact" class="btn btn-light btn-lg mt-4 px-5">Contact Us Now</a>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-5 bg-white text-center">
        <div class="container">
            <h2 class="display-5 fw-bold mb-4" style="color: var(--orange);" data-aos="fade-up">Let’s Connect</h2>
            <p class="lead text-muted mx-auto mb-5" style="max-width: 700px;" data-aos="fade-up" data-aos-delay="100">
                Have questions or ready to book a service? We're here to help!
            </p>
            <div class="row justify-content-center g-4">
                <div class="col-md-4">
                    <p><i class="fas fa-map-marker-alt fa-2x text-orange mb-3"></i><br>
                    123 Auto Street, Karachi, Pakistan</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-phone-alt fa-2x text-orange mb-3"></i><br>
                    +92 300 1234567</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-envelope fa-2x text-orange mb-3"></i><br>
                    info@autoshop.pk</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} <strong>AutoShop</strong> • Designed with <i class="fas fa-heart text-orange"></i> by <a href="#">Farhan</a></p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>

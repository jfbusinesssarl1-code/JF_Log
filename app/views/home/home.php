<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenue | J.F Business - Comptabilité et Services</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.theme.default.min.css">
  <link rel="stylesheet" href="/asset.php?f=css/custom.css">
  <style>
    * {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    :root {
      --primary-color: #1a5490;
      --secondary-color: #f39c12;
      --text-color: #2c3e50;
      --light-bg: #f8f9fa;
      --dark-navy: #0a1628;
      --medium-navy: #1a2332;
    }

    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      line-height: 1.6;
    }

    html {
      scroll-padding-top: 100px;
      scroll-behavior: smooth;
    }

    .navbar-public {
      background: linear-gradient(135deg, var(--dark-navy) 0%, var(--medium-navy) 100%);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 100;
      padding: 1rem 0;
      backdrop-filter: blur(10px);
    }

    .navbar-public .navbar-brand {
      font-weight: 700;
      font-size: 1.7rem;
      display: flex;
      align-items: center;
      gap: 12px;
      letter-spacing: -0.5px;
      color: #ffffff !important;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .navbar-public .navbar-brand:hover {
      color: #87CEEB !important;
    }

    .navbar-public .navbar-brand i {
      font-size: 2rem;
      color: #87CEEB;
      background: linear-gradient(135deg, rgba(135, 206, 235, 0.2), rgba(79, 195, 247, 0.3));
      padding: 12px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(135, 206, 235, 0.4);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .navbar-public .navbar-brand:hover i {
      color: #ffffff;
      background: linear-gradient(135deg, #87CEEB, #4FC3F7);
      transform: rotate(-5deg) scale(1.1);
      box-shadow: 0 6px 20px rgba(135, 206, 235, 0.6);
    }

    .navbar-public .navbar-brand span {
      color: #ffffff;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .navbar-public .navbar-brand img {
      height: 45px;
      width: auto;
    }

    .navbar-public .nav-link {
      color: rgba(255, 255, 255, 0.9) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin: 0 12px;
      font-weight: 500;
      font-size: 1rem;
      position: relative;
    }

    .navbar-public .nav-link::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 50%;
      transform: translateX(-50%) scaleX(0);
      width: 80%;
      height: 3px;
      background: var(--secondary-color);
      border-radius: 2px;
      transition: transform 0.3s ease;
    }

    .navbar-public .nav-link:hover::after,
    .navbar-public .nav-link.active::after {
      transform: translateX(-50%) scaleX(1);
    }

    .navbar-public .nav-link:hover,
    .navbar-public .nav-link.active {
      color: var(--secondary-color) !important;
      font-weight: 600;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--secondary-color), #e67e22);
      color: white;
      border: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 600;
      padding: 0.6rem 1.5rem !important;
      border-radius: 50px;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #e67e22, var(--secondary-color));
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5);
      color: white;
    }

    /* HERO SECTION - Carrousel */
    .hero-carousel {
      position: relative;
      min-height: 600px;
      margin-top: 30px;
      overflow: hidden;
    }

    .carousel-slide {
      position: relative;
      background-size: cover;
      background-position: center;
      min-height: 600px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .carousel-slide::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(26, 84, 144, 0.5);
    }

    .carousel-content {
      position: relative;
      z-index: 2;
      color: white;
      text-align: center;
      max-width: 700px;
    }

    .carousel-content h2 {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 25px;
      text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.4);
      letter-spacing: -1px;
      line-height: 1.2;
    }

    .carousel-content p {
      font-size: 1.3rem;
      margin-bottom: 35px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      font-weight: 400;
      line-height: 1.6;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .carousel-btn {
      background: linear-gradient(135deg, var(--secondary-color), #e67e22);
      color: white;
      padding: 14px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      border-radius: 50px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
      letter-spacing: 0.3px;
    }

    .carousel-btn:hover {
      background: linear-gradient(135deg, #e67e22, #d68910);
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 10px 30px rgba(243, 156, 18, 0.6);
    }

    /* ANIMATION CAROUSEL 3D ATTRAYANTE */
    #heroCarousel {
      overflow: hidden;
    }

    #heroCarousel .carousel-inner {
      position: relative;
    }

    #heroCarousel .carousel-item {
      position: absolute;
      width: 100%;
      height: 100%;
      animation: slideInRight 0s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    #heroCarousel .carousel-item.active {
      position: relative;
      animation: none;
    }

    #heroCarousel .carousel-item-prev {
      animation: slideOutLeft 0s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100%) scaleY(0.98);
      }

      to {
        opacity: 1;
        transform: translateX(0) scaleY(1);
      }
    }

    @keyframes slideOutLeft {
      from {
        opacity: 1;
        transform: translateX(0) scaleY(1);
      }

      to {
        opacity: 0;
        transform: translateX(-100%) scaleY(0.98);
      }
    }

    #heroCarousel .carousel-control-prev,
    #heroCarousel .carousel-control-next {
      opacity: 0.6;
      transition: all 0.3s ease;
    }

    #heroCarousel .carousel-control-prev:hover,
    #heroCarousel .carousel-control-next:hover {
      opacity: 1;
    }

    #heroCarousel .carousel-control-prev-icon,
    #heroCarousel .carousel-control-next-icon {
      filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.5));
    }

    @media (prefers-reduced-motion: reduce) {
      #heroCarousel .carousel-item {
        animation: none !important;
      }

      #heroCarousel .carousel-item.active {
        animation: none !important;
      }

      padding-bottom: 20px;
    }

    /* SECTION STYLES - Espacement amélioré */
    .section {
      padding: 80px 0;
    }

    .section-title {
      text-align: center;
      position: relative;
      margin-bottom: 60px;
      padding-bottom: 25px;
    }
      color: var(--text-color);
      font-size: 2.8rem;
      font-weight: 700;
      margin-bottom: 15px;
      letter-spacing: -0.5px;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 5px;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border-radius: 3px;
      box-shadow: 0 2px 10px rgba(243, 156, 18, 0.3);
    }

    .section-title p {
      color: #5a6c7d;
      font-size: 1.2rem;
      font-weight: 400;
      margin-top: 15px;
    }

    /* SECTION À PROPOS */
    #about {
      background-color: var(--light-bg);
    }

    .about-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      align-items: center;
      transition: all 0.4s ease;
    }

    .about-container.hidden {
      display: none;
    }

    .about-text h3 {
      color: var(--text-color);
      font-weight: 700;
      margin-bottom: 20px;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }

    .about-text p {
      color: #5a6c7d;
      line-height: 1.9;
      margin-bottom: 25px;
      font-size: 1.05rem;
    }

    .btn-see-more {
      background: linear-gradient(135deg, var(--primary-color), #0d3b7a);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 600;
      font-size: 1rem;
      box-shadow: 0 4px 15px rgba(26, 84, 144, 0.3);
    }

    .btn-see-more:hover {
      background: linear-gradient(135deg, #0d3b7a, #082952);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(26, 84, 144, 0.5);
    }

    .about-carousel {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .about-carousel img {
      width: 100%;
      height: auto;
      display: block;
    }

    .about-carousel .carousel {
      height: 400px;
    }

    .about-carousel .carousel-item {
      height: 100%;
    }

    .about-carousel .carousel-item img {
      object-fit: cover;
      height: 50%;
    }

    /* CONTENU COMPLET À PROPOS */
    .about-full-wrapper {
      display: none;
      transition: all 0.4s ease;
    }

    .about-full-wrapper.active {
      display: grid;
      grid-template-columns: 1fr;
      gap: 30px;
    }

    .about-carousel {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      height: 70%;
      width: 100%;
    }

    .about-carousel .carousel {
      height: 100%;
      width: 100%;
    }

    .about-carousel .carousel-inner {
      height: 100%;
      width: 100%;
    }

    .about-carousel .carousel-item {
      height: 100%;
      width: 100%;
    }

    .about-carousel .carousel-item img {
      object-fit: cover;
      height: 100%;
      width: 100%;
      max-width: 100%;
      display: block;
    }

    .about-content {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        max-height: 0;
      }

      to {
        opacity: 1;
        max-height: 2000px;
      }
    }

    .about-full-section {
      margin-bottom: 30px;
    }

    .about-full-section h4 {
      color: var(--text-color);
      font-weight: 700;
      margin-bottom: 18px;
      font-size: 1.8rem;
      letter-spacing: -0.5px;
    }

    .about-full-section p {
      color: #5a6c7d;
      line-height: 1.9;
      margin-bottom: 18px;
      font-size: 1.05rem;
    }

    .about-sections {
      margin-top: 30px;
      padding-top: 30px;
      border-top: 2px solid var(--light-bg);
    }

    .about-section-item {
      margin-bottom: 25px;
      padding-bottom: 25px;
      border-bottom: 1px solid var(--light-bg);
    }

    .about-section-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
      margin-bottom: 0;
    }

    .about-section-item h5 {
      color: var(--text-color);
      font-weight: 700;
      margin-bottom: 15px;
      font-size: 1.4rem;
      letter-spacing: -0.3px;
    }

    .about-section-item p {
      color: #5a6c7d;
      line-height: 1.9;
      font-size: 1.05rem;
    }

    .btn-see-less {
      background: linear-gradient(135deg, var(--secondary-color), #e67e22);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 25px;
      font-weight: 600;
      font-size: 1rem;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    .btn-see-less:hover {
      background: linear-gradient(135deg, #e67e22, #d68910);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5);
    }

    /* SECTION SERVICES */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .service-card {
      background: white;
      border: none;
      border-radius: 16px;
      padding: 35px;
      text-align: center;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .service-card:hover::before {
      transform: scaleX(1);
    }

    .service-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .service-icon {
      margin-bottom: 20px;
      min-height: 90px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .service-card:hover .service-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .service-icon img {
      max-width: 100%;
      max-height: 90px;
      object-fit: contain;
      display: block;
    }

    .service-icon i {
      font-size: 3.5rem;
      color: var(--primary-color);
      transition: color 0.3s ease;
    }

    .service-card:hover .service-icon i {
      color: var(--secondary-color);
    }

    .service-card h4 {
      color: var(--text-color);
      font-weight: 700;
      margin-bottom: 15px;
      font-size: 1.4rem;
      letter-spacing: -0.3px;
    }

    .service-card p {
      color: #5a6c7d;
      font-size: 1rem;
      line-height: 1.7;
    }

    /* SECTION ACTIVITÉS */
    .activities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .activity-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .activity-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .activity-image {
      width: 100%;
      height: 250px;
      background-color: #e0e0e0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: #666;
    }

    .activity-content {
      padding: 20px;
    }

    .activity-content h5 {
      color: var(--text-color);
      font-weight: 700;
      margin-bottom: 12px;
      font-size: 1.3rem;
      letter-spacing: -0.3px;
    }

    .activity-content p {
      color: #5a6c7d;
      font-size: 1rem;
      margin-bottom: 18px;
      line-height: 1.6;
    }

    .btn-activity {
      background: linear-gradient(135deg, var(--secondary-color), #e67e22);
      color: white;
      padding: 10px 25px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 0.95rem;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    .btn-activity:hover {
      background: linear-gradient(135deg, #e67e22, #d68910);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5);
      color: white;
    }

    /* SECTION PARTENAIRES */
    #partners {
      background-color: var(--light-bg);
    }

    .partners-carousel {
      padding: 40px 0;
    }

    .partner-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 140px;
      padding: 25px;
      background: white;
      border-radius: 12px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.06);
    }

    .partner-logo:hover {
      transform: translateY(-8px) scale(1.05);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .partner-logo img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    /* SECTION CONTACT */
    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }

    .contact-left {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .contact-info {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      display: flex;
      align-items: flex-start;
      gap: 18px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .contact-info:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .contact-info-icon {
      font-size: 2rem;
      color: var(--primary-color);
      min-width: 40px;
      transition: all 0.3s ease;
    }

    .contact-info:hover .contact-info-icon {
      color: var(--secondary-color);
      transform: scale(1.1);
    }

    .contact-info-text h4 {
      color: var(--text-color);
      margin-bottom: 8px;
      font-weight: 700;
      font-size: 1.2rem;
    }

    .contact-info-text p {
      color: #5a6c7d;
      margin: 0;
      font-size: 1rem;
      line-height: 1.6;
    }

    .contact-map {
      width: 100%;
      height: 300px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
    }

    .contact-map iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .contact-right {
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .contact-right h4 {
      color: var(--text-color);
      margin-bottom: 25px;
      font-weight: 700;
      font-size: 1.6rem;
      letter-spacing: -0.3px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      color: var(--text-color);
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
      font-size: 1rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 13px 18px;
      border: 2px solid #e1e8ed;
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-family: 'Poppins', sans-serif;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(26, 84, 144, 0.1);
      transform: translateY(-2px);
    }

    .btn-send {
      background: linear-gradient(135deg, var(--primary-color), #0d3b7a);
      color: white;
      padding: 14px 35px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      font-weight: 700;
      font-size: 1.05rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      width: 100%;
      box-shadow: 0 4px 15px rgba(26, 84, 144, 0.3);
      letter-spacing: 0.3px;
    }

    .btn-send:hover {
      background: linear-gradient(135deg, #0d3b7a, #082952);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(26, 84, 144, 0.5);
    }

    /* FOOTER */
    footer {
      background: linear-gradient(135deg, var(--dark-navy) 0%, var(--medium-navy) 100%);
      color: white;
      padding: 60px 0 30px;
      margin-top: 80px;
      box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.2);
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-section h5 {
      font-weight: 700;
      margin-bottom: 20px;
      color: var(--secondary-color);
      font-size: 1.3rem;
      letter-spacing: -0.3px;
    }

    .footer-section p,
    .footer-section a {
      color: rgba(255, 255, 255, 0.85);
      transition: all 0.3s ease;
      text-decoration: none;
      font-size: 1rem;
      line-height: 1.8;
    }

    .footer-section a:hover {
      color: var(--secondary-color);
      padding-left: 5px;
    }

    .footer-divider {
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      padding-top: 25px;
      text-align: center;
    }

    .footer-divider p {
      margin: 0;
      color: rgba(255, 255, 255, 0.7);
      font-size: 1rem;
    }

    .footer-divider a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-divider a:hover {
      color: var(--secondary-color);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      body {
        padding-top: 60px;
      }

      .navbar-public {
        padding: 0.75rem 0;
      }

      .navbar-public .navbar-brand {
        font-size: 1.4rem;
      }

      .navbar-public .navbar-brand i {
        font-size: 1.6rem;
        padding: 10px;
      }

      .carousel-content h2 {
        font-size: 2.2rem;
        margin-bottom: 20px;
      }

      .carousel-content p {
        font-size: 1.05rem;
        margin-bottom: 25px;
      }

      .carousel-btn {
        padding: 12px 30px;
        font-size: 1rem;
      }

      .section {
        padding: 50px 0;
      }

      .section-title {
        margin-bottom: 40px;
      }

      .section-title h2 {
        font-size: 2.2rem;
      }

      .section-title p {
        font-size: 1.05rem;
      }

      .about-container {
        grid-template-columns: 1fr;
      }

      .about-text h3 {
        font-size: 1.8rem;
      }

      .about-carousel-full {
        height: 300px;
      }

      .contact-container {
        grid-template-columns: 1fr;
      }

      .service-card {
        padding: 25px;
      }

      .activity-card {
        margin-bottom: 20px;
      }

      footer {
        padding: 40px 0 20px;
      }

      .footer-section h5 {
        font-size: 1.15rem;
      }
    }
  </style>
</head>

<body>
  <!-- NAVBAR PUBLIC -->
  <nav class="navbar navbar-expand-lg navbar-public">
    <div class="container-fluid">
      <a class="navbar-brand" href="#home">
        <i class="bi bi-briefcase"></i>
        <span>J.F Business</span>
      </a>

      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#home">Accueil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">À propos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#services">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#activities">Activités</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>
          <li class="nav-item ms-2">
            <a href="?page=login" class="btn btn-login btn-sm">
              <i class="bi bi-box-arrow-in-right"></i> Connexion
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php
  use App\Models\HomeModel;
  use App\Models\AboutModel;
  use App\Models\ServiceModel;
  use App\Models\ActivityModel;
  use App\Models\PartnerModel;
  use App\Helpers\AssetHelper;

  // Session is started in the front controller (public/index.php)
  
  $homeModel = new HomeModel();
  $aboutModel = new AboutModel();
  $serviceModel = new ServiceModel();
  $activityModel = new ActivityModel();
  $partnerModel = new PartnerModel();

  $homeItems = $homeModel->getAll();
  $aboutItems = $aboutModel->getAll();
  $services = $serviceModel->getAll();
  $activities = $activityModel->getAll();
  $partners = $partnerModel->getAll();

  ?>

  <!-- HERO SECTION - CARROUSEL (Bootstrap) -->
  <section id="home">
    <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000"
      data-bs-pause="false">
      <div class="carousel-inner">
        <?php if (!empty($homeItems)): ?>
          <?php $firstSlide = true; ?>
          <?php foreach ($homeItems as $h): ?>
            <?php $bg = isset($h['image']) ? AssetHelper::url($h['image']) : "data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 600%22%3E%3Crect fill=%221a5490%22 width=%221200%22 height=%22600%22/%3E%3C/svg%3E"; ?>
            <div class="carousel-item <?php echo $firstSlide ? 'active' : ''; ?>">
              <div class="carousel-slide"
                style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($bg, ENT_QUOTES); ?>')">
                <div class="carousel-content">
                  <h2><?php echo htmlspecialchars($h['title'] ?? 'Bienvenue chez J.F Business'); ?></h2>
                  <p>
                    <?php echo htmlspecialchars($h['description'] ?? 'Solutions comptables et services professionnels de qualité'); ?>
                  </p>
                  <?php if (!empty($h['link'])): ?>
                    <a href="<?php echo htmlspecialchars($h['link']); ?>" class="carousel-btn">Découvrir plus</a>
                  <?php else: ?>
                    <button class="carousel-btn">Découvrir plus</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php $firstSlide = false; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="carousel-item active">
            <div class="carousel-slide"
              style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 600%22%3E%3Crect fill=%221a5490%22 width=%221200%22 height=%22600%22/%3E%3C/svg%3E')">
              <div class="carousel-content">
                <h2>Bienvenue chez J.F Business</h2>
                <p>Solutions comptables et services professionnels de qualité</p>
                <button class="carousel-btn">Découvrir plus</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Précédent</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Suivant</span>
      </button>
    </div>
  </section>
  <!-- Modal pour détails d'activité -->
  <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="activityModalLabel">Détails de l'activité</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-5">
              <img id="activityModalImage" src="" alt=""
                style="width:100%; height:250px; object-fit:cover; border-radius:6px; background:#f0f0f0">
            </div>
            <div class="col-md-7">
              <h4 id="activityModalTitle"></h4>
              <p id="activityModalDescription"></p>
              <p class="small text-muted" id="activityModalMeta"></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Remplir et ouvrir le modal d'activité après que la page et Bootstrap soient chargés
    window.addEventListener('load', function () {
      function onClick(e) {
        var btn = e.currentTarget;
        var title = btn.getAttribute('data-title') || '';
        var description = btn.getAttribute('data-description') || '';
        var image = btn.getAttribute('data-image') || '';
        var status = btn.getAttribute('data-status') || '';
        var date = btn.getAttribute('data-date') || '';

        document.getElementById('activityModalTitle').textContent = title;
        document.getElementById('activityModalDescription').textContent = description;
        document.getElementById('activityModalMeta').textContent = 'Statut: ' + status + (date ? ' — Date: ' + date :
          '');
        var imgEl = document.getElementById('activityModalImage');
        if (image) {
          imgEl.src = image;
          imgEl.style.display = 'block';
        } else {
          imgEl.src = '';
          imgEl.style.display = 'none';
        }

        var modalEl = document.getElementById('activityModal');
        var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
      }

      document.querySelectorAll('.btn-activity').forEach(function (b) {
        b.addEventListener('click', onClick);
      });
    });
  </script>

  <!-- SECTION À PROPOS -->
  <section id="about" class="section">
    <div class="container" style="padding-bottom: 50px;">
      <div class="section-title">
        <h2>Découvrez J.F Business</h2>
      </div>

      <?php $about = $aboutItems[0] ?? null; ?>
      <?php if (!empty($about)): ?>
        <div class="about-container " style="height: 400px;">
          <div class="about-text">
            <h3><?php echo htmlspecialchars($about['title'] ?? 'J.F Business'); ?></h3>
            <p id="aboutPreview" class="about-preview">
              <?php
              $fullText = $about['text'] ?? '';
              $previewText = strlen($fullText) > 200 ? substr($fullText, 0, 200) . '...' : $fullText;
              echo htmlspecialchars($previewText);
              ?>
            </p>

            <?php if (!empty($about['sections']) || strlen($about['text'] ?? '') > 200): ?>
              <button class="btn-see-more" id="aboutMoreBtn">Voir plus</button>
            <?php endif; ?>
          </div>

          <!-- CAROUSEL D'IMAGES -->
          <div class="about-carousel" style="height: 100%;">
            <?php $images = $about['images'] ?? []; ?>
            <?php if (!empty($images)): ?>
              <?php if (count($images) > 1): ?>
                <div id="aboutImagesCarousel" class="carousel carousel-dark slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php foreach ($images as $idx => $img): ?>
                      <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($img); ?>" class="d-block w-100" alt="À propos">
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#aboutImagesCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#aboutImagesCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  </button>
                </div>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="À propos" class="w-100">
              <?php endif; ?>
            <?php else: ?>
              <img
                src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 500 400%22%3E%3Crect fill=%23e0e0e0%22 width=%22500%22 height=%22400%22/%3E%3Ctext x=%22250%22 y=%22200%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2224%22 fill=%22%23999%22%3EImages À propos%3C/text%3E%3C/svg%3E"
                alt="À propos" class="w-100">
            <?php endif; ?>
          </div>
        </div>

        <!-- MODAL/EXPANDABLE "VOIR PLUS" -->
        <div id="aboutFullWrapper" class="about-full-wrapper" style="width: 80%; margin: auto; text-align: justify;">
          <!-- CAROUSEL D'IMAGES EN PLEINE LARGEUR -->
          <div class="about-carousel-full">
            <?php $images = $about['images'] ?? []; ?>
            <?php if (!empty($images)): ?>
              <?php if (count($images) > 1): ?>
                <div id="aboutImagesCarouselFull" class="carousel carousel-dark slide" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <?php foreach ($images as $idx => $img): ?>
                      <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($img); ?>" class="d-block w-100"
                          style="height: 100%; object-fit: cover; width: 100%;" alt="À propos">
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#aboutImagesCarouselFull"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#aboutImagesCarouselFull"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  </button>
                </div>
              <?php else: ?>
                <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="À propos" class="w-100"
                  style="height: 500px; object-fit: cover; width: 100%; display: block;">
              <?php endif; ?>
            <?php else: ?>
              <img
                src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 500 400%22%3E%3Crect fill=%23e0e0e0%22 width=%22500%22 height=%22400%22/%3E%3Ctext x=%22250%22 y=%22200%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2224%22 fill=%22%23999%22%3EImages À propos%3C/text%3E%3C/svg%3E"
                alt="À propos" class="w-100" style="height: 400px; object-fit: cover;">
            <?php endif; ?>
          </div>

          <!-- CONTENU TEXTE COMPLET -->
          <div class="about-full-content">
            <!-- Texte principal complet -->
            <div class="about-full-section">
              <h4><?php echo htmlspecialchars($about['title'] ?? 'J.F Business'); ?></h4>
              <p><?php echo nl2br(htmlspecialchars($about['text'] ?? '')); ?></p>
            </div>

            <!-- Sections supplémentaires -->
            <?php if (!empty($about['sections'])): ?>
              <div class="about-sections">
                <?php foreach ($about['sections'] as $section): ?>
                  <div class="about-section-item">
                    <h5><?php echo htmlspecialchars($section['subtitle'] ?? ''); ?></h5>
                    <p><?php echo nl2br(htmlspecialchars($section['text'] ?? '')); ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <button class="btn-see-less" id="aboutLessBtn">Voir moins</button>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- SECTION SERVICES -->
  <section id="services" class="section">
    <div class="container">
      <div class="section-title">
        <h2>Nos Services</h2>
        <p>Les solutions que nous proposons</p>
      </div>

      <div class="services-grid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $s): ?>
            <div class="service-card">
              <div class="service-icon">
                <?php if (!empty($s['icon'])): ?>
                  <img src="<?php echo AssetHelper::url($s['icon']); ?>"
                    alt="<?php echo htmlspecialchars($s['name'] ?? ''); ?>">
                <?php else: ?>
                  <i class="bi bi-gear-fill"></i>
                <?php endif; ?>
              </div>
              <h4><?php echo htmlspecialchars($s['name'] ?? 'Service'); ?></h4>
              <?php
              $desc = $s['description'] ?? '';
              $short = strlen($desc) > 140 ? substr($desc, 0, 140) . '...' : $desc;
              ?>
              <p><?php echo nl2br(htmlspecialchars($short)); ?></p>
              <p><a href="?page=home&action=service&id=<?php echo $s['_id']; ?>" class="btn btn-sm btn-primary">Voir
                  détails</a></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="service-card">
            <div class="service-icon"><i class="bi bi-calculator-fill"></i></div>
            <h4>Comptabilité</h4>
            <p>Gestion complète de votre comptabilité avec les meilleures pratiques.</p>
          </div>
          <div class="service-card">
            <div class="service-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <h4>Audit Financier</h4>
            <p>Audit complet de vos données financières pour assurer la conformité.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- SECTION ACTIVITÉS EN COURS -->
  <section id="activities" class="section" style="background-color: var(--light-bg);">
    <div class="container">
      <div class="section-title">
        <h2>Activités en cours</h2>
        <p>Les projets actuellement en exécution</p>
      </div>

      <div class="activities-grid">
        <?php if (!empty($activities)): ?>
          <?php foreach ($activities as $a): ?>
            <div class="activity-card">
              <div class="activity-image">
                <?php if (!empty($a['image'])): ?>
                  <img src="<?php echo AssetHelper::url($a['image']); ?>"
                    alt="<?php echo htmlspecialchars($a['title'] ?? ''); ?>"
                    style="width:100%; height:250px; object-fit:cover;">
                <?php else: ?>
                  <img
                    src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 250%22%3E%3Crect fill=%23e0e0e0%22 width=%22300%22 height=%22250%22/%3E%3C/svg%3E"
                    alt="">
                <?php endif; ?>
              </div>
              <div class="activity-content">
                <h5><?php echo htmlspecialchars($a['title'] ?? 'Projet'); ?></h5>
                <p><?php echo htmlspecialchars($a['description'] ?? ''); ?></p>
                <button type="button" class="btn-activity btn btn-sm btn-outline-primary"
                  data-title="<?php echo htmlspecialchars($a['title'] ?? ''); ?>"
                  data-description="<?php echo htmlspecialchars($a['description'] ?? ''); ?>"
                  data-image="<?php echo htmlspecialchars(AssetHelper::url($a['image'] ?? ''), ENT_QUOTES); ?>"
                  data-status="<?php echo htmlspecialchars($a['status'] ?? ''); ?>"
                  data-date="<?php echo htmlspecialchars($a['date'] ?? ''); ?>">
                  Voir plus
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="activity-card">
            <div class="activity-image"><img
                src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 250%22%3E%3Crect fill=%23e0e0e0%22 width=%22300%22 height=%22250%22/%3E%3C/svg%3E"
                alt=""></div>
            <div class="activity-content">
              <h5>Projet 1</h5>
              <p>Description du projet en cours d'exécution.</p>
              <button type="button" class="btn-activity btn btn-sm btn-outline-primary" data-title="Projet 1"
                data-description="Description du projet en cours d'exécution.">
                Voir plus
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- SECTION PARTENAIRES -->
  <section id="partners" class="section">
    <div class="container">
      <div class="section-title">
        <h2>Nos Partenaires</h2>
        <p>Ils nous font confiance</p>
      </div>

      <div class="partners-carousel">
        <div class="row">
          <?php if (!empty($partners)): ?>
            <?php foreach ($partners as $p): ?>
              <div class="col-md-6 col-lg-3 mb-4">
                <div class="partner-logo">
                  <?php if (!empty($p['logo'])): ?>
                    <?php if (!empty($p['link'])): ?><a href="<?php echo htmlspecialchars($p['link']); ?>"
                        target="_blank"><?php endif; ?>
                      <img src="<?php echo AssetHelper::url($p['logo']); ?>"
                        alt="<?php echo htmlspecialchars($p['name'] ?? 'Partenaire'); ?>">
                      <?php if (!empty($p['link'])): ?></a><?php endif; ?>
                  <?php else: ?>
                    <img
                      src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 100%22%3E%3Crect fill=%23f0f0f0%22 width=%22200%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2216%22 fill=%22%23999%22%3E<?php echo rawurlencode('Partenaire'); ?>%3C/text%3E%3C/svg%3E"
                      alt="Partenaire">
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="partner-logo"><img
                  src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 100%22%3E%3Crect fill=%23f0f0f0%22 width=%22200%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2216%22 fill=%22%23999%22%3EPartenaire%201%3C/text%3E%3C/svg%3E"
                  alt="Partenaire 1"></div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="partner-logo"><img
                  src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 100%22%3E%3Crect fill=%23f0f0f0%22 width=%22200%22 height=%22100%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22sans-serif%22 font-size=%2216%22 fill=%22%23999%22%3EPartenaire%202%3C/text%3E%3C/svg%3E"
                  alt="Partenaire 2"></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION CONTACT -->
  <section id="contact" class="section">
    <div class="container">
      <div class="section-title">
        <h2>Nous Contacter</h2>
        <p>Envoyez-nous votre message</p>
      </div>

      <div class="contact-container">
        <div class="contact-left">
          <div class="contact-info">
            <div class="contact-info-icon">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="contact-info-text">
              <h4>Adresse</h4>
              <p>123 Rue de l'Affaire<br>75000 Paris, France</p>
            </div>
          </div>

          <div class="contact-info">
            <div class="contact-info-icon">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="contact-info-text">
              <h4>Email</h4>
              <p><a href="mailto:info@jfbusiness.com" style="color: inherit;">info@jfbusiness.com</a></p>
            </div>
          </div>

          <div class="contact-info">
            <div class="contact-info-icon">
              <i class="bi bi-whatsapp"></i>
            </div>
            <div class="contact-info-text">
              <h4>WhatsApp</h4>
              <p><a href="https://wa.me/33612345678" style="color: inherit;">+33 6 12 34 56 78</a></p>
            </div>
          </div>

          <div class="contact-map">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9955896450473!2d2.2922926!3d48.8588897!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66fb6b6f3f0f7%3A0x2f2f2f2f2f2f2f2f!2sParis%2C%20France!5e0!3m2!1sfr!2sfr!4v1234567890"
              allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>

        <div class="contact-right">
          <h4>Envoyez-nous un message</h4>
          <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-success">
              <?php echo htmlspecialchars($_SESSION['flash']);
              unset($_SESSION['flash']); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
              <?php echo htmlspecialchars($_SESSION['flash_error']);
              unset($_SESSION['flash_error']); ?>
            </div>
          <?php endif; ?>
          <form id="contactForm" method="post" action="?page=home&action=contact">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="form-group">
              <label for="contactName">Nom *</label>
              <input type="text" id="contactName" name="name" required>
            </div>

            <div class="form-group">
              <label for="contactEmail">Email *</label>
              <input type="email" id="contactEmail" name="email" required>
            </div>

            <div class="form-group">
              <label for="contactSubject">Sujet *</label>
              <input type="text" id="contactSubject" name="subject" required>
            </div>

            <div class="form-group">
              <label for="contactMessage">Message *</label>
              <textarea id="contactMessage" name="message" rows="5" required></textarea>
            </div>

            <button type="submit" class="btn-send">
              <i class="bi bi-send"></i> Envoyer
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h5>À propos</h5>
          <p>J.F Business est votre partenaire de confiance pour tous vos besoins en comptabilité et services
            professionnels.</p>
        </div>

        <div class="footer-section">
          <h5>Services</h5>
          <a href="#services">Comptabilité</a><br>
          <a href="#services">Audit Financier</a><br>
          <a href="#services">Conseil Fiscal</a><br>
          <a href="#services">Paie et RH</a>
        </div>

        <div class="footer-section">
          <h5>Contact</h5>
          <p>Email: info@jfbusiness.com<br>
            Tél: +33 6 12 34 56 78<br>
            Adresse: 123 Rue de l'Affaire<br>75000 Paris, France</p>
        </div>

        <div class="footer-section">
          <h5>Suivez-nous</h5>
          <a href="#" class="footer-link"><i class="bi bi-facebook"></i> Facebook</a><br>
          <a href="#" class="footer-link"><i class="bi bi-twitter"></i> Twitter</a><br>
          <a href="#" class="footer-link"><i class="bi bi-linkedin"></i> LinkedIn</a>
        </div>
      </div>

      <div class="footer-divider">
        <p>&copy; 2024 J.F Business. Tous droits réservés. | <a href="#">Politique de confidentialité</a> | <a
            href="#">Conditions d'utilisation</a></p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script>
    // Smooth scrolling for navbar links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#' && document.querySelector(href)) {
          e.preventDefault();
          document.querySelector(href).scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Update active navbar link on scroll
    window.addEventListener('scroll', function () {
      let current = '';
      const sections = document.querySelectorAll('section');

      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (scrollY >= (sectionTop - 200)) {
          current = section.getAttribute('id');
        }
      });

      document.querySelectorAll('.navbar-nav a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === current) {
          link.classList.add('active');
        }
      });
    });

    // Contact form is submitted to server-side handler

    // Carousel auto-play
    $(document).ready(function () {
      if ($('.owl-carousel').length) {
        // initialize non-hero carousels (partners, about, etc.)
        $(".owl-carousel").not('.hero-carousel').owlCarousel({
          loop: true,
          margin: 10,
          responsive: {
            0: {
              items: 1
            },
            600: {
              items: 2
            },
            1000: {
              items: 4
            }
          }
        });

        // initialize hero carousel with autoplay
        if ($('.hero-carousel.owl-carousel').length) {
          $(".hero-carousel.owl-carousel").owlCarousel({
            items: 1,
            loop: true,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            animateOut: 'fadeOut'
          });
        }
      }

      // Gestion du "Voir plus" / "Voir moins" pour la section À propos
      const aboutMoreBtn = document.getElementById('aboutMoreBtn');
      const aboutLessBtn = document.getElementById('aboutLessBtn');
      const aboutContainer = document.querySelector('.about-container');
      const aboutFullWrapper = document.getElementById('aboutFullWrapper');

      if (aboutMoreBtn) {
        aboutMoreBtn.addEventListener('click', function (e) {
          e.preventDefault();
          // Masquer la partie gauche et le carousel
          aboutContainer.classList.add('hidden');
          // Afficher le contenu complet
          aboutFullWrapper.classList.add('active');
          // Scroll vers le haut de la section
          setTimeout(() => {
            document.getElementById('about').scrollIntoView({
              behavior: 'smooth'
            });
          }, 100);
        });
      }

      if (aboutLessBtn) {
        aboutLessBtn.addEventListener('click', function (e) {
          e.preventDefault();
          // Afficher la partie gauche et le carousel
          aboutContainer.classList.remove('hidden');
          // Masquer le contenu complet
          aboutFullWrapper.classList.remove('active');
          // Scroll vers le haut de la section
          document.getElementById('about').scrollIntoView({
            behavior: 'smooth'
          });
        });
      }
    });
  </script>
</body>

</html>
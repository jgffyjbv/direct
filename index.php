<?php
require_once 'config/config.php';

$routesData = [];
if (file_exists(__DIR__ . '/routes.json')) {
    $routesData = json_decode(file_get_contents(__DIR__ . '/routes.json'), true) ?? [];
}

$featuredCards = [];
foreach (($routesData['featured_routes'] ?? []) as $featured) {
    $match = null;
    foreach (($routesData['routes'] ?? []) as $route) {
        if (($route['from'] ?? null) === ($featured['from'] ?? null)
            && ($route['to'] ?? null) === ($featured['to'] ?? null)) {
            $match = $route;
            break;
        }
    }
    if (!$match || empty($match['pricing'])) continue;
    $prices = array_filter(array_values($match['pricing']), 'is_numeric');
    if (empty($prices)) continue;
    $featuredCards[] = [
        'from' => $featured['from'],
        'to' => $featured['to'],
        'price' => (int) min($prices),
        'description' => $featured['description'] ?? '',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Service Monsey, Brooklyn, Lakewood, Airports &amp; Catskills | Direct Car Service</title>
    <meta name="description" content="Heimishe, reliable car service from Monsey, Brooklyn &amp; Lakewood to JFK, LaGuardia &amp; Newark airports, the Catskills, Montreal and beyond. 24/6 service. Call 845-642-1317.">
    <meta name="keywords" content="Monsey car service, Brooklyn car service, Lakewood car service, car service to JFK, car service to Newark airport, Catskills car service, Monsey to Brooklyn, Monsey to Lakewood, Monsey to Montreal, heimishe car service">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://directcsny.com/">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Direct Car Service - Monsey, Brooklyn, Lakewood, Airports &amp; Catskills">
    <meta property="og:description" content="Heimishe, reliable 24/6 car service from Monsey, Brooklyn &amp; Lakewood to the airports, the Catskills, Montreal and beyond. Call 845-642-1317.">
    <meta property="og:url" content="https://directcsny.com/">
    <meta property="og:image" content="https://directcsny.com/images/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Direct Car Service - Monsey, Brooklyn, Lakewood, Airports &amp; Catskills">
    <meta name="twitter:description" content="Heimishe, reliable 24/6 car service to the airports, the Catskills, Montreal and beyond. Call 845-642-1317.">

    <!-- LocalBusiness structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Direct Car Service",
        "image": "https://directcsny.com/images/logo.png",
        "logo": "https://directcsny.com/images/logo.png",
        "url": "https://directcsny.com",
        "telephone": "+1-845-642-1317",
        "email": "info@directcsny.com",
        "priceRange": "$$",
        "description": "Heimishe and reliable car service serving Monsey, Brooklyn, Lakewood and the surrounding Jewish communities, with trips to the airports, the Catskills, Montreal and beyond. Available 24 hours a day, 6 days a week.",
        "areaServed": [
            {"@type": "Place", "name": "Monsey, NY"},
            {"@type": "Place", "name": "Spring Valley, NY"},
            {"@type": "Place", "name": "New Square, NY"},
            {"@type": "Place", "name": "Monroe / Kiryas Joel, NY"},
            {"@type": "Place", "name": "Brooklyn, NY"},
            {"@type": "Place", "name": "Borough Park, Brooklyn"},
            {"@type": "Place", "name": "Williamsburg, Brooklyn"},
            {"@type": "Place", "name": "Flatbush, Brooklyn"},
            {"@type": "Place", "name": "Crown Heights, Brooklyn"},
            {"@type": "Place", "name": "Manhattan, NYC"},
            {"@type": "Place", "name": "Lakewood, NJ"},
            {"@type": "Place", "name": "Passaic / Clifton, NJ"},
            {"@type": "Place", "name": "Catskills / Sullivan County, NY"},
            {"@type": "Place", "name": "Monticello, NY"},
            {"@type": "Place", "name": "Liberty, NY"},
            {"@type": "Place", "name": "Montreal, Quebec"},
            {"@type": "City", "name": "JFK Airport"},
            {"@type": "City", "name": "LaGuardia Airport"},
            {"@type": "City", "name": "Newark Liberty Airport (EWR)"}
        ],
        "openingHoursSpecification": [{
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday"],
            "opens": "00:00",
            "closes": "23:59"
        }],
        "contactPoint": [{
            "@type": "ContactPoint",
            "telephone": "+1-845-642-1317",
            "contactType": "reservations"
        },{
            "@type": "ContactPoint",
            "telephone": "+1-845-306-6055",
            "contactType": "customer service"
        }]
    }
    </script>

    <link rel="icon" type="image/png" href="./images/logo.png">
    <link rel="apple-touch-icon" href="./images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY, ENT_QUOTES) ?>&libraries=places&callback=initAutocomplete" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .header-content {
            position: relative;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #4CAF50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            color: #45a049;
            transform: scale(1.05);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        nav a:hover {
            color: #4CAF50;
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #4CAF50;
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 100%;
        }

        .reserve-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%) !important;
            color: white !important;
            padding: 12px 24px !important;
            border-radius: 25px !important;
            font-weight: bold !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3) !important;
        }

        .reserve-btn:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4) !important;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .mobile-menu span {
            width: 25px;
            height: 3px;
            background: white;
            margin: 3px 0;
            transition: 0.3s;
        }
        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 0 0 15px 15px;
        }

        .mobile-nav.open {
            display: block;
        }

        .mobile-nav ul {
            flex-direction: column;
            padding: 20px;
            gap: 0;
        }

        .mobile-nav li {
            border-bottom: 1px solid #e9ecef;
        }

        .mobile-nav li:last-child {
            border-bottom: none;
        }

        .mobile-nav a {
            display: block;
            padding: 15px 10px;
            color: #2c3e50 !important;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-nav a:hover {
            background: #f8f9fa;
            color: #4CAF50 !important;
        }

        .mobile-nav .reserve-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%) !important;
            color: white !important;
            margin: 10px 0;
            text-align: center;
            border-radius: 25px !important;
        }

        /* Hero Section */
.hero {
    position: relative;
    /* Use the corrected image URL for the background */
    background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('./images/road.png');
    background-size: cover;
    background-position: center;
    background-attachment: fixed; /* This creates the parallax-like effect */
    color: white;
    padding: 120px 0 80px;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 40px;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-logo {
            text-align: center;
        }

        .hero-logo img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            transition: all 0.3s ease;
        }

        /* Quick Booking Widget */
.quick-booking {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    border-radius: 20px;
    padding: 30px 35px;
    margin-top: 40px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

        .quick-booking h2 {
            margin-bottom: 15px;
            color: white;
            font-weight: 600;
        }

        .booking-form-inline {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: center;
        }

        .booking-form-inline input,
        .booking-form-inline select {
            padding: 12px 15px;
            border: none;
            border-radius: 15px;
            font-size: 0.9rem;
            font-family: inherit;
            background: rgba(255,255,255,0.9);
            width: 100%;
            box-sizing: border-box;
        }

        .booking-form-inline input:focus,
        .booking-form-inline select:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }

        .btn-reserve {
            background: linear-gradient(135deg, #fff 0%, #f5f5f5 100%);
            color: #4CAF50;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-reserve:hover {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            transform: translateY(-2px);
        }

        /* About Us Section */
        .about {
            padding: 100px 0;
            background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #dee2e6, #f8f9fa);
            background-size: 400% 400%;
            animation: subtle-gradient 12s ease infinite;
            position: relative;
            overflow: hidden;
        }

        .about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 70%, rgba(76, 175, 80, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 70% 30%, rgba(76, 175, 80, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        @keyframes subtle-gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            position: relative;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
            border-radius: 2px;
        }

        .section-header p {
            font-size: 1.2rem;
            color: #666;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        /* Enhanced hover effects */
        .about-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.5s ease;
            border-top: 4px solid #4CAF50;
            position: relative;
            overflow: hidden;
        }

        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .about-card:hover::before {
            left: 100%;
        }

        .about-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border-top-color: #2E7D32;
        }

        .about-card i {
            font-size: 3rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .about-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .about-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Popular Trips */
        .popular-trips {
            padding: 100px 0;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .popular-trips::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(76, 175, 80, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        /* Enhanced trip cards */
        .trip-card {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.5s ease;
            border: 1px solid rgba(76, 175, 80, 0.1);
            position: relative;
            overflow: hidden;
        }

        .trip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
        }

        .trip-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .trip-card:hover::after {
            left: 100%;
        }

        .trip-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
            border-color: #4CAF50;
        }

        .trip-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .trip-details {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Fleet Section - Updated with Carousel */
.fleet {
    padding: 100px 0;
    background: linear-gradient(-45deg, #4CAF50, #66BB6A, #81C784, #4CAF50);
    background-size: 300% 300%;
    animation: gradient-animation 10s ease infinite;
    color: white;
    position: relative;
    overflow: hidden;
}

.fleet::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
    pointer-events: none;
}

.fleet .section-header h2,
.fleet .section-header p {
    color: white;
}

/* Carousel Container */
.fleet-carousel {
    position: relative;
    overflow: hidden;
    margin: 0 60px; /* Space for navigation arrows */
}

.fleet-track {
    display: flex;
    transition: transform 0.5s ease;
    gap: 40px;
}

.fleet-track.auto-moving {
    animation: carouselMove 20s linear infinite;
}

@keyframes carouselMove {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

/* Fleet Cards - Updated to match about-card style */
.fleet-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.5s ease;
    border-top: 4px solid #4CAF50;
    position: relative;
    overflow: hidden;
    min-width: 300px;
    flex-shrink: 0;
}

.fleet-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.1), transparent);
    transition: left 0.5s ease;
}

.fleet-card:hover::before {
    left: 100%;
}

.fleet-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
    border-top-color: #2E7D32;
}

.fleet-card i {
    font-size: 3rem;
    color: #4CAF50;
    margin-bottom: 20px;
}

.fleet-card h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
}

.fleet-card .fleet-specs {
    color: #666;
    line-height: 1.6;
}

.fleet-card .fleet-image {
    width: 100%;
    height: 180px;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.fleet-card .fleet-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Navigation Arrows */
.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #4CAF50;
    transition: all 0.3s ease;
    z-index: 10;
}

.carousel-nav:hover {
    background: white;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.carousel-nav.prev {
    left: 10px;
}

.carousel-nav.next {
    right: 10px;
}

/* Mobile Responsive - Static display */
@media (max-width: 768px) {
    .fleet-carousel {
        overflow: visible;
        margin: 0;
    }

    .fleet-track {
        flex-direction: column;
        gap: 30px;
        animation: none !important;
    }

    .fleet-track.auto-moving {
        animation: none !important;
    }

    .fleet-card {
        min-width: auto;
        width: 100%;
    }

    .carousel-nav {
        display: none;
    }
}

        /* Testimonials */
        .testimonials {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f1f3f4 100%);
            position: relative;
            overflow: hidden;
        }

        .testimonials::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(76, 175, 80, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }

        .testimonial-card {
            background: #f8f9fa;
            padding: 35px;
            border-radius: 15px;
            border-left: 4px solid #4CAF50;
            position: relative;
        }

        .testimonial-content {
            font-style: italic;
            color: #555;
            margin-bottom: 25px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .testimonial-info h4 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .testimonial-info p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Contact Section */
        .contact {
            padding: 100px 0;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #3c4e63 100%);
            background-size: 300% 300%;
            animation: gradient-animation 12s ease infinite;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 70%, rgba(76, 175, 80, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }

        .contact-info h3 {
            color: #4CAF50;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }

        .contact-item i {
            font-size: 1.5rem;
            color: #4CAF50;
            margin-right: 20px;
            margin-top: 5px;
        }

        .contact-form {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .contact-form h3 {
            color: #4CAF50;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.9);
            font-size: 1rem;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: #4CAF50;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #4CAF50;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #34495e;
            color: #bbb;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-content nav {
                display: none;
            }

            .mobile-menu {
                display: flex;
            }

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            /* Reorder hero content on mobile: Logo on top */
            .hero-logo {
                grid-row: 1; /* Move logo to the first row */
                margin-bottom: -20px; /* Adjust spacing */
            }
            .hero-text {
                grid-row: 2; /* Move text to the second row */
            }
            .hero-logo img {
                max-height: 200px; /* Make logo smaller on mobile */
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .booking-form-inline {
                grid-template-columns: 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .about-grid {
                grid-template-columns: 1fr;
            }

            .trips-grid {
                grid-template-columns: 1fr;
            }

            .fleet-grid {
                grid-template-columns: 1fr;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
            }
        }

        /* CTA Buttons */
        .cta-secondary {
            background: transparent;
            color: #4CAF50;
            padding: 12px 25px;
            border: 2px solid #4CAF50;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .cta-secondary:hover {
            background: #4CAF50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .cta-white {
            background: white;
            color: #4CAF50;
            padding: 12px 25px;
            border: 2px solid white;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .cta-white:hover {
            background: transparent;
            color: white;
            border-color: white;
            transform: translateY(-2px);
        }

        .section-cta {
            text-align: center;
            margin-top: 40px;
        }


        .pac-item-selected {
            background-color: #4CAF50 !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php" style="color: inherit; text-decoration: none; display: flex; align-items: center;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #4CAF50;"></i>
                        Direct Car Service
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="#about">About</a></li>
                        <li><a href="pricing.html">Pricing</a></li>
                        <li><a href="#fleet">Fleet</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="booking.php" class="reserve-btn">Reserve Now</a></li>
                    </ul>
                </nav>
                <div class="mobile-menu" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="mobile-nav" id="mobileNav">
                <ul>
                    <li><a href="#about">About</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#fleet">Fleet</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="booking.php" class="reserve-btn">Reserve Now</a></li>
                </ul>
            </div>
        </div>
    </header>

    <section class="hero" id="booking">
        <div class="container">
            <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
                <div class="hero-text">
                    <h1 data-aos="fade-right" data-aos-delay="200">Heimishe and Reliable Drivers</h1>
                    <p data-aos="fade-right" data-aos-delay="400">Rely on our round-the-clock transportation service to meet your travel needs at any time of the day. Whether it's a late-night arrival or an early morning departure, we've got you covered with our 24 hour service a day 6 days a week.</p>
                </div>
                <div class="hero-logo" data-aos="fade-left" data-aos-delay="300">
                    <img src="./images/logo.png" alt="Direct Car Service Logo">
                </div>
            </div>

            <div class="quick-booking" data-aos="fade-up" data-aos-delay="600">
                <h2>Get a Quick Quote</h2>
                <form class="booking-form-inline" id="quickBookingForm">
                    <input type="text" placeholder="From" id="pickup" required>
                    <input type="text" placeholder="To" id="dropoff" required>
                    <input type="date" id="date" required>
                    <select id="timeSlot" required>
                        <option value="">Select time</option>
                        <option value="Morning (6AM - 12PM)">Morning</option>
                        <option value="Afternoon (12PM - 6PM)">Afternoon</option>
                        <option value="Evening (6PM - 12AM)">Evening</option>
                        <option value="Late Night (12AM - 6AM)">Late Night</option>
                    </select>
                    <select id="tripType" required>
                        <option value="">Trip type</option>
                        <option value="One Way">One Way</option>
                        <option value="Round Trip">Round Trip</option>
                    </select>
                    <button type="submit" class="btn-reserve">Get Quote</button>
                </form>
            </div>
        </div>
    </section>

    <section class="about" id="about">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Why Choose Direct Car Service</h2>
                <p>Your trusted transportation partner</p>
            </div>

            <div class="about-grid">
                <div class="about-card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-clock"></i>
                    <h3>24/6 Service</h3>
                    <p>Round-the-clock availability for all your transportation needs, whenever you need us.</p>
                </div>

                <div class="about-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-car"></i>
                    <h3>Comfortable Cars</h3>
                    <p>Well-maintained, clean, and comfortable vehicles to ensure a pleasant journey every time.</p>
                </div>

                <div class="about-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas fa-users"></i>
                    <h3>Heimishe & Reliable Drivers</h3>
                    <p>Professional, courteous, and trusted drivers who understand your community and values.</p>
                </div>

                <div class="about-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-check-circle"></i>
                    <h3>On Time Every Time!</h3>
                    <p>Punctuality is our promise. We ensure you reach your destination right on schedule.</p>
                </div>
            </div>

            <div class="section-cta" data-aos="fade-up" data-aos-delay="500">
                <a href="/booking.php" class="cta-secondary">Get a Quote</a>
                <a href="/pricing.html" class="cta-secondary" style="margin-left: 15px;">Pricing</a>
            </div>
        </div>
    </section>

    <section class="popular-trips" id="pricing">
        <div class="container">
            <div class="section-header">
                <h2>Popular Routes</h2>
                <p>Our most requested destinations</p>
            </div>

            <div class="trips-grid">
                <?php foreach ($featuredCards as $i => $card): ?>
                <div class="trip-card" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 100 ?>">
                    <h3><?= htmlspecialchars($card['from']) ?> to <?= htmlspecialchars($card['to']) ?></h3>
                    <div class="price">From $<?= $card['price'] ?></div>
                    <div class="trip-details">
                        <?= nl2br(htmlspecialchars($card['description'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="section-cta" data-aos="fade-up" data-aos-delay="400">
                <a href="pricing.html" class="cta-secondary">View All Pricing</a>
            </div>
        </div>
    </section>

    <section class="fleet" id="fleet">
    <div class="container">
        <div class="section-header">
            <h2>Our Fleet</h2>
            <p>Choose the perfect vehicle for your needs</p>
        </div>

        <div class="fleet-carousel">
            <button class="carousel-nav prev" id="fleetPrev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-nav next" id="fleetNext">
                <i class="fas fa-chevron-right"></i>
            </button>

            <div class="fleet-track" id="fleetTrack">
                <div class="fleet-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="fleet-image">
                        <img src="./images/suv.jpeg" alt="Luxury SUV for Monsey, Brooklyn and Lakewood car service">
                    </div>
                    <i class="fas fa-car-side"></i>
                    <h3>Luxury SUV</h3>
                    <div class="fleet-specs">
                        <strong>Capacity:</strong> 6-7 passengers<br>
                        <strong>Luggage:</strong> 4-5 large bags<br>
                        <strong>Features:</strong> Leather seats, Climate control, Premium sound
                    </div>
                </div>

                <div class="fleet-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="fleet-image">
                        <img src="./images/minivan.jpeg" alt="Minivan car service for families and groups">
                    </div>
                    <i class="fas fa-shuttle-van"></i>
                    <h3>Minivan</h3>
                    <div class="fleet-specs">
                        <strong>Capacity:</strong> 7-8 passengers<br>
                        <strong>Luggage:</strong> 6-8 large bags<br>
                        <strong>Features:</strong> Spacious interior, Multiple charging ports, Extra legroom
                    </div>
                </div>

                <div class="fleet-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="fleet-image">
                        <img src="./images/full-size.jpeg" alt="Full-size SUV for airport and long-distance car service">
                    </div>
                    <i class="fas fa-truck"></i>
                    <h3>Full Size SUV</h3>
                    <div class="fleet-specs">
                        <strong>Capacity:</strong> 8 passengers<br>
                        <strong>Luggage:</strong> 6-8 large bags<br>
                        <strong>Features:</strong> Maximum space, Premium comfort, Advanced safety
                    </div>
                </div>

                <div class="fleet-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="fleet-image">
                        <img src="./images/sprinter.png" alt="Mercedes Sprinter van for large group transportation">
                    </div>
                    <i class="fas fa-bus"></i>
                    <h3>Sprinter Van</h3>
                    <div class="fleet-specs">
                        <strong>Capacity:</strong> Up to 15 passengers<br>
                        <strong>Luggage:</strong> 10+ large bags<br>
                        <strong>Features:</strong> Ideal for large groups, Ample space, Professional service
                    </div>
                </div>
            </div>
        </div>

        <div class="section-cta" data-aos="fade-up" data-aos-delay="500" style="text-align: center; margin-top: 30px;">
            <a href="booking.php" class="cta-secondary">Get a Quote</a>
        </div>
    </div>
</section>

    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>What Our Clients Say</h2>
                <p>Trusted by families and businesses throughout the community</p>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Direct Car Service has been our go-to for family trips. The drivers are always professional and the cars are spotless. Highly recommend!"
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">SM</div>
                        <div class="testimonial-info">
                            <h4>Sarah M.</h4>
                            <p>Brooklyn, NY</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "I use them for all my airport runs. They're always on time and the service is reliable. Great value for the quality."
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">DL</div>
                        <div class="testimonial-info">
                            <h4>David L.</h4>
                            <p>Monsey, NY</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "The long-distance trips to Montreal are comfortable and stress-free. The drivers know the route well and make great time."
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">RM</div>
                        <div class="testimonial-info">
                            <h4>Rachel M.</h4>
                            <p>Lakewood, NJ</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-cta" data-aos="fade-up" data-aos-delay="400" style="text-align: center; margin-top: 30px;">
                <a href="booking.php" class="cta-secondary">Get Your Quote Today</a>
            </div>
        </div>
    </section>

    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header">
                <h2>Contact Us</h2>
                <p>Get in touch to book your ride</p>
            </div>

            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Get in Touch</h3>

                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <strong>Call Us:</strong><br>
                            845-642-1317<br>
                            845-306-6055
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Email:</strong><br>
                            info@directcsny.com
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Service Area:</strong><br>
                            New York Metro Area, Montreal, and surrounding regions
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Availability:</strong><br>
                            24/6 Service Available
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <h3>Send Message</h3>
                    <form id="contactForm">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" placeholder="Your Phone">
                        </div>
                        <div class="form-group">
                            <textarea name="message" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #4CAF50;"></i>Direct Car Service</h3>
                    <p>Professional and reliable transportation service serving the New York Metro Area, Montreal, and surrounding regions. Available 24/6 for all your travel needs.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#booking">Book a Ride</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#fleet">Our Fleet</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Airport Transportation</a></li>
                        <li><a href="#">Long Distance Travel</a></li>
                        <li><a href="#">Corporate Travel</a></li>
                        <li><a href="#">Special Events</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul>
                        <li>Phone: 845-642-1317</li>
                        <li>Phone: 845-306-6055</li>
                        <li>Email: info@directcsny.com</li>
                        <li>Available 24/6</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Direct Car Service. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Global variables for autocomplete
        let autocompletePickup, autocompleteDropoff;

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('date').min = new Date().toISOString().split('T')[0];
        });

        // Google Places autocomplete for homepage pickup/dropoff fields
        function initAutocomplete() {
            const pickupInput = document.getElementById('pickup');
            const dropoffInput = document.getElementById('dropoff');

            if (pickupInput && dropoffInput) {
                autocompletePickup = new google.maps.places.Autocomplete(pickupInput);
                autocompletePickup.setFields(['formatted_address']);
                autocompletePickup.setComponentRestrictions({ country: ['us', 'ca'] });

                autocompleteDropoff = new google.maps.places.Autocomplete(dropoffInput);
                autocompleteDropoff.setFields(['formatted_address']);
                autocompleteDropoff.setComponentRestrictions({ country: ['us', 'ca'] });
            }
        }

        // Quick booking form — redirect to contact.php with prefilled params
        document.getElementById('quickBookingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const pickup = document.getElementById('pickup').value;
            const dropoff = document.getElementById('dropoff').value;
            const date = document.getElementById('date').value;
            const timeSlot = document.getElementById('timeSlot').value;
            const tripType = document.getElementById('tripType').value;

            if (!pickup || !dropoff || !date || !timeSlot || !tripType) {
                alert('Please fill in all fields');
                return;
            }

            const params = new URLSearchParams({
                pickup: pickup,
                dropoff: dropoff,
                date: date,
                timeSlot: timeSlot,
                tripType: tripType
            });

            window.location.href = `/contact.php?${params.toString()}`;
        });

        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            const formData = {
                name: this.querySelector('[name="name"]').value,
                email: this.querySelector('[name="email"]').value,
                phone: this.querySelector('[name="phone"]').value,
                message: this.querySelector('[name="message"]').value
            };

            try {
                const response = await fetch('contact-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Thank you for your message! We will get back to you soon.');
                    this.reset();
                } else {
                    alert(result.error || 'Failed to send message. Please try again.');
                }
            } catch (error) {
                alert('Failed to send message. Please call us at 845-642-1317.');
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileNav = document.getElementById('mobileNav');

        if (mobileMenuToggle && mobileNav) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileNav.classList.toggle('open');
            });
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
           anchor.addEventListener('click', function (e) {
               e.preventDefault();
               const target = document.querySelector(this.getAttribute('href'));
               if (target) {
                    // Calculate header height to offset scroll
                    const headerOffset = document.querySelector('header').offsetHeight;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                   // Close mobile menu if open
                   if (mobileNav && mobileNav.classList.contains('open')) {
                       mobileNav.classList.remove('open');
                   }
               }
           });
        });
    // Fleet Carousel Functionality
document.addEventListener('DOMContentLoaded', function() {
    const fleetTrack = document.getElementById('fleetTrack');
    const prevBtn = document.getElementById('fleetPrev');
    const nextBtn = document.getElementById('fleetNext');

    if (!fleetTrack || window.innerWidth <= 768) return; // Skip on mobile

    const cards = Array.from(fleetTrack.children);
    const cardWidth = 340; // Card width + gap
    let currentIndex = 0;
    let autoMoveInterval;
    let isManualNavigation = false;

    // Clone cards for infinite loop
    function createInfiniteLoop() {
        cards.forEach(card => {
            const clone = card.cloneNode(true);
            fleetTrack.appendChild(clone);
        });
        fleetTrack.style.width = `${cards.length * 2 * cardWidth}px`;
    }

    // Start auto-movement
    function startAutoMove() {
        if (isManualNavigation) return;

        autoMoveInterval = setInterval(() => {
            if (isManualNavigation) return;
            moveNext();
        }, 3000);

        fleetTrack.classList.add('auto-moving');
    }

    // Stop auto-movement
    function stopAutoMove() {
        clearInterval(autoMoveInterval);
        fleetTrack.classList.remove('auto-moving');
    }

    // Move to next card
    function moveNext() {
        currentIndex++;
        if (currentIndex >= cards.length) {
            // Reset to beginning without animation
            fleetTrack.style.transition = 'none';
            currentIndex = 0;
            fleetTrack.style.transform = `translateX(0px)`;

            setTimeout(() => {
                fleetTrack.style.transition = 'transform 0.5s ease';
                currentIndex++;
                fleetTrack.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
            }, 50);
        } else {
            fleetTrack.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }
    }

    // Move to previous card
    function movePrev() {
        currentIndex--;
        if (currentIndex < 0) {
            // Jump to end without animation
            fleetTrack.style.transition = 'none';
            currentIndex = cards.length - 1;
            fleetTrack.style.transform = `translateX(-${currentIndex * cardWidth}px)`;

            setTimeout(() => {
                fleetTrack.style.transition = 'transform 0.5s ease';
            }, 50);
        } else {
            fleetTrack.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }
    }

    // Event listeners
    nextBtn.addEventListener('click', () => {
        isManualNavigation = true;
        stopAutoMove();
        moveNext();

        // Resume auto-movement after 5 seconds
        setTimeout(() => {
            isManualNavigation = false;
            startAutoMove();
        }, 5000);
    });

    prevBtn.addEventListener('click', () => {
        isManualNavigation = true;
        stopAutoMove();
        movePrev();

        // Resume auto-movement after 5 seconds
        setTimeout(() => {
            isManualNavigation = false;
            startAutoMove();
        }, 5000);
    });

    // Pause on hover
    fleetTrack.addEventListener('mouseenter', stopAutoMove);
    fleetTrack.addEventListener('mouseleave', () => {
        if (!isManualNavigation) startAutoMove();
    });

    // Initialize
    createInfiniteLoop();
    startAutoMove();
});
    </script>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>
</body>
</html>

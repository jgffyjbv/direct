<?php
// Load routes data
$routesData = json_decode(file_get_contents('routes.json'), true);
$routes = $routesData['routes'] ?? [];
$vehicleTypes = $routesData['vehicle_types'] ?? [];

// Get route from URL
$routeSlug = $_GET['route'] ?? '';

// Common abbreviations and aliases mapping
$abbreviations = [
    // Brooklyn neighborhoods
    'bp' => 'Borough Park',
    'boro-park' => 'Borough Park',
    'boropark' => 'Borough Park',
    'willy' => 'Williamsburg',
    'wburg' => 'Williamsburg',
    'wb' => 'Williamsburg',
    'ch' => 'Crown Heights',
    'crown-hts' => 'Crown Heights',
    'flatbush' => 'Flatbush',
    'fb' => 'Flatbush',
    'midwood' => 'Midwood',
    'bklyn' => 'Brooklyn',
    'bk' => 'Brooklyn',

    // Areas
    'nyc' => 'NYC',
    'ny' => 'NY',
    'nj' => 'NJ',
    'mtl' => 'Montreal',
    'montreal' => 'Montreal Quebec',
    'toronto' => 'Toronto',
    'to' => 'Toronto',
    'philly' => 'Philadelphia',
    'phila' => 'Philadelphia',

    // Local areas
    'kj' => 'Monroe (KJ)',
    'kiryas-joel' => 'Monroe (KJ)',
    'kiryas joel' => 'Monroe (KJ)',
    'monroe' => 'Monroe (KJ)',
    'lkwd' => 'Lakewood',
    'lakewood' => 'Lakewood NJ',
    'toms-river' => 'Lakewood/Tom\'s River (NJ)',
    'passaic' => 'Passaic/Clifton NJ',
    'clifton' => 'Passaic/Clifton NJ',
    'spring-valley' => 'Spring Valley NY',
    'sv' => 'Spring Valley NY',
    'new-square' => 'New Square NY',
    'ns' => 'New Square NY',
    'catskills' => 'Catskills Mountains',
    'liberty' => 'Liberty (Catskills)',
    'manhattan' => 'Manhattan NYC',

    // Airports
    'jfk' => 'JFK Airport',
    'lga' => 'LaGuardia Airport',
    'laguardia' => 'LaGuardia Airport',
    'ewr' => 'Newark Airport',
    'newark' => 'Newark Airport',
];

// Function to create slug from location names
function createSlug($from, $to) {
    $slug = strtolower($from . '-to-' . $to);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// Function to expand abbreviations in a string
function expandAbbreviations($text, $abbreviations) {
    $text = strtolower(trim($text));
    $text = str_replace('-', ' ', $text);

    // Check for exact abbreviation match first (whole string)
    if (isset($abbreviations[$text])) {
        return $abbreviations[$text];
    }

    // Check for abbreviation with dashes
    $textDashed = str_replace(' ', '-', $text);
    if (isset($abbreviations[$textDashed])) {
        return $abbreviations[$textDashed];
    }

    // Split into words and check each word for abbreviation
    $words = explode(' ', $text);
    $expandedWords = [];
    foreach ($words as $word) {
        if (isset($abbreviations[$word])) {
            $expandedWords[] = $abbreviations[$word];
        } else {
            $expandedWords[] = $word;
        }
    }

    return ucwords(implode(' ', $expandedWords));
}

// Function to parse slug back to locations
function parseSlug($slug, $abbreviations) {
    // Common patterns to match
    $parts = explode('-to-', $slug);
    if (count($parts) >= 2) {
        $fromRaw = $parts[0];
        $toRaw = implode('-to-', array_slice($parts, 1));

        $from = expandAbbreviations($fromRaw, $abbreviations);
        $to = expandAbbreviations($toRaw, $abbreviations);

        return ['from' => $from, 'to' => $to, 'fromRaw' => $fromRaw, 'toRaw' => $toRaw];
    }
    return null;
}

// Function to calculate similarity between two location strings
function locationMatch($search, $target) {
    $search = strtolower(preg_replace('/[^a-z0-9]/', '', $search));
    $target = strtolower(preg_replace('/[^a-z0-9]/', '', $target));

    // Exact match
    if ($search === $target) return 100;

    // One contains the other
    if (strpos($target, $search) !== false) return 80;
    if (strpos($search, $target) !== false) return 70;

    // Similar text
    similar_text($search, $target, $percent);
    return $percent;
}

// Find matching route
$currentRoute = null;
$parsedSlug = parseSlug($routeSlug, $abbreviations);
$redirectToBooking = false;
$pickupLocation = '';
$dropoffLocation = '';

if ($parsedSlug) {
    $pickupLocation = $parsedSlug['from'];
    $dropoffLocation = $parsedSlug['to'];

    // First: Try exact slug match
    foreach ($routes as $route) {
        $routeSlugCheck = createSlug($route['from'], $route['to']);
        if ($routeSlugCheck === $routeSlug) {
            $currentRoute = $route;
            break;
        }
    }

    // Second: Try matching with expanded abbreviations
    if (!$currentRoute) {
        $bestMatch = null;
        $bestScore = 0;

        foreach ($routes as $route) {
            $fromScore = locationMatch($parsedSlug['from'], $route['from']);
            $toScore = locationMatch($parsedSlug['to'], $route['to']);
            $totalScore = ($fromScore + $toScore) / 2;

            if ($totalScore > $bestScore && $totalScore >= 60) {
                $bestScore = $totalScore;
                $bestMatch = $route;
            }
        }

        if ($bestMatch) {
            $currentRoute = $bestMatch;
        }
    }

    // Third: If still no match, prepare to redirect to booking page
    if (!$currentRoute) {
        $redirectToBooking = true;
    }
} else {
    // Slug doesn't have "-to-" pattern, redirect to booking
    $redirectToBooking = true;
    // Try to extract any meaningful text for the booking form
    $cleanSlug = ucwords(str_replace('-', ' ', $routeSlug));
    if (!empty($cleanSlug) && $cleanSlug !== 'Car Service') {
        $pickupLocation = $cleanSlug;
    }
}

// If no route found, redirect to booking page with pre-filled data
if ($redirectToBooking) {
    $bookingUrl = '/booking';
    $params = [];

    if (!empty($pickupLocation)) {
        $params[] = 'pickup=' . urlencode($pickupLocation);
    }
    if (!empty($dropoffLocation)) {
        $params[] = 'dropoff=' . urlencode($dropoffLocation);
    }

    if (!empty($params)) {
        $bookingUrl .= '?' . implode('&', $params);
    }

    header("Location: $bookingUrl", true, 302);
    exit;
}

// Prepare SEO variables
$fromLocation = $currentRoute['from'];
$toLocation = $currentRoute['to'];
$basePrice = $currentRoute['pricing']['full_size_car'] ?? 0;
$category = $currentRoute['category'] ?? 'local';

// Self-referential canonical URL (uses the ACTUAL request URL, never a guessed path)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'directcsny.com';
$canonicalUrl = htmlspecialchars($scheme . '://' . $host . ($_SERVER['REQUEST_URI'] ?? ('/route.php?route=' . $routeSlug)), ENT_QUOTES);

// Create SEO-friendly titles
$pageTitle = "Car Service from {$fromLocation} to {$toLocation} | Starting at \${$basePrice} | Direct Car Service";
$metaDescription = "Professional car service from {$fromLocation} to {$toLocation}. Prices starting at \${$basePrice}. Luxury SUVs, minivans, and sprinter vans available. Book online or call 845-642-1317.";

// Determine category-specific content
$categoryDescriptions = [
    'airports' => 'Reliable airport transportation with flight tracking and meet & greet service.',
    'local' => 'Comfortable local transportation with professional drivers.',
    'long-distance' => 'Long distance travel with experienced drivers and comfortable vehicles for extended journeys.'
];
$categoryDesc = $categoryDescriptions[$category] ?? $categoryDescriptions['local'];

// Booking URL with pre-filled data
$bookingUrl = "/booking?pickup=" . urlencode($fromLocation) . "&dropoff=" . urlencode($toLocation);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary SEO Meta Tags -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="car service <?php echo htmlspecialchars(strtolower($fromLocation)); ?> to <?php echo htmlspecialchars(strtolower($toLocation)); ?>, <?php echo htmlspecialchars(strtolower($fromLocation)); ?> <?php echo htmlspecialchars(strtolower($toLocation)); ?> transportation, private car <?php echo htmlspecialchars(strtolower($fromLocation)); ?>, <?php echo htmlspecialchars(strtolower($toLocation)); ?> car service">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo $canonicalUrl; ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="og:image" content="https://directcsny.com/images/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">

    <!-- Structured Data for this specific route -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "Car Service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?>",
        "description": "<?php echo htmlspecialchars($metaDescription); ?>",
        "brand": {
            "@type": "Brand",
            "name": "Direct Car Service"
        },
        "offers": {
            "@type": "Offer",
            "price": "<?php echo $basePrice; ?>",
            "priceCurrency": "USD",
            "availability": "https://schema.org/InStock",
            "priceValidUntil": "<?php echo date('Y-12-31'); ?>",
            "url": "<?php echo $canonicalUrl; ?>"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "serviceType": "Car Service",
        "name": "<?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?> Car Service",
        "description": "Professional car service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?>. <?php echo htmlspecialchars($categoryDesc); ?>",
        "provider": {
            "@type": "LocalBusiness",
            "name": "Direct Car Service",
            "telephone": "+1-845-642-1317",
            "url": "https://directcsny.com"
        },
        "areaServed": [
            {"@type": "Place", "name": "<?php echo htmlspecialchars($fromLocation); ?>"},
            {"@type": "Place", "name": "<?php echo htmlspecialchars($toLocation); ?>"}
        ],
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "Vehicle Options",
            "itemListElement": [
                <?php
                $offers = [];
                foreach ($currentRoute['pricing'] as $vehicleKey => $price) {
                    if ($price && isset($vehicleTypes[$vehicleKey])) {
                        $offers[] = '{
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "' . htmlspecialchars($vehicleTypes[$vehicleKey]['name']) . '"
                            },
                            "price": "' . $price . '",
                            "priceCurrency": "USD"
                        }';
                    }
                }
                echo implode(",\n                ", $offers);
                ?>
            ]
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How much does a car service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?> cost?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Car service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?> starts at $<?php echo $basePrice; ?> for a full-size car. Prices vary by vehicle type: SUV from $<?php echo $currentRoute['pricing']['standard_suv'] ?? 'N/A'; ?>, Full-Size SUV from $<?php echo $currentRoute['pricing']['full_size_suv'] ?? 'N/A'; ?>, Minivan from $<?php echo $currentRoute['pricing']['minivan'] ?? 'N/A'; ?>."
                }
            },
            {
                "@type": "Question",
                "name": "How do I book a car from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?>?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can book online at directcsny.com, call 845-642-1317, or email info@directcsny.com. We recommend booking at least 24 hours in advance."
                }
            },
            {
                "@type": "Question",
                "name": "What vehicles are available for <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?>?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "We offer Full-Size Cars (4 passengers), Standard SUVs (6 passengers), Full-Size SUVs (7-8 passengers), Minivans (7-8 passengers), and Mercedes Sprinter Vans (up to 14 passengers)."
                }
            }
        ]
    }
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/images/logo.png">

    <!-- Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; line-height: 1.6; color: #333; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Header */
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #4CAF50; text-decoration: none; }
        .logo a { color: inherit; text-decoration: none; display: flex; align-items: center; }
        nav ul { display: flex; list-style: none; gap: 2rem; }
        nav a { color: white; text-decoration: none; font-weight: 500; }
        nav a:hover { color: #4CAF50; }
        .reserve-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white !important;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
        }

        /* Hero Section */
        .route-hero {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            padding: 140px 0 80px;
            text-align: center;
        }
        .route-hero h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .route-hero .subtitle { font-size: 1.3rem; opacity: 0.9; margin-bottom: 2rem; }
        .price-highlight {
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        .price-highlight span { font-size: 1rem; font-weight: 400; }
        .cta-button {
            display: inline-block;
            background: white;
            color: #4CAF50;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .cta-button:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

        /* Pricing Grid */
        .pricing-section { padding: 80px 0; background: white; }
        .section-title { text-align: center; font-size: 2rem; margin-bottom: 50px; color: #2c3e50; }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }
        .pricing-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .pricing-card:hover { border-color: #4CAF50; transform: translateY(-5px); }
        .pricing-card i { font-size: 2.5rem; color: #4CAF50; margin-bottom: 15px; }
        .pricing-card h3 { font-size: 1.2rem; margin-bottom: 10px; color: #2c3e50; }
        .pricing-card .capacity { color: #666; font-size: 0.9rem; margin-bottom: 15px; }
        .pricing-card .price { font-size: 2rem; font-weight: 700; color: #4CAF50; }
        .pricing-card .price span { font-size: 1rem; color: #666; }

        /* Info Section */
        .info-section { padding: 60px 0; background: #f8f9fa; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; }
        .info-card { background: white; padding: 30px; border-radius: 15px; }
        .info-card h3 { color: #4CAF50; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .info-card ul { list-style: none; }
        .info-card li { padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-card li:last-child { border-bottom: none; }

        /* FAQ Section */
        .faq-section { padding: 60px 0; background: white; }
        .faq-item { background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 15px; }
        .faq-item h3 { color: #2c3e50; margin-bottom: 10px; font-size: 1.1rem; }
        .faq-item p { color: #666; }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .cta-section h2 { font-size: 2rem; margin-bottom: 20px; }
        .cta-section p { margin-bottom: 30px; opacity: 0.9; }
        .cta-buttons { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .cta-buttons a {
            padding: 15px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary { background: #4CAF50; color: white; }
        .btn-secondary { background: transparent; color: white; border: 2px solid white; }
        .btn-primary:hover, .btn-secondary:hover { transform: translateY(-3px); }

        /* Footer */
        footer { background: #1a252f; color: #bbb; padding: 40px 0; text-align: center; }
        footer a { color: #4CAF50; text-decoration: none; }

        /* Mobile */
        @media (max-width: 768px) {
            .route-hero h1 { font-size: 1.8rem; }
            .price-highlight { font-size: 1.5rem; padding: 10px 25px; }
            nav ul { display: none; }
            .pricing-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Direct Car Service</a>
                </div>
                <nav>
                    <ul>
                        <li><a href="/#about">About</a></li>
                        <li><a href="/pricing">Pricing</a></li>
                        <li><a href="/#fleet">Fleet</a></li>
                        <li><a href="/#contact">Contact</a></li>
                        <li><a href="<?php echo $bookingUrl; ?>" class="reserve-btn">Book Now</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Route Hero -->
    <section class="route-hero">
        <div class="container">
            <h1>Car Service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?></h1>
            <p class="subtitle"><?php echo htmlspecialchars($categoryDesc); ?></p>
            <div class="price-highlight">
                Starting at $<?php echo $basePrice; ?> <span>/ one way</span>
            </div>
            <br><br>
            <a href="<?php echo $bookingUrl; ?>" class="cta-button">
                <i class="fas fa-calendar-check"></i> Book This Trip Now
            </a>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <h2 class="section-title">Choose Your Vehicle</h2>
            <div class="pricing-grid">
                <?php
                $icons = [
                    'full_size_car' => 'fa-car',
                    'standard_suv' => 'fa-car-side',
                    'full_size_suv' => 'fa-truck-monster',
                    'minivan' => 'fa-shuttle-van',
                    'sprinter' => 'fa-bus'
                ];
                foreach ($currentRoute['pricing'] as $vehicleKey => $price):
                    if ($price && isset($vehicleTypes[$vehicleKey])):
                        $vehicle = $vehicleTypes[$vehicleKey];
                ?>
                <div class="pricing-card">
                    <i class="fas <?php echo $icons[$vehicleKey] ?? 'fa-car'; ?>"></i>
                    <h3><?php echo htmlspecialchars($vehicle['name']); ?></h3>
                    <div class="capacity"><?php echo htmlspecialchars($vehicle['description']); ?></div>
                    <div class="price">$<?php echo $price; ?> <span>one way</span></div>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-check-circle"></i> What's Included</h3>
                    <ul>
                        <li>Professional, experienced driver</li>
                        <li>Door-to-door service</li>
                        <li>Free waiting time (15 mins)</li>
                        <li>Flight tracking for airport pickups</li>
                        <li>Child seats available on request</li>
                        <li>24/6 customer support</li>
                    </ul>
                </div>
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Trip Details</h3>
                    <ul>
                        <li><strong>From:</strong> <?php echo htmlspecialchars($fromLocation); ?></li>
                        <li><strong>To:</strong> <?php echo htmlspecialchars($toLocation); ?></li>
                        <li><strong>Category:</strong> <?php echo ucfirst(str_replace('-', ' ', $category)); ?></li>
                        <li><strong>Starting Price:</strong> $<?php echo $basePrice; ?></li>
                        <li><strong>Available:</strong> 24 hours, 6 days/week</li>
                    </ul>
                </div>
                <div class="info-card">
                    <h3><i class="fas fa-phone"></i> Contact Us</h3>
                    <ul>
                        <li><strong>Phone:</strong> <a href="tel:+18456421317">845-642-1317</a></li>
                        <li><strong>Alt Phone:</strong> <a href="tel:+18453066055">845-306-6055</a></li>
                        <li><strong>Email:</strong> <a href="mailto:info@directcsny.com">info@directcsny.com</a></li>
                        <li><strong>Hours:</strong> Sun-Fri, 24 hours</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="faq-item">
                <h3>How much does a car service from <?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?> cost?</h3>
                <p>Prices start at $<?php echo $basePrice; ?> for a full-size car. SUVs start at $<?php echo $currentRoute['pricing']['standard_suv'] ?? 'N/A'; ?>, minivans from $<?php echo $currentRoute['pricing']['minivan'] ?? 'N/A'; ?>, and sprinter vans from $<?php echo $currentRoute['pricing']['sprinter'] ?? 'N/A'; ?>.</p>
            </div>
            <div class="faq-item">
                <h3>How do I book this trip?</h3>
                <p>You can book online using our booking form, call us at 845-642-1317, or email info@directcsny.com. We recommend booking at least 24 hours in advance for the best availability.</p>
            </div>
            <div class="faq-item">
                <h3>What vehicles are available?</h3>
                <p>We offer full-size cars (4 passengers), standard SUVs (6 passengers), full-size/premium SUVs (7-8 passengers), minivans (7-8 passengers), and Mercedes Sprinter vans (up to 14 passengers).</p>
            </div>
            <div class="faq-item">
                <h3>Is round-trip available?</h3>
                <p>Yes! Round trips are available at a discounted rate. Contact us for a custom quote or select "Round Trip" when booking online.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Book Your Trip?</h2>
            <p><?php echo htmlspecialchars($fromLocation); ?> to <?php echo htmlspecialchars($toLocation); ?> - Starting at $<?php echo $basePrice; ?></p>
            <div class="cta-buttons">
                <a href="<?php echo $bookingUrl; ?>" class="btn-primary"><i class="fas fa-calendar-check"></i> Book Online Now</a>
                <a href="tel:+18456421317" class="btn-secondary"><i class="fas fa-phone"></i> Call 845-642-1317</a>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Direct Car Service. All rights reserved.</p>
            <p><a href="/">Home</a> | <a href="/pricing">All Routes</a> | <a href="/booking">Book Now</a> | <a href="/#contact">Contact</a></p>
        </div>
    </footer>
</body>
</html>

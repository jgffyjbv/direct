<?php
require_once 'config/config.php';

$pickup   = htmlspecialchars($_GET['pickup']   ?? '', ENT_QUOTES);
$dropoff  = htmlspecialchars($_GET['dropoff']  ?? '', ENT_QUOTES);
$date     = htmlspecialchars($_GET['date']     ?? '', ENT_QUOTES);
$timeSlot = htmlspecialchars($_GET['timeSlot'] ?? '', ENT_QUOTES);
$tripType = htmlspecialchars($_GET['tripType'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Quote - Monsey, Brooklyn &amp; Lakewood Car Service | Direct Car Service</title>
    <meta name="description" content="Request a custom car service quote from Direct Car Service. Door-to-door rides from Monsey, Brooklyn &amp; Lakewood to the airports, the Catskills, Montreal and beyond. 24/6. Call 845-642-1317.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://directcsny.com/contact">
    <link rel="icon" type="image/png" href="./images/logo.png">
    <link rel="apple-touch-icon" href="./images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY, ENT_QUOTES) ?>&libraries=places&callback=initContactAutocomplete" defer></script>
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
            background: #f4f6f8;
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

        /* Page body */
        .page-body {
            padding: 120px 0 80px;
            min-height: 100vh;
        }

        /* Quote card */
        .quote-card {
            max-width: 760px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .quote-card-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        .quote-card-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .quote-card-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .quote-card-body {
            padding: 40px;
        }

        /* Form layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row.single {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #333;
            transition: border-color 0.2s ease;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            background: white;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        .section-divider {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 28px 0;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 16px;
        }

        /* Error */
        .form-error {
            display: none;
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* Submit button */
        .btn-submit {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Success state */
        .quote-success {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .quote-success i {
            color: #4CAF50;
            font-size: 3rem;
            margin-bottom: 16px;
            display: block;
        }

        .quote-success p {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 8px;
        }

        .quote-success small {
            color: #666;
            font-size: 0.95rem;
            display: block;
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

        /* Mobile */
        @media (max-width: 768px) {
            .header-content nav {
                display: none;
            }

            .mobile-menu {
                display: flex;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .quote-card-body {
                padding: 25px 20px;
            }

            .quote-card-header {
                padding: 25px 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="premium.css?v=1">
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
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="pricing.html">Pricing</a></li>
                        <li><a href="index.php#fleet">Fleet</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
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
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="pricing.html">Pricing</a></li>
                    <li><a href="index.php#fleet">Fleet</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="booking.php" class="reserve-btn">Reserve Now</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="page-body">
        <div class="container">
            <div class="quote-card">
                <div class="quote-card-header">
                    <h1>Request a Quote</h1>
                    <p>We'll call you back shortly with a personalized quote for your trip.</p>
                </div>
                <div class="quote-card-body">
                    <div id="quoteFormWrap">
                        <p class="section-label">Your Trip</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fromLocation">From</label>
                                <input type="text" id="fromLocation" placeholder="e.g. Monsey, Brooklyn..." value="<?= $pickup ?>" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="toLocation">To</label>
                                <input type="text" id="toLocation" placeholder="e.g. JFK Airport, Manhattan..." value="<?= $dropoff ?>" required autocomplete="off">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="requestedDate">Date</label>
                                <input type="date" id="requestedDate" value="<?= $date ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="timeSlot">Time Slot</label>
                                <select id="timeSlot" required>
                                    <option value="">Select time</option>
                                    <option value="Morning (6AM - 12PM)"<?= $timeSlot === 'Morning (6AM - 12PM)' ? ' selected' : '' ?>>Morning (6AM - 12PM)</option>
                                    <option value="Afternoon (12PM - 6PM)"<?= $timeSlot === 'Afternoon (12PM - 6PM)' ? ' selected' : '' ?>>Afternoon (12PM - 6PM)</option>
                                    <option value="Evening (6PM - 12AM)"<?= $timeSlot === 'Evening (6PM - 12AM)' ? ' selected' : '' ?>>Evening (6PM - 12AM)</option>
                                    <option value="Late Night (12AM - 6AM)"<?= $timeSlot === 'Late Night (12AM - 6AM)' ? ' selected' : '' ?>>Late Night (12AM - 6AM)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label for="tripType">Trip Type</label>
                                <select id="tripType" required>
                                    <option value="">Select type</option>
                                    <option value="One Way"<?= $tripType === 'One Way' ? ' selected' : '' ?>>One Way</option>
                                    <option value="Round Trip"<?= $tripType === 'Round Trip' ? ' selected' : '' ?>>Round Trip</option>
                                </select>
                            </div>
                        </div>

                        <hr class="section-divider">
                        <p class="section-label">Your Contact Info</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" placeholder="Your name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" placeholder="Best number to reach you" required>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" placeholder="Your email address" required>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label for="notes">Notes <span style="font-weight:400; color:#999;">(optional)</span></label>
                                <textarea id="notes" placeholder="Anything we should know? Passenger count, luggage, etc."></textarea>
                            </div>
                        </div>

                        <div class="form-error" id="formError"></div>

                        <button type="button" class="btn-submit" id="submitBtn" onclick="submitQuote()">Request a Quote</button>
                    </div>

                    <div class="quote-success" id="quoteSuccess">
                        <i class="fas fa-check-circle"></i>
                        <p>Thanks for reaching out, someone will reach out to you shortly.</p>
                        <small>We'll be in touch within a few hours during business hours.</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#fleet">Our Fleet</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
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
        // Google Places autocomplete for From/To fields on this page
        function initContactAutocomplete() {
            const fromInput = document.getElementById('fromLocation');
            const toInput = document.getElementById('toLocation');

            if (fromInput) {
                const acFrom = new google.maps.places.Autocomplete(fromInput);
                acFrom.setFields(['formatted_address']);
                acFrom.setComponentRestrictions({ country: ['us', 'ca'] });
            }

            if (toInput) {
                const acTo = new google.maps.places.Autocomplete(toInput);
                acTo.setFields(['formatted_address']);
                acTo.setComponentRestrictions({ country: ['us', 'ca'] });
            }
        }

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const dateField = document.getElementById('requestedDate');
            if (dateField && !dateField.value) {
                dateField.min = new Date().toISOString().split('T')[0];
            } else if (dateField) {
                dateField.min = new Date().toISOString().split('T')[0];
            }
        });

        async function submitQuote() {
            const fromLocation  = document.getElementById('fromLocation').value.trim();
            const toLocation    = document.getElementById('toLocation').value.trim();
            const requestedDate = document.getElementById('requestedDate').value;
            const timeSlot      = document.getElementById('timeSlot').value;
            const tripType      = document.getElementById('tripType').value;
            const name          = document.getElementById('name').value.trim();
            const phone         = document.getElementById('phone').value.trim();
            const email         = document.getElementById('email').value.trim();
            const notes         = document.getElementById('notes').value.trim();

            const errorEl  = document.getElementById('formError');
            const submitBtn = document.getElementById('submitBtn');

            function showError(msg) {
                errorEl.textContent = msg;
                errorEl.style.display = 'block';
                errorEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            errorEl.style.display = 'none';

            if (!fromLocation || !toLocation || !requestedDate || !timeSlot || !tripType) {
                showError('Please fill in all trip details (From, To, Date, Time, and Trip Type).');
                return;
            }

            if (!name || !phone || !email) {
                showError('Please fill in your name, phone, and email.');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Please enter a valid email address.');
                return;
            }

            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('contact-handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, phone, email, fromLocation, toLocation, requestedDate, timeSlot, tripType, notes })
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('quoteFormWrap').style.display = 'none';
                    document.getElementById('quoteSuccess').style.display = 'block';
                } else {
                    showError(result.error || 'Something went wrong. Please call us at 845-642-1317.');
                    submitBtn.textContent = 'Request a Quote';
                    submitBtn.disabled = false;
                }
            } catch (err) {
                showError('Could not send your request. Please call us at 845-642-1317.');
                submitBtn.textContent = 'Request a Quote';
                submitBtn.disabled = false;
            }
        }

        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileNav = document.getElementById('mobileNav');

        if (mobileMenuToggle && mobileNav) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileNav.classList.toggle('open');
            });
        }
    </script>
</body>
</html>

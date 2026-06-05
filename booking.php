<?php require_once 'config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Ride Online - Monsey, Brooklyn, Lakewood &amp; Airport Car Service | Direct Car Service</title>
    <meta name="description" content="Book your car service online in minutes. Reliable rides from Monsey, Brooklyn &amp; Lakewood to JFK, LaGuardia, Newark, the Catskills, Montreal and beyond. 24/6 service. Call 845-642-1317.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://directcsny.com/booking">
    <link rel="icon" type="image/png" href="./images/logo.png">
    <link rel="apple-touch-icon" href="./images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        /* ── Page-wide 90% zoom ── */
        html {
            zoom: 0.9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('./images/road.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
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
        }

        nav a:hover {
            color: #4CAF50;
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
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .mobile-nav.open {
            display: block;
        }

        .mobile-nav ul {
            flex-direction: column;
            padding: 20px;
            gap: 1rem;
        }

        .mobile-nav a {
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Main Content */
        .main-content {
            margin-top: 100px;
            padding: 40px 0 80px;
        }

        .booking-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .booking-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .booking-header h1 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .booking-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Progress Bar */
        .progress-container {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }

        .progress-line {
            position: absolute;
            top: 50%;
            left: 0;
            height: 2px;
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
            transition: width 0.5s ease;
            z-index: 2;
        }

        .progress-step {
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            z-index: 3;
            transition: all 0.3s ease;
        }

        .progress-step.active {
            border-color: #4CAF50;
            color: #4CAF50;
        }

        .progress-step.completed {
            background: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }

        /* Form Steps */
        .form-content {
            padding: 40px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Poppins', sans-serif;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 70px;
        }
        
        .date-input-wrapper {
            position: relative;
            cursor: pointer;
        }

        .date-input-wrapper input[type="date"] {
            cursor: pointer;
            background: white;
            /* Shift text left to make room for green icon */
            padding-right: 44px;
        }

        /* Hide the default browser calendar icon — we use our own green one */
        .date-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .date-input-wrapper::after {
            content: '\f073';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #4CAF50;
            pointer-events: none;
            z-index: 1;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row-extended {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            align-items: end;
        }

        /* Vehicle Selection Styles */
        .vehicle-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .vehicle-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: white;
        }

        .vehicle-card:hover {
            border-color: #4CAF50;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .vehicle-card.selected {
            border-color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
        }

        .vehicle-card i {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .vehicle-card h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .vehicle-card p {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .vehicle-price {
            background: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stops-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }

        .stops-btn {
            background: #4CAF50;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stops-btn:hover {
            background: #45a049;
            transform: scale(1.1);
        }

        .stops-display {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            min-width: 40px;
            text-align: center;
        }

        .stops-note {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Quote Display */
        .quote-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .route-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .route-point {
            text-align: center;
            flex: 1;
        }

        .route-point i {
            color: #4CAF50;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .route-point h4 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .route-point p {
            color: #666;
            font-size: 0.9rem;
        }

        .route-arrow {
            color: #4CAF50;
            font-size: 1.5rem;
            margin: 0 20px;
        }

        .price-breakdown {
            margin-bottom: 20px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }

        .price-item.total {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }

        .total-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
            margin: 20px 0;
        }

        .total-price::before {
            content: '$';
            font-size: 1.8rem;
            margin-right: 2px;
        }

        /* Payment Step Styles */
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #4CAF50;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .payment-option.selected {
            border-color: #4CAF50;
            background: rgba(76, 175, 80, 0.05);
        }

        .payment-option h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-option p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
        }

        .fee-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .fee-notice p {
            margin: 0;
            color: #856404;
            font-size: 0.95rem;
        }

        #card-element {
            border: 2px solid #e9ecef;
            padding: 15px;
            border-radius: 10px;
            background: white;
            margin: 20px 0;
        }

        #card-errors {
            color: #dc3545;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .payment-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .payment-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .payment-line.total {
            font-weight: 600;
            font-size: 1.2rem;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
        }

        .processing-payment {
            text-align: center;
            padding: 40px;
        }

        .processing-payment i {
            font-size: 3rem;
            color: #4CAF50;
            animation: spin 1s linear infinite;
        }

        /* Buttons */
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            background: #6c757d;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: modalSlideIn 0.3s ease;
            overflow: hidden;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .modal-header i {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: checkPulse 0.6s ease;
        }

        @keyframes checkPulse {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-body p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .booking-ref-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }

        .booking-ref-display h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .booking-ref-display .ref-number {
            font-size: 1.3rem;
            font-weight: bold;
            color: #4CAF50;
            font-family: 'Courier New', monospace;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-home {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            flex: 1;
            text-align: center;
        }

        .btn-home:hover {
            background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        }

        .btn-close-modal {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-close-modal:hover {
            background: #5a6268;
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

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #34495e;
            color: #bbb;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }

        .loading {
            display: none;
            text-align: center;
            color: #4CAF50;
            margin: 20px 0;
        }

        .loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Google Places Autocomplete styling */
        .pac-container {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: none;
            margin-top: 5px;
            z-index: 10000;
        }

        .pac-item {
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .pac-item:hover {
            background-color: #f8f9fa;
        }

        .pac-item-selected {
            background-color: #4CAF50 !important;
            color: white !important;
        }

        /* ── Custom Searchable Dropdown ── */
        /* Hide native select on desktop; show custom trigger instead */
        .searchable-select-wrapper {
            position: relative;
        }

        /* On desktop we hide the native <select> entirely */
        @media (min-width: 769px) {
            .searchable-select-wrapper .native-select {
                display: none;
            }
        }

        /* On mobile we show only the native select, hide the custom UI */
        @media (max-width: 768px) {
            .searchable-select-wrapper .custom-dropdown-trigger,
            .searchable-select-wrapper .custom-dropdown-popover {
                display: none !important;
            }
            .searchable-select-wrapper .native-select {
                display: block;
            }
        }

        /* The trigger button — matches existing .form-group input styling */
        .custom-dropdown-trigger {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
            text-align: left;
            color: #333;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            -webkit-appearance: none;
            appearance: none;
        }

        .custom-dropdown-trigger:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .custom-dropdown-trigger.open {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .custom-dropdown-trigger .trigger-text {
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .custom-dropdown-trigger .trigger-text.placeholder {
            color: #aaa;
        }

        .custom-dropdown-trigger .trigger-arrow {
            flex-shrink: 0;
            color: #999;
            font-size: 0.75rem;
            transition: transform 0.2s ease;
        }

        .custom-dropdown-trigger.open .trigger-arrow {
            transform: rotate(180deg);
        }

        /* Popover panel */
        .custom-dropdown-popover {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            z-index: 5000;
            display: none;
            overflow: hidden;
        }

        .custom-dropdown-popover.open {
            display: block;
        }

        /* Search input inside popover */
        .custom-dropdown-search-wrap {
            padding: 10px 10px 8px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-dropdown-search-wrap i {
            color: #aaa;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .custom-dropdown-search {
            flex: 1;
            border: none;
            outline: none;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: transparent;
            padding: 2px 0;
        }

        .custom-dropdown-search::placeholder {
            color: #bbb;
        }

        /* Options list */
        .custom-dropdown-list {
            max-height: 220px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .custom-dropdown-option {
            padding: 11px 14px;
            cursor: pointer;
            font-size: 0.95rem;
            color: #333;
            transition: background 0.15s ease;
            border-bottom: 1px solid #f8f8f8;
        }

        .custom-dropdown-option:last-child {
            border-bottom: none;
        }

        .custom-dropdown-option:hover,
        .custom-dropdown-option.focused {
            background: #f0faf0;
            color: #2c3e50;
        }

        .custom-dropdown-option.selected {
            background: rgba(76, 175, 80, 0.1);
            color: #2c3e50;
            font-weight: 500;
        }

        .custom-dropdown-empty {
            padding: 14px;
            text-align: center;
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-content nav {
                display: none;
            }

            .mobile-menu {
                display: flex;
            }

            .booking-container {
                margin: 10px;
                border-radius: 15px;
                max-width: calc(100% - 20px);
            }

            .form-content {
                padding: 25px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-row-extended {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .vehicle-options {
                grid-template-columns: 1fr;
            }

            .route-info {
                flex-direction: column;
                gap: 15px;
            }

            .route-arrow {
                transform: rotate(90deg);
                margin: 10px 0;
            }

            .button-group {
                flex-direction: column;
            }

            .progress-container {
                padding: 20px 15px;
            }

            .booking-header {
                padding: 25px 20px;
            }

            .booking-header h1 {
                font-size: 1.8rem;
            }
        }

    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="/index" style="color: inherit; text-decoration: none; display: flex; align-items: center;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #4CAF50;"></i>
                        Direct Car Service
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="/index">Home</a></li>
                        <li><a href="/index#about">About</a></li>
                        <li><a href="/pricing">Pricing</a></li>
                        <li><a href="/index#fleet">Fleet</a></li>
                        <li><a href="/index#contact">Contact</a></li>
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
                    <li><a href="/index">Home</a></li>
                    <li><a href="/index#about">About</a></li>
                    <li><a href="/pricing">Pricing</a></li>
                    <li><a href="/index#fleet">Fleet</a></li>
                    <li><a href="/index#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="booking-container">
                <div class="booking-header">
                    <h1>Book Your Ride</h1>
                    <p>Safe, comfortable, and reliable transportation</p>
                </div>

                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-line" id="progressLine"></div>
                        <div class="progress-step active" id="step1">1</div>
                        <div class="progress-step" id="step2">2</div>
                        <div class="progress-step" id="step3">3</div>
                        <div class="progress-step" id="step4">4</div>
                        <div class="progress-step" id="step5">5</div>
                    </div>
                </div>

                <div class="form-content">
                    <div class="step active" id="stepContent1">
                        <h2>Trip Details</h2>
                        <form id="tripForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="pickup">From</label>
                                    <div class="searchable-select-wrapper" id="pickupWrapper">
                                        <!-- Native select (mobile only) -->
                                        <select id="pickup" name="pickup" required class="native-select">
                                            <option value="">Select pickup location</option>
                                        </select>
                                        <!-- Custom dropdown (desktop) -->
                                        <button type="button" class="custom-dropdown-trigger" id="pickupTrigger" aria-haspopup="listbox" aria-expanded="false">
                                            <span class="trigger-text placeholder" id="pickupTriggerText">Select pickup location</span>
                                            <i class="fas fa-chevron-down trigger-arrow"></i>
                                        </button>
                                        <div class="custom-dropdown-popover" id="pickupPopover" role="listbox">
                                            <div class="custom-dropdown-search-wrap">
                                                <i class="fas fa-search"></i>
                                                <input type="text" class="custom-dropdown-search" id="pickupSearch" placeholder="Search locations..." autocomplete="off">
                                            </div>
                                            <div class="custom-dropdown-list" id="pickupList"></div>
                                        </div>
                                    </div>
                                    <div class="error-message" id="pickupError">Please select a pickup location</div>
                                </div>
                                <div class="form-group">
                                    <label for="dropoff">To</label>
                                    <div class="searchable-select-wrapper" id="dropoffWrapper">
                                        <!-- Native select (mobile only) -->
                                        <select id="dropoff" name="dropoff" required class="native-select">
                                            <option value="">Select destination</option>
                                        </select>
                                        <!-- Custom dropdown (desktop) -->
                                        <button type="button" class="custom-dropdown-trigger" id="dropoffTrigger" aria-haspopup="listbox" aria-expanded="false">
                                            <span class="trigger-text placeholder" id="dropoffTriggerText">Select destination</span>
                                            <i class="fas fa-chevron-down trigger-arrow"></i>
                                        </button>
                                        <div class="custom-dropdown-popover" id="dropoffPopover" role="listbox">
                                            <div class="custom-dropdown-search-wrap">
                                                <i class="fas fa-search"></i>
                                                <input type="text" class="custom-dropdown-search" id="dropoffSearch" placeholder="Search destinations..." autocomplete="off">
                                            </div>
                                            <div class="custom-dropdown-list" id="dropoffList"></div>
                                        </div>
                                    </div>
                                    <div class="error-message" id="dropoffError">Please select a destination</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="pickupNotes">Exact pickup address / notes for driver <span style="font-weight:400; color:#888;">(optional)</span></label>
                                <textarea id="pickupNotes" name="pickupNotes" rows="2" placeholder="e.g. 45 Main St, Apt 3B — or any details helpful for the driver"></textarea>
                            </div>

                            <div style="text-align:center; margin-bottom:20px;">
                                <a href="#" onclick="goToContact(); return false;" style="color:#4CAF50; font-size:0.9rem; text-decoration:none; border-bottom:1px dotted #4CAF50;">Don't see your route? Click here to request a quote</a>
                            </div>

                            <div class="form-row-extended">
                                <div class="form-group">
                                    <label for="date">Travel Date</label>
                                    <div class="date-input-wrapper">
                                        <input type="date" id="date" name="date" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="timeSlot">Preferred Time</label>
                                    <select id="timeSlot" name="timeSlot" required>
                                        <option value="">Select time slot</option>
                                        <option value="morning">Morning (6AM - 12PM)</option>
                                        <option value="afternoon">Afternoon (12PM - 6PM)</option>
                                        <option value="evening">Evening (6PM - 12AM)</option>
                                        <option value="late">Late Night (12AM - 6AM)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tripType">Trip Type</label>
                                    <select id="tripType" name="tripType" required>
                                        <option value="">Select trip type</option>
                                        <option value="oneway">One Way</option>
                                        <option value="roundtrip">Round Trip</option>
                                    </select>
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="step" id="stepContent2">
                        <h2>Vehicle & Trip Options</h2>
                        
                        <div class="form-group">
                            <label>Select Vehicle Type</label>
                            <div class="vehicle-options" id="vehicleOptions">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="extraStops">Number of Extra Stops</label>
                            <div class="stops-selector">
                                <button type="button" class="stops-btn" id="decreaseStops">-</button>
                                <span class="stops-display" id="stopsDisplay">0</span>
                                <button type="button" class="stops-btn" id="increaseStops">+</button>
                            </div>
                            <p class="stops-note">Each extra stop: $5</p>
                        </div>
                    </div>

                    <div class="step" id="stepContent3">
                        <h2>Your Quote</h2>
                        <div class="quote-summary">
                            <div class="route-info">
                                <div class="route-point">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <h4>From</h4>
                                    <p id="quotePickup">-</p>
                                </div>
                                <div class="route-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div class="route-point">
                                    <i class="fas fa-flag-checkered"></i>
                                    <h4>To</h4>
                                    <p id="quoteDropoff">-</p>
                                </div>
                            </div>

                            <div class="price-breakdown" id="priceBreakdown">
                                <!-- Will be populated by JavaScript -->
                            </div>

                            <div class="total-price" id="totalPrice">0</div>
                        </div>
                        <div id="priceError" class="error-message" style="display: none; text-align: center; font-size: 1.1rem; margin-top: 20px;">
                            Sorry, the minimum fare for a trip is $50. Please adjust your trip details.
                        </div>
                    </div>

                    <div class="step" id="stepContent4">
                        <h2>Contact Information</h2>
                        <form id="contactForm">
                            <div class="form-group">
                                <label for="customerName">Full Name</label>
                                <input type="text" id="customerName" name="name" placeholder="Enter your full name" required>
                            </div>

                            <div class="form-group">
                                <label for="customerEmail">Email</label>
                                <input type="email" id="customerEmail" name="email" placeholder="Enter your email" required>
                            </div>

                            <div class="form-group">
                                <label for="customerPhone">Phone Number</label>
                                <input type="tel" id="customerPhone" name="phone" placeholder="Enter your phone number" required>
                            </div>
                        </form>
                    </div>

                    <div class="step" id="stepContent5">
                        <h2>Payment Options</h2>
                        
                        <div class="payment-option" id="payNow" onclick="selectPaymentMethod('now')">
                            <h3><i class="fas fa-credit-card"></i> Pay Now (Card)</h3>
                            <p>Secure payment with credit/debit card</p>
                        </div>
                        
                        <div class="payment-option" id="payDriver" onclick="selectPaymentMethod('driver')">
                            <h3><i class="fas fa-money-bill-wave"></i> Pay Driver</h3>
                            <p>Pay cash or card directly to the driver</p>
                        </div>
                        
                        <div id="cardPaymentSection" style="display: none;">
                            <div class="fee-notice">
                                <p><i class="fas fa-info-circle"></i> A 3% processing fee will be added for card payments</p>
                            </div>
                            
                            <div class="payment-summary">
                                <div class="payment-line">
                                    <span>Ride Fare</span>
                                    <span id="baseFare">$0.00</span>
                                </div>
                                <div class="payment-line">
                                    <span>Processing Fee (3%)</span>
                                    <span id="processingFee">$0.00</span>
                                </div>
                                <div class="payment-line total">
                                    <span>Total Due</span>
                                    <span id="totalWithFee">$0.00</span>
                                </div>
                            </div>
                            
                            <div id="card-element">
                                <!-- Stripe card element will be inserted here -->
                            </div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                        
                        <div id="processingPayment" class="processing-payment" style="display: none;">
                            <i class="fas fa-spinner"></i>
                            <p>Processing payment...</p>
                        </div>
                    </div>

                    <div class="button-group" id="navigationButtons">
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">Get Quote</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-clock"></i>
                <h2>Reservation Submitted!</h2>
            </div>
            <div class="modal-body">
                <p id="modalMessage">Thank you for choosing Direct Car Service. Your reservation has been confirmed. Your driver will contact you before the trip to confirm the details.</p>
                
                <div class="booking-ref-display">
                    <h4>Booking Reference:</h4>
                    <div class="ref-number" id="modalBookingRef"></div>
                    <p style="font-size: 0.9rem; margin-top: 10px; color: #666;">Please save this reference number for your records.</p>
                </div>

                <div class="modal-buttons">
                    <a href="/index" class="btn-home">
                        <i class="fas fa-home" style="margin-right: 8px;"></i>
                        Return Home
                    </a>
                    <button type="button" class="btn-close-modal" onclick="closeModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-map-marker-alt" style="margin-right: 8px; color: #4CAF50;"></i>Direct Car Service</h3>
                    <p>Professional and reliable transportation service serving the New York Metro Area, Montreal, and surrounding regions.</p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul style="list-style: none;">
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
        // Global variables
        let currentStep = 1;
        let totalSteps = 5;
        let bookingData = {};

        // Payment handling variables
        let stripe = null;
        let elements = null;
        let cardElement = null;
        let selectedPaymentMethod = null;

        // Routes data will be loaded from routes.json
        let routesData = null;

        // Load routes data on page load
        async function loadRoutesData() {
            try {
                const response = await fetch('routes.json');
                routesData = await response.json();
            } catch (error) {
                // Fallback data
                routesData = {
                    "vehicle_types": {
                        "full_size_car": { "name": "Full Size Car", "capacity": 4, "description": "4 passengers" },
                        "standard_suv": { "name": "Standard SUV", "capacity": 5, "description": "5 passengers" },
                        "full_size_suv": { "name": "Full Size/Premium SUV", "capacity": 8, "description": "7/8 passengers" },
                        "minivan": { "name": "Minivan", "capacity": 7, "description": "7 passengers" },
                        "sprinter": { "name": "Sprinter", "capacity": 15, "description": "Large group" }
                    },
                    "routes": [],
                    "pricing_rules": {
                        "time_rate_per_hour": 75,
                        "rate_per_mile": 0.65,
                        "round_trip_multiplier": 1.4,
                        "vehicle_multipliers": {
                            "full_size_car": 1.0,
                            "standard_suv": 1.15,
                            "full_size_suv": 1.30,
                            "minivan": 1.20,
                            "sprinter": 1.80
                        },
                        "stop_fees": { "short_stop": 5.00, "one_hour_stop": 40.00 },
                        "surcharges": { "late_hours": 30.00 }
                    }
                };
            }
        }

        // Normalize a location string for dedup comparison (lowercase + trim)
        function normalizeLocation(str) {
            return str.trim().toLowerCase();
        }

        // Pick the better display label when two entries normalize to the same key
        function pickBestLabel(a, b) {
            if (a.length !== b.length) return a.length > b.length ? a : b;
            // Same length — prefer the one with more uppercase (better title casing)
            const upperA = (a.match(/[A-Z]/g) || []).length;
            const upperB = (b.match(/[A-Z]/g) || []).length;
            return upperA >= upperB ? a : b;
        }

        // Build the From dropdown with all unique locations from routes.json
        // Populates both the native <select> (mobile) and the custom dropdown list (desktop)
        function buildFromDropdown() {
            const pickupSelect = document.getElementById('pickup');
            const seen = new Map();

            routesData.routes.forEach(route => {
                [route.from, route.to].forEach(loc => {
                    const key = normalizeLocation(loc);
                    const candidate = loc.trim();
                    if (!seen.has(key)) {
                        seen.set(key, candidate);
                    } else {
                        seen.set(key, pickBestLabel(seen.get(key), candidate));
                    }
                });
            });

            const sorted = Array.from(seen.entries()).sort((a, b) => a[1].localeCompare(b[1]));

            // Populate native <select>
            sorted.forEach(([, display]) => {
                const opt = document.createElement('option');
                opt.value = display;
                opt.textContent = display;
                pickupSelect.appendChild(opt);
            });

            // Populate custom dropdown list
            renderCustomList('pickupList', sorted.map(([, d]) => d), null);
        }

        // Build (or rebuild) the To dropdown based on the selected From value
        function buildToDropdown(selectedFrom) {
            const dropoffSelect = document.getElementById('dropoff');
            const fromNorm = normalizeLocation(selectedFrom);

            dropoffSelect.innerHTML = '<option value="">Select destination</option>';

            if (!selectedFrom) {
                renderCustomList('dropoffList', [], null);
                setCustomDropdownValue('dropoff', '', 'Select destination');
                return;
            }

            const seen = new Map();

            routesData.routes.forEach(route => {
                const fromN = normalizeLocation(route.from);
                const toN = normalizeLocation(route.to);

                let candidate = null;
                if (fromN === fromNorm) {
                    candidate = route.to.trim();
                } else if (toN === fromNorm) {
                    candidate = route.from.trim();
                }

                if (candidate) {
                    const key = normalizeLocation(candidate);
                    if (!seen.has(key)) {
                        seen.set(key, candidate);
                    } else {
                        seen.set(key, pickBestLabel(seen.get(key), candidate));
                    }
                }
            });

            const sorted = Array.from(seen.entries()).sort((a, b) => a[1].localeCompare(b[1]));

            // Populate native <select>
            sorted.forEach(([, display]) => {
                const opt = document.createElement('option');
                opt.value = display;
                opt.textContent = display;
                dropoffSelect.appendChild(opt);
            });

            // Populate custom dropdown list (reset any prior selection)
            renderCustomList('dropoffList', sorted.map(([, d]) => d), null);
        }

        // ── Custom Searchable Dropdown helpers ──

        // Render option items into a custom list element
        // allOptions: string[], selectedValue: string|null
        // Filters by the current search text in the sibling search input
        function renderCustomList(listId, allOptions, selectedValue) {
            const list = document.getElementById(listId);
            if (!list) return;

            // Determine search text from sibling input
            const isPickup = listId === 'pickupList';
            const searchId = isPickup ? 'pickupSearch' : 'dropoffSearch';
            const searchEl = document.getElementById(searchId);
            const query = searchEl ? searchEl.value.toLowerCase().trim() : '';

            const filtered = query
                ? allOptions.filter(o => o.toLowerCase().includes(query))
                : allOptions;

            if (filtered.length === 0) {
                list.innerHTML = '<div class="custom-dropdown-empty">No matches found</div>';
                return;
            }

            list.innerHTML = filtered.map(opt => {
                const isSel = selectedValue && normalizeLocation(opt) === normalizeLocation(selectedValue);
                return `<div class="custom-dropdown-option${isSel ? ' selected' : ''}" data-value="${opt.replace(/"/g, '&quot;')}">${opt}</div>`;
            }).join('');
        }

        // Set the visual state of a custom dropdown trigger after a value is chosen
        function setCustomDropdownValue(which, value, label) {
            const triggerId = which === 'pickup' ? 'pickupTrigger' : 'dropoffTrigger';
            const textId = which === 'pickup' ? 'pickupTriggerText' : 'dropoffTriggerText';
            const trigger = document.getElementById(triggerId);
            const textEl = document.getElementById(textId);
            if (!trigger || !textEl) return;

            if (value) {
                textEl.textContent = label || value;
                textEl.classList.remove('placeholder');
            } else {
                textEl.textContent = label || (which === 'pickup' ? 'Select pickup location' : 'Select destination');
                textEl.classList.add('placeholder');
            }
            trigger.setAttribute('aria-expanded', 'false');
        }

        // Open/close a specific popover
        function openCustomDropdown(which) {
            const triggerId = which === 'pickup' ? 'pickupTrigger' : 'dropoffTrigger';
            const popoverId = which === 'pickup' ? 'pickupPopover' : 'dropoffPopover';
            const searchId = which === 'pickup' ? 'pickupSearch' : 'dropoffSearch';
            const trigger = document.getElementById(triggerId);
            const popover = document.getElementById(popoverId);
            if (!trigger || !popover) return;

            trigger.classList.add('open');
            trigger.setAttribute('aria-expanded', 'true');
            popover.classList.add('open');

            const searchEl = document.getElementById(searchId);
            if (searchEl) {
                searchEl.value = '';
                searchEl.focus();
            }
        }

        function closeCustomDropdown(which) {
            const triggerId = which === 'pickup' ? 'pickupTrigger' : 'dropoffTrigger';
            const popoverId = which === 'pickup' ? 'pickupPopover' : 'dropoffPopover';
            const trigger = document.getElementById(triggerId);
            const popover = document.getElementById(popoverId);
            if (trigger) { trigger.classList.remove('open'); trigger.setAttribute('aria-expanded', 'false'); }
            if (popover) popover.classList.remove('open');
        }

        function closeAllCustomDropdowns() {
            closeCustomDropdown('pickup');
            closeCustomDropdown('dropoff');
        }

        // Wire all custom dropdown interactions
        function setupCustomDropdowns() {
            // ── Pickup trigger ──
            document.getElementById('pickupTrigger').addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = document.getElementById('pickupPopover').classList.contains('open');
                closeAllCustomDropdowns();
                if (!isOpen) openCustomDropdown('pickup');
            });

            // ── Pickup search ──
            document.getElementById('pickupSearch').addEventListener('input', function() {
                const currentVal = document.getElementById('pickup').value;
                const allOptions = Array.from(document.getElementById('pickup').options)
                    .filter(o => o.value).map(o => o.value);
                renderCustomList('pickupList', allOptions, currentVal);
            });

            // ── Pickup list clicks ──
            document.getElementById('pickupList').addEventListener('click', function(e) {
                const opt = e.target.closest('.custom-dropdown-option');
                if (!opt) return;
                const value = opt.dataset.value;
                // Sync to native select
                document.getElementById('pickup').value = value;
                // Update trigger display
                setCustomDropdownValue('pickup', value, value);
                // Close popover
                closeCustomDropdown('pickup');
                // Rebuild To dropdown (same as native change event)
                buildToDropdown(value);
                // Hide any prior error
                document.getElementById('pickupError').style.display = 'none';
            });

            // ── Dropoff trigger ──
            document.getElementById('dropoffTrigger').addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = document.getElementById('dropoffPopover').classList.contains('open');
                closeAllCustomDropdowns();
                if (!isOpen) openCustomDropdown('dropoff');
            });

            // ── Dropoff search ──
            document.getElementById('dropoffSearch').addEventListener('input', function() {
                const currentVal = document.getElementById('dropoff').value;
                const allOptions = Array.from(document.getElementById('dropoff').options)
                    .filter(o => o.value).map(o => o.value);
                renderCustomList('dropoffList', allOptions, currentVal);
            });

            // ── Dropoff list clicks ──
            document.getElementById('dropoffList').addEventListener('click', function(e) {
                const opt = e.target.closest('.custom-dropdown-option');
                if (!opt) return;
                const value = opt.dataset.value;
                document.getElementById('dropoff').value = value;
                setCustomDropdownValue('dropoff', value, value);
                closeCustomDropdown('dropoff');
                document.getElementById('dropoffError').style.display = 'none';
            });

            // ── Close on outside click ──
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#pickupWrapper') && !e.target.closest('#dropoffWrapper')) {
                    closeAllCustomDropdowns();
                }
            });

            // ── Close on Escape ──
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeAllCustomDropdowns();
            });

            // ── Mobile: sync native select → bookingData ──
            document.getElementById('pickup').addEventListener('change', function() {
                if (window.innerWidth <= 768) {
                    buildToDropdown(this.value);
                }
            });
        }

        // Wire the From dropdown change event
        function setupLocationDropdowns() {
            buildFromDropdown();
            setupCustomDropdowns();
        }

        // Initialize Stripe when reaching payment step
        function initializeStripe() {
            // Stripe publishable key is loaded from config/config.php (see config/README.md)
            stripe = Stripe('<?= STRIPE_PUBLISHABLE_KEY ?>');
            elements = stripe.elements();
            
            const style = {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                }
            };
            
            cardElement = elements.create('card', { style });
            cardElement.mount('#card-element');
            
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }

        // Select payment method
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            document.getElementById('payNow').classList.remove('selected');
            document.getElementById('payDriver').classList.remove('selected');
            
            if (method === 'now') {
                document.getElementById('payNow').classList.add('selected');
                document.getElementById('cardPaymentSection').style.display = 'block';
                
                // Calculate fees
                const baseFare = bookingData.totalPrice || 0;
                const processingFee = baseFare * 0.03; // 3% fee
                const totalWithFee = baseFare + processingFee;
                
                document.getElementById('baseFare').textContent = `$${baseFare.toFixed(2)}`;
                document.getElementById('processingFee').textContent = `$${processingFee.toFixed(2)}`;
                document.getElementById('totalWithFee').textContent = `$${totalWithFee.toFixed(2)}`;
                
                bookingData.processingFee = processingFee;
                bookingData.totalWithFee = totalWithFee;
                
                // Initialize Stripe if not already done
                if (!stripe) {
                    initializeStripe();
                }
            } else {
                document.getElementById('payDriver').classList.add('selected');
                document.getElementById('cardPaymentSection').style.display = 'none';
            }
        }

        // Process URL parameters from quick booking or pricing page
        function processURLParameters() {
            const urlParams = new URLSearchParams(window.location.search);

            // Pre-fill pickup/dropoff from URL params — values must match a dropdown option
            if (urlParams.get('pickup')) {
                const pickupValue = decodeURIComponent(urlParams.get('pickup'));
                document.getElementById('pickup').value = pickupValue;
                if (document.getElementById('pickup').value) {
                    buildToDropdown(pickupValue);
                    setCustomDropdownValue('pickup', pickupValue, pickupValue);
                    bookingData.pickup = pickupValue;
                }
            }
            if (urlParams.get('dropoff')) {
                const dropoffValue = decodeURIComponent(urlParams.get('dropoff'));
                document.getElementById('dropoff').value = dropoffValue;
                if (document.getElementById('dropoff').value) {
                    setCustomDropdownValue('dropoff', dropoffValue, dropoffValue);
                    bookingData.dropoff = dropoffValue;
                }
            }
            if (urlParams.get('date')) {
                document.getElementById('date').value = urlParams.get('date');
                bookingData.date = urlParams.get('date');
            }
            if (urlParams.get('timeSlot')) {
                document.getElementById('timeSlot').value = urlParams.get('timeSlot');
                bookingData.timeSlot = urlParams.get('timeSlot');
            }
            if (urlParams.get('tripType')) {
                document.getElementById('tripType').value = urlParams.get('tripType');
                bookingData.tripType = urlParams.get('tripType');
            }

            // Only skip to step 2 if explicitly requested AND all required fields are present
            if (urlParams.get('step') === '2' && bookingData.pickup && bookingData.dropoff &&
                bookingData.date && bookingData.timeSlot && bookingData.tripType) {
                currentStep = 2;
                updateStepDisplay();
                updateProgressBar();
            }
        }

        // DOM Content Loaded
        document.addEventListener('DOMContentLoaded', async function() {
            // Load routes data first
            await loadRoutesData();

            const dateInput = document.getElementById('date');
            dateInput.min = new Date().toISOString().split('T')[0];

            // Make the entire date field box clickable (not just the icon)
            const dateWrapper = document.querySelector('.date-input-wrapper');
            if (dateWrapper) {
                dateWrapper.addEventListener('click', e => {
                    // Focus the input; showPicker() opens the calendar if supported
                    dateInput.focus();
                    if (typeof dateInput.showPicker === 'function') {
                        try { dateInput.showPicker(); } catch (_) { /* older browsers — focus is enough */ }
                    }
                });
            }

            setupLocationDropdowns();
            setupVehicleSelection();
            setupStopsControls();
            setupMobileMenu();
            updateProgressBar();

            // Process URL parameters LAST so everything is set up first
            processURLParameters();
        });

        function setupVehicleSelection() {
            const vehicleOptions = document.getElementById('vehicleOptions');
            const vehicleTypes = routesData.vehicle_types;
            const vehicleIcons = { 
                'full_size_car': 'fas fa-car', 
                'standard_suv': 'fas fa-car-side', 
                'full_size_suv': 'fas fa-truck', 
                'minivan': 'fas fa-shuttle-van', 
                'sprinter': 'fas fa-bus' 
            };
            
            vehicleOptions.innerHTML = Object.keys(vehicleTypes).map(key => 
                `<div class="vehicle-card" data-vehicle="${key}">
                    <i class="${vehicleIcons[key] || 'fas fa-car'}"></i>
                    <h4>${vehicleTypes[key].name}</h4>
                    <p>${vehicleTypes[key].description}</p>
                    <div class="vehicle-price">Select</div>
                </div>`
            ).join('');
            
            document.querySelectorAll('.vehicle-card').forEach(card => 
                card.addEventListener('click', function() {
                    document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    bookingData.vehicleType = this.dataset.vehicle;
                })
            );
        }

        function setupStopsControls() {
            const decreaseStops = document.getElementById('decreaseStops');
            const increaseStops = document.getElementById('increaseStops');
            const stopsDisplay = document.getElementById('stopsDisplay');
            
            if (decreaseStops && increaseStops && stopsDisplay) {
                decreaseStops.addEventListener('click', () => { 
                    let count = parseInt(stopsDisplay.textContent); 
                    if (count > 0) { 
                        stopsDisplay.textContent = count - 1; 
                        bookingData.extraStops = count - 1; 
                    } 
                });
                
                increaseStops.addEventListener('click', () => { 
                    let count = parseInt(stopsDisplay.textContent); 
                    if (count < 10) { 
                        stopsDisplay.textContent = count + 1; 
                        bookingData.extraStops = count + 1; 
                    } 
                });
            }
        }

        function setupMobileMenu() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileNav = document.getElementById('mobileNav');
            if (mobileMenuToggle && mobileNav) {
                mobileMenuToggle.addEventListener('click', () => mobileNav.classList.toggle('open'));
            }
        }

        function changeStep(direction) {
            if (direction === 1 && !validateCurrentStep()) return;
            
            if (currentStep === 3 && direction === -1) {
                document.getElementById('nextBtn').disabled = false;
                document.getElementById('priceError').style.display = 'none';
            }

            const oldStep = currentStep;
            currentStep += direction;
            if (currentStep < 1) currentStep = 1;
            if (currentStep > totalSteps) currentStep = totalSteps;
            updateStepDisplay();
            updateProgressBar();
            if (currentStep === 3 && oldStep === 2) calculateQuote();
        }

        function updateStepDisplay() {
            document.querySelectorAll('.step').forEach(step => step.classList.remove('active'));
            document.getElementById(`stepContent${currentStep}`).classList.add('active');
            
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
            
            if (currentStep === totalSteps) { 
                if (selectedPaymentMethod === 'now') {
                    nextBtn.textContent = 'Complete Payment';
                } else {
                    nextBtn.textContent = 'Confirm Booking';
                }
                nextBtn.onclick = confirmBooking; 
            } else if (currentStep === 1) { 
                nextBtn.textContent = 'Get Quote'; 
                nextBtn.onclick = () => changeStep(1); 
            } else { 
                nextBtn.textContent = 'Next'; 
                nextBtn.onclick = () => changeStep(1); 
            }
        }

        function updateProgressBar() {
            const progressLine = document.getElementById('progressLine');
            progressLine.style.width = ((currentStep - 1) / (totalSteps - 1)) * 100 + '%';
            
            for (let i = 1; i <= totalSteps; i++) {
                const stepElement = document.getElementById(`step${i}`);
                stepElement.classList.remove('active', 'completed');
                if (i < currentStep) stepElement.classList.add('completed');
                else if (i === currentStep) stepElement.classList.add('active');
            }
        }

        function validateCurrentStep() {
            switch (currentStep) {
                case 1: return validateStep1();
                case 2: return validateStep2();
                case 3: return true;
                case 4: return validateStep4();
                case 5: return validateStep5();
                default: return true;
            }
        }

        function validateStep1() {
            let isValid = true;
            const fields = {
                pickup: document.getElementById('pickup').value,
                dropoff: document.getElementById('dropoff').value,
                date: document.getElementById('date').value,
                timeSlot: document.getElementById('timeSlot').value,
                tripType: document.getElementById('tripType').value,
                pickupNotes: document.getElementById('pickupNotes').value.trim()
            };

            if (!fields.pickup) {
                document.getElementById('pickupError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('pickupError').style.display = 'none';
            }

            if (!fields.dropoff) {
                document.getElementById('dropoffError').style.display = 'block';
                isValid = false;
            } else {
                document.getElementById('dropoffError').style.display = 'none';
            }

            if (!fields.date || !fields.timeSlot || !fields.tripType) {
                isValid = false;
                alert('Please fill in all trip details.');
            }

            if (isValid) Object.assign(bookingData, fields);
            return isValid;
        }

        function validateStep2() {
            const vehicleType = document.querySelector('.vehicle-card.selected');
            if (!vehicleType) { 
                alert('Please select a vehicle type'); 
                return false; 
            }
            bookingData.vehicleType = vehicleType.dataset.vehicle;
            bookingData.extraStops = parseInt(document.getElementById('stopsDisplay').textContent) || 0;
            return true;
        }

        function validateStep4() {
            const name = document.getElementById('customerName').value;
            const email = document.getElementById('customerEmail').value;
            const phone = document.getElementById('customerPhone').value;
            
            if (!name || !email || !phone) { 
                alert('Please fill in all contact information'); 
                return false; 
            }
            
            Object.assign(bookingData, { 
                customerName: name, 
                customerEmail: email, 
                customerPhone: phone 
            });
            return true;
        }

        function validateStep5() {
            if (!selectedPaymentMethod) {
                alert('Please select a payment method');
                return false;
            }
            return true;
        }

        // Find a matching route by display name, bidirectionally (from→to or to→from)
        function findMatchingRoute(fromValue, toValue) {
            if (!routesData || !routesData.routes) return null;
            const fromNorm = normalizeLocation(fromValue);
            const toNorm = normalizeLocation(toValue);
            for (const route of routesData.routes) {
                const rFrom = normalizeLocation(route.from);
                const rTo = normalizeLocation(route.to);
                if ((rFrom === fromNorm && rTo === toNorm) || (rFrom === toNorm && rTo === fromNorm)) {
                    return route;
                }
            }
            return null;
        }

        function calculateQuote() {
            document.getElementById('quotePickup').textContent = bookingData.pickup;
            document.getElementById('quoteDropoff').textContent = bookingData.dropoff;

            const route = findMatchingRoute(bookingData.pickup, bookingData.dropoff);
            if (route) {
                calculatePredefinedRoutePrice(route);
            } else {
                displayNoRouteMessage();
            }
        }

        function displayNoRouteMessage() {
            const priceBreakdown = document.getElementById('priceBreakdown');
            priceBreakdown.innerHTML = '<div class="price-item" style="color:#666; text-align:center;">No set rate found for this route. Use the link below to request a custom quote.</div>';
            document.getElementById('totalPrice').textContent = '—';
            document.getElementById('nextBtn').disabled = true;
        }

        function calculatePredefinedRoutePrice(route) {
            const vehicleType = bookingData.vehicleType || 'full_size_car';
            let basePrice = route.pricing[vehicleType] || route.pricing.full_size_car || 100;

            // Round trip = one-way × 2 × 0.7 = × 1.4 (30% discount on return)
            const roundTripMultiplier = routesData.pricing_rules?.round_trip_multiplier || 1.4;
            const tripMultiplier = bookingData.tripType === 'roundtrip' ? roundTripMultiplier : 1.0;
            const extraStopsFee = (bookingData.extraStops || 0) * (routesData.pricing_rules?.stop_fees?.short_stop || 5);
            const total = (basePrice * tripMultiplier) + extraStopsFee;

            displayPriceBreakdown({
                basePrice,
                tripMultiplier,
                extraStopsFee,
                total,
                isPredefined: true,
                routeName: `${route.from} to ${route.to}`
            });

            bookingData.totalPrice = total;
            bookingData.isPredefinedRoute = true;
        }

        function displayPriceBreakdown(pricing) {
            const priceBreakdown = document.getElementById('priceBreakdown');
            let breakdown = '';

            breakdown += `<div class="price-item"><span>Route: ${pricing.routeName || 'Set Route'}</span><span>$${pricing.basePrice.toFixed(2)}</span></div>`;

            // Round trip discount display
            if (pricing.tripMultiplier > 1.0) {
                const oneWayPrice = pricing.isPredefined ? pricing.basePrice : (pricing.basePrice * (pricing.vehicleMultiplier || 1));
                const roundTripTotal = oneWayPrice * pricing.tripMultiplier;
                const savings = (oneWayPrice * 2) - roundTripTotal;
                breakdown += `<div class="price-item" style="color: #4CAF50;"><span>Round Trip (30% off return)</span><span>-$${savings.toFixed(2)} saved</span></div>`;
            }

            if (pricing.extraStopsFee > 0) {
                breakdown += `<div class="price-item"><span>Extra Stops (${bookingData.extraStops} × $5)</span><span>+$${pricing.extraStopsFee.toFixed(2)}</span></div>`;
            }

            breakdown += `<div class="price-item total"><span>Total</span><span>$${pricing.total.toFixed(2)}</span></div>`;

            priceBreakdown.innerHTML = breakdown;
            document.getElementById('totalPrice').textContent = pricing.total.toFixed(0);

            // Price threshold check
            const priceError = document.getElementById('priceError');
            const nextBtn = document.getElementById('nextBtn');
            if (pricing.total < 50) {
                priceError.style.display = 'block';
                nextBtn.disabled = true;
            } else {
                priceError.style.display = 'none';
                nextBtn.disabled = false;
            }
        }

        async function confirmBooking() {
            if (!validateStep5()) {
                return;
            }

            // Collect customer information from form
            bookingData.customerName = document.getElementById('customerName').value;
            bookingData.customerEmail = document.getElementById('customerEmail').value;
            bookingData.customerPhone = document.getElementById('customerPhone').value;

            // Generate booking reference
            const bookingRef = 'DCS' + Date.now().toString().slice(-5);
            bookingData.bookingReference = bookingRef;
            
            if (selectedPaymentMethod === 'now') {
                // Process payment with Stripe
                document.getElementById('processingPayment').style.display = 'block';
                document.getElementById('navigationButtons').style.display = 'none';
                
                try {
                    // Step 1: Create PaymentIntent on server
                    const paymentResponse = await fetch('payment-handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'create_payment_intent',
                            amount: bookingData.totalWithFee,
                            bookingRef: bookingData.bookingReference
                        })
                    });

                    const paymentData = await paymentResponse.json();

                    if (paymentData.error) {
                        throw new Error(paymentData.error.message || 'Failed to create payment');
                    }

                    // Step 2: Confirm payment with Stripe
                    const { error, paymentIntent } = await stripe.confirmCardPayment(paymentData.client_secret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: bookingData.customerName,
                                email: bookingData.customerEmail,
                                phone: bookingData.customerPhone
                            }
                        }
                    });

                    if (error) {
                        throw new Error(error.message);
                    }

                    if (paymentIntent.status === 'succeeded') {
                        // Payment succeeded
                        bookingData.paymentStatus = 'paid';
                        bookingData.paymentMethod = 'card';
                        bookingData.paymentIntentId = paymentIntent.id;

                        // Send booking to server
                        await submitBooking();
                    } else {
                        throw new Error('Payment was not completed. Status: ' + paymentIntent.status);
                    }

                } catch (err) {
                    alert('Payment failed: ' + err.message);
                    document.getElementById('processingPayment').style.display = 'none';
                    document.getElementById('navigationButtons').style.display = 'flex';
                }
            } else {
                // Pay driver directly
                bookingData.paymentStatus = 'pending';
                bookingData.paymentMethod = 'driver';
                
                // Send booking to server
                await submitBooking();
            }
        }

        // New function to submit booking to server
        async function submitBooking() {
            try {
                const response = await fetch('booking-handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                });

                const responseText = await response.text();

                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error('Server returned invalid response: ' + responseText.substring(0, 200));
                }

                if (result.success) {
                    // Update modal message based on payment method
                    if (selectedPaymentMethod === 'now') {
                        document.getElementById('modalMessage').textContent = 'Your payment has been processed successfully and your reservation is confirmed. Your driver will contact you before the trip to confirm the details.';
                    } else {
                        document.getElementById('modalMessage').textContent = 'Thank you for choosing Direct Car Service. Your reservation has been confirmed. Your driver will contact you before the trip to confirm the details.';
                    }
                    
                    // Show confirmation modal
                    showConfirmationModal();
                } else {
                    throw new Error(result.error || 'Booking submission failed');
                }
                
            } catch (error) {
                alert('Booking submission failed. Please try again or call us directly at 845-642-1317');
                
                // Reset UI
                if (selectedPaymentMethod === 'now') {
                    document.getElementById('processingPayment').style.display = 'none';
                }
                document.getElementById('navigationButtons').style.display = 'flex';
            }
        }

        function showConfirmationModal() {
            document.getElementById('modalBookingRef').textContent = bookingData.bookingReference;
            document.getElementById('confirmationModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target === modal) modal.style.display = 'none';
        }

        // "Don't see your route?" link — redirect to contact.php carrying any values already entered
        function goToContact() {
            const pickup = document.getElementById('pickup').value;
            const dropoff = document.getElementById('dropoff').value;
            const date = document.getElementById('date').value;
            const timeSlot = document.getElementById('timeSlot').value;
            const tripType = document.getElementById('tripType').value;

            const params = new URLSearchParams();
            if (pickup)   params.set('pickup',   pickup);
            if (dropoff)  params.set('dropoff',  dropoff);
            if (date)     params.set('date',      date);
            if (timeSlot) params.set('timeSlot',  timeSlot);
            if (tripType) params.set('tripType',  tripType);

            const qs = params.toString();
            window.location.href = qs ? `/contact.php?${qs}` : '/contact.php';
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY, ENT_QUOTES) ?>&libraries=places" async defer></script>
</body>
</html>
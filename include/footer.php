<html>

<head>
    <style>
        .footer-section {
            background: #1b2a4a;
            color: #ccc;
            padding: 3rem 0 1.5rem;
        }

        .footer-section h5 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #aab;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #3699ff;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 2rem;
            padding-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: #888;
        }
    </style>
</head>

<!--begin::Footer-->
<footer class="footer-section">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="/sirimace/assets/media/logos/ace.png" alt="Logo" height="36" />
                    <img src="/sirimace/assets/media/logos/sirim.jpg" alt="SIRIM" height="36" />
                </div>
                <p style="font-size:0.9rem;">SIRIM Ace is a comprehensive event attendance management platform designed for seamless event tracking and participation.</p>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="/sirimace/">Home</a></li>
                    <li><a href="/sirimace/#about">About</a></li>
                    <li><a href="/sirimace/#how-it-works">How It Works</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Resources</h5>
                <ul class="footer-links">
                    <li><a href="#">Help Centre</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Contact</h5>
                <ul class="footer-links">
                    <li><i class="fas fa-envelope me-2" style="color:#3699ff;"></i> sirimace@sirim.my</li>
                    <li><i class="fas fa-phone me-2" style="color:#3699ff;"></i> +603-5544 6000</li>
                    <li><i class="fas fa-map-marker-alt me-2" style="color:#3699ff;"></i> Shah Alam, Selangor</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2025 - <?php echo date("Y"); ?> SIRIM Ace. All rights reserved.
        </div>
    </div>
</footer>
<!--end::Footer-->
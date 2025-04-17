
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/EVENT_MANAGEMENT/mainpage.css"></a>
    <title>Event Website</title>
    
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <img src="/api/placeholder/120/40" alt="Logo" class="logo">
            <div class="nav-items">
                <div class="notification-icon">🔔</div>
                <div class="account-icon">👤</div>
            </div>
        </div>
    </header>

    <!-- Banner -->
    <div class="banner">
        <h1>Welcome to EventHub</h1>
        <img src="images/banner.jpg" alt="Banner Image" class="banner-image">
    </div>

    <!-- Main Content -->
    <main class="container">
        <!-- Recruitment Posts -->
        <h2 class="section-title">Latest Recruitment</h2>
        <div class="post-grid">
            <div class="post-card">
            <img src="img/chess.jpg" alt="Event Coordinator" class="post-image">
            <div class="post-content">
                    <h3 class="post-title">Event Coordinator</h3>
                    <p class="post-details">New York • $45-55k</p>
                </div>
            </div>
            <div class="post-card">
            <img src="img/debate.jpg" alt="Marketing Manager" class="post-image">
            <div class="post-content">
                    <h3 class="post-title">Marketing Manager</h3>
                    <p class="post-details">Los Angeles • $60-70k</p>
                </div>
            </div>
            <div class="post-card">
            <img src="images/recruitment3.jpg" alt="Technical Support" class="post-image">
            <div class="post-content">
                    <h3 class="post-title">Technical Support</h3>
                    <p class="post-details">Chicago • $40-50k</p>
                </div>
            </div>
        </div>

        <!-- Event Posts -->
        <h2 class="section-title">Upcoming Events</h2>
        <div class="post-grid">
            <div class="post-card">
                <img src="/api/placeholder/300/200" alt="Event 1" class="post-image">
                <div class="post-content">
                    <h3 class="post-title">Summer Music Festival</h3>
                    <p class="post-details">July 15, 2025 • Central Park</p>
                </div>
            </div>
            <div class="post-card">
                <img src="/api/placeholder/300/200" alt="Event 2" class="post-image">
                <div class="post-content">
                    <h3 class="post-title">Tech Conference 2025</h3>
                    <p class="post-details">August 20, 2025 • Convention Center</p>
                </div>
            </div>
            <div class="post-card">
                <img src="/api/placeholder/300/200" alt="Event 3" class="post-image">
                <div class="post-content">
                    <h3 class="post-title">Food & Wine Expo</h3>
                    <p class="post-details">September 5, 2025 • Downtown Plaza</p>
                </div>
            </div>
        </div>

        <!-- Gallery -->
        <h2 class="section-title">Past Event Gallery</h2>
        <div class="gallery-grid">
            <div class="gallery-item">
            <img src="images/gallery1.jpg" alt="Gallery Image 1" class="gallery-image">
            </div>
            <div class="gallery-item">
            <img src="images/gallery2.jpg" alt="Gallery Image 2" class="gallery-image">
            </div>
            <div class="gallery-item">
            <img src="images/gallery3.jpg" alt="Gallery Image 3" class="gallery-image">
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">About Us</h3>
                <p>Your premier destination for events and opportunities in the events industry.</p>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Events</a></li>
                    <li><a href="#">Jobs</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li>Email: info@eventhub.com</li>
                    <li>Phone: (555) 123-4567</li>
                    <li>Address: 123 Event Street</li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Add notification functionality
        document.querySelector('.notification-icon').addEventListener('click', () => {
            alert('Notifications clicked');
        });

        // Add account functionality
        document.querySelector('.account-icon').addEventListener('click', () => {
            alert('Account menu clicked');
        });
    </script>
</body>
</html>
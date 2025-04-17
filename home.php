<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DunBian Club - Home</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-section">
                <img src="img/logo.jpg" alt="Club Logo" class="logo">
                <span class="club-name">DunBian Club</span>
            </div>
            <nav class="nav-items">
                <a href="homepage.php" class="active">Home</a>
                <a href="aboutpage.php">About</a>
                <a href="eventlisting.php">Events</a>
                <a href="recruitmentopportunities.php">Recruitments</a>
                <a href="contactus.php">Contact Us</a>
            </nav>
            <?php if (isset($_SESSION['stu_matric'])): ?>
            <!-- Logged-in State -->
            <div class="user-controls">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="account">
                    <i class="fas fa-user"></i>
                    <div class="account-menu" style="display: none;">
                        <a href="studentprofile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Logged-out State -->
            <div class="auth-controls">
                <a href="login_form.php" class="login-btn">Login Now</a>
            </div>
            <?php endif; ?>
        </div>      
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Banner Section -->
        <section class="hero-banner">
            <div class="banner-slider">
                <div class="banner-slide active">
                    <div class="banner-content">
                        <h1>Annual Debate Tournament 2025</h1>
                        <p>Join us for the most exciting debate competition of the year!</p>
                        <a href="#" class="banner-btn">Learn More</a>
                    </div>
                </div>
                <div class="banner-slide">
                    <div class="banner-content">
                        <h1>Recruitment Open: Join Our Team</h1>
                        <p>We're looking for passionate debaters to join DunBian Club!</p>
                        <a href="#" class="banner-btn">Apply Now</a>
                    </div>
                </div>
                <div class="banner-navigation">
                    <span class="banner-dot active"></span>
                    <span class="banner-dot"></span>
                </div>
            </div>
        </section>

        <!-- Latest Updates Section -->
        <section class="latest-updates">
            <div class="container">
                <h2 class="section-title">Latest Updates</h2>
                <div class="updates-grid">
                    <!-- Latest Event -->
                    <div class="update-card">
                        <div class="card-image">
                            <img src="img/event-placeholder.jpg" alt="Latest Event">
                            <span class="card-tag event-tag">Event</span>
                        </div>
                        <div class="card-content">
                            <h3>Public Speaking Workshop</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> April 15, 2025</p>
                            <p>Learn essential public speaking skills from industry professionals.</p>
                            <a href="#" class="card-link">Read More</a>
                        </div>
                    </div>
                    
                    <!-- Latest Recruitment -->
                    <div class="update-card">
                        <div class="card-image">
                            <img src="img/recruitment-placeholder.jpg" alt="Latest Recruitment">
                            <span class="card-tag recruitment-tag">Recruitment</span>
                        </div>
                        <div class="card-content">
                            <h3>Executive Committee Positions</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> Deadline: April 30, 2025</p>
                            <p>Apply now for leadership roles in our club for the upcoming academic year.</p>
                            <a href="#" class="card-link">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Events Section -->
        <section class="events-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Upcoming Events</h2>
                    <a href="eventlisting.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="cards-grid">
                    <!-- Event Card 1 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/event1.jpg" alt="Debate Competition">
                            <span class="card-tag event-tag">Event</span>
                        </div>
                        <div class="card-content">
                            <h3>Inter-University Debate</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> May 10, 2025</p>
                            <p class="card-location"><i class="fas fa-map-marker-alt"></i> Main Auditorium</p>
                            <p class="card-excerpt">Join us for an exciting debate competition between top universities.</p>
                            <a href="#" class="card-link">Read More</a>
                        </div>
                    </div>
                    
                    <!-- Event Card 2 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/event2.jpg" alt="Workshop">
                            <span class="card-tag event-tag">Event</span>
                        </div>
                        <div class="card-content">
                            <h3>Argumentation Workshop</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> May 15, 2025</p>
                            <p class="card-location"><i class="fas fa-map-marker-alt"></i> Conference Room B</p>
                            <p class="card-excerpt">Learn advanced argumentation techniques from expert debaters.</p>
                            <a href="#" class="card-link">Read More</a>
                        </div>
                    </div>
                    
                    <!-- Event Card 3 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/event3.jpg" alt="Seminar">
                            <span class="card-tag event-tag">Event</span>
                        </div>
                        <div class="card-content">
                            <h3>Debate Seminar</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> May 22, 2025</p>
                            <p class="card-location"><i class="fas fa-map-marker-alt"></i> Library Hall</p>
                            <p class="card-excerpt">An educational seminar on effective debate strategies.</p>
                            <a href="#" class="card-link">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recruitment Section -->
        <section class="recruitment-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Open Recruitment</h2>
                    <a href="recruitmentopportunities.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="cards-grid">
                    <!-- Recruitment Card 1 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/recruitment1.jpg" alt="Debate Team">
                            <span class="card-tag recruitment-tag">Recruitment</span>
                        </div>
                        <div class="card-content">
                            <h3>Debate Team Members</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> Deadline: May 5, 2025</p>
                            <p class="card-location"><i class="fas fa-users"></i> 5 Positions Available</p>
                            <p class="card-excerpt">Looking for passionate debaters to join our award-winning team.</p>
                            <a href="#" class="card-link">Apply Now</a>
                        </div>
                    </div>
                    
                    <!-- Recruitment Card 2 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/recruitment2.jpg" alt="Event Coordinators">
                            <span class="card-tag recruitment-tag">Recruitment</span>
                        </div>
                        <div class="card-content">
                            <h3>Event Coordinators</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> Deadline: May 12, 2025</p>
                            <p class="card-location"><i class="fas fa-users"></i> 3 Positions Available</p>
                            <p class="card-excerpt">Join our events team to plan and execute engaging activities.</p>
                            <a href="#" class="card-link">Apply Now</a>
                        </div>
                    </div>
                    
                    <!-- Recruitment Card 3 -->
                    <div class="card">
                        <div class="card-image">
                            <img src="img/recruitment3.jpg" alt="Social Media Manager">
                            <span class="card-tag recruitment-tag">Recruitment</span>
                        </div>
                        <div class="card-content">
                            <h3>Social Media Manager</h3>
                            <p class="card-date"><i class="far fa-calendar-alt"></i> Deadline: May 20, 2025</p>
                            <p class="card-location"><i class="fas fa-users"></i> 1 Position Available</p>
                            <p class="card-excerpt">Help us grow our online presence through engaging content.</p>
                            <a href="#" class="card-link">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="container">
                <div class="newsletter-content">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to our newsletter and never miss an event or opportunity!</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your Email Address" required>
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer (as provided, unchanged) -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">About Us</h3>
                <p>We strive to promote the art of debate, create an environment where students can learn, practice and understand the different aspects of debating through different engaging activities.</p>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="aboutpage.php">About</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="recruitmentopportunities.php">Recruitments</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li>Email: dunbianclub@gmail.com</li>
                    <li>Phone: 0123456789</li>
                    <li>University Tun Hussein Onn Malaysia</li>
                    <li>Address: Persiaran Tun Dr. Ismail, 86400 Parit Raja, Johor Darul Ta'zim</li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Simple banner slider functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.banner-slide');
            const dots = document.querySelectorAll('.banner-dot');
            
            // Set up click events for dots
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    slides.forEach(slide => slide.classList.remove('active'));
                    dots.forEach(dot => dot.classList.remove('active'));
                    
                    slides[index].classList.add('active');
                    dots[index].classList.add('active');
                });
            });
            
            // Auto-rotate slides every 5 seconds
            let currentSlide = 0;
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));
                
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
            }, 5000);
            
            // Toggle account menu
            const accountIcon = document.querySelector('.account');
            if (accountIcon) {
                accountIcon.addEventListener('click', function() {
                    const menu = this.querySelector('.account-menu');
                    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                });
            }
        });
    </script>
</body>
</html>
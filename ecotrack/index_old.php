<?php
require_once 'includes/config.php';
$pageTitle = 'Home';
include 'includes/header.php';
?>

<section class="hero-section d-flex justify-content-center align-items-center" id="home">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 mx-auto text-center">
                <h1>Track Your Ecological Impact</h1>
                <h6 class="mb-4"><?php echo SITE_SLOGAN; ?></h6>
                <p class="text-white mb-4">EcoTrack helps you measure, analyze, and improve your environmental footprint through energy consumption, transport choices, and waste management.</p>
                
                <?php if (!isLoggedIn()): ?>
                <div class="d-flex justify-content-center gap-3">
                    <a href="modules/auth/register.php" class="custom-btn">Get Started</a>
                    <a href="modules/auth/login.php" class="custom-btn custom-btn-outline">Sign In</a>
                </div>
                <?php else: ?>
                <a href="dashboard.php" class="custom-btn">Go to Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="featured-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="custom-block bg-white shadow-lg text-center">
                    <span class="badge bg-energy rounded-pill mx-auto mb-3">
                        <i class="bi-lightning-charge"></i>
                    </span>
                    <h5 class="mb-2">Energy Tracking</h5>
                    <p class="mb-0">Monitor your electricity, gas, and renewable energy consumption</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="custom-block bg-white shadow-lg text-center">
                    <span class="badge bg-transport rounded-pill mx-auto mb-3">
                        <i class="bi-car-front"></i>
                    </span>
                    <h5 class="mb-2">Transport</h5>
                    <p class="mb-0">Track your travel emissions and discover greener alternatives</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="custom-block bg-white shadow-lg text-center">
                    <span class="badge bg-waste rounded-pill mx-auto mb-3">
                        <i class="bi-trash"></i>
                    </span>
                    <h5 class="mb-2">Waste Management</h5>
                    <p class="mb-0">Log your waste and improve recycling habits</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-12 mb-4">
                <div class="custom-block bg-white shadow-lg text-center">
                    <span class="badge bg-report rounded-pill mx-auto mb-3">
                        <i class="bi-flag"></i>
                    </span>
                    <h5 class="mb-2">Report Trash</h5>
                    <p class="mb-0">Report improper trash disposal in your community</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-12 mb-4 mb-lg-0">
                <h2 class="mb-4">Why EcoTrack?</h2>
                <p>EcoTrack is an intelligent platform designed to help you understand and reduce your environmental impact. By tracking your daily activities, you can make informed decisions that benefit both you and the planet.</p>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Easy to use</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Real-time tracking</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Detailed reports</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi-check-circle-fill text-success me-2 fs-4"></i>
                            <span>Community impact</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600" alt="Eco friendly" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<section class="timeline-section section-padding" id="how-it-works">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="text-white">How It Works</h2>
                <p class="text-white">Simple steps to start your eco-journey</p>
            </div>
        </div>

        <div class="timeline-container">
            <ul class="vertical-scrollable-timeline">
                <li>
                    <div class="icon-holder">
                        <i class="bi-person-plus"></i>
                    </div>
                    <div class="text-white">
                        <h5 class="text-white">Create Your Account</h5>
                        <p class="text-white-50">Sign up for free and set up your eco-profile</p>
                    </div>
                </li>
                <li>
                    <div class="icon-holder">
                        <i class="bi-graph-up-arrow"></i>
                    </div>
                    <div class="text-white">
                        <h5 class="text-white">Track Your Activities</h5>
                        <p class="text-white-50">Log your energy usage, transport, and waste habits</p>
                    </div>
                </li>
                <li>
                    <div class="icon-holder">
                        <i class="bi-bar-chart-line"></i>
                    </div>
                    <div class="text-white">
                        <h5 class="text-white">View Your Impact</h5>
                        <p class="text-white-50">Get insights and visualize your ecological footprint</p>
                    </div>
                </li>
                <li>
                    <div class="icon-holder">
                        <i class="bi-globe-americas"></i>
                    </div>
                    <div class="text-white">
                        <h5 class="text-white">Make a Difference</h5>
                        <p class="text-white-50">Take action and contribute to a sustainable future</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="section-padding section-bg" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-12 mb-4 mb-lg-0">
                <h2 class="mb-4">Get In Touch</h2>
                <p class="mb-4">Have questions about EcoTrack? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                
                <div class="d-flex align-items-center mb-3">
                    <i class="bi-envelope-fill text-success me-3 fs-4"></i>
                    <span>contact@ecotrack.com</span>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi-geo-alt-fill text-success me-3 fs-4"></i>
                    <span>Tunis, Tunisia</span>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <form action="contact.php" method="POST" class="custom-form">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>
                    </div>
                    <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                    <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                    <button type="submit" class="custom-btn mt-3">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

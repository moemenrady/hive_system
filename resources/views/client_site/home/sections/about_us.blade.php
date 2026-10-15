<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<section id="about-us-section" class="about-us-section">
    <div class="section-wave"></div>
    <div class="section-wave"></div>

    <div class="container custom-about-container">
        <div class="row align-items-center">
            <!-- نص الـ About Us -->
            <div class="col-lg-6 about-text-col">
                <div class="about-content-card">
                    <span class="sub-title">Our Story</span>
                    <h2>Creating Comfort & Productivity <br> <span>Since 2024</span></h2>
                    <p>
                        At our workspace and café, we provide the perfect blend of comfort, inspiring environment, 
                        and productivity. Whether you're here to work, meet, or relax, we offer a space designed to 
                        make your day both enjoyable and fruitful.
                    </p>

                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-coffee"></i>
                            <span>Premium Coffee & Snacks</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-laptop"></i>
                            <span>Work-Friendly Spaces</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mug-hot"></i>
                            <span>Relaxing Ambiance</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Collaborative Environment</span>
                        </div>
                    </div>

                    <div class="social-connect">
                        <p class="connect-text">Follow Our Journey:</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/your_cafe_workspace" target="_blank"
                                class="social-btn instagram" title="Instagram">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>

                            <a href="https://www.facebook.com/your_cafe_workspace" target="_blank"
                                class="social-btn facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </a>

                            <a href="https://www.linkedin.com/company/your_cafe_workspace" target="_blank"
                                class="social-btn linkedin" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                                <span>LinkedIn</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الصورة / الvisual -->
            <div class="col-lg-6 about-image-col">
                <div class="about-visual">
                    <img src="{{ asset('images/about_workspace_cafe.png') }}" alt="Workspace & Café" class="img-fluid rounded-3 shadow">
                    <div class="floating-badge">
                        <span class="number">100%</span>
                        <span class="txt">Comfort & Productivity</span>
                    </div>
                    <div class="abstract-shape"></div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('client_site.home.styles.about_us')
@include('client_site.home.scripts.about_us')

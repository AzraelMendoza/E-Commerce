<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | CubeClass</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>

<?php include 'header.php'?>

<!-- HERO -->
<header class="hero-section">
    <div class="container text-center">
        <h1 class="display-3 fw-bold reveal">
            Mastering the <span class="text-red">Art of Play</span>
        </h1>
        <p class="hero-subtitle reveal">
            Where strategy, craftsmanship, and imagination come together
        </p>
        <small class="text-muted reveal">Curated by Azrael Mendoza</small>
    </div>
</header>

<!-- STORY -->
<section class="section-layered">
    <div class="container py-5">
        <div class="content-panel reveal">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h6 class="section-label">Our Story</h6>
                    <h2 class="fw-bold mb-4">A Vision for Excellence</h2>

                    <p class="lead text-muted">
                        Founded by <strong>Azrael Mendoza</strong>, CubeClass began as a passion project
                        centered on precision, logic, and meaningful play.
                    </p>

                    <p class="text-muted">
                        Located in San Pedro, San Fernando, CubeClass offers more than toys —
                        we curate experiences that sharpen thinking, celebrate craftsmanship,
                        and reward mastery.
                    </p>
                </div>

                <div class="col-lg-5">
                    <div class="quote-box">
                        <p class="fst-italic mb-2">
                            “Every puzzle solved and every move played is a step toward mastery.”
                        </p>
                        <small>— Azrael Mendoza</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VALUES -->
<section class="section-soft">
    <div class="container py-5">
        <div class="text-center mb-5 reveal">
            <h2 class="fw-bold">What Defines CubeClass</h2>
            <div class="red-line mx-auto"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="value-card">
                    <h5>Precision</h5>
                    <p>Every product is selected for accuracy, performance, and reliability.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="value-card">
                    <h5>Integrity</h5>
                    <p>Authentic items, transparent pricing, and quality above trends.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="value-card">
                    <h5>Community</h5>
                    <p>Supporting collectors, hobbyists, and competitive players alike.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COLLECTION -->
<section class="section-layered">
    <div class="container py-5">
        <div class="text-center mb-5 reveal">
            <h2 class="fw-bold">Our Collection</h2>
            <div class="red-line mx-auto"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal">
                <div class="clean-card h-100">
                    <div class="icon-circle"><i class="bi bi-grid-3x3-gap"></i></div>
                    <h4>Puzzles</h4>
                    <p>Speed cubes and logic challenges designed for mastery.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="clean-card h-100">
                    <div class="icon-circle"><i class="bi bi-controller"></i></div>
                    <h4>Board Games</h4>
                    <p>Strategy-driven tabletop games for all skill levels.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="clean-card h-100">
                    <div class="icon-circle"><i class="bi bi-collection"></i></div>
                    <h4>Figures</h4>
                    <p>Detailed collectibles crafted with authenticity.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="clean-card h-100">
                    <div class="icon-circle"><i class="bi bi-truck"></i></div>
                    <h4>RC Toys</h4>
                    <p>Performance-focused remote-controlled vehicles.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LOCATION -->
<section class="section-soft">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-4 reveal">
                <h3 class="fw-bold mb-3">
                    Visit Us in <span class="text-red">San Fernando</span>
                </h3>

                <p class="text-muted">
                    Visit CubeClass and experience our collection firsthand.
                </p>

                <div class="d-flex align-items-center mt-4">
                    <i class="bi bi-geo-alt-fill text-red fs-4 me-3"></i>
                    <span>San Pedro, City of San Fernando, Pampanga</span>
                </div>
            </div>

            <div class="col-lg-8 reveal">
                <div class="map-frame rounded-3 overflow-hidden shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15418.57164923293!2d120.68652255!3d15.02941575!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396f7375e2f7537%3A0x6b490f2b3802957b!2sSan%20Pedro%2C%20City%20of%20San%20Fernando%2C%20Pampanga!5e0!3m2!1sen!2sph!4v1700000000000"
                        width="100%"
                        height="350"
                        style="border:0;"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'?>

<script>
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('active');
    });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>

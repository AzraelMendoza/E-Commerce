<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
   
</head>
<body>
    <footer class="bg-black text-light pt-5 pb-4 mt-5">
  <div class="container">

    <div class="row gy-4">

      <!-- BRAND -->
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center mb-3">
          <img src="img/media/logo.png" alt="CubeClass Logo" width="60" class="me-2">
          <h5 class="fw-bold mb-0">CubeClass</h5>
        </div>
        <p class="text-secondary small">
          Your ultimate destination for high quality toys.
          Fun, learning, and excitement for all ages.
        </p>
      </div>

      <!-- QUICK LINKS -->
      <div class="col-lg-2 col-md-6">
        <h6 class="fw-semibold mb-3">Quick Links</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="welcome.php">Home</a></li>
          <li><a href="figures/bestsellers.php">Best Sellers</a></li>
          <li><a href="AboutUs.php">About Us</a></li>
           
        </ul>
      </div>

      <!-- CATEGORIES -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-semibold mb-3">Categories</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="figures/figures.php">Robot Toys</a></li>
          <li><a href="figures/puzz.php">Puzzle Games</a></li>
          <li><a href="figures/rc.php">RC Cars & Drones</a></li>
          <li><a href="figures/boardgames.php">Board Games</a></li>
        </ul>
      </div>

      <!-- SOCIAL / CONTACT -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-semibold mb-3">Connect With Us</h6>

        <div class="d-flex gap-3 mb-3">
          <a href="#" class="footer-icon"><i class="bi bi-facebook"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="footer-icon"><i class="bi bi-youtube"></i></a>
        </div>

        <p class="small text-secondary mb-1">
          <i class="bi bi-envelope me-2"></i> support@cubeclass.com
        </p>
        <p class="small text-secondary">
          <i class="bi bi-telephone me-2"></i> +63 9XX XXX XXXX
        </p>
      </div>

    </div>

    <hr class="border-secondary my-4">

    <!-- BOTTOM -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
      <p class="mb-2 mb-md-0">
        © <?= date("Y") ?> CubeClass. All rights reserved.
      </p>
      <div class="d-flex gap-3">
        <a href="#" class="footer-bottom-link">Privacy Policy</a>
        <a href="#" class="footer-bottom-link">Terms of Service</a>
      </div>
    </div>

  </div>
</footer>

</body>
</html>
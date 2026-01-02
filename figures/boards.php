






<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOARD GAMES</title>
     <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 
     
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="css/banner.css"> 
    <link rel="stylesheet" href="css/welcome.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/welcome.css">
</head>
<body>
    <?php include 'header.php' ?>

    <div class="container my-5">
            <div class="row g-4">

                <!-- CLICKABLE PRODUCT CARD -->
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="checkers.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                <img src="../figures/img/products/checkers.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Wooden Checkers Set</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                    <span class="text-warning">★★★★★</span>
                                    <span class="text-muted-small">(12 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">₱1199</span>
                                    <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
            
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="eureka.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/Eureka.jpeg" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Original Eureka Chess Set</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(55 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱599</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="triple.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products//triple.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Triple Weighted Chess Pieces</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(22 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱1299</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
        </div>
    </div>


     <div class="container my-5">
            <div class="row g-4">

                <!-- CLICKABLE PRODUCT CARD -->
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="scrabble.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                <img src="../figures/img/products/Scrabble.avif" class="img-fluid product-img" alt="Product" style="height: 195px;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Scrabble</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                    <span class="text-warning">★★★★★</span>
                                    <span class="text-muted-small">(11 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">₱649</span>
                                    <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
            
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="mono.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/mono2.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Monopoly </h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(68 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱999</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="battle.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/battleship.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Battleship Board Game</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(43 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱799</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
        </div>
    </div>

    <div class="container my-5">
            <div class="row g-4">

                <!-- CLICKABLE PRODUCT CARD -->
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="rumi.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                <img src="../figures/img/products/Rummikub.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Rummikub Board Game</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                    <span class="text-warning">★★★★</span>
                                    <span class="text-muted-small">(82 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">₱449</span>
                                    <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
            
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="product.php?id=1" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/Jumanji.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Jumanji</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(31 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱1699</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="fox.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/outfoxed.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Outfoxed Board Games</h5>
                                <p class="text-muted-small mb-2">Figures</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(29 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱499</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
        </div>
    </div>

     <div class="container my-5">
            <div class="row g-4">

                <!-- CLICKABLE PRODUCT CARD -->
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="clue.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                <img src="../figures/img/products/Clue.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Clue Board Game</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                    <span class="text-warning">★★★★</span>
                                    <span class="text-muted-small">(7 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">₱499</span>
                                    <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
            
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="mousepad.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/mousepadboard.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Mouse Pad Chess Board</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(44 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱299</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="liars.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/LiarsDice.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Liar's Dice</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(27 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱1249</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
        </div>
    </div>

       <div class="container my-5">
            <div class="row g-4">

                <!-- CLICKABLE PRODUCT CARD -->
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="catan.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                <img src="../figures/img/products/catan.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;">
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Catan</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                    <span class="text-warning">★★★★★</span>
                                    <span class="text-muted-small">(23 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5 fw-bold">₱649</span>
                                    <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
            
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="animal.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/animalUponAnimal.PNG" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Animal Upon Animal</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(20 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱699</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                        <a href="back.php" class="text-decoration-none text-dark">
                            <div class="card h-100 product-card">

                            <div class="position-relative text-center p-3">
                                
                                <img src="../figures/img/products/backgammon.jpg" class="img-fluid product-img" alt="Product" style="height: 195px;" >
                            </div>

                            <div class="card-body">
                                <h5 class="card-title fw-semibold mb-1">Backgammon</h5>
                                <p class="text-muted-small mb-2">Board Games</p>

                                <!-- rating preview -->
                                <div class="mb-2">
                                        <span class="text-warning">★★★★★</span>
                                        <span class="text-muted-small">(21 reviews)</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">₱1199</span>
                                        <span class="text-muted-small">View details →</span>
                                </div>
                            </div>

                            </div>
                        </a>
                </div>
        </div>
    </div>


    <?php include 'footer.php';?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
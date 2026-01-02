<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    require_once 'db.php'; 
    $user_id = (int)$_SESSION['user_id'];
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $countStmt->bind_param("i", $user_id);
    $countStmt->execute();
    $cartCount = $countStmt->get_result()->fetch_row()[0];
}
?>

<style>
    .nav-search { position: relative; }
    #suggestionBox { 
        z-index: 1050; 
        top: 100%; 
        left: 0; 
        right: 0; 
        background: white; 
        border-radius: 10px; 
        overflow: hidden;
    }
    #suggestionBox .list-group-item { 
        cursor: pointer; 
        border: none; 
        color: #333;
        padding: 10px 20px;
    }
    #suggestionBox .list-group-item:hover { 
        background-color: #f8f9fa; 
        color: #000; 
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-black py-3">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="../welcome.php">
      <img src="img/media/logo.png" alt="Logo" width="75" class="me-2">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 nav-gap">
        <li class="nav-item"><a class="nav-link active" href="welcome.php">Home</a></li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Products</a>
            <ul class="dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item" href="figures/puzz.php">Puzzles</a></li>
                <li><a class="dropdown-item" href="figures/figures.php">Figures</a></li>
                <li><a class="dropdown-item" href="figures/rc.php">RC Toys</a></li>
                <li><a class="dropdown-item" href="figures/boards.php">Board Games</a></li>
            </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="figures/bestsellers.php">Best Sellers</a></li>
        <li class="nav-item"><a class="nav-link" href="figures/about.php">About Us</a></li>
      </ul>

      <div class="ms-lg-auto mt-3 mt-lg-0 d-flex flex-column flex-lg-row align-items-lg-center gap-3">
        <form class="d-flex nav-search w-100 w-lg-auto" action="search_results.php" method="GET" autocomplete="off">
          <input id="searchInput" class="form-control rounded-pill" type="search" name="search" placeholder="Search products..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
          <button class="search-icon" type="submit"><i class="bi bi-search"></i></button>
          
          <div id="suggestionBox" class="list-group position-absolute shadow d-none"></div>
        </form>

        <div class="d-flex align-items-center nav-icons me-3">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="figures/dashboard.php" class="icon-btn me-3" title="Profile"><i class="bi bi-person-circle"></i></a>
          <?php else: ?>
            <a href="login.php" class="icon-btn me-3" title="Login"><i class="bi bi-person"></i></a>
          <?php endif; ?>

          <a href="figures/cart.php" class="icon-btn me-3 position-relative">
            <i class="bi bi-cart"></i>
            <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= ($cartCount > 0) ? '' : 'd-none' ?>">
                <?= $cartCount ?>
            </span>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionBox = document.getElementById('suggestionBox');

    searchInput.addEventListener('input', function() {
        const term = this.value.trim();

        if (term.length < 2) {
            suggestionBox.classList.add('d-none');
            return;
        }

        fetch(`get_suggestions.php?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionBox.innerHTML = data.map(name => 
                        `<div class="list-group-item">${name}</div>`
                    ).join('');
                    suggestionBox.classList.remove('d-none');
                } else {
                    suggestionBox.classList.add('d-none');
                }
            })
            .catch(err => console.error('Error fetching suggestions:', err));
    });

    suggestionBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('list-group-item')) {
            searchInput.value = e.target.innerText;
            suggestionBox.classList.add('d-none');
            searchInput.closest('form').submit();
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.classList.add('d-none');
        }
    });
});
</script>
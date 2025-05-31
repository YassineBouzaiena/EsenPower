<?php
session_start();
$user = isset($_SESSION['user_id'])
    ? ['id'=>$_SESSION['user_id'],'nom'=>$_SESSION['nom'],'role'=>$_SESSION['role']]
    : null;
$mysqli = new mysqli("localhost","root","","shop");
if ($mysqli->connect_error) {
    die("Erreur de connexion : ".$mysqli->connect_error);
}
// 1️⃣ Fetch your 3 latest products into $productResult
$productQuery   = "SELECT * FROM products ORDER BY id DESC LIMIT 3";
$productResult  = $mysqli->query($productQuery)
                   or die("Erreur SQL produits : ".$mysqli->error);
// 2️⃣ Fetch up to 10 approved testimonials into $testimonials
$max = 10;
$tsSql = "
  SELECT name, message
    FROM testimonials
   WHERE approved = 1
   ORDER BY created_at DESC
   LIMIT ?
";
$stmt = $mysqli->prepare($tsSql)
     or die("Prepare témoignages échoué : ".$mysqli->error);
$stmt->bind_param('i', $max);
$stmt->execute();
$testimonials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Esen Power - Boost Your Strength</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<style>
.original-price {
  text-decoration: line-through;
  color: #888;
  font-size: 1.1em;
}

.discounted-price {
  color: #e74c3c; /* Red color for discounted price */
  font-weight: bold;
  font-size: 1.2em;
}

.promo-text {
  font-size: 0.9em;
  color: #2ecc71; /* Green color for promo percentage */
}
</style>
<body>
  <!-- Header -->
  <header class="header">
    <div class="logo">Esen <span>Power</span></div>
    <nav class="navbar">
      <a href="#home">Accueil</a>
      <a href="view/shop.php">Boutique</a>
      <a href="view/temoignages.php">Témoignages</a>
      <a href="view/contact.php">Contact</a>
      <a href="view/apply.php">Apply</a>
      <a href="view/game.php">Game</a>
      <a href="view/about.php">About</a>
    </nav>
    <div class="icons">
    <div class="user-panel-container">
  <a href="" id="user-icon" title="Utilisateur">
    <i class="fas fa-user"></i>
  </a>
  <div class="user-popup" id="user-popup">
  <?php if ($user): ?>
  <div class="user-greeting">Bonjour, <strong><?= htmlspecialchars($user['nom']) ?></strong></div>
  <a href="controlers/user/user.php">
  <i class="fas fa-user"></i> Mon profil
  </a>

  <a href="controlers/orders.php"><i class="fas fa-shopping-cart"></i> Mes commandes</a>
  <?php if ($user['role'] === 'admin'): ?>
    <a href="controlers/admin/admin_dashboard.php"><i class="fas fa-cogs"></i> Admin Panel</a>
  <?php endif; ?>
  <?php if ($user['role'] === 'livreur'): ?>
    <a href="controlers/livreur/livreur.php"><i class="fas fa-cogs"></i> livreur Panel</a>
  <?php endif; ?>
  <hr>
  <a href="controlers/logout.php" class="logout-btn"><i class="fas fa-door-open"></i> Déconnexion</a>
<?php else: ?>
  <a href="view/login.php"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
  <a href="view/register.php"><i class="fas fa-user-plus"></i> Créer un compte</a>
<?php endif; ?>

</div>
</div>      
<a href="controlers/cart.php" id="cart-icon" title="Panier"><i class="fas fa-shopping-cart"></i></a>
    </div>
  </header>
  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="hero-overlay">
      <div class="hero-content">
        <h1>Libère ta <span>puissance</span></h1>
        <p>Des compléments pensés pour les vrais performeurs.</p>
        <a href="shop.php" class="btn">Voir la boutique</a>
      </div>
    </div>
  </section>
<!-- Shop Section -->
<section id="shop" class="shop-preview">
  <h2 class="section-title">Meilleures ventes</h2>
  <div class="products-grid">
    <?php while ($row = $productResult->fetch_assoc()) : ?>
      <div class="product-card">
        <img src="<?= htmlspecialchars($row['image']) ?>" 
             alt="<?= htmlspecialchars($row['name']) ?>">
        <div class="product-info">
          <h3><?= htmlspecialchars($row['name']) ?></h3>
          <p><?= htmlspecialchars($row['description']) ?></p>
          
          <!-- Check if there is a promo -->
          <?php if ($row['promo'] > 0): ?>
            <!-- Calculate the discounted price -->
            <?php 
                $discountedPrice = $row['price'] * (1 - $row['promo'] / 100);
            ?>
            <span class="original-price"><?= number_format($row['price'], 2) ?> €</span>
            <span class="discounted-price"><?= number_format($discountedPrice, 2) ?> €</span>
            <p class="promo-text">(<?= $row['promo'] ?>% off)</p>
          <?php else: ?>
            <span class="price"><?= number_format($row['price'], 2) ?> €</span>
          <?php endif; ?>
          
          <button class="add-to-cart-btn"
                  data-id="<?= $row['id'] ?>"
                  data-name="<?= htmlspecialchars($row['name']) ?>"
                  data-price="<?= isset($discountedPrice) ? $discountedPrice : $row['price'] ?>">
            Ajouter au panier
          </button>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
  <a href="shop.php" class="btn">Explorer la boutique</a>
</section>

<script>
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
  button.addEventListener('click', () => {
    const productId = button.dataset.id;

    fetch('add_to_cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'product_id=' + productId
    })
    .then(response => response.text())
    .then(data => {
      ; // Tu peux personnaliser ça en toast ou message de confirmation
    });
  });
});
</script>

  <!-- Testimonials -->
  <section id="testimonials" class="testimonials">
  <h2 class="section-title">Témoignages clients</h2>
  <div class="testimonial-slider">
    <?php foreach($testimonials as $i => $t): ?>
      <div class="testimonial<?= $i===0 ? ' active' : '' ?>">
        <p><?= htmlspecialchars($t['message'], ENT_QUOTES) ?></p>
        <h4>- <?= htmlspecialchars($t['name'], ENT_QUOTES) ?></h4>
      </div>
    <?php endforeach; ?>
    <div class="arrows">
      <span class="prev">&#10094;</span>
      <span class="next">&#10095;</span>
    </div>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  let current = 0;
  const items = document.querySelectorAll('#testimonials .testimonial');

  function showItem(index) {
    items.forEach((item, i) => {
      item.classList.toggle('active', i === index);
    });
  }

  document.querySelector('#testimonials .prev').addEventListener('click', () => {
    current = (current - 1 + items.length) % items.length;
    showItem(current);
  });

  document.querySelector('#testimonials .next').addEventListener('click', () => {
    current = (current + 1) % items.length;
    showItem(current);
  });
});
</script>

<section class="feature-highlight">
  <div class="highlight-content">
    <h2>POUR UNE PERFORMANCE OPTIMALE, FAITES CONFIANCE À ESEN POWER </h2>
    <p>Nous développons des compléments de qualité, comme notre whey protéine, pour vous aider à atteindre vos objectifs de forme, de force et de bien-être.</p>
    <a href="#" class="btn-highlight">EN SAVOIR PLUS</a>
  </div>
</section>
<div class="section-divider"></div>
  <footer class="footer">
  <div class="footer-container">
    <div class="footer-col">
      <h3><span class="logo-color">Esen</span> <span class="accent">Power</span></h3>
      <p>Découvrez des produits formulés pour soutenir votre progression, améliorer votre récupération et maximiser vos résultats.</p>
    </div>
    <div class="footer-col">
      <h4>Navigation</h4>
      <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="view/shop.php">Tous les produits</a></li>
        <li><a href="view/shop.php">Catégories</a></li>
        <li><a href="view/about.php">À propos</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Catégories</h4>
      <ul>
        <li><a href="#">Protéines</a></li>
        <li><a href="#">Pré-entraînement</a></li>
        <li><a href="#">Créatine</a></li>
        <li><a href="#">Vitamines</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <li><i class="fas fa-map-marker-alt"></i> Technopole de la Manouba <br>Manouba, CP 2010</li>
        <li><i class="fas fa-phone-alt"></i> +216 28564961</li>
        <li><i class="fas fa-envelope"></i> mohamedjamazi17@gmail.com</li>
      </ul>
    </div>
  </div>
</footer>
</body>
<!-- Toast container -->
<div id="toast" style="position: fixed; bottom: 20px; right: 20px; 
                       background-color: #333; color: #fff; 
                       padding: 10px 20px; border-radius: 5px; 
                       display: none; z-index: 1000; 
                       box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    const toast = document.getElementById('toast');
    function showToast(message) {
      toast.textContent = message;
      toast.style.display = 'block';
      // Hide after 2 seconds
      setTimeout(() => {
        toast.style.display = 'none';
      }, 2000);
    }
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function() {
        const productName = this.getAttribute('data-name');
        showToast(productName + " a été ajouté au panier !");
      });
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
  const slides   = document.querySelectorAll('#testimonials .testimonial');
  const prevBtn  = document.querySelector('#testimonials .prev');
  const nextBtn  = document.querySelector('#testimonials .next');
  let current    = 0;
  let animating  = false;
  function goToSlide(newIndex, direction) {
    if (animating || newIndex === current) return;
    animating = true;
    const outgoing = slides[current];
    const incoming = slides[newIndex];
    // prepare incoming off-screen
    incoming.style.display   = 'block';
    incoming.style.transform = `translateX(${ direction==='next'? '100%':'-100%' })`;
    incoming.style.opacity   = 0;
    // force reflow
    incoming.getBoundingClientRect();
    // animate both
    outgoing.style.transition = incoming.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
    outgoing.style.transform  = `translateX(${ direction==='next'? '-100%':'100%' })`;
    outgoing.style.opacity    = 0;
    incoming.style.transform  = 'translateX(0)';
    incoming.style.opacity    = 1;
    incoming.addEventListener('transitionend', function handler() {
      // cleanup
      outgoing.style.display   = 'none';
      outgoing.classList.remove('active');
      outgoing.style.transition = outgoing.style.transform = outgoing.style.opacity = '';
      incoming.classList.add('active');
      incoming.style.transition = incoming.style.transform = incoming.style.opacity = '';
      incoming.removeEventListener('transitionend', handler);
      current = newIndex;
      animating = false;
    });
  }
  prevBtn.addEventListener('click', () => {
    const idx = (current - 1 + slides.length) % slides.length;
    goToSlide(idx, 'prev');
  });

  nextBtn.addEventListener('click', () => {
    const idx = (current + 1) % slides.length;
    goToSlide(idx, 'next');
  });
  // auto‑advance every 5s
  setInterval(() => {
    const idx = (current + 1) % slides.length;
    goToSlide(idx, 'next');
  }, 5000);
});
document.getElementById('user-icon').addEventListener('click', function(e) {
  e.preventDefault();
  const popup = document.getElementById('user-popup');
  popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
});
// Optional: Close it if user clicks outside
window.addEventListener('click', function(e) {
  const popup = document.getElementById('user-popup');
  const icon = document.getElementById('user-icon');
  if (!popup.contains(e.target) && !icon.contains(e.target)) {
    popup.style.display = 'none';
  }
});
</script>
</html>

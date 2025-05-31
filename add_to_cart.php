<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  echo "Veuillez vous connecter pour ajouter au panier.";
  exit;
}
$mysqli = new mysqli("localhost", "root", "", "shop");
if ($mysqli->connect_error) {
  die("Erreur : " . $mysqli->connect_error);
}
if (isset($_POST['product_id'])) {
  $product_id = intval($_POST['product_id']);
  $user_id = $_SESSION['user_id'];
  // Vérifier si ce produit est déjà dans le panier
  $check = $mysqli->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
  $check->bind_param("ii", $user_id, $product_id);
  $check->execute();
  $check->store_result();
  if ($check->num_rows > 0) {
    // Si le produit existe, on augmente la quantité
    $update = $mysqli->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
    $update->bind_param("ii", $user_id, $product_id);
    $update->execute();
  } else {
    // Sinon, on l’ajoute
    $insert = $mysqli->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
  }
} else {
}

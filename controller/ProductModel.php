<?php
// controller/ProductModel.php
function isProductInStock($productId, $requestedQty) {
    global $conn;
    $query = "SELECT product_stok FROM produk WHERE product_id = :productId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':productId', $productId);
    $stmt->execute();
    $stock = $stmt->fetchColumn();
    return $stock >= $requestedQty;
}
?>

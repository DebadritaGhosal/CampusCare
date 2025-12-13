<?php
session_start();
require_once 'config/database.php';

$product_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        die('Product not found');
    }
    ?>
    
    <h3>Suggest Your Price for: <?php echo htmlspecialchars($product['title']); ?></h3>
    
    <?php if($product['asking_price']): ?>
    <div style="text-align: center; margin: 20px 0;">
        <p>Seller's asking price: <strong style="font-size: 1.2em;">₹<?php echo number_format($product['asking_price'], 2); ?></strong></p>
        <p style="color: #718096; font-size: 0.9em;">Most successful offers are between 70-90% of asking price</p>
    </div>
    <?php endif; ?>
    
    <!-- Price Slider -->
    <div style="margin: 20px 0;">
        <label for="priceSlider">Drag to select your price:</label>
        <input type="range" id="priceSlider" class="price-slider" min="1" max="10000" value="1000">
        <div style="text-align: center; margin: 10px 0;">
            <span style="font-size: 2em; font-weight: bold; color: #667eea;" id="sliderPrice">₹1,000</span>
        </div>
    </div>
    
    <!-- Manual Price Input -->
    <div style="margin: 20px 0;">
        <label for="proposedPrice">Or enter exact amount:</label>
        <div style="display: flex; align-items: center; margin-top: 10px;">
            <span style="font-size: 1.5em; margin-right: 10px;">₹</span>
            <input type="number" 
                   id="proposedPrice" 
                   class="proposal-input"
                   min="1"
                   step="10"
                   value="1000"
                   style="flex: 1;">
        </div>
    </div>
    
    <!-- Optional Message -->
    <div style="margin: 20px 0;">
        <label for="proposalMessage">Add a message (optional):</label>
        <textarea id="proposalMessage" 
                  placeholder="E.g., 'I can pick up today' or 'Is the item still available?'"
                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 10px;"
                  rows="3"></textarea>
    </div>
    
    <input type="hidden" id="askingPrice" value="<?php echo $product['asking_price'] ?? 0; ?>">
    
    <button onclick="submitProposal()" class="proposal-btn" style="margin-top: 20px;">
        📨 Send Price Suggestion
    </button>
    
    <p style="font-size: 0.8em; color: #718096; margin-top: 10px; text-align: center;">
        The seller has 48 hours to respond. You can withdraw your offer anytime before acceptance.
    </p>
    
    <?php
} catch(PDOException $e) {
    echo '<p>Error loading product information</p>';
}
?>
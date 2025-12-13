<?php
session_start();
require_once 'config/database.php';

$product_id = $_GET['product_id'];
$seller_id = $_SESSION['user_id'];

try {
    // Verify seller owns the product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        die('<p>Unauthorized access</p>');
    }
    
    // Get all proposals
    $stmt = $pdo->prepare("SELECT * FROM proposals WHERE product_id = ? ORDER BY proposed_price DESC, created_at DESC");
    $stmt->execute([$product_id]);
    $proposals = $stmt->fetchAll();
    
    if(empty($proposals)) {
        echo '<p>No proposals yet. Share the listing to get offers!</p>';
        return;
    }
    ?>
    
    <h3>Proposals for: <?php echo htmlspecialchars($product['title']); ?></h3>
    
    <div class="negotiation-chat">
    <?php foreach($proposals as $proposal): ?>
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 10px 0; background: <?php echo $proposal['status'] == 'pending' ? '#f7fafc' : '#fff'; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong><?php echo htmlspecialchars($proposal['buyer_name']); ?></strong>
                    <span style="color: #718096; font-size: 0.9em;">
                        <?php echo date('M d, H:i', strtotime($proposal['created_at'])); ?>
                    </span>
                </div>
                <div style="font-size: 1.5em; font-weight: bold; color: #667eea;">
                    ₹<?php echo number_format($proposal['proposed_price'], 2); ?>
                </div>
            </div>
            
            <?php if($proposal['message']): ?>
            <p style="margin: 10px 0; padding: 10px; background: #edf2f7; border-radius: 6px;">
                "<?php echo htmlspecialchars($proposal['message']); ?>"
            </p>
            <?php endif; ?>
            
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <?php if($proposal['status'] == 'pending'): ?>
                    <button onclick="acceptProposal(<?php echo $proposal['id']; ?>)" 
                            style="background: #48bb78; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer;">
                        ✓ Accept
                    </button>
                    <button onclick="makeCounterOffer(<?php echo $proposal['id']; ?>)" 
                            style="background: #ed8936; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer;">
                        ↔ Counter
                    </button>
                    <button onclick="rejectProposal(<?php echo $proposal['id']; ?>)" 
                            style="background: #fc8181; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer;">
                        ✗ Reject
                    </button>
                <?php elseif($proposal['status'] == 'countered'): ?>
                    <div style="color: #d69e2e; font-weight: bold;">
                        Counter offered: ₹<?php echo number_format($proposal['counter_price'], 2); ?>
                    </div>
                    <?php if($proposal['seller_response']): ?>
                    <div style="font-size: 0.9em; color: #718096;">
                        Your message: <?php echo htmlspecialchars($proposal['seller_response']); ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="status-<?php echo $proposal['status']; ?>" style="padding: 5px 10px; border-radius: 4px;">
                        <?php echo ucfirst($proposal['status']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    
    <?php
} catch(PDOException $e) {
    echo '<p>Error loading proposals</p>';
}
?>
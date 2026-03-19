<!-- Cart Icon - Direct Link (No Dropdown) -->
<div class="menu-init" id="navigation3">
    <a href="index.php?cart" class="btn btn--icon toggle-button toggle-button--secondary fas fa-shopping-bag toggle-button-shop" style="text-decoration: none;">
        <span style="font-family: var(--font-family);"> CART</span>
    </a>
    <span class="total-item-round" id="cart_items_count_1">0</span>
    <div class="ah-lg-mode">
        <span class="ah-close">✕ Close</span>
        <ul class="ah-list ah-list--design1 ah-list--link-color-secondary">
            <li>
                <a href="index.php" aria-label="Home">
                    <i class="fas fa-home u-c-brand"></i>
                </a>
            </li>
            <?php if ($login_status): ?>
                <li>
                    <a href="index.php?wishlist" aria-label="Wishlist">
                        <i class="far fa-heart"></i>
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="index.php?sign-in" aria-label="Sign in to view wishlist">
                        <i class="far fa-heart"></i>
                    </a>
                </li>
            <?php endif; ?>
            <!-- Direct link to cart - NO dropdown -->
            <li>
                <a href="index.php?cart" class="mini-cart-shop-link" aria-label="View cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="total-item-round" id="cart_items_count_2">0</span>
                </a>
            </li>
        </ul>
    </div>
</div>
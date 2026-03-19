<!-- ====== ALIEXPRESS STYLE SHOP — MOBILE-FIRST FULL REBUILD ====== -->

<?php
/* ── Query vars ── */
$pageno    = isset($_GET['pageno']) ? max(1, (int)$_GET['pageno']) : 1;
$sortby    = isset($_GET['sortby']) ? $_GET['sortby'] : 'RAND()';
$condition = isset($_GET['condition']) ? $_GET['condition'] : '';

$allowed_sorts = [
    'RAND()',
    'p.register_date DESC',
    'p.register_date ASC',
    'CAST(pr.price AS SIGNED INTEGER) ASC',
    'CAST(pr.price AS SIGNED INTEGER) DESC',
    'CAST(p.product_rating AS SIGNED INTEGER) DESC'
];
if (!in_array($sortby, $allowed_sorts)) $sortby = 'RAND()';

$per_page    = 24;
$offset      = ($pageno - 1) * $per_page;
$count_res   = mysqli_query($conn, "SELECT COUNT(p.product_id) FROM product p JOIN product_price pr ON pr.product_id = p.product_id $condition");
$total_rows  = $count_res ? mysqli_fetch_array($count_res)[0] : 0;
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;
$customer_id = isset($_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021']) ? $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'] : '';

/* ── Category query (stored so mobile drawer can reuse) ── */
$sql_cat    = "SELECT * FROM product_category";
$result_cat = $conn->query($sql_cat);
?>

<div class="gb-shop">
  <div class="gb-wrap">
    <div class="gb-layout">

      <!-- ═══════════════════════════════════════════
           SIDEBAR — desktop only
      ════════════════════════════════════════════ -->
      <aside class="gb-sidebar" id="gbSidebar" aria-label="Category filters">
        <div class="gb-sidebar-inner">
          <h2 class="gb-sidebar-title">
            <i class="fas fa-list-ul" aria-hidden="true"></i> Categories
          </h2>
          <ul class="gb-cat-list" role="list">
            <?php
            /* reset pointer in case it was consumed */
            if ($result_cat) $result_cat->data_seek(0);
            while ($result_cat && $row_cat = $result_cat->fetch_assoc()):
                $cat_id   = $row_cat['category_id'];
                $cat_name = $row_cat['category_name'];
                $cnt_res  = $conn->query("SELECT COUNT(*) AS c FROM product WHERE category_id='$cat_id'");
                $cat_cnt  = $cnt_res ? $cnt_res->fetch_assoc()['c'] : 0;
                $sub_res  = $conn->query("SELECT * FROM product_sub_category WHERE category_id='$cat_id'");
            ?>
            <li class="gb-cat-item">
              <a href="index.php?shop-search&search=<?php echo urlencode($cat_name); ?>"
                 class="gb-cat-link">
                <span class="gb-cat-name"><?php echo htmlspecialchars($cat_name); ?></span>
                <span class="gb-cat-badge"><?php echo $cat_cnt; ?></span>
              </a>
              <?php if ($sub_res && $sub_res->num_rows > 0): ?>
              <ul class="gb-subcat-list" role="list">
                <?php while ($sub = $sub_res->fetch_assoc()): ?>
                <li>
                  <a href="index.php?shop-search&search=<?php echo urlencode($sub['sub_category_name']); ?>"
                     class="gb-subcat-link">
                    <?php echo htmlspecialchars($sub['sub_category_name']); ?>
                  </a>
                </li>
                <?php endwhile; ?>
              </ul>
              <?php endif; ?>
            </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </aside>

      <!-- ═══════════════════════════════════════════
           MAIN CONTENT
      ════════════════════════════════════════════ -->
      <main class="gb-main" id="gbMain">

        <!-- ── TOOLBAR ── -->
        <div class="gb-toolbar" id="gbToolbar" role="toolbar" aria-label="Shop controls">
          <div class="gb-toolbar-left">
            <!-- Mobile: open category drawer -->
            <button class="gb-filter-btn gb-mobile-only"
                    onclick="gbToggleDrawer()"
                    aria-expanded="false"
                    aria-controls="gbDrawer"
                    id="gbFilterBtn">
              <i class="fas fa-sliders-h" aria-hidden="true"></i>
              <span>Categories</span>
            </button>
            <p class="gb-item-count">
              <strong><?php echo number_format($total_rows); ?></strong> items found
            </p>
          </div>

          <div class="gb-toolbar-right">
            <label for="gb_sort" class="gb-sort-label">Sort:</label>
            <select id="gb_sort"
                    class="gb-sort-select"
                    onchange="location.href='index.php?shop&sortby='+encodeURIComponent(this.value)+'&condition=<?php echo urlencode($condition); ?>'">
              <option value="RAND()"
                <?php if ($sortby==='RAND()') echo 'selected'; ?>>Best Match</option>
              <option value="p.register_date DESC"
                <?php if ($sortby==='p.register_date DESC') echo 'selected'; ?>>Newest</option>
              <option value="CAST(pr.price AS SIGNED INTEGER) ASC"
                <?php if ($sortby==='CAST(pr.price AS SIGNED INTEGER) ASC') echo 'selected'; ?>>Lowest Price</option>
              <option value="CAST(pr.price AS SIGNED INTEGER) DESC"
                <?php if ($sortby==='CAST(pr.price AS SIGNED INTEGER) DESC') echo 'selected'; ?>>Highest Price</option>
              <option value="CAST(p.product_rating AS SIGNED INTEGER) DESC"
                <?php if ($sortby==='CAST(p.product_rating AS SIGNED INTEGER) DESC') echo 'selected'; ?>>Top Rated</option>
            </select>
          </div>
        </div>

        <!-- ── PRODUCTS GRID ── -->
        <div class="gb-grid" id="pGrid">

          <?php
          /* ══════════════════════════════════════════════════════════════
             FIXED: Using COALESCE to check both 'units' and 'product_unit'
             - First tries 'units' (what Bulk Editor saves)
             - Falls back to 'product_unit' (legacy column)
             - Defaults to 'unit' if both are empty
          ══════════════════════════════════════════════════════════════ */
          $sql = "SELECT p.product_id, p.product_name, 
                         COALESCE(NULLIF(p.units, ''), p.product_unit, 'unit') as product_unit,
                         p.short_description, p.product_rating, pr.price
                  FROM product p
                  JOIN product_price pr ON pr.product_id = p.product_id
                  $condition
                  ORDER BY $sortby
                  LIMIT $offset, $per_page";

          $res = $conn->query($sql);

          if ($res && $res->num_rows > 0):
              $pidx = 0;
              while ($row = $res->fetch_assoc()):
                  $product_id   = $row['product_id'];
                  $product_name = htmlspecialchars($row['product_name']);
                  $product_unit = htmlspecialchars($row['product_unit'] ?: 'unit');
                  $product_rating = isset($row['product_rating']) ? (float)$row['product_rating'] : 0;
                  $price          = (float)$row['price'];

                  $img_q = $conn->query("SELECT picture FROM product_picture WHERE product_id='$product_id' ORDER BY register_date DESC LIMIT 1");
                  $img   = ($img_q && $img_q->num_rows > 0) ? $img_q->fetch_assoc()['picture'] : 'no-image.png';

                  $full_stars  = floor($product_rating);
                  $half_star   = ($product_rating - $full_stars) >= 0.5;
                  $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                  $uid         = 'prod_' . $product_id . '_' . $pidx;
          ?>

          <!-- PRODUCT CARD -->
          <article class="gb-card" role="listitem">

            <!-- Image -->
            <div class="gb-card-img-wrap">
              <a href="index.php?product-detail&product=<?php echo $product_id; ?>"
                 class="gb-card-img-link"
                 aria-label="<?php echo $product_name; ?>">
                <img src="uploads/<?php echo $img; ?>"
                     alt="<?php echo $product_name; ?>"
                     loading="lazy"
                     onerror="this.src='assets/images/no-image.png'"
                     class="gb-card-img">
              </a>

              <!-- Hover / always-visible on touch -->
              <div class="gb-card-actions" aria-label="Quick actions">
                <a href="#quick-look"
                   data-toggle="modal"
                   data-product-id="<?php echo $product_id; ?>"
                   class="gb-action-btn"
                   aria-label="Quick look at <?php echo $product_name; ?>">
                  <i class="fas fa-eye" aria-hidden="true"></i>
                </a>

                <?php if (isset($login_status) && $login_status): ?>
                <button onclick="add_to_wishlist('<?php echo $product_id; ?>','<?php echo $customer_id; ?>')"
                        class="gb-action-btn"
                        aria-label="Add <?php echo $product_name; ?> to wishlist">
                  <i class="far fa-heart" aria-hidden="true"></i>
                </button>
                <?php else: ?>
                <a href="index.php?sign-in"
                   class="gb-action-btn"
                   aria-label="Sign in to add to wishlist">
                  <i class="far fa-heart" aria-hidden="true"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Info -->
            <div class="gb-card-body">

              <h3 class="gb-card-title">
                <a href="index.php?product-detail&product=<?php echo $product_id; ?>">
                  <?php echo mb_strimwidth($product_name, 0, 50, '...'); ?>
                </a>
              </h3>

              <!-- Stars + sold -->
              <div class="gb-card-rating" aria-label="Rating: <?php echo $product_rating; ?> out of 5">
                <span class="gb-stars" aria-hidden="true">
                  <?php
                  echo str_repeat('<i class="fas fa-star"></i>', $full_stars);
                  if ($half_star) echo '<i class="fas fa-star-half-alt"></i>';
                  echo str_repeat('<i class="far fa-star"></i>', $empty_stars);
                  ?>
                </span>
                <span class="gb-sold"><?php echo rand(50,500); ?> sold</span>
              </div>

              <!-- Price -->
              <div class="gb-card-price">
                <span class="gb-price" data-price="<?php echo $price; ?>">
                  <?php echo number_format($price, 0); ?> RWF
                </span>
                <span class="gb-unit">/ <?php echo $product_unit; ?></span>
              </div>

              <!-- Qty + Add to cart -->
              <div class="gb-card-footer">
                <div class="gb-qty-wrap" role="group" aria-label="Quantity">
                  <button type="button"
                          class="gb-qty-btn"
                          onclick="gbDecQty('<?php echo $uid; ?>')"
                          aria-label="Decrease quantity">
                    <i class="fas fa-minus" aria-hidden="true"></i>
                  </button>
                  <input type="text"
                         id="qty_<?php echo $uid; ?>"
                         class="gb-qty-input"
                         value=""
                         placeholder="Qty"
                         inputmode="decimal"
                         aria-label="Quantity for <?php echo $product_name; ?>"
                         oninput="gbValidateQty(this)"
                         onblur="gbBlurQty(this)">
                  <button type="button"
                          class="gb-qty-btn"
                          onclick="gbIncQty('<?php echo $uid; ?>')"
                          aria-label="Increase quantity">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                  </button>
                </div>

                <button type="button"
                        class="gb-cart-btn"
                        onclick="gbAddToCart('<?php echo $product_id; ?>','<?php echo $customer_id; ?>','<?php echo $price; ?>','<?php echo $uid; ?>',event)"
                        aria-label="Add <?php echo $product_name; ?> to cart">
                  <i class="fas fa-cart-plus" aria-hidden="true"></i>
                  <span class="gb-btn-text">Add</span>
                </button>
              </div>

            </div><!-- /gb-card-body -->
          </article>

          <?php
              $pidx++;
              endwhile;
          else:
          ?>
          <div class="gb-empty" role="status">
            <i class="fas fa-box-open" aria-hidden="true"></i>
            <h3>No products found</h3>
            <p>Try adjusting your filters or search terms.</p>
            <a href="index.php?shop" class="gb-reset-btn">Clear Filters</a>
          </div>
          <?php endif; ?>

        </div><!-- /gb-grid -->

        <!-- Infinite scroll sentinels -->
        <div class="gb-loader" id="ldr" aria-live="polite" aria-label="Loading more products">
          <div class="gb-spinner" role="status"></div>
          <span>Loading more…</span>
        </div>
        <div class="gb-done" id="ldn" aria-live="polite">
          <i class="fas fa-check-circle" aria-hidden="true"></i> You've seen everything!
        </div>

        <!-- Cart AJAX target -->
        <div id="result_response_cart" style="display:none;" aria-hidden="true"></div>

        <!-- Ad unit -->
        <div class="gb-ad-wrap">
          <ins class="adsbygoogle"
               style="display:block;text-align:center"
               data-ad-client="ca-pub-5745320266901948"
               data-ad-slot="6630621214"
               data-ad-format="auto"
               data-full-width-responsive="true"></ins>
        </div>

      </main><!-- /gb-main -->
    </div><!-- /gb-layout -->
  </div><!-- /gb-wrap -->
</div><!-- /gb-shop -->

<!-- ═══════════════════════════════════════════════════
     MOBILE CATEGORY DRAWER
════════════════════════════════════════════════════ -->
<div class="gb-overlay" id="gbOverlay" onclick="gbToggleDrawer()" aria-hidden="true"></div>

<div class="gb-drawer"
     id="gbDrawer"
     role="dialog"
     aria-modal="true"
     aria-label="Category filters">

  <div class="gb-drawer-header">
    <h2><i class="fas fa-list-ul" aria-hidden="true"></i> Categories</h2>
    <button class="gb-drawer-close"
            onclick="gbToggleDrawer()"
            aria-label="Close category drawer">
      <i class="fas fa-times" aria-hidden="true"></i>
    </button>
  </div>

  <div class="gb-drawer-body">
    <ul class="gb-cat-list" role="list">
      <?php
      $result_cat_mob = $conn->query($sql_cat);
      if ($result_cat_mob):
          while ($row_mob = $result_cat_mob->fetch_assoc()):
              $mid   = $row_mob['category_id'];
              $mname = $row_mob['category_name'];
              $mcnt  = $conn->query("SELECT COUNT(*) AS c FROM product WHERE category_id='$mid'")->fetch_assoc()['c'];
              $msub  = $conn->query("SELECT * FROM product_sub_category WHERE category_id='$mid'");
      ?>
      <li class="gb-cat-item">
        <a href="index.php?shop-search&search=<?php echo urlencode($mname); ?>"
           class="gb-cat-link"
           onclick="gbToggleDrawer()">
          <span class="gb-cat-name"><?php echo htmlspecialchars($mname); ?></span>
          <span class="gb-cat-badge"><?php echo $mcnt; ?></span>
        </a>
        <?php if ($msub && $msub->num_rows > 0): ?>
        <ul class="gb-subcat-list" role="list">
          <?php while ($ms = $msub->fetch_assoc()): ?>
          <li>
            <a href="index.php?shop-search&search=<?php echo urlencode($ms['sub_category_name']); ?>"
               class="gb-subcat-link"
               onclick="gbToggleDrawer()">
              <?php echo htmlspecialchars($ms['sub_category_name']); ?>
            </a>
          </li>
          <?php endwhile; ?>
        </ul>
        <?php endif; ?>
      </li>
      <?php endwhile; endif; ?>
    </ul>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════ -->
<script>
'use strict';

/* ── Drawer ── */
function gbToggleDrawer() {
    var overlay = document.getElementById('gbOverlay');
    var drawer  = document.getElementById('gbDrawer');
    var btn     = document.getElementById('gbFilterBtn');
    var open    = drawer.classList.contains('is-open');

    overlay.classList.toggle('is-open', !open);
    drawer.classList.toggle('is-open', !open);
    document.body.style.overflow = open ? '' : 'hidden';
    if (btn) btn.setAttribute('aria-expanded', String(!open));

    /* trap focus inside drawer when open */
    if (!open) {
        var first = drawer.querySelector('a,button,[tabindex]');
        if (first) setTimeout(function(){ first.focus(); }, 310);
    }
}

/* close drawer on Escape */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var d = document.getElementById('gbDrawer');
        if (d && d.classList.contains('is-open')) gbToggleDrawer();
    }
});

/* ── Quantity helpers ── */
function gbFmt(n) {
    if (n === '' || isNaN(n)) return '';
    return parseFloat(parseFloat(n).toFixed(3)).toString();
}

function gbIncQty(uid) {
    var el  = document.getElementById('qty_' + uid);
    var cur = parseFloat(el.value) || 0;
    el.value = gbFmt(cur + (cur % 1 !== 0 ? 0.5 : 1));
}

function gbDecQty(uid) {
    var el  = document.getElementById('qty_' + uid);
    var cur = parseFloat(el.value) || 0;
    var nxt = cur - (cur % 1 !== 0 ? 0.5 : 1);
    el.value = nxt > 0 ? gbFmt(nxt) : '';
}

function gbValidateQty(input) {
    var v = input.value.replace(/[^0-9.]/g, '');
    var p = v.split('.');
    input.value = p.length > 2 ? p[0] + '.' + p.slice(1).join('') : v;
}

function gbBlurQty(input) {
    var v = input.value.trim();
    if (!v || v === '.') { input.value = ''; return; }
    var n = parseFloat(v);
    input.value = (isNaN(n) || n <= 0) ? '' : gbFmt(n);
}

/* ── Add to cart ── */
function gbAddToCart(productId, customerId, price, uid, evt) {
    var input = document.getElementById('qty_' + uid);
    var qStr  = input.value.trim();

    if (!qStr || qStr === '0') {
        gbNotify('Please enter a quantity', 'warning');
        input.focus();
        input.classList.add('gb-qty-error');
        setTimeout(function(){ input.classList.remove('gb-qty-error'); }, 2000);
        return;
    }

    var qty = parseFloat(qStr);
    if (isNaN(qty) || qty <= 0) {
        gbNotify('Please enter a valid quantity', 'warning');
        input.focus();
        return;
    }

    /* resolve guest customer id */
    if (!customerId) {
        var tmp = document.getElementById('customer_temp_id');
        if (tmp && tmp.value) customerId = tmp.value;
    }

    var btn = evt ? evt.currentTarget : null;
    var orig = btn ? btn.innerHTML : '';
    if (btn) { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true; }

    function restore() {
        if (btn) { btn.innerHTML = orig; btn.disabled = false; }
    }

    if (typeof add_to_cart === 'function') {
        add_to_cart(productId, customerId, price, qty);
        setTimeout(restore, 800);
    } else {
        gbAjaxCart(productId, customerId, price, qty, restore);
    }

    input.value = '';
}

function gbAjaxCart(productId, customerId, price, qty, done) {
    if (typeof $ === 'undefined') { done(); return; }
    $.ajax({
        url: 'action/insert.php',
        type: 'POST',
        data: { action:'ADD_TO_CART', product_id:productId,
                customer_id:customerId, price:price, product_quantity:qty },
        success: function(r) {
            $('#result_response_cart').html(r);
            gbNotify('Added to cart!', 'success');
            if (typeof get_cart_items === 'function') get_cart_items();
            done();
        },
        error: function() { gbNotify('Error adding to cart.', 'error'); done(); }
    });
}

function gbNotify(msg, type) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type, title: msg,
            showConfirmButton: false, timer: 2200,
            toast: true, position: 'top-end'
        });
    }
}

/* ── Infinite scroll (page-based fetch) ── */
(function() {
    var page   = <?php echo (int)$pageno; ?>;
    var maxPg  = <?php echo (int)$total_pages; ?>;
    var sort   = <?php echo json_encode(urlencode($sortby)); ?>;
    var cond   = <?php echo json_encode(urlencode($condition)); ?>;

    var grid   = document.getElementById('pGrid');
    var ldr    = document.getElementById('ldr');
    var ldn    = document.getElementById('ldn');
    var busy   = false;

    if (!grid || !ldr || !ldn) return;

    /* initial state */
    if (page >= maxPg) {
        ldr.style.display = 'none';
        if (maxPg > 0) ldn.style.display = 'flex';
        return;
    }
    ldr.style.display = 'flex';

    function loadNext() {
        if (busy || page >= maxPg) return;
        busy = true;
        page++;

        fetch('index.php?shop&pageno=' + page + '&sortby=' + sort + '&condition=' + cond)
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var doc   = new DOMParser().parseFromString(html, 'text/html');
                var cards = doc.querySelectorAll('#pGrid .gb-card');

                cards.forEach(function(card) {
                    /* simple fade-in for newly appended cards */
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(16px)';
                    grid.appendChild(card);
                    requestAnimationFrame(function() {
                        card.style.transition = 'opacity .35s ease, transform .35s ease';
                        card.style.opacity    = '1';
                        card.style.transform  = 'translateY(0)';
                    });
                });

                if (typeof gbUpdatePrices === 'function') gbUpdatePrices();
                if (typeof convertGBPrices === 'function') convertGBPrices();

                busy = false;
                if (page >= maxPg) {
                    ldr.style.display = 'none';
                    ldn.style.display = 'flex';
                }
            })
            .catch(function(err) {
                console.error('[GB Shop] Infinite scroll error:', err);
                busy = false;
                ldr.style.display = 'none';
            });
    }

    /* Intersection Observer (primary) */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) loadNext();
        }, { rootMargin: '700px' });
        io.observe(ldr);
    } else {
        /* fallback scroll listener */
        var ticking = false;
        function onScroll() {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function() {
                var r = ldr.getBoundingClientRect();
                if (r.top <= (window.innerHeight || document.documentElement.clientHeight) + 700) {
                    loadNext();
                }
                ticking = false;
            });
        }
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* safety net: fire once after DOM settles */
    setTimeout(function() {
        var r = ldr.getBoundingClientRect();
        if (r.top <= (window.innerHeight || document.documentElement.clientHeight) + 700) loadNext();
    }, 500);
})();

/* init price converter if available */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gbUpdatePrices === 'function') gbUpdatePrices();
});
</script>

<!-- ═══════════════════════════════════════════════════
     CSS — MOBILE-FIRST
════════════════════════════════════════════════════ -->
<style>
/* ── Tokens ── */
:root {
    --gb-orange:   #ff5000;
    --gb-orange-d: #e64a00;
    --gb-dark:     #1a1a1a;
    --gb-gray:     #666;
    --gb-light:    #f5f6f8;
    --gb-border:   #e8e8e8;
    --gb-white:    #ffffff;
    --gb-warn:     #e53935;
    --gb-star:     #ffc107;
    --gb-radius:   12px;
    --gb-shadow:   0 2px 10px rgba(0,0,0,.06);
    --gb-shadow-h: 0 8px 24px rgba(0,0,0,.10);
    --gb-trans:    .22s ease;
    --gb-sidebar:  240px;
    --gb-gap:      16px;
}

/* ── Reset / base ── */
*, *::before, *::after { box-sizing: border-box; }
img { display: block; max-width: 100%; }

/* ── Shell ── */
.gb-shop   { padding: 12px 0; background: var(--gb-light); min-height: 60vh; }
.gb-wrap   { max-width: 1440px; margin: 0 auto; padding: 0 12px; }
.gb-layout { display: flex; gap: var(--gb-gap); align-items: flex-start; }

/* ══════════════════════════════════════
   SIDEBAR
══════════════════════════════════════ */
.gb-sidebar { display: none; } /* hidden by default (mobile first) */

.gb-sidebar-inner {
    position: sticky;
    top: 76px;
    background: var(--gb-white);
    border-radius: var(--gb-radius);
    padding: 18px;
    box-shadow: var(--gb-shadow);
    max-height: calc(100vh - 100px);
    overflow-y: auto;
    scrollbar-width: thin;
}

.gb-sidebar-title {
    font-size: .95rem;
    font-weight: 800;
    color: var(--gb-dark);
    margin: 0 0 14px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--gb-border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.gb-sidebar-title i { color: var(--gb-orange); }

/* ── Category list (shared sidebar + drawer) ── */
.gb-cat-list    { list-style: none; padding: 0; margin: 0; }
.gb-cat-item    { margin-bottom: 4px; }

.gb-cat-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 11px;
    border-radius: 8px;
    text-decoration: none;
    color: var(--gb-dark);
    font-size: .875rem;
    font-weight: 500;
    border: 1px solid transparent;
    transition: var(--gb-trans);
}
.gb-cat-link:hover,
.gb-cat-link:focus {
    background: #fff3ee;
    color: var(--gb-orange);
    border-color: #ffd8c8;
    outline: none;
}

.gb-cat-badge {
    background: #f0f0f0;
    color: var(--gb-gray);
    font-size: .75rem;
    padding: 2px 7px;
    border-radius: 20px;
    transition: var(--gb-trans);
}
.gb-cat-link:hover .gb-cat-badge { background: var(--gb-orange); color: #fff; }

.gb-subcat-list {
    list-style: none;
    padding: 4px 0 4px 16px;
    margin: 2px 0 6px 12px;
    border-left: 2px solid var(--gb-border);
}
.gb-subcat-link {
    display: block;
    padding: 5px 6px;
    font-size: .82rem;
    color: var(--gb-gray);
    text-decoration: none;
    border-radius: 5px;
    transition: var(--gb-trans);
}
.gb-subcat-link:hover { color: var(--gb-orange); background: #fff3ee; }

/* ══════════════════════════════════════
   MAIN
══════════════════════════════════════ */
.gb-main { flex: 1; min-width: 0; }

/* ── Toolbar ── */
.gb-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    align-items: center;
    background: var(--gb-white);
    border-radius: var(--gb-radius);
    padding: 12px 16px;
    margin-bottom: 14px;
    box-shadow: var(--gb-shadow);
    position: sticky;
    top: 60px;
    z-index: 90;
}

.gb-toolbar-left  { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.gb-toolbar-right { display: flex; align-items: center; gap: 8px; }

.gb-item-count { font-size: .875rem; color: var(--gb-gray); margin: 0; }
.gb-item-count strong { color: var(--gb-dark); }

.gb-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    background: var(--gb-white);
    border: 1.5px solid var(--gb-border);
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 600;
    color: var(--gb-dark);
    cursor: pointer;
    transition: var(--gb-trans);
    touch-action: manipulation;
}
.gb-filter-btn:hover { border-color: var(--gb-orange); color: var(--gb-orange); }

.gb-sort-label { font-size: .875rem; color: var(--gb-gray); white-space: nowrap; }

.gb-sort-select {
    padding: 8px 32px 8px 12px;
    border: 1.5px solid var(--gb-border);
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 500;
    color: var(--gb-dark);
    background: var(--gb-white)
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23444' d='M6 8L1 3h10z'/%3E%3C/svg%3E")
        no-repeat right 10px center;
    appearance: none;
    cursor: pointer;
    outline: none;
    transition: var(--gb-trans);
}
.gb-sort-select:focus { border-color: var(--gb-orange); }

/* ── Grid ── */
.gb-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* mobile: 2-col */
    gap: 10px;
}

/* ── Product card ── */
.gb-card {
    background: var(--gb-white);
    border-radius: var(--gb-radius);
    overflow: hidden;
    box-shadow: var(--gb-shadow);
    display: flex;
    flex-direction: column;
    border: 1.5px solid transparent;
    transition: transform var(--gb-trans), box-shadow var(--gb-trans), border-color var(--gb-trans);
    position: relative;
}
.gb-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--gb-shadow-h);
    border-color: #ffd0be;
}

/* ── Card image ── */
.gb-card-img-wrap {
    aspect-ratio: 1 / 1;
    position: relative;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    overflow: hidden;
}

.gb-card-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform .4s ease;
}
.gb-card:hover .gb-card-img { transform: scale(1.06); }

/* ── Quick-action buttons (eye + wishlist) ── */
.gb-card-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    flex-direction: row;
    gap: 6px;
    /* visible on touch devices always; fade-in on desktop hover */
    opacity: 1;
    transition: opacity var(--gb-trans), transform var(--gb-trans);
}

.gb-action-btn {
    width: 34px;
    height: 34px;
    background: rgba(255,255,255,.92);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gb-gray);
    font-size: .85rem;
    box-shadow: 0 2px 6px rgba(0,0,0,.12);
    cursor: pointer;
    text-decoration: none;
    transition: var(--gb-trans);
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.gb-action-btn:hover { background: var(--gb-orange); color: #fff; }

/* ── Card body ── */
.gb-card-body {
    padding: 10px 10px 12px;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 6px;
}

.gb-card-title {
    margin: 0;
    font-size: .82rem;
    line-height: 1.35;
}
.gb-card-title a {
    color: var(--gb-dark);
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-weight: 600;
}
.gb-card-title a:hover { color: var(--gb-orange); }

/* Stars */
.gb-card-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}
.gb-stars    { color: var(--gb-star); font-size: .7rem; letter-spacing: .5px; }
.gb-sold     { font-size: .72rem; color: var(--gb-gray); }

/* Price */
.gb-card-price { display: flex; align-items: baseline; gap: 4px; }
.gb-price      { font-size: 1rem; font-weight: 800; color: var(--gb-orange); }
.gb-unit       { font-size: .72rem; color: var(--gb-gray); }

/* Footer: qty + cart */
.gb-card-footer {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

/* Quantity */
.gb-qty-wrap {
    display: flex;
    border: 1.5px solid var(--gb-border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--gb-white);
    height: 38px;
    transition: border-color var(--gb-trans);
}
.gb-qty-wrap:focus-within { border-color: var(--gb-orange); }

.gb-qty-btn {
    width: 38px;
    flex-shrink: 0;
    background: #f3f3f3;
    border: none;
    color: var(--gb-dark);
    font-size: .8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--gb-trans);
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.gb-qty-btn:hover { background: var(--gb-orange); color: #fff; }
.gb-qty-btn:active { background: var(--gb-orange-d); }

.gb-qty-input {
    flex: 1;
    min-width: 0;
    border: none;
    text-align: center;
    font-size: .9rem;
    font-weight: 700;
    color: var(--gb-dark);
    background: transparent;
    padding: 0 4px;
}
.gb-qty-input:focus { outline: none; }

/* Cart button */
.gb-cart-btn {
    width: 100%;
    padding: 10px 8px;
    background: var(--gb-orange);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: var(--gb-trans);
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.gb-cart-btn:hover  { background: var(--gb-orange-d); transform: translateY(-1px); }
.gb-cart-btn:active { background: var(--gb-orange-d); transform: translateY(0); }

/* Qty error */
.gb-qty-error { border-color: var(--gb-warn) !important; animation: gbShake .4s; }
@keyframes gbShake {
    0%,100% { transform: translateX(0); }
    25%,75%  { transform: translateX(-5px); }
    50%      { transform: translateX(5px); }
}

/* Empty state */
.gb-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--gb-white);
    border-radius: var(--gb-radius);
}
.gb-empty i  { font-size: 3rem; color: #ddd; display: block; margin-bottom: 12px; }
.gb-empty h3 { font-size: 1.1rem; margin: 0 0 8px; }
.gb-empty p  { font-size: .9rem; color: var(--gb-gray); }

.gb-reset-btn {
    display: inline-block;
    margin-top: 14px;
    padding: 10px 24px;
    background: var(--gb-orange);
    color: #fff;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: .9rem;
    transition: var(--gb-trans);
}
.gb-reset-btn:hover { background: var(--gb-orange-d); }

/* Infinite scroll sentinels */
.gb-loader,
.gb-done {
    display: none;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 32px 20px;
    color: var(--gb-gray);
    font-size: .9rem;
    font-weight: 600;
    grid-column: 1 / -1;
}

.gb-done {
    background: var(--gb-white);
    border-radius: var(--gb-radius);
    margin-top: 10px;
    border: 1px dashed var(--gb-border);
}

.gb-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid var(--gb-border);
    border-top-color: var(--gb-orange);
    border-radius: 50%;
    animation: gbSpin .65s linear infinite;
    flex-shrink: 0;
}
@keyframes gbSpin { to { transform: rotate(360deg); } }

/* Ad */
.gb-ad-wrap { margin-top: 36px; }

/* ══════════════════════════════════════
   MOBILE OVERLAY + DRAWER
══════════════════════════════════════ */
.gb-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: opacity .3s, visibility .3s;
    -webkit-tap-highlight-color: transparent;
}
.gb-overlay.is-open { opacity: 1; visibility: visible; }

.gb-drawer {
    position: fixed;
    bottom: -100%;
    left: 0;
    width: 100%;
    max-height: 88vh;
    background: var(--gb-white);
    border-radius: 20px 20px 0 0;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    transition: bottom .35s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 -4px 24px rgba(0,0,0,.12);
}
.gb-drawer.is-open { bottom: 0; }

.gb-drawer-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gb-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.gb-drawer-header h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gb-dark);
}
.gb-drawer-header i { color: var(--gb-orange); }

.gb-drawer-close {
    width: 36px;
    height: 36px;
    background: #f3f3f3;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: var(--gb-gray);
    cursor: pointer;
    transition: var(--gb-trans);
    touch-action: manipulation;
}
.gb-drawer-close:hover { background: var(--gb-orange); color: #fff; }

.gb-drawer-body {
    overflow-y: auto;
    padding: 16px 20px 32px; /* extra bottom for safe area */
    flex: 1;
    /* iOS momentum scroll */
    -webkit-overflow-scrolling: touch;
}

/* safe area on modern iPhones */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .gb-drawer-body { padding-bottom: calc(32px + env(safe-area-inset-bottom)); }
}

/* ══════════════════════════════════════
   UTILITY
══════════════════════════════════════ */
.gb-mobile-only { display: inline-flex; }

/* ══════════════════════════════════════
   RESPONSIVE — TABLET  ≥ 600px
══════════════════════════════════════ */
@media (min-width: 600px) {
    .gb-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    .gb-card-title { font-size: .875rem; }
    .gb-price      { font-size: 1.05rem; }
}

/* ══════════════════════════════════════
   RESPONSIVE — TABLET LANDSCAPE  ≥ 768px
══════════════════════════════════════ */
@media (min-width: 768px) {
    .gb-shop  { padding: 16px 0; }
    .gb-grid  { grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .gb-card-body { padding: 12px 12px 14px; }

    /* desktop-style actions: hide until hover */
    .gb-card-actions {
        opacity: 0;
        transform: translateX(8px);
        flex-direction: column;
    }
    .gb-card:hover .gb-card-actions {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ══════════════════════════════════════
   RESPONSIVE — DESKTOP  ≥ 992px
══════════════════════════════════════ */
@media (min-width: 992px) {
    .gb-wrap { padding: 0 20px; }

    /* reveal sidebar */
    .gb-sidebar {
        display: block;
        flex: 0 0 var(--gb-sidebar);
        width: var(--gb-sidebar);
    }

    /* hide mobile filter button */
    .gb-mobile-only { display: none !important; }

    .gb-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }

    .gb-toolbar { top: 70px; }
}

/* ══════════════════════════════════════
   RESPONSIVE — WIDE DESKTOP  ≥ 1200px
══════════════════════════════════════ */
@media (min-width: 1200px) {
    .gb-grid { grid-template-columns: repeat(4, 1fr); }
    .gb-card-title { font-size: .9rem; }
}

@media (min-width: 1440px) {
    .gb-grid { grid-template-columns: repeat(5, 1fr); }
}

/* ══════════════════════════════════════
   SMALL PHONES  ≤ 380px
══════════════════════════════════════ */
@media (max-width: 380px) {
    .gb-wrap   { padding: 0 8px; }
    .gb-grid   { gap: 8px; }
    .gb-price  { font-size: .9rem; }
    .gb-cart-btn { font-size: .8rem; padding: 9px 6px; }
    .gb-btn-text { display: none; } /* icon only on very small screens */
    .gb-toolbar { padding: 10px 10px; }
    .gb-item-count { font-size: .8rem; }
    .gb-sort-select { font-size: .8rem; }
}
</style>
<?php
// includes/theme_runtime_flatsome.php
// Applies Theme Options to Flatsome product cards + typography globally

if (!isset($conn) || !($conn instanceof mysqli)) { return; }

require_once __DIR__ . '/theme_options.php';
$opt = gb_get_theme_options($conn);

// fonts (safe local)
$fontMap = [
  "system" => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif",
  "inter"  => "'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif",
  "roboto" => "Roboto,-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif",
  "poppins"=> "Poppins,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif",
];
$font = $fontMap[$opt['font_family']] ?? $fontMap["system"];

$ratio = $opt['card_image_ratio'] ?? '1:1';
$ratioCss = "1 / 1";
if ($ratio === "4:3") $ratioCss = "4 / 3";
if ($ratio === "16:9") $ratioCss = "16 / 9";

$cardStyle = $opt['card_style'] ?? "shadow";
$shadow = $cardStyle === "shadow" ? "0 10px 26px rgba(0,0,0,.10)" : "none";
$border = "1px solid rgba(0,0,0,.08)";
if ($cardStyle === "flat") $border = "1px solid transparent";

$btnStyle = $opt['btn_style'] ?? "pill";
$btnRadius = "999px";
if ($btnStyle === "rounded") $btnRadius = "14px";
if ($btnStyle === "square") $btnRadius = "8px";

$spacing = $opt['spacing'] ?? "normal";
$pad = "12px";
$gap = "12px";
if ($spacing === "compact") { $pad="10px"; $gap="10px"; }
if ($spacing === "spacious") { $pad="14px"; $gap="14px"; }

$gd = max(1, min(6, (int)$opt['product_grid_desktop']));
$gt = max(1, min(4, (int)$opt['product_grid_tablet']));
$gm = max(1, min(3, (int)$opt['product_grid_mobile']));

$container = max(980, min(1600, (int)$opt['container_width']));

$primary = htmlspecialchars($opt['primary_color']);
$accent  = htmlspecialchars($opt['accent_color']);
$muted   = htmlspecialchars($opt['muted_color']);
$bg      = htmlspecialchars($opt['bg_color']);
$text    = htmlspecialchars($opt['text_color']);
$price   = htmlspecialchars($opt['price_color']);

$imgFit  = ($opt['card_image_fit'] === 'contain') ? 'contain' : 'cover';

$showBadges = ($opt['show_badges'] ?? '1') === '1';
$showCat    = ($opt['show_category_chip'] ?? '1') === '1';
$showUnit   = ($opt['show_unit_chip'] ?? '1') === '1';
$showQuick  = ($opt['show_quick_add'] ?? '1') === '1';
?>
<style id="gb-theme-runtime-flatsome">
/* =======================
   Global typography/colors
   ======================= */
html, body{
  font-family: <?php echo $font; ?> !important;
  font-size: <?php echo (int)$opt['base_font_size']; ?>px !important;
  color: <?php echo $text; ?> !important;
  background: <?php echo $bg; ?> !important;
}
:root{
  --gb-primary: <?php echo $primary; ?>;
  --gb-accent: <?php echo $accent; ?>;
  --gb-muted: <?php echo $muted; ?>;
  --gb-bg: <?php echo $bg; ?>;
  --gb-text: <?php echo $text; ?>;
  --gb-price: <?php echo $price; ?>;

  --gb-radius: <?php echo (int)$opt['radius']; ?>px;
  --gb-btn-radius: <?php echo $btnRadius; ?>;
  --gb-card-shadow: <?php echo $shadow; ?>;
  --gb-card-border: <?php echo $border; ?>;
  --gb-card-pad: <?php echo $pad; ?>;
  --gb-card-gap: <?php echo $gap; ?>;
  --gb-img-ratio: <?php echo $ratioCss; ?>;
  --gb-img-fit: <?php echo $imgFit; ?>;
  --gb-container: <?php echo $container; ?>px;
}

/* Optional: tighten page container (Flatsome uses .container, .row) */
.container, .row, .page-wrapper{
  max-width: var(--gb-container);
}

/* =======================
   Flatsome product cards
   ======================= */
/* Card outer */
.products .product-small,
.products .product,
.woocommerce .products .product{
  border: var(--gb-card-border) !important;
  border-radius: var(--gb-radius) !important;
  overflow: hidden !important;
  box-shadow: var(--gb-card-shadow) !important;
  background: #fff !important;
}

/* Inner padding for text */
.products .product-small .box-text,
.woocommerce .products .product .box-text,
.woocommerce .products .product .woocommerce-loop-product__title,
.products .product-small .box-text-inner{
  padding: var(--gb-card-pad) !important;
}

/* Title */
.products .product-small .name,
.woocommerce-loop-product__title,
.products .product .name{
  font-weight: 800 !important;
  color: var(--gb-text) !important;
}

/* Price */
.price, .woocommerce-Price-amount{
  color: var(--gb-price) !important;
  font-weight: 900 !important;
}

/* Image ratio + fit */
.products .product-small .box-image,
.woocommerce .products .product .box-image,
.woocommerce .products .product a img{
  aspect-ratio: var(--gb-img-ratio);
}
.products .product-small .box-image img,
.woocommerce .products .product img{
  width:100% !important;
  height:100% !important;
  object-fit: var(--gb-img-fit) !important;
}

/* Buttons */
.button, button, .ux-search-submit, .cart .button, .checkout-button,
.add_to_cart_button, .single_add_to_cart_button{
  border-radius: var(--gb-btn-radius) !important;
}

/* Primary button feel */
.button.primary, .button.alt, .checkout-button, .single_add_to_cart_button{
  background: var(--gb-primary) !important;
  border-color: var(--gb-primary) !important;
  color:#fff !important;
}
.button.primary:hover, .button.alt:hover, .checkout-button:hover, .single_add_to_cart_button:hover{
  filter: brightness(.96);
}

/* Links hover accent */
a:hover{ color: var(--gb-accent) !important; }

/* Product grid columns (Flatsome uses responsive classes, we override with CSS grid safely) */
.woocommerce ul.products,
ul.products{
  display:grid !important;
  gap: var(--gb-card-gap) !important;
  grid-template-columns: repeat(<?php echo $gd; ?>, minmax(0, 1fr)) !important;
}
@media(max-width:1024px){
  .woocommerce ul.products, ul.products{
    grid-template-columns: repeat(<?php echo $gt; ?>, minmax(0, 1fr)) !important;
  }
}
@media(max-width:640px){
  .woocommerce ul.products, ul.products{
    grid-template-columns: repeat(<?php echo $gm; ?>, minmax(0, 1fr)) !important;
  }
}

/* Hide elements by options */
<?php if(!$showBadges): ?>
.woocommerce span.onsale, .badge, .badge-container { display:none !important; }
<?php endif; ?>

/* Category/unit chips: Flatsome often shows category below title; if you render unit text somewhere else, we can hide by selector later */
<?php if(!$showCat): ?>
.products .product-small .category, .products .product .category { display:none !important; }
<?php endif; ?>

<?php if(!$showQuick): ?>
/* quick add buttons / add to cart in loops */
.add_to_cart_button, .ajax_add_to_cart { display:none !important; }
<?php endif; ?>
</style>
            <!--====== Section 1 ======-->
            <!--<div class="u-s-p-y-60">

                <div class="section__content">
                    <div class="container">
                        <div class="breadcrumb">
                            <div class="breadcrumb__wrap">
                                <ul class="breadcrumb__list">
                                    <li class="has-separator">

                                        <a href="index.php">Home</a></li>
                                    <li class="is-marked">

                                        <a href="index.php?sign-in">Signin</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>-->
            <!--====== End - Section 1 ======-->


            <!--====== Section 2 ======-->
            <div class="u-s-p-b-60">

                <!--====== Section Intro ======-->
                <!--<div class="section__intro u-s-m-b-60">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section__text-wrap">
                                    <h1 class="section__heading u-c-secondary" style='font-size:140%;'>ALREADY REGISTERED?</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->
                <!--====== End - Section Intro ======-->


                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="container-fluid">
                        <div class="row row--center">
                            <div class="col-lg-4 u-s-m-b-30" id="hidden_in_small">



<div class="col-lg-12">
<center><h1 class="gl-h1" style="padding:0;">BROWSE OUR SHOP</h1></center>

                                <div class="filter__grid-wrapper u-s-m-t-30">
                                    <div class="row">

                                        <?php
                                    	//$sql53="SELECT * FROM product WHERE category_id IN (SELECT DISTINCT category_id from product) GROUP BY category_id LIMIT 0,3";
                                    
                                    	$sql5311="SELECT DISTINCT category_id from product ORDER BY RAND()";
                                        $result5311=$conn->query($sql5311);
                                        $b = 1;
                                        while ($row5311 = $result5311->fetch_assoc()) {
                                            
                                            if($b >= 5){
                                                goto end1;
                                            }
                                            
                                        $category_id_1=$row5311['category_id'];
                                        
                                        //$sql53="SELECT * from product order by register_date DESC limit 20";
                                       // $sql53="SELECT * from product WHERE category_id='$category_id_1' ORDER BY RAND() LIMIT 0,4";
                                        $sql53="SELECT * from product WHERE category_id='$category_id_1' ORDER BY RAND() LIMIT 0,1";
                                        $result53=$conn->query($sql53);
                                        $a = 1;
                                        
                                        while ($row53 = $result53->fetch_assoc()) {
                                            
                                            
                                            
                                            $product_id =$row53['product_id'];
                                            $category_id=$row53['category_id'];
                                            $sub_category_id =$row53['sub_category_id'];
                                            $product_name =$row53['product_name'];
                                            $product_unit =$row53['product_unit'];
                                            $short_description =$row53['short_description'];
                                            $long_description =$row53['long_description'];
                                        	$product_minimum_order =$row53['product_minimum_order'];
                                            $register_date =$row53['register_date'];

                                            $product_quantity = 0;
                                            $sql="SELECT * from product_stock where product_id='$product_id'";
                                            $result=$conn->query($sql);
                                            while ($row = $result->fetch_assoc()) {
                                              $product_quantity=$row['stock_quantity'];
                                          }

                                          $sql="SELECT * from product_price where product_id='$product_id'";
                                          $result=$conn->query($sql);
                                          while ($row = $result->fetch_assoc()) {
                                              $product_price=$row['price'];
                                          }

                                          $sql="SELECT * from product_category where category_id='$category_id'";
                                          $result=$conn->query($sql);
                                          while ($row = $result->fetch_assoc()) {
                                              $category_name=$row['category_name'];
                                          }

                                          $customer_id_1 = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];

                                          $id1="Mine".$a;
                                          $id2="Mine1".$a;
                                          $id3="Mine2".$a;
                                          $id4="Mine3".$a;
                                         

                                          ?>

                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 u-s-m-b-30 filter__item <?php echo $category_id; ?>">
                                            <div class="product-o product-o--hover-on product-o--radius">
                                                <div class="product-o__wrap">
                                                    <?php
                                                    $sql="SELECT * from product_picture where product_id='$product_id' order by register_date DESC limit 1";
                                                    $result=$conn->query($sql);
                                                    while ($row = $result->fetch_assoc()) {
                                                        $picture=$row['picture'];

                                                        ?>
                                                        <a class="aspect aspect--bg-grey aspect--square u-d-block" href="index.php?product-detail&product=<?php echo $product_id; ?>">

                                                            <img class="aspect__img" src="uploads/<?php echo $picture; ?>" alt="" style='object-fit: cover;'></a>

                                                        <?php } ?>

                                                    <div class="product-o__action-wrap">
                                                        <ul class="product-o__action-list">
                                                            <li>

                                                                <a href="#quick-look" data-toggle="modal" data-modal="modal" data-product-id="<?php echo $product_id; ?>" data-tooltip="tooltip" data-placement="top" title="Quick View"><i class="fas fa-search-plus"></i></a></li>
                                                            <li>
                                                                <?php if($product_quantity > 0): ?>
                                                                <a onclick="add_to_cart('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>','<?php echo $product_price; ?>','<?php echo $product_minimum_order; ?>')" data-tooltip="tooltip" data-placement="top" title="Add to Cart [<?php echo $product_minimum_order; ?> <?php echo $product_unit; ?>] "><i class="fas fa-plus-circle"></i></a>
                                                                <?php else: ?>
                                                                <a style="opacity:.4;cursor:not-allowed;pointer-events:none;" data-tooltip="tooltip" data-placement="top" title="Out of Stock"><i class="fas fa-ban"></i></a>
                                                                <?php endif; ?>
                                                            </li>
                                                        
                                                        	<?php if($login_status){ ?>
                                
                                							<li>
                                                                <a href="#/" onclick="add_to_wishlist('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>')" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"><i class="fas fa-heart"></i></a>
                                                        	</li>
                                
                                							<?php }else{ ?>
                                                        
                                							<li>
                                                                <a href="index.php?sign-in" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"><i class="fas fa-heart"></i></a>
                                                        	</li>
                                                        
                                							<?php } ?>
                                                                                                                
                                                        </ul>
                                                    </div>
                                                </div>

                                                <span class="product-o__category">

                                                    <a href="index.php?shop-search&search=<?php echo $category_name; ?>"><?php echo $category_name; ?></a></span>

                                                <span class="product-o__name">

                                                    <a href="index.php?product-detail&product=<?php echo $product_id; ?>"><?php echo $product_name; ?></a></span>
                                                <div class="product-o__rating gl-rating-style"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>

                                                    <span class="product-o__review">(23)</span></div>

                                                <span class="product-o__price"><?php echo number_format( $product_price, 0 ); ?> RWF / <?php echo $product_unit; ?>

                                                    <!--<span class="product-o__discount">$160.00</span>--></span>
                                            </div>
                                        </div>

                                        <?php $a++; } $b++; end1: } ?>

                                        <div id="result_response_cart" style="display: none;"></div>

                                    </div>
                                </div>
                                
                                <a href="index.php?shop">
                                <div class="load-more" style="padding:0;">
                                    <button class="btn btn--e-brand" type="button">Load More</button>
                                </div>
                                </a>
                                
                            </div>




                            </div>
                            <div class="col-lg-4 col-md-8 u-s-m-b-30">
                                <div class="l-f-o">
                                    <div class="l-f-o__pad-box">
                                        
                                        <h1 class="gl-h1">ALMOST THERE!</h1>

                                        <div class="u-s-m-b-15">

                                            <a class="l-f-o__create-link btn--e-transparent-brand-b-2" href="index.php?sign-in">LOG IN OR CREATE AN ACCOUNT</a></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 u-s-m-b-30" id="hidden_in_small">






<div class="col-lg-12">
<center><h1 class="gl-h1" style="padding:0;">BROWSE OUR SHOP</h1></center>    

                                <div class="filter__grid-wrapper u-s-m-t-30">
                                    <div class="row">

                                        <?php
                                    	//$sql53="SELECT * FROM product WHERE category_id IN (SELECT DISTINCT category_id from product) GROUP BY category_id LIMIT 0,3";
                                    
                                    	$sql5311="SELECT DISTINCT category_id from product ORDER BY RAND()";
                                        $result5311=$conn->query($sql5311);
                                        $b=1;
                                        while ($row5311 = $result5311->fetch_assoc()) {
                                            
                                            if($b >= 5){
                                                goto end2;
                                            }
                                            
                                        $category_id_1=$row5311['category_id'];
                                        
                                        //$sql53="SELECT * from product order by register_date DESC limit 20";
                                       // $sql53="SELECT * from product WHERE category_id='$category_id_1' ORDER BY RAND() LIMIT 0,4";
                                        $sql53="SELECT * from product WHERE category_id='$category_id_1' ORDER BY RAND() LIMIT 0,1";
                                        $result53=$conn->query($sql53);
                                        $a = 1;
                                        while ($row53 = $result53->fetch_assoc()) {
                                            
                                            $product_id =$row53['product_id'];
                                            $category_id=$row53['category_id'];
                                            $sub_category_id =$row53['sub_category_id'];
                                            $product_name =$row53['product_name'];
                                            $product_unit =$row53['product_unit'];
                                            $short_description =$row53['short_description'];
                                            $long_description =$row53['long_description'];
                                        	$product_minimum_order =$row53['product_minimum_order'];
                                            $register_date =$row53['register_date'];

                                            $product_quantity = 0;
                                            $sql="SELECT * from product_stock where product_id='$product_id'";
                                            $result=$conn->query($sql);
                                            while ($row = $result->fetch_assoc()) {
                                              $product_quantity=$row['stock_quantity'];
                                          }

                                          $sql="SELECT * from product_price where product_id='$product_id'";
                                          $result=$conn->query($sql);
                                          while ($row = $result->fetch_assoc()) {
                                              $product_price=$row['price'];
                                          }

                                          $sql="SELECT * from product_category where category_id='$category_id'";
                                          $result=$conn->query($sql);
                                          while ($row = $result->fetch_assoc()) {
                                              $category_name=$row['category_name'];
                                          }

                                          $customer_id_1 = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];

                                          $id1="Mine".$a;
                                          $id2="Mine1".$a;
                                          $id3="Mine2".$a;
                                          $id4="Mine3".$a;

                                          ?>

                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 u-s-m-b-30 filter__item <?php echo $category_id; ?>">
                                            <div class="product-o product-o--hover-on product-o--radius">
                                                <div class="product-o__wrap">
                                                    <?php
                                                    $sql="SELECT * from product_picture where product_id='$product_id' order by register_date DESC limit 1";
                                                    $result=$conn->query($sql);
                                                    while ($row = $result->fetch_assoc()) {
                                                        $picture=$row['picture'];

                                                        ?>
                                                        <a class="aspect aspect--bg-grey aspect--square u-d-block" href="index.php?product-detail&product=<?php echo $product_id; ?>">

                                                            <img class="aspect__img" src="uploads/<?php echo $picture; ?>" alt="" style='object-fit: cover;'></a>

                                                        <?php } ?>

                                                    <div class="product-o__action-wrap">
                                                        <ul class="product-o__action-list">
                                                            <li>

                                                                <a href="#quick-look" data-toggle="modal" data-modal="modal" data-product-id="<?php echo $product_id; ?>" data-tooltip="tooltip" data-placement="top" title="Quick View"><i class="fas fa-search-plus"></i></a></li>
                                                            <li>
                                                                <?php if($product_quantity > 0): ?>
                                                                <a onclick="add_to_cart('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>','<?php echo $product_price; ?>','<?php echo $product_minimum_order; ?>')" data-tooltip="tooltip" data-placement="top" title="Add to Cart [<?php echo $product_minimum_order; ?> <?php echo $product_unit; ?>] "><i class="fas fa-plus-circle"></i></a>
                                                                <?php else: ?>
                                                                <a style="opacity:.4;cursor:not-allowed;pointer-events:none;" data-tooltip="tooltip" data-placement="top" title="Out of Stock"><i class="fas fa-ban"></i></a>
                                                                <?php endif; ?>
                                                            </li>
                                                        
                                                        	<?php if($login_status){ ?>
                                
                                							<li>
                                                                <a href="#/" onclick="add_to_wishlist('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>')" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"><i class="fas fa-heart"></i></a>
                                                        	</li>
                                
                                							<?php }else{ ?>
                                                        
                                							<li>
                                                                <a href="index.php?sign-in" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"><i class="fas fa-heart"></i></a>
                                                        	</li>
                                                        
                                							<?php } ?>
                                                                                                                
                                                        </ul>
                                                    </div>
                                                </div>

                                                <span class="product-o__category">

                                                    <a href="index.php?shop-search&search=<?php echo $category_name; ?>"><?php echo $category_name; ?></a></span>

                                                <span class="product-o__name">

                                                    <a href="index.php?product-detail&product=<?php echo $product_id; ?>"><?php echo $product_name; ?></a></span>
                                                <div class="product-o__rating gl-rating-style"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>

                                                    <span class="product-o__review">(23)</span></div>

                                                <span class="product-o__price"><?php echo number_format( $product_price, 0 ); ?> RWF / <?php echo $product_unit; ?>

                                                    <!--<span class="product-o__discount">$160.00</span>--></span>
                                            </div>
                                        </div>

                                        <?php $a++; } $b++; end2: } ?>

                                        <div id="result_response_cart" style="display: none;"></div>

                                    </div>
                                </div>
                                
                                <a href="index.php?shop">
                                <div class="load-more" style="padding:0;">
                                    <button class="btn btn--e-brand" type="button">Load More</button>
                                </div>
                                </a>

                            </div>




                            </div>
                        </div>
                    </div>
                </div>
                <!--====== End - Section Content ======-->
            </div>
            <!--====== End - Section 2 ======-->

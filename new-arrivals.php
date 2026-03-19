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

                                        <a href="index.php?new-arrivals">New arrivals (THIS MONTH)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>-->
            <!--====== End - Section 1 ======-->

            <!--====== Section 1 ======-->
            <div class="u-s-p-y-90" style='margin-top:-6%;'>
                <div class="container">
                    <div class="row">
                        
                        <div class="col-lg-12 col-md-12" style='background:white;'>
                            <div class="shop-w-master">
                            
                                <h1 class="shop-w-master__heading u-s-m-b-30" data-target="#all-s-filter" data-toggle="collapse" style='cursor:pointer;'>
                                	<i class="fas fa-filter u-s-m-r-8"></i>
                                    <span>TOOGLE FILTERS</span>
                            	</h1>
                            
                                <div class="shop-w-master__sidebar collapse" id="all-s-filter">
                                
                                    <div class="u-s-m-b-30">
                                        <div class="shop-w shop-w--style">
                                            <div class="shop-w__intro-wrap" data-target="#s-category" data-toggle="collapse">
                                                <h1 class="shop-w__h">CATEGORY</h1>
                                                <span class="fas fa-minus shop-w__toggle" data-target="#s-category" data-toggle="collapse"></span>
                                            </div>

                                            <div class="shop-w__wrap collapse" id="s-category">
                                                <ul class="shop-w__category-list gl-scroll" id="ulcols">


                                                    <?php
                                                    $sql="SELECT * from product_category";
                                                    $result=$conn->query($sql);
                                                    while ($row = $result->fetch_assoc()) {
                                                        $category_id=$row['category_id'];
                                                        $category_name=$row['category_name'];

                                                        $sql2="SELECT count(*) as c_p_1 from product where category_id='$category_id'";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }

                                                        ?>


                                                    <li class="has-list">

                                                        <a href="index.php?shop-search&search=<?php echo $category_name; ?>"><?php echo $category_name; ?></a>

                                                        <span class="category-list__text u-s-m-l-6">(<?php echo $product_count_1; ?>)</span>

                                                        <span class="js-shop-category-span is-expanded fas fa-plus u-s-m-l-6"></span>
                                                        <ul style="display:none">
                                                        

                                                            <?php
                                                            $sql1="SELECT * from product_sub_category where category_id='$category_id'";
                                                            $result1=$conn->query($sql1);
                                                            while ($row1 = $result1->fetch_assoc()) {
                                                                $sub_category_id=$row1['sub_category_id'];
                                                                $sub_category_name=$row1['sub_category_name'];

                                                                ?>

                                                            <li class="has-list">

                                                                <a href="index.php?shop-search&search=<?php echo $sub_category_name; ?>"><?php echo $sub_category_name; ?></a>

                                                            </li>

                                                        <?php } ?>


                                                        </ul>
                                                    </li>

                                                <?php } ?>

                                                </ul>
                                            </div>

                                        </div>
                                    </div>
                                
                                    <div class="u-s-m-b-30">
                                        <div class="shop-w shop-w--style">
                                            <div class="shop-w__intro-wrap" data-target="#s-rating" data-toggle="collapse">
                                                <h1 class="shop-w__h">RATING</h1>

                                                <span class="fas fa-minus shop-w__toggle" data-target="#s-rating" data-toggle="collapse"></span>
                                            </div>
                                            <div class="shop-w__wrap collapse" id="s-rating">
                                                <ul class="shop-w__list gl-scroll" id="ulcols">
                                                    <li onclick="sort_products_condition('and CAST(p.product_rating AS SIGNED INTEGER) >= 5')">
                                                        <div class="rating__check">

                                                            <input type="checkbox">
                                                            <div class="rating__check-star-wrap"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                                        </div>
                                                    
                                                    	<?php
                                                    		
                                                    	$sql2="SELECT count(*) as c_p_1 from product where CAST(product_rating AS SIGNED INTEGER) >= 5";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }
                                                    
                                                    	?>

                                                        <span class="shop-w__total-text">(<?php echo $product_count_1; ?>)</span>
                                                    </li>
                                                    <li onclick="sort_products_condition('and CAST(p.product_rating AS SIGNED INTEGER) >= 4')">
                                                        <div class="rating__check">

                                                            <input type="checkbox">
                                                            <div class="rating__check-star-wrap"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>

                                                                <span>& Up</span></div>
                                                        </div>
                                                    
                                                    	<?php
                                                    		
                                                    	$sql2="SELECT count(*) as c_p_1 from product where CAST(product_rating AS SIGNED INTEGER) >= 4";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }
                                                    
                                                    	?>

                                                        <span class="shop-w__total-text">(<?php echo $product_count_1; ?>)</span>
                                                    </li>
                                                    <li onclick="sort_products_condition('and CAST(p.product_rating AS SIGNED INTEGER) >= 3')">
                                                        <div class="rating__check">

                                                            <input type="checkbox">
                                                            <div class="rating__check-star-wrap"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>

                                                                <span>& Up</span></div>
                                                        </div>
                                                    
                                                    	<?php
                                                    		
                                                    	$sql2="SELECT count(*) as c_p_1 from product where CAST(product_rating AS SIGNED INTEGER) >= 3";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }
                                                    
                                                    	?>

                                                        <span class="shop-w__total-text">(<?php echo $product_count_1; ?>)</span>
                                                    </li>
                                                    <li onclick="sort_products_condition('and CAST(p.product_rating AS SIGNED INTEGER) >= 2')">
                                                        <div class="rating__check">

                                                            <input type="checkbox">
                                                            <div class="rating__check-star-wrap"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>

                                                                <span>& Up</span></div>
                                                        </div>
                                                    
                                                    	<?php
                                                    		
                                                    	$sql2="SELECT count(*) as c_p_1 from product where CAST(product_rating AS SIGNED INTEGER) >= 2";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }
                                                    
                                                    	?>

                                                        <span class="shop-w__total-text">(<?php echo $product_count_1; ?>)</span>
                                                    </li>
                                                    <li onclick="sort_products_condition('and CAST(p.product_rating AS SIGNED INTEGER) >= 1')">
                                                        <div class="rating__check">

                                                            <input type="checkbox">
                                                            <div class="rating__check-star-wrap"><i class="fas fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>

                                                                <span>& Up</span></div>
                                                        </div>
                                                    
                                                    	<?php
                                                    		
                                                    	$sql2="SELECT count(*) as c_p_1 from product where CAST(product_rating AS SIGNED INTEGER) >= 1";
                                                        $result2=$conn->query($sql2);
                                                        while ($row2 = $result2->fetch_assoc()) {
                                                            $product_count_1=$row2['c_p_1'];
                                                        }
                                                    
                                                    	?>

                                                        <span class="shop-w__total-text">(<?php echo $product_count_1; ?>)</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="u-s-m-b-30">
                                        <div class="shop-w shop-w--style">
                                            <div class="shop-w__intro-wrap" data-target="#s-price" data-toggle="collapse">
                                                <h1 class="shop-w__h">PRICE</h1>

                                                <span class="fas fa-minus shop-w__toggle" data-target="#s-price" data-toggle="collapse"></span>
                                            </div>
                                            <div class="shop-w__wrap collapse" id="s-price">
                                                <form class="shop-w__form-p" method="post" action="#/" onsubmit="sort_products_condition_price();return false;">
                                                    <div class="shop-w__form-p-wrap">
                                                        <div>

                                                            <label for="price-min"></label>

                                                            <input class="input-text input-text--primary-style" type="text" id="price-min" placeholder="Min"></div>
                                                        <div>

                                                            <label for="price-max"></label>

                                                            <input class="input-text input-text--primary-style" type="text" id="price-max" placeholder="Max"></div>
                                                        <div>

                                                            <button class="btn btn--icon fas fa-angle-right btn--e-transparent-platinum-b-2" type="submit"></button></div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                
                	<div class="row">
                        <!--====== Category Side Bar ======-->
                        <?php include "includes/category-side-bar.php"; ?>
                        <div class="col-lg-9 col-md-9">
                            <div class="shop-p">
                                <div class="shop-p__toolbar u-s-m-b-30">

                                    <!--<div class="shop-p__meta-wrap u-s-m-b-60">

                                        <span class="shop-p__meta-text-1">FOUND 18 RESULTS</span>
                                        <div class="shop-p__meta-text-2">

                                            <span>Related Searches:</span>

                                            <a class="gl-tag btn--e-brand-shadow" href="#">men's clothing</a>

                                            <a class="gl-tag btn--e-brand-shadow" href="#">mobiles & tablets</a>

                                            <a class="gl-tag btn--e-brand-shadow" href="#">books & audible</a></div>
                                    </div>-->
                                	<?php
                                
                                		if (isset($_GET['pageno'])) {
                                            $pageno = $_GET['pageno'];
                                        } else {
                                            $pageno = 1;
                                        }
                                    	if (isset($_GET['sortby'])) {
                                        	$sortby = $_GET['sortby'];
                                        } else {
                                        	$sortby = 'p.register_date DESC';
                                        }
                                		if (isset($_GET['condition'])) {
                                        	$condition = $_GET['condition'];
                                        } else {
                                        	$condition = '';
                                        }
                                
                                	?>

                                    <div class="shop-p__tool-style">
                                        <div class="tool-style__group u-s-m-b-8">

                                            <span class="js-shop-grid-target is-active">Grid</span>

                                            <span class="js-shop-list-target">List</span></div>
                                        <form>
                                            <div class="tool-style__form-wrap">
                                                <!--<div class="u-s-m-b-8"><select class="select-box select-box--transparent-b-2">
                                                        <option>Show: 8</option>
                                                        <option selected>Show: 12</option>
                                                        <option>Show: 16</option>
                                                        <option>Show: 28</option>
                                                    </select></div>-->
                                                <div class="u-s-m-b-8">
                                                	<select class="select-box select-box--transparent-b-2" id='select_sort' onchange='sort_products()'>
                                                    
                                                    	<?php if($sortby === 'p.register_date DESC'){ ?>
                                                    		<option selected value="p.register_date DESC">Sort By: Newest Items</option>
                                                    	<?php }else{ ?>
                                                    		<option value="p.register_date DESC">Sort By: Newest Items</option>
                                                    	<?php } ?>
                                                    
                                                    	<?php if($sortby === 'p.register_date ASC'){ ?>
                                                    		<option selected value="p.register_date ASC">Sort By: Latest Items</option>
                                                    	<?php }else{ ?>
                                                    		<option value="p.register_date ASC">Sort By: Latest Items</option>
                                                    	<?php } ?>
                                                    
                                                    	<?php if($sortby === 'CAST(p.product_rating AS SIGNED INTEGER) DESC'){ ?>
                                                    		<option selected value="CAST(p.product_rating AS SIGNED INTEGER) DESC">Sort By: Best Rating</option>
                                                    	<?php }else{ ?>
                                                    		<option value="CAST(p.product_rating AS SIGNED INTEGER) DESC">Sort By: Best Rating</option>
                                                    	<?php } ?>
                                                    
                                                    	<?php if($sortby === 'CAST(pr.price AS SIGNED INTEGER) ASC'){ ?>
                                                    		<option selected value="CAST(pr.price AS SIGNED INTEGER) ASC">Sort By: Lowest Price</option>
                                                    	<?php }else{ ?>
                                                    		<option value="CAST(pr.price AS SIGNED INTEGER) ASC">Sort By: Lowest Price</option>
                                                    	<?php } ?>
                                                    
                                                    	<?php if($sortby === 'CAST(pr.price AS SIGNED INTEGER) DESC'){ ?>
                                                    		<option selected value="CAST(pr.price AS SIGNED INTEGER) DESC">Sort By: Highest Price</option>
                                                    	<?php }else{ ?>
                                                    		<option value="CAST(pr.price AS SIGNED INTEGER) DESC">Sort By: Highest Price</option>
                                                    	<?php } ?>
                                                        
                                                        <!--<option>Sort By: Best Selling</option>-->
                                                        
                                                    </select>
                                            	</div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="shop-p__collection">
                                    <div class="row is-grid-active">

                                        <?php

                                        $no_of_records_per_page = 16;
                                        $offset = ($pageno-1) * $no_of_records_per_page;

                                        //$total_pages_sql = "SELECT COUNT(p.product_id) FROM product p, product_price pr where pr.product_id=p.product_id and (month(p.register_date)=month(now()) or MONTH(p.register_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) $condition order by $sortby";
                                    	$total_pages_sql = "SELECT COUNT(p.product_id) FROM product p, product_price pr where pr.product_id=p.product_id $condition order by $sortby";
                                        $result = mysqli_query($conn,$total_pages_sql);
                                        //$total_rows = mysqli_fetch_array($result)[0];
                                    	$total_rows = 64;
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);

                                        //$sql53="SELECT p.product_id,p.category_id,p.sub_category_id,p.product_name,p.product_unit,p.short_description,p.long_description,p.register_date from product p, product_price pr where pr.product_id=p.product_id and (month(p.register_date)=month(now()) or MONTH(p.register_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) $condition order by $sortby limit $offset, $no_of_records_per_page";
                                    	$sql53="SELECT p.product_id,p.category_id,p.sub_category_id,p.product_name,p.product_unit,p.short_description,p.long_description,p.register_date from product p, product_price pr where pr.product_id=p.product_id $condition order by $sortby limit $offset, $no_of_records_per_page";
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
                                            $register_date =$row53['register_date'];

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
                                        
                                          $sql="SELECT * from product_sub_category where sub_category_id='$sub_category_id'";
                                          $result=$conn->query($sql);
                                          while ($row = $result->fetch_assoc()) {
                                              $sub_category_name=$row['sub_category_name'];
                                          }

                                          $customer_id_1 = $_SESSION['GBDELIVERING_TEMP_CUSTOMER_USER_2021'];

                                          $id1="Mine".$a;
                                          $id2="Mine1".$a;
                                          $id3="Mine2".$a;
                                          $id4="Mine3".$a;

                                          ?>

                                        <div class="col-lg-4 col-md-6 col-sm-6">
                                            <div class="product-m">
                                                <div class="product-m__thumb">

                                                    <?php
                                                    $sql="SELECT * from product_picture where product_id='$product_id' order by register_date DESC limit 1";
                                                    $result=$conn->query($sql);
                                                    while ($row = $result->fetch_assoc()) {
                                                        $picture=$row['picture'];

                                                        ?>
                                                        <a class="aspect aspect--bg-grey aspect--square u-d-block" href="index.php?product-detail&product=<?php echo $product_id; ?>">
                                                            <img class="aspect__img" src="uploads/<?php echo $picture; ?>" alt="" style='object-fit: cover;'>
                                                        </a>
                                                        <?php } ?>

                                                    <div class="product-m__quick-look">
                                                        <a href="#quick-look" data-toggle="modal" data-modal="modal" data-product-id="<?php echo $product_id; ?>" class="fas fa-search" data-tooltip="tooltip" data-placement="top" title="Quick Look"></a>
                                                    </div>

                                                    <div class="product-m__add-cart">
                                                        <a onclick="add_to_cart('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>','<?php echo $product_price; ?>','1')" class="btn--e-brand" >Add to Cart</a>
                                                    </div>

                                                </div>
                                                <div class="product-m__content">
                                                    <div class="product-m__category">

                                                        <a href="index.php?shop-search&search=<?php echo $sub_category_name; ?>"><?php echo $sub_category_name ?></a></div>
                                                    <div class="product-m__name">

                                                        <a href="index.php?product-detail&product=<?php echo $product_id; ?>"><?php echo $product_name ?></a></div>
                                                    <div class="product-m__rating gl-rating-style"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i><i class="far fa-star"></i>

                                                        <span class="product-m__review">(23)</span></div>
                                                    <div class="product-m__price"><?php echo number_format( $product_price, 0 ); ?> RWF / <?php echo $product_unit; ?></div>
                                                    <div class="product-m__hover">
                                                        <div class="product-m__preview-description">

                                                            <span><?php echo $short_description; ?></span></div>
                                                        <div class="product-m__wishlist">
                                                        
                                                        	<?php if($login_status){ ?>
                                                        
                                                        	<a class="far fa-heart" href="#/" onclick="add_to_wishlist('<?php echo $product_id; ?>','<?php echo $customer_id_1; ?>')" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"></a>
                                
                                							<?php }else{ ?>
                                                        
                                                                <a class="far fa-heart" href="index.php?sign-in" data-tooltip="tooltip" data-placement="top" title="Add to Wishlist"></a>
                                                        
                                							<?php } ?>
                                                        
                                                            
                                                    	</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <?php $a++; } ?>
                                        <div id="result_response_cart" style="display: none;"></div>
                                    
                                        
                                    </div>
                                </div>
                                <div class="u-s-p-y-60">

                                    <!--====== Pagination ======-->
                                    <ul class="shop-p__pagination">

                                        <li class="is-active">
                                            SHOWING PAGE <?php echo $pageno; ?> OF <?php echo $total_pages; ?>
                                        </li>
                                        <li class="is-active">
                                            -
                                        </li>                                        
                                    
                                    	<li><a href="index.php?new-arrivals&pageno=1&sortby=<?php echo $sortby; ?>&condition=<?php echo $condition; ?>"><span class="fas fa-angle-left"></span><span class="fas fa-angle-left"></span> First</a></li>
                                        <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "index.php?new-arrivals&sortby=".$sortby."&condition=".$condition."&pageno=".($pageno - 1); } ?>"><span class="fas fa-angle-left"></span> Prev</a>
                                        </li>
                                        <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                            <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "index.php?new-arrivals&sortby=".$sortby."&condition=".$condition."&pageno=".($pageno + 1); } ?>">Next <span class="fas fa-angle-right"></span></a>
                                        </li>
                                        <li><a href="index.php?new-arrivals&pageno=<?php echo $total_pages; ?>&sortby=<?php echo $sortby; ?>&condition=<?php echo $condition; ?>">Last <span class="fas fa-angle-right"></span><span class="fas fa-angle-right"></span></a></li>

                                        <!--
                                        <li class="is-active">
                                            <a href="shop-side-version-2.html">1</a>
                                        </li>
                                        <li>
                                            <a href="shop-side-version-2.html">2</a>
                                        </li>
                                        <li>
                                            <a href="shop-side-version-2.html">3</a>
                                        </li>
                                        <li>
                                            <a href="shop-side-version-2.html">4</a>
                                        </li>
                                        <li>
                                            <a class="fas fa-angle-right" href="shop-side-version-2.html"></a>
                                        </li>-->

                                    </ul>
                                    <!--====== End - Pagination ======-->
                                </div>
                            
<center>         
<!-- gbdel1 -->
<ins class="adsbygoogle"
     style="display:block;text-align:center;"
     data-ad-client="ca-pub-5745320266901948"
     data-ad-slot="6630621214"
     data-ad-format="auto"
     data-full-width-responsive="true">
</ins>
</center>
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--====== End - Section 1 ======-->

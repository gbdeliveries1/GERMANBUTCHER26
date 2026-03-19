            <!--====== Section 1 ======-->
            <div class="u-s-p-y-60">

                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="container">
                        <div class="breadcrumb">
                            <div class="breadcrumb__wrap">
                                <ul class="breadcrumb__list">
                                    <li class="has-separator">

                                        <a href="index.php">Home</a></li>
                                    <li class="is-marked">

                                        <a href="index.php?dashboard">My Addresses</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--====== End - Section 1 ======-->

            <!--====== Section 2 ======-->
            <div class="u-s-p-b-60">

                <!--====== Section Content ======-->
                <div class="section__content">
                    <div class="dash">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">

                                    <!--====== Dashboard Features ======-->
                                    <div class="dash__box dash__box--bg-white dash__box--shadow u-s-m-b-30">
                                        <div class="dash__pad-1">

                                            <span class="dash__text u-s-m-b-16">Hello, <?php echo $user_first_name." ".$user_last_name ?></span>
                                            <ul class="dash__f-list">
                                                <li>

                                                    <a href="index.php?dashboard">Manage My Account</a></li>
                                            	<li>

                                                    <a class="dash-active" href="index.php?manage_address">Manage My Addresses</a></li>
                                                <li>

                                                    <a href="index.php?my-profile">My Profile</a></li>
                                                <!--<li>

                                                    <a href="index.php?address-book">Address Book</a></li>-->
                                                <li>

                                                    <a href="index.php?track-my-order">Track Order</a></li>
                                                <li>

                                                    <a href="index.php?my-orders">My Orders</a></li>
                                                
                                                <!--<li>

                                                    <a href="index.php?cancellation">My Returns & Cancellations</a></li>-->
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="dash__box dash__box--bg-white dash__box--shadow dash__box--w">
                                        <div class="dash__pad-1">
                                            <ul class="dash__w-list">
                                                <a href="index.php?my-orders">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-2"><i class="fas fa-cart-arrow-down"></i></span>

                                                    <?php
                                                    
		$sql="SELECT count(o.order_id) as c1 from orders o, cart c where o.cart_id=c.cart_id and c.customer_id='$user_id' and o.status not like 'Pending_payment'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$orders_count=$row['c1'];
		}
                                            
        $sql="SELECT count(o.order_id) as c1 from orders o, cart c where o.cart_id=c.cart_id and c.customer_id='$user_id' and o.status='Canceled'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$c_orders_count=$row['c1'];
		}
                                            
        $sql="SELECT count(wi.item_id) as c1 from wishlist_item wi, wishlist w where wi.wishlist_id=w.wishlist_id and w.customer_id='$user_id'";
		$result=$conn->query($sql);
		while ($row = $result->fetch_assoc()) {
			$wishlist_count=$row['c1'];
		}
                                                    
                                                    ?>
                                                    
                                                        <span class="dash__w-text"><?php echo $orders_count ?></span>

                                                        <span class="dash__w-name">Orders Placed</span></div>
                                                </li>
                                                </a>
                                                <a href="index.php?my-orders">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-1"><i class="fas fa-times"></i></span>

                                                        <span class="dash__w-text"><?php echo $c_orders_count ?></span>

                                                        <span class="dash__w-name">Cancel Orders</span></div>
                                                </li>
                                                </a>
                                                <a href="index.php?wishlist">
                                                <li>
                                                    <div class="dash__w-wrap">

                                                        <span class="dash__w-icon dash__w-icon-style-3"><i class="far fa-heart"></i></span>

                                                        <span class="dash__w-text"><?php echo $wishlist_count ?></span>

                                                        <span class="dash__w-name">Wishlist</span></div>
                                                </li>
                                                </a>
                                            </ul>
                                        </div>
                                    </div>
                                    <!--====== End - Dashboard Features ======-->
                                </div>
                                <div class="col-lg-9 col-md-12">
                                    <div class="dash__box dash__box--shadow dash__box--radius dash__box--bg-white u-s-m-b-30">
                                        <div class="dash__pad-2">
                                            <h1 class="dash__h1 u-s-m-b-14">My Addresses</h1>

                                            <span class="dash__text u-s-m-b-30">Manage your primary and secondary addresses.</span>
                                            <!--<form class="m-order u-s-m-b-30">
                                                <div class="m-order__select-wrapper">

                                                    <label class="u-s-m-r-8" for="my-order-sort">Show:</label><select class="select-box select-box--primary-style" id="my-order-sort">
                                                        <option selected>Last 5 orders</option>
                                                        <option>Last 15 days</option>
                                                        <option>Last 30 days</option>
                                                        <option>Last 6 months</option>
                                                        <option>Orders placed in 2018</option>
                                                        <option>All Orders</option>
                                                    </select></div>
                                            </form>-->
                                        	<div>
                                            	<a class="dash__custom-link btn--e-brand-b-2" href="index.php?new_address">New Address</a>
                                        	</div><br>
                                            <div class="m-order__list">
                                            
<?php
       
$sql53="SELECT * from address where user_id='$user_id' ORDER BY address_type ASC";
                      $result53=$conn->query($sql53);
                      $a = 1;
                      while ($row53 = $result53->fetch_assoc()) {
                        $address_id =$row53['address_id'];
                        $address_type =$row53['address_type'];
                        $country=$row53['country'];
                        $province =$row53['province'];
                        $district =$row53['district'];
                        $sector =$row53['sector'];
                        $cell =$row53['cell'];
                      $village =$row53['village'];
                      $street =$row53['street'];
                      $described_address =$row53['described_address'];
                      
	$sql1="DELETE from address where address_id not like '$address_id' and user_id='$user_id' and address_type='$address_type' and country='$country' and province='$province' and district='$district' and sector='$sector' and cell='$cell' and village='$village' and street='$street' and described_address='$described_address'";
	if ($conn->query($sql1)===TRUE) {}else{}

                        $id1="Mine".$a;
                        $id2="Mine1".$a;
                        $id3="Mine2".$a;
                        $id4="Mine3".$a;
                        $id5="Mine4".$a;

                        ?>                                            
                                            
                                                <div class="m-order__get">

                                                	<div class="manage-o__header">
                                                        <div class="dash-l-r">
                                                            <div>
                          <?php if ($address_type === 'SECONDARY') { ?>
                            <span class="manage-o__badge badge--processing"><?php echo $address_type; ?></span>
                          <?php } ?>
                          <?php if ($address_type === 'PRIMARY') { ?>
                            <span class="manage-o__badge badge--shipped"><?php echo $address_type; ?></span>
                          <?php } ?> 
                                                            </div>
                                                            <div>
                                                            	<?php if ($address_type === 'SECONDARY') { ?>
                                                            	<div class="dash__link dash__link--brand">

                                                                    <a href="#/" onclick="make_primary('<?php echo $user_id ?>','<?php echo $address_id ?>');">MAKE PRIMARY</a></div>
                          										<?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="manage-o__header">
                                                        <div class="dash-l-r">
                                                            <div>
                                                                <div class="manage-o__text-2 u-c-secondary"><?php echo $province ?> - <?php echo $district ?> - <?php echo $sector ?> - <?php echo $cell ?> - <?php echo $village ?></div>
                                                                <div class="manage-o__text u-c-silver"><?php echo $street ?> - <?php echo $described_address ?></div>
                                                            </div>
                                                            <div>
                                                                
                                                            	<div class="dash__link dash__link--brand">

                                                                    <a href="#/" onclick="delete_address('<?php echo $address_id ?>');">DELETE ADDRESS</a></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                            <?php } ?>
                                            <input type="text" id="result_response" style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--====== End - Section Content ======-->
            </div>
            <!--====== End - Section 2 ======-->
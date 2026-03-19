        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-12 grid-margin">
              <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end flex-wrap">
                  <div class="mr-md-3 mr-xl-5">
                    <h2>Last 30 Orders placed</h2>
                    <p class="mb-md-0">All on your products.</p>
                  </div>
                  <div class="d-flex">
                    <i class="mdi mdi-home text-muted hover-cursor"></i>
                    <p class="text-muted mb-0 hover-cursor">&nbsp;/&nbsp;Dashboard&nbsp;/&nbsp;</p>
                    <p class="text-primary mb-0 hover-cursor">Last 30 Placed orders</p>
                  </div>
                </div>
                <!--
                <div class="d-flex justify-content-between align-items-end flex-wrap">
                  <button type="button" class="btn btn-light bg-white btn-icon mr-3 d-none d-md-block ">
                    <i class="mdi mdi-download text-muted"></i>
                  </button>
                  <button type="button" class="btn btn-light bg-white btn-icon mr-3 mt-2 mt-xl-0">
                    <i class="mdi mdi-clock-outline text-muted"></i>
                  </button>
                  <button type="button" class="btn btn-light bg-white btn-icon mr-3 mt-2 mt-xl-0">
                    <i class="mdi mdi-plus text-muted"></i>
                  </button>
                  <button class="btn btn-primary mt-2 mt-xl-0">Download report</button>
                </div>
              -->
            </div>
          </div>
        </div>
        
          <!--
          <div class="row">
            <div class="col-md-7 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Cash deposits</p>
                  <p class="mb-4">To start a blog, think of a topic about and first brainstorm party is ways to write details</p>
                  <div id="cash-deposits-chart-legend" class="d-flex justify-content-center pt-3"></div>
                  <canvas id="cash-deposits-chart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-md-5 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Total sales</p>
                  <h1>$ 28835</h1>
                  <h4>Gross sales over the years</h4>
                  <p class="text-muted">Today, many people rely on computers to do homework, work, and create or store useful information. Therefore, it is important </p>
                  <div id="total-sales-chart-legend"></div>                  
                </div>
                <canvas id="total-sales-chart"></canvas>
              </div>
            </div>
          </div>
        -->
        <div class="row">
          <div class="col-md-12 stretch-card">
            <div class="card">
              <div class="card-body">
                <p class="card-title">Last 30 Placed Orders</p>
                <div class="table-responsive">
                  <table id="recent-purchases-listing" class="table table_1">
                    <thead>
                      <tr>
                        <th style="display: none;"></th>
                        <th>Order date</th>
                      	<th>Estimated delivery date</th>
                      	<th>Order Number</th>
                        <th>Products & Quantity</th>
                        <th>Client First Name</th>
                      	<th>Client Last Name</th>
                        <th>Client Email</th>
                      	<th>Client Phone No</th>
                        <th>Shipping amount</th>
                      	<th>Sub total amount</th>
                      	<th>Total amount</th>
                        <th>Order description</th>
                        <th>Shipping address</th>
                        <th>ORDER STATUS</th>
                        <!--<th>ACTION</th>-->
                      </tr>
                    </thead>
                    <tbody>

                      <?php

                      //$sql53="SELECT o.order_id,o.first_name,o.last_name,o.email,o.phone_no,o.order_number,o.cart_id,o.address_id,o.shipping_amount,o.sub_total_amount,o.total_amount,o.order_description,o.order_date,o.estimated_delivery_date,o.shipped_date,o.delivered_date,o.status,a.user_id,a.address_type,a.country,a.province,a.district,a.sector,a.cell,a.village,a.street,a.described_address,i.item_id,i.cart_id,i.product_id,i.product_quantity,i.price,i.register_date,p.product_id,p.category_id,p.sub_category_id,p.seller_id,p.product_name,p.product_unit,p.short_description,p.long_description,p.product_rating from orders o, address a, cart_item i, product p where o.address_id=a.address_id and o.cart_id=i.cart_id and i.product_id=p.product_id and p.seller_id='$user_id' and o.status not like 'Pending_payment' and o.status not like 'Canceled' and i.status like 'ACTIVE' ORDER BY o.order_date DESC";
                    $sql53="SELECT o.order_id,o.first_name,o.last_name,o.email,o.phone_no,o.order_number,o.cart_id,o.address_id,o.shipping_amount,o.sub_total_amount,o.total_amount,o.order_description,o.order_date,o.estimated_delivery_date,o.shipped_date,o.delivered_date,o.status,a.user_id,a.address_type,a.country,a.province,a.district,a.sector,a.cell,a.village,a.street,a.described_address from orders o, address a, cart_item i, product p where o.address_id=a.address_id and o.cart_id=i.cart_id and o.order_number=i.order_number and i.product_id=p.product_id and i.status like 'CHECKED_OUT' GROUP BY o.order_id ORDER BY o.order_date DESC limit 30";
                      $result53=$conn->query($sql53);
                      $a = 1;
                      while ($row53 = $result53->fetch_assoc()) {
                        $order_id =$row53['order_id'];
                        $first_name =$row53['first_name'];
                        $last_name=$row53['last_name'];
                        $email =$row53['email'];
                        $phone_no =$row53['phone_no'];
                        $order_number =$row53['order_number'];
                        $cart_id =$row53['cart_id'];
                      $address_id =$row53['address_id'];
                      $shipping_amount =$row53['shipping_amount'];
                      $sub_total_amount =$row53['sub_total_amount'];
                      $total_amount =$row53['total_amount'];
                      $order_description =$row53['order_description'];
                      $order_date =$row53['order_date'];
                      $estimated_delivery_date =$row53['estimated_delivery_date'];
                      $shipped_date =$row53['shipped_date'];
                      $delivered_date =$row53['delivered_date'];
                      $status =$row53['status'];
                      $address_user_id =$row53['user_id'];
                      $address_type =$row53['address_type'];
                      $country =$row53['country'];
                      $province =$row53['province'];
                      $district =$row53['district'];
                      $sector =$row53['sector'];
                      $cell =$row53['cell'];
                      $village =$row53['village'];
                      $street =$row53['street'];
                      $described_address =$row53['described_address'];
                      /*
                      $item_id =$row53['item_id'];
                      $product_id =$row53['product_id'];
                      $product_quantity =$row53['product_quantity'];
                      $price =$row53['price'];
                      $register_date =$row53['register_date'];
                      $category_id =$row53['category_id'];
                      $sub_category_id =$row53['sub_category_id'];
                      $seller_id =$row53['seller_id'];
                      $product_name =$row53['product_name'];
                      $product_unit =$row53['product_unit'];
                      $short_description =$row53['short_description'];
                      $long_description =$row53['long_description'];
                      $product_rating =$row53['product_rating'];
                      */

                      	/*
                        $sql="SELECT * from user_type where type_id='$type_id'";
                        $result=$conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                          $type_name=$row['type_name'];
                        }
                        */

                        $id1="Mine".$a;
                        $id2="Mine1".$a;
                        $id3="Mine2".$a;
                        $id4="Mine3".$a;
                        $id5="Mine4".$a;

                        ?>   

                        <tr id='<?php echo $id3; ?>'>
                          <td id="<?php echo $id1; ?>" style='display: none;'><?php echo $order_id ; ?></td>
                          <td><?php echo $order_date; ?></td>
                          <td><?php echo $estimated_delivery_date; ?></td>
                          <td><?php echo $order_number; ?></td>
                          <td>
                        <?php
                        $sql="SELECT * from cart_item where cart_id='$cart_id' and order_number='$order_number' order by register_date DESC";
                        $result=$conn->query($sql);
                      	$sum_sub_t = 0;
                        while ($row = $result->fetch_assoc()) {
                        	$item_id =$row['item_id'];
                      		$product_id_cart =$row['product_id'];
                      		$product_quantity_cart =$row['product_quantity'];
                      		$price_cart =$row['price'];
                      		$register_date_cart =$row['register_date'];
                        
                        $sql1="SELECT * from product where product_id='$product_id_cart'";
                        $result1=$conn->query($sql1);
                        while ($row1 = $result1->fetch_assoc()) {
                      		$product_name_cart =$row1['product_name'];
                        	$seller_id_cart =$row1['seller_id'];
                        }
                        
                        $sql1="SELECT * from user where user_id='$seller_id_cart'";
                        $result1=$conn->query($sql1);
                        while ($row1 = $result1->fetch_assoc()) {
                      		$first_name_seller_cart =$row1['first_name'];
                        	$last_name_seller_cart =$row1['last_name'];
                        	$phone_no_seller_cart =$row1['phone_no'];
                        }
                        
                        	echo $first_name_seller_cart." ".$last_name_seller_cart." (".$phone_no_seller_cart.") --> ".$product_name_cart." - x".$product_quantity_cart." - ".$price_cart." RWF<br>";
                        
                        $sum_sub_t = $sum_sub_t + $price_cart;
                        }
                        ?>
						</td>
                          <td><?php echo $first_name; ?></td>
                          <td><?php echo $last_name; ?></td>
                        <td><?php echo $email; ?></td>
                        <td><?php echo $phone_no; ?></td>
                        <td><?php echo $shipping_amount; ?></td>
                        <td><?php echo $sub_total_amount; ?></td>
                        <td><?php echo $total_amount; ?></td>
                        <td><?php echo $order_description; ?></td>
                        <td><?php echo $province.", ".$district.", ".$sector.", ".$cell.", ".$village.", ".$street.", ".$described_address; ?></td>
                        
                          <?php if ($status === 'Processing') { ?>
                            <td id="<?php echo $id4; ?>"><span class="btn btn-rounded btn-inverse-primary"><?php echo $status; ?></span></td>
                          <?php } ?>
                          <?php if ($status === 'Shipped') { ?>
                            <td id="<?php echo $id4; ?>"><span class="btn btn-rounded btn-inverse-success"><?php echo $status; ?></span></td>
                          <?php } ?>
                          <?php if ($status === 'Delivered') { ?>
                            <td id="<?php echo $id4; ?>"><span class="btn btn-rounded btn-inverse-warning"><?php echo $status; ?></span></td>
                          <?php } ?>
                          
                          <?php if ($status === 'Canceled') { ?>
                            <td id="<?php echo $id4; ?>"><span class="btn btn-rounded btn-inverse-danger"><?php echo $status; ?></span></td>
                          <?php } ?>
                        
                        <!--

                          <?php if ($status === 'Processing') { ?>
                            <td id="<?php echo $id5; ?>"><button class='btn btn-rounded btn-success' onclick="confirm_order('<?php echo $id1 ?>','<?php echo $id3 ?>','<?php echo $id4 ?>','<?php echo $id5 ?>');">CONFIRM SHIPMENT</button><br><br><button class='btn btn-rounded btn-warning' onclick="cancel_order('<?php echo $id1 ?>','<?php echo $id3 ?>','<?php echo $id4 ?>','<?php echo $id5 ?>');">CANCEL ORDER</button></td>
                          <?php } ?>
                          <?php if ($status === 'Shipped') { ?>
                            <td><span class="btn btn-rounded btn-success"><?php echo $status; ?></span></td>
                          <?php } ?>
                          <?php if ($status === 'Delivered') { ?>
                            <td><span class="btn btn-rounded btn-warning"><?php echo $status; ?></span></td>
                          <?php } ?>
                          
                          <?php if ($status === 'Canceled') { ?>
                            <td><span class="btn btn-rounded btn-warning"><?php echo $status; ?></span></td>
                          <?php } ?>

						-->
                          
                          <!--<td><button class='btn btn-sm btn-warning' onclick="delete_category('<?php echo $id1 ?>','<?php echo $id2 ?>','<?php echo $id3 ?>');">DELETE CATEGORY</button><br><div id='<?php echo $id2 ?>'></div></td>-->
                        </tr>

                        <?php $a++; } ?>

                        <input type="text" id="result_response" style="display: none;">

                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
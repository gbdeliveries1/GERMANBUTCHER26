<style>
    .show_category_section {
        max-height: none !important;
        columns: auto !important;
        margin-left: -74px !important;
    }
    
    @media (max-width: 425px) {
        .category_side_section {
            display: none !important;
        }
        .mega-menu{
            display: block;
        }
        .mega-menu-list,.js-menu-toggle {
            display: none;
        }
    }
</style>

<div class="col-lg-3 col-md-3 category_side_section">
    <div class="shop-w__wrap collapse show" id="s-category">
        <h1 class="shop-w__h" style="margin-left: -35px;">CATEGORY</h1>
        <ul class="shop-w__category-list gl-scroll show_category_section" id="ulcols" style="">


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